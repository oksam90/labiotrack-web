<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class DestructionController extends Controller
{
    public function index()
    {
        $user  = Auth::user();
        $query = DB::table('destructions')
            ->join('collectes', 'destructions.collecte_id', '=', 'collectes.id')
            ->leftJoin('users as p', 'destructions.prestataire_id', '=', 'p.id')
            ->leftJoin('etablissements', 'collectes.etablissement_id', '=', 'etablissements.id')
            ->select('destructions.*', 'collectes.numero_bordereau', 'etablissements.nom as etablissement_nom',
                DB::raw("CONCAT(p.prenom,' ',p.nom) as prestataire_nom"))
            ->orderByDesc('destructions.date_destruction');

        $user->filtreEtab($query, 'collectes.etablissement_id');
        $destructions = $query->paginate(10);
        return view('destructions.index', compact('destructions'));
    }

    public function create($collecteId)
    {
        $this->authorize('create', \App\Models\Destruction::class);

        $user    = Auth::user();
        $query   = DB::table('collectes')->where('id', $collecteId);
        $user->filtreEtab($query);
        $collecte = $query->firstOrFail();
        return view('destructions.create', compact('collecte'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', \App\Models\Destruction::class);

        $request->validate([
            'collecte_id'      => 'required|exists:collectes,id',
            'poids_reel_kg'    => 'required|numeric|min:0.01',
            'methode'          => 'required|in:incineration,autoclave,desinfection_chimique,autre',
            'site_traitement'  => 'nullable|string|max:255',
            'date_reception'   => 'nullable|date',
            'date_destruction' => 'required|date',
            'conforme'         => 'boolean',
            'notes'            => 'nullable|string|max:500',
        ]);

        $user          = Auth::user();
        $certificatNum = 'CERT-' . date('Y') . '-' . strtoupper(substr(uniqid(), -8));

        // Récupérer l'etablissement_id depuis la collecte associée
        $collecte = DB::table('collectes')->where('id', $request->collecte_id)->firstOrFail();

        $id = DB::table('destructions')->insertGetId([
            'collecte_id'       => $request->collecte_id,
            'etablissement_id'  => $collecte->etablissement_id,
            'prestataire_id'    => $user->id,
            'poids_reel_kg'     => $request->poids_reel_kg,
            'methode'           => $request->methode,
            'site_traitement'   => $request->site_traitement,
            'certificat_numero' => $certificatNum,
            'date_reception'    => $request->date_reception,
            'date_destruction'  => $request->date_destruction,
            'conforme'          => $request->boolean('conforme', true),
            'notes'             => $request->notes,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        $declarationIds = DB::table('collecte_declarations')
            ->where('collecte_id', $request->collecte_id)->pluck('declaration_id');
        DB::table('declarations')->whereIn('id', $declarationIds)->update([
            'statut'        => 'detruit',
            'poids_reel_kg' => $request->poids_reel_kg,
            'updated_at'    => now(),
        ]);

        // Génération PDF certificat au format Bordereau
        $destruction   = DB::table('destructions')->find($id);
        // $collecte déjà récupérée avant l'insert
        $etablissement = DB::table('etablissements')->find($collecte->etablissement_id);
        $prestataire   = $user;

        $pdf  = Pdf::loadView('destructions.certificat_pdf',
            compact('destruction','collecte','etablissement','certificatNum','prestataire'));
        $pdf->setPaper('A4');
        // Créer le dossier s'il n'existe pas
        $dossier = storage_path('app/public/certificats');
        if (! is_dir($dossier)) {
            mkdir($dossier, 0755, true); // true = création récursive
        }
        $path = 'certificats/cert_' . $id . '.pdf';
        $pdf->save(storage_path('app/public/' . $path));
        DB::table('destructions')->where('id', $id)->update(['certificat_path' => $path]);

        DB::table('alertes')->insert([
            'etablissement_id' => $collecte->etablissement_id,
            'type'             => 'autre',
            'niveau'           => 'info',
            'message'          => "Destruction confirmée — Certificat n° {$certificatNum} | Poids réel : {$request->poids_reel_kg} kg | Méthode : {$request->methode}",
            'lu'               => 0,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        return redirect()->route('destructions.certificat', $id)
            ->with('success', "Destruction enregistrée. Certificat n° {$certificatNum} généré.");
    }

    public function show($id)
    {
        $user  = Auth::user();
        $query = DB::table('destructions')
            ->join('collectes', 'destructions.collecte_id', '=', 'collectes.id')
            ->select('destructions.*', 'collectes.numero_bordereau', 'collectes.etablissement_id')
            ->where('destructions.id', $id);
        $user->filtreEtab($query, 'collectes.etablissement_id');
        $destruction   = $query->firstOrFail();
        $etablissement = DB::table('etablissements')->find($destruction->etablissement_id);
        return view('destructions.show', compact('destruction', 'etablissement'));
    }

    public function certificat($id)
    {
        $user  = Auth::user();
        $query = DB::table('destructions')
            ->join('collectes', 'destructions.collecte_id', '=', 'collectes.id')
            ->select('destructions.*', 'collectes.numero_bordereau',
                'collectes.etablissement_id', 'collectes.poids_declare_kg')
            ->where('destructions.id', $id);
        $user->filtreEtab($query, 'collectes.etablissement_id');
        $destruction   = $query->firstOrFail();
        $etablissement = DB::table('etablissements')->find($destruction->etablissement_id);
        $prestataire   = DB::table('users')->find($destruction->prestataire_id);
        $collecte      = DB::table('collectes')->find($destruction->collecte_id);
        return view('destructions.certificat', compact('destruction','etablissement','prestataire','collecte'));
    }

    public function certificatPdf($id)
    {
        $user  = Auth::user();
        $query = DB::table('destructions')
            ->join('collectes', 'destructions.collecte_id', '=', 'collectes.id')
            ->select('destructions.*', 'collectes.numero_bordereau',
                'collectes.etablissement_id', 'collectes.poids_declare_kg')
            ->where('destructions.id', $id);
        $user->filtreEtab($query, 'collectes.etablissement_id');
        $destruction   = $query->firstOrFail();
        $etablissement = DB::table('etablissements')->find($destruction->etablissement_id);
        $certificatNum = $destruction->certificat_numero;
        $collecte      = DB::table('collectes')->find($destruction->collecte_id);
        $prestataire   = DB::table('users')->find($destruction->prestataire_id);

        $pdf = Pdf::loadView('destructions.certificat_pdf',
            compact('destruction','etablissement','certificatNum','collecte','prestataire'));
        $pdf->setPaper('A4');
        return $pdf->download("bordereau_destruction_{$certificatNum}.pdf");
    }
}
