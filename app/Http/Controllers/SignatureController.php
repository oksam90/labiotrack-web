<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateBordereauPdf;
use App\Models\Collecte;
use App\Models\Signature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Signature électronique du bordereau de collecte.
 *
 * Workflow : voir doc/LaBioTrack_Signature_Electronique.docx — section 3.
 *  1) GET  /collectes/{id}/signature      — écran de signature canvas
 *  2) POST /collectes/{id}/signature      — capture + preuve numérique
 *  3) GET  /signatures                    — historique
 *  4) GET  /signatures/{id}               — détail de preuve
 *  5) GET  /signatures/{id}/pdf           — téléchargement PDF signé
 *  6) DELETE /signatures/{id}             — révocation (superadmin)
 */
class SignatureController extends Controller
{
    // ── 1. Écran de signature ─────────────────────────────────────

    public function create($collecteId)
    {
        $collecte = Collecte::with(['collecteur', 'declarations'])->findOrFail($collecteId);
        $this->authorize('signatureOpen', $collecte);

        $etablissement = DB::table('etablissements')->find($collecte->etablissement_id);

        return view('collectes.signature', [
            'collecte'      => $collecte,
            'etablissement' => $etablissement,
            'user'          => Auth::user(),
        ]);
    }

    // ── 2. Capture de la signature ────────────────────────────────

