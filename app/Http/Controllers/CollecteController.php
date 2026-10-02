<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Jobs\GenerateCollecteBordereauPdf;

class CollecteController extends Controller
{
    public function index()
    {
        $user  = Auth::user();
        $query = DB::table('collectes')
            ->leftJoin('users as collecteurs', 'collectes.collecteur_id', '=', 'collecteurs.id')
            ->leftJoin('etablissements', 'collectes.etablissement_id', '=', 'etablissements.id')
            ->select('collectes.*',
                'etablissements.nom as etablissement_nom',
                DB::raw("CONCAT(collecteurs.prenom,' ',collecteurs.nom) as collecteur_nom"))
            ->orderByDesc('collectes.date_collecte');

        $user->filtreEtab($query, 'collectes.etablissement_id');

        if ($user->role === 'collecteur') {
            $query->where('collectes.collecteur_id', $user->id);
        }

        $collectes = $query->paginate(10);
        return view('collectes.index', compact('collectes'));
    }

    public function create()
    {
        $this->authorize('create', \App\Models\Collecte::class);

        $user  = Auth::user();
        // Une déclaration peut porter plusieurs services / contenants (lignes) :
        // on agrège les libellés distincts par déclaration pour l'affichage.
        $decQuery = DB::table('declarations')
            ->leftJoin('declaration_lignes', 'declaration_lignes.declaration_id', '=', 'declarations.id')
            ->leftJoin('services', 'declaration_lignes.service_id', '=', 'services.id')
            ->leftJoin('type_contenants', 'declaration_lignes.type_contenant_id', '=', 'type_contenants.id')
            ->select(
                'declarations.id',
                'declarations.nombre_contenants',
                'declarations.poids_estime_kg',
                'declarations.date_declaration',
                'declarations.etablissement_id',
                DB::raw("GROUP_CONCAT(DISTINCT services.nom ORDER BY services.nom SEPARATOR ', ') as service_nom"),
                DB::raw("GROUP_CONCAT(DISTINCT type_contenants.nom ORDER BY type_contenants.nom SEPARATOR ', ') as contenant_nom")
            )
            ->where('declarations.statut', 'en_stock')
            ->groupBy('declarations.id', 'declarations.nombre_contenants',
                'declarations.poids_estime_kg', 'declarations.date_declaration',
                'declarations.etablissement_id')
            ->orderByDesc('declarations.date_declaration');

        $user->filtreEtab($decQuery, 'declarations.etablissement_id');
        $declarationsDisponibles = $decQuery->get();

        // Collecteurs assignables : limités au réseau du créateur (les
        // collecteurs sont rattachés via users.reseau_id). Superadmin → tous.
        $collecteursQ = DB::table('users')->where('role', 'collecteur')->where('actif', 1);
        if (! $user->isGlobal() && $user->reseau_id) {
            $collecteursQ->where('reseau_id', $user->reseau_id);
        }
        $collecteurs = $collecteursQ->get();
        return view('collectes.create', compact('declarationsDisponibles', 'collecteurs'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', \App\Models\Collecte::class);

        $request->validate([
            'collecteur_id'   => 'nullable|exists:users,id',
            'declarations'    => 'required|array|min:1',
            'declarations.*'  => 'exists:declarations,id',
            'vehicule'        => 'nullable|string|max:100',
            'notes'           => 'nullable|string|max:500',
            'photo'           => 'nullable|image|max:5120',
        ]);

        $user = Auth::user();
        $ids  = array_values(array_unique(array_map('intval', $request->declarations)));

        // SECURITY : un collecteur assigné doit relever du même réseau que le
        // créateur (empêche l'assignation cross-réseau via un POST forgé).
        if ($request->collecteur_id && ! $user->isGlobal() && $user->reseau_id) {
            $assigne = DB::table('users')->where('id', $request->collecteur_id)
                ->where('role', 'collecteur')->first();
            if (! $assigne || (int) $assigne->reseau_id !== (int) $user->reseau_id) {
                return back()->withErrors(['collecteur_id' => __('collectes.collecteur_out_of_scope')]);
            }
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('collectes/photos', 'public');
        }

        // SECURITY / intégrité (M3) : lecture des déclarations sous verrou dans
        // la transaction qui les passe « en_transport ». Deux collectes
        // simultanées (double-clic, deux collecteurs) ne peuvent plus embarquer
        // la même déclaration.
        $resultat = DB::transaction(function () use ($user, $ids, $request, $photoPath) {
            // DB::table() contourne les global scopes Eloquent : périmètre explicite.
            $declQuery = DB::table('declarations')->whereIn('id', $ids)->lockForUpdate();
            $user->filtreEtab($declQuery, 'etablissement_id');
            $declarations = $declQuery->get();

            // IDs absents → hors périmètre.
            if ($declarations->count() !== count($ids)) {
                return ['erreur' => __('collectes.declarations_out_of_scope')];
            }
            // Déjà collectées / détruites → refus.
            if ($declarations->contains(fn ($d) => $d->statut !== 'en_stock')) {
                return ['erreur' => __('collectes.declarations_not_in_stock')];
            }
            // Un bordereau = un établissement (traçabilité + signature du client).
            if ($declarations->pluck('etablissement_id')->unique()->count() > 1) {
                return ['erreur' => __('collectes.declarations_multi_etab')];
            }

            $etabId          = (int) $declarations->first()->etablissement_id;
            $numeroBordereau = 'BRD-' . date('Ymd') . '-' . strtoupper(uniqid());

            $collecteId = DB::table('collectes')->insertGetId([
                'etablissement_id'  => $etabId,
                'collecteur_id'     => $request->collecteur_id,
                'numero_bordereau'  => $numeroBordereau,
                'nombre_contenants' => $declarations->sum('nombre_contenants'),
                'poids_declare_kg'  => $declarations->sum('poids_estime_kg'),
                'vehicule'          => $request->vehicule,
                'photo'             => $photoPath,
                'statut'            => 'en_cours',
                'date_collecte'     => now(),
                'notes'             => $request->notes,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            foreach ($ids as $declId) {
                DB::table('collecte_declarations')->insert([
                    'collecte_id'    => $collecteId,
                    'declaration_id' => $declId,
                ]);
            }
            DB::table('declarations')->whereIn('id', $ids)->update([
                'statut'     => 'en_transport',
                'updated_at' => now(),
            ]);

            return ['id' => $collecteId, 'numero' => $numeroBordereau];
        });

        if (isset($resultat['erreur'])) {
            return back()->withInput()->withErrors(['declarations' => $resultat['erreur']]);
        }

        return redirect()->route('collectes.show', $resultat['id'])
            ->with('success', __('collectes.created_success', ['ref' => $resultat['numero']]));
    }

    public function show($id)
    {
        $user  = Auth::user();
        $query = DB::table('collectes')
            ->leftJoin('users as c', 'collectes.collecteur_id', '=', 'c.id')
            ->select('collectes.*', DB::raw("CONCAT(c.prenom,' ',c.nom) as collecteur_nom"))
            ->where('collectes.id', $id);
        $user->filtreEtab($query, 'collectes.etablissement_id');
        $collecte = $query->firstOrFail();

        // Une ligne par ligne de déclaration (service × contenant).
        $declarations = DB::table('collecte_declarations')
            ->join('declarations', 'collecte_declarations.declaration_id', '=', 'declarations.id')
            ->join('declaration_lignes', 'declaration_lignes.declaration_id', '=', 'declarations.id')
            ->join('services', 'declaration_lignes.service_id', '=', 'services.id')
            ->join('type_contenants', 'declaration_lignes.type_contenant_id', '=', 'type_contenants.id')
            ->select(
                'declarations.id',
                'declaration_lignes.nombre_contenants',
                'declaration_lignes.poids_estime_kg',
                'services.nom as service_nom',
                'type_contenants.nom as contenant_nom'
            )
            ->where('collecte_declarations.collecte_id', $id)
            ->get();

        $destruction = DB::table('destructions')->where('collecte_id', $id)->first();
        return view('collectes.show', compact('collecte', 'declarations', 'destruction'));
    }

    // NOTE : la méthode legacy valider() (double signature texte) a été
    // retirée. La validation se fait désormais via la signature électronique
    // sur tablette — voir SignatureController + Job GenerateBordereauPdf
    // (le statut passe alors de 'en_cours' à 'signee').

    /**
     * Déclenche la génération ASYNCHRONE du bordereau PDF (non signé).
     * Si le PDF est déjà prêt, redirige directement vers le téléchargement.
     * Sinon dispatch le job et revient avec un message « en préparation ».
     */
    public function bordereau($id)
    {
        $user  = Auth::user();
        $query = DB::table('collectes')->where('id', $id);
        $user->filtreEtab($query, 'etablissement_id');
        $collecte = $query->firstOrFail();

        if ($collecte->bordereau_pdf_path
            && Storage::disk('local')->exists($collecte->bordereau_pdf_path)) {
            return redirect()->route('collectes.bordereau.download', $collecte->id);
        }

        GenerateCollecteBordereauPdf::dispatch((int) $collecte->id, app()->getLocale());

        return back()->with('success', __('collectes.bordereau_preparing'));
    }

    /**
     * Télécharge le bordereau PDF déjà généré (dans le périmètre de l'user).
     */
    public function downloadBordereau($id)
    {
        $user  = Auth::user();
        $query = DB::table('collectes')->where('id', $id);
        $user->filtreEtab($query, 'etablissement_id');
        $collecte = $query->firstOrFail();

        if (! $collecte->bordereau_pdf_path
            || ! Storage::disk('local')->exists($collecte->bordereau_pdf_path)) {
            return back()->with('error', __('collectes.bordereau_not_ready'));
        }

        return Storage::disk('local')->download(
            $collecte->bordereau_pdf_path,
            "bordereau_{$collecte->numero_bordereau}.pdf"
        );
    }
}