    public function store(Request $request, $collecteId)
    {
        $collecte = Collecte::findOrFail($collecteId);
        $this->authorize('signatureSign', $collecte);

        $validated = $request->validate([
            'signature_image'     => 'required|string',
            'signataire_nom'      => 'required|string|max:255',
            'signataire_fonction' => 'nullable|string|max:255',
            'commentaire'         => 'required|string|max:500',
        ]);

        // Décodage data-URL → binaire PNG
        $base64 = preg_replace('#^data:image/png;base64,#', '', $validated['signature_image']);
        $imageData = base64_decode($base64, true);
        if ($imageData === false || strlen($imageData) < 100) {
            throw ValidationException::withMessages([
                'signature_image' => 'Image de signature invalide.',
            ]);
        }

        $hash = hash('sha256', $imageData);
        $path = sprintf(
            'signatures/%d/%s.png',
            $collecte->etablissement_id,
            $hash
        );
        Storage::disk('local')->put($path, $imageData);

        $signature = Signature::create([
            'collecte_id'         => $collecte->id,
            'etablissement_id'    => $collecte->etablissement_id,
            'signataire_user_id'  => Auth::id(),
            'agent_user_id'       => $collecte->collecteur_id,
            'signataire_nom'      => $validated['signataire_nom'],
            'signataire_fonction' => $validated['signataire_fonction'] ?? null,
            'signature_image_path'=> $path,
            'signature_hash'      => $hash,
            'commentaire'         => $validated['commentaire'],
            'ip_address'          => $request->ip(),
            'user_agent'          => substr((string) $request->userAgent(), 0, 1000),
            'device_info'         => $this->extractDeviceInfo($request),
            'signed_at'           => now(),
        ]);

        $collecte->update(['statut' => 'signee']);

        // Génération PDF asynchrone (queue)
        GenerateBordereauPdf::dispatch($signature);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'message'      => 'Signature enregistrée avec succès.',
                'signature_id' => $signature->id,
                'redirect'     => route('signatures.show', $signature->id),
            ], 201);
        }

        return redirect()->route('signatures.show', $signature->id)
            ->with('success', 'Signature enregistrée. Le PDF est en cours de génération.');
    }

    // ── 3. Historique ─────────────────────────────────────────────

    public function index(Request $request)
    {
        $this->authorize('viewAny', Signature::class);

        $user  = Auth::user();
        $query = Signature::with([
                'collecte:id,numero_bordereau,date_collecte,collecteur_id,etablissement_id,statut',
                'collecte.collecteur:id,nom,prenom',
                'etablissement:id,nom',
                'signataire:id,nom,prenom',
            ])
            ->orderByDesc('signed_at');

        // Restriction des collecteurs/agents : leurs collectes uniquement
        if ($user->isCollecteur() || $user->role === 'agent') {
            $query->whereHas('collecte', fn ($q) =>
                $q->where('collecteur_id', $user->id));
        }

        // Filtres
        if ($request->filled('etablissement_id')) {
            $query->where('etablissement_id', (int) $request->etablissement_id);
        }
        if ($request->filled('date_debut')) {
            $query->whereDate('signed_at', '>=', $request->date_debut);
        }
        if ($request->filled('date_fin')) {
            $query->whereDate('signed_at', '<=', $request->date_fin);
        }
        if ($request->filled('statut_pdf')) {
            $statut = $request->statut_pdf;
            if ($statut === 'genere')   $query->whereNotNull('pdf_generated_at');
            if ($statut === 'attente')  $query->whereNull('pdf_generated_at');
        }

        $signatures = $query->paginate(15)->withQueryString();

        // Établissements pour le filtre (selon scope user)
        $etablissementsFiltre = collect();
        if ($user->isGlobal() || $user->isReseauScoped() || $user->isCollecteur() || $user->isPrestataire()) {
            $etablissementsFiltre = DB::table('etablissements')
                ->select('id', 'nom')
                ->orderBy('nom')
                ->get();
        }

        return view('signatures.index', compact('signatures', 'etablissementsFiltre'));
    }

    // ── 4. Détail preuve numérique ────────────────────────────────

    public function show($id)
    {
        $signature = Signature::with([
            'collecte.collecteur',
            'etablissement',
            'signataire',
            'agent',
            'revokedBy',
        ])->findOrFail($id);

        $this->authorize('view', $signature);

        // Image en base64 pour aperçu inline (disque privé)
        $imgBase64 = null;
        if (Storage::disk('local')->exists($signature->signature_image_path)) {
            $imgBase64 = 'data:image/png;base64,' .
                base64_encode(Storage::disk('local')->get($signature->signature_image_path));
        }

        return view('signatures.show', compact('signature', 'imgBase64'));
    }

    // ── 5. Téléchargement PDF signé ──────────────────────────────

    public function downloadPdf($id)
    {
        $signature = Signature::findOrFail($id);
        $this->authorize('downloadPdf', $signature);

        if (! $signature->pdfReady() || ! Storage::disk('local')->exists($signature->pdf_path)) {
            return back()->with('error', 'Le PDF n\'est pas encore disponible. Réessayez dans quelques secondes.');
        }

        $filename = sprintf(
            'bordereau_signe_%s.pdf',
            $signature->collecte->numero_bordereau ?? $signature->id
        );

        return Storage::disk('local')->download($signature->pdf_path, $filename);
    }

    // ── 6. Révocation (superadmin uniquement) ────────────────────

    public function revoke(Request $request, $id)
    {
        $signature = Signature::findOrFail($id);
        $this->authorize('revoke', $signature);

        $validated = $request->validate([
            'revocation_reason' => 'required|string|max:500',
        ]);

        $signature->update([
            'revoked_at'         => now(),
            'revocation_reason'  => $validated['revocation_reason'],
            'revoked_by_user_id' => Auth::id(),
        ]);

        // Remet la collecte au statut « en_cours » pour permettre une nouvelle signature
        if ($signature->collecte) {
            $signature->collecte->update(['statut' => 'en_cours']);
        }

        return redirect()->route('signatures.show', $signature->id)
            ->with('success', 'Signature révoquée. La collecte peut être signée à nouveau.');
    }

    // ── Helpers ───────────────────────────────────────────────────

    /**
     * Extrait quelques infos device à partir de l'User-Agent.
     * Volontairement minimaliste — pas de parser externe.
     */
    private function extractDeviceInfo(Request $request): array
    {
        $ua = (string) $request->userAgent();
        $info = [
            'user_agent_raw' => substr($ua, 0, 500),
            'is_mobile'      => (bool) preg_match('/Mobile|Android|iPhone|iPad/i', $ua),
            'is_tablet'      => (bool) preg_match('/iPad|Tablet|SM-T/i', $ua),
        ];

        if (preg_match('/(Windows NT|Mac OS X|Android|iPad|iPhone|Linux)[^;)]*/i', $ua, $m)) {
            $info['os'] = trim($m[0]);
        }
        if (preg_match('/(Chrome|Safari|Firefox|Edg|Opera)\/[\d\.]+/i', $ua, $m)) {
            $info['browser'] = $m[0];
        }

        // Optionnel : résolution écran transmise par le client
        if ($request->filled('screen_resolution')) {
            $info['screen_resolution'] = substr((string) $request->screen_resolution, 0, 30);
        }

        return $info;
    }
}
