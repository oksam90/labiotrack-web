<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Services\CacheService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class RapportController extends Controller
{
    public function index()
    {
        $user  = Auth::user();
        $query = DB::table('rapports')
            ->leftJoin('etablissements', 'rapports.etablissement_id', '=', 'etablissements.id')
            ->select('rapports.*', 'etablissements.nom as etablissement_nom')
            ->orderByDesc('genere_at');
        $user->filtreEtab($query, 'rapports.etablissement_id');
        $rapports = $query->paginate(10);
        return view('rapports.index', compact('rapports'));
    }

    public function generer(Request $request)
    {
        $request->validate([
            'type'          => 'required|in:mensuel,trimestriel,annuel,ad_hoc',
            'periode_debut' => 'required|date',
            'periode_fin'   => 'required|date|after_or_equal:periode_debut',
        ]);

        $user   = Auth::user();
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;

        // Résolution de l'établissement cible
        if ($user->isGlobal()) {
            // Priorité : 1. tenant sélectionné en session, 2. champ formulaire, 3. erreur
            $etabId = $tenant?->id ?? ($request->etablissement_id ?: null);
            if (! $etabId) {
                return back()->with('error',
                    'Veuillez sélectionner une structure via le dashboard réseau ' .
                    'avant de générer un rapport, ou choisissez-en une dans le formulaire.'
                );
            }
        } else {
            $etabId = $user->etablissement_id;
        }

        $etablissement = DB::table('etablissements')->find($etabId);
        if (! $etablissement) {
            return back()->with('error', "Établissement introuvable (id: {$etabId}).");
        }

        $data = Cache::remember(
            CacheService::rapportKey($etabId, $request->periode_debut, $request->periode_fin),
            CacheService::TTL_RAPPORT,
            fn () => $this->collecterDonneesRapport($etabId, $request->periode_debut, $request->periode_fin, $user)
        );

        $pdf = Pdf::loadView('rapports.rapport_pdf', compact('data', 'etablissement', 'request'));

        $dossier = storage_path('app/public/rapports');
        if (! is_dir($dossier)) {
            mkdir($dossier, 0755, true);
        }

        $path = 'rapports/rapport_' . now()->format('Ymd_His') . '.pdf';
        $pdf->save(storage_path('app/public/' . $path));

        $rapportId = DB::table('rapports')->insertGetId([
            'etablissement_id' => $etabId,
            'user_id'          => $user->id,
            'type'             => $request->type,
            'periode_debut'    => $request->periode_debut,
            'periode_fin'      => $request->periode_fin,
            'fichier_path'     => $path,
            'genere_at'        => now(),
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        return redirect()->route('rapports.pdf', $rapportId)
            ->with('success', 'Rapport généré avec succès.');
    }

    public function pdf($id)
    {
        $user  = Auth::user();
        $query = DB::table('rapports')->where('id', $id);
        $user->filtreEtab($query);
        $rapport = $query->firstOrFail();

        $fichier = storage_path('app/public/' . $rapport->fichier_path);
        if (! file_exists($fichier)) {
            abort(404, 'Fichier rapport introuvable. Veuillez le régénérer.');
        }

        return response()->file($fichier);
    }

    public function analyseFinanciere()
    {
        $user    = Auth::user();
        $mois    = Carbon::now()->format('Y-m');
        $etabId  = $user->isGlobal() ? null : $user->etablissement_id;

        $cached = Cache::remember(
            CacheService::financierKey($etabId, $mois),
            CacheService::TTL_FINANCIER,
            function () use ($user, $mois) {

                $etabFilter = function ($q, string $col = 'declarations.etablissement_id') use ($user) {
                    if ($user->isGlobal()) return $q;
                    return $q->where($col, $user->etablissement_id);
                };

                $coutParContenant = $etabFilter(
                    DB::table('declarations')
                        ->join('type_contenants', 'declarations.type_contenant_id', '=', 'type_contenants.id')
                        ->selectRaw('type_contenants.nom, type_contenants.cout_unitaire,
                            SUM(declarations.nombre_contenants) as total_contenants,
                            SUM(declarations.nombre_contenants * type_contenants.cout_unitaire) as cout_total')
                        ->whereRaw("DATE_FORMAT(date_declaration,'%Y-%m') = ?", [$mois])
                        ->groupBy('type_contenants.id', 'type_contenants.nom', 'type_contenants.cout_unitaire')
                )->paginate(10, ['*'], 'contenants_page');

                $coutSacs = $etabFilter(
                    DB::table('declarations')
                        ->join('type_contenants', 'declarations.type_contenant_id', '=', 'type_contenants.id')
                        ->selectRaw("
                            COALESCE(SUM(CASE WHEN type_contenants.code LIKE 'SJ%'
                                THEN nombre_contenants * type_contenants.cout_unitaire END), 0) AS cout_jaune,
                            COALESCE(SUM(CASE WHEN type_contenants.code LIKE 'SN%'
                                THEN nombre_contenants * type_contenants.cout_unitaire END), 0) AS cout_noir
                        ")
                        ->whereRaw("DATE_FORMAT(date_declaration,'%Y-%m') = ?", [$mois])
                        ->whereRaw("(type_contenants.code LIKE 'SJ%' OR type_contenants.code LIKE 'SN%')")
                )->first();

                $sacJaune = (float) $coutSacs->cout_jaune;
                $sacNoir  = (float) $coutSacs->cout_noir;

                $surcoutEstime = $sacJaune * 0.20;
                $coutOptimise  = $sacJaune - $surcoutEstime;
                $economie      = $surcoutEstime;

                $coutParService = $etabFilter(
                    DB::table('declarations')
                        ->join('services', 'declarations.service_id', '=', 'services.id')
                        ->join('type_contenants', 'declarations.type_contenant_id', '=', 'type_contenants.id')
                        ->selectRaw('services.nom, SUM(declarations.nombre_contenants * type_contenants.cout_unitaire) as cout_total')
                        ->whereRaw("DATE_FORMAT(date_declaration,'%Y-%m') = ?", [$mois])
                        ->groupBy('services.id', 'services.nom')
                        ->orderByDesc('cout_total')
                )->paginate(10, ['*'], 'services_page');

                return compact(
                    'coutParContenant', 'sacJaune', 'sacNoir',
                    'surcoutEstime', 'coutOptimise', 'economie', 'coutParService'
                );
            }
        );

        extract($cached);
        return view('rapports.financier', compact(
            'coutParContenant', 'sacJaune', 'sacNoir',
            'surcoutEstime', 'coutOptimise', 'economie', 'coutParService', 'mois'
        ));
    }

    private function collecterDonneesRapport($etablissementId, $debut, $fin, $user = null): array
    {
        $isGlobal  = $user && $user->isGlobal() && ! $etablissementId;
        $debutJour = $debut . ' 00:00:00';
        $finJour   = $fin . ' 23:59:59';

        // Clause établissement réutilisable
        $etabWhere    = $isGlobal ? '' : 'AND etablissement_id = ?';
        $etabBindings = $isGlobal ? [] : [$etablissementId];

        // ── 1 SEULE REQUÊTE pour les 7 scalaires (A + B + C fusionnés) ──
        $kpis = DB::selectOne("
            SELECT
                -- Groupe A : declarations
                (SELECT COUNT(*)
                 FROM declarations
                 WHERE date_declaration BETWEEN ? AND ? {$etabWhere}
                ) AS decl_count,

                (SELECT COALESCE(SUM(poids_estime_kg), 0)
                 FROM declarations
                 WHERE date_declaration BETWEEN ? AND ? {$etabWhere}
                ) AS decl_poids,

                -- Groupe B : destructions (direct, sans JOIN)
                (SELECT COUNT(*)
                 FROM destructions
                 WHERE date_destruction BETWEEN ? AND ? {$etabWhere}
                ) AS dest_count,

                (SELECT COALESCE(SUM(poids_reel_kg), 0)
                 FROM destructions
                 WHERE date_destruction BETWEEN ? AND ? {$etabWhere}
                ) AS dest_poids,

                -- Groupe C : collectes, checklists, alertes
                (SELECT COUNT(*)
                 FROM collectes
                 WHERE date_collecte BETWEEN ? AND ? {$etabWhere}
                ) AS collectes_count,

                (SELECT COALESCE(AVG(score_conformite), 0)
                 FROM checklists
                 WHERE date_checklist BETWEEN ? AND ? {$etabWhere}
                ) AS score_conformite,

                (SELECT COUNT(*)
                 FROM alertes
                 WHERE created_at BETWEEN ? AND ? {$etabWhere}
                ) AS alertes_count
        ", array_merge(
            [$debut, $fin],         $etabBindings,  // decl_count
            [$debut, $fin],         $etabBindings,  // decl_poids
            [$debutJour, $finJour], $etabBindings,  // dest_count
            [$debutJour, $finJour], $etabBindings,  // dest_poids
            [$debutJour, $finJour], $etabBindings,  // collectes_count
            [$debut, $fin],         $etabBindings,  // score_conformite
            [$debutJour, $finJour], $etabBindings,  // alertes_count
        ));

        // ── Requête D : par service (GROUP BY — ne peut pas être fusionnée) ─
        $serviceQuery = DB::table('declarations')
            ->join('services', 'declarations.service_id', '=', 'services.id')
            ->selectRaw('services.nom, SUM(poids_estime_kg) as poids, COUNT(*) as nb')
            ->whereBetween('declarations.date_declaration', [$debut, $fin])
            ->groupBy('services.id', 'services.nom');
        if (! $isGlobal) $serviceQuery->where('declarations.etablissement_id', $etablissementId);

        return [
            'total_declarations' => (int) $kpis->decl_count,
            'poids_total_estime' => (float) $kpis->decl_poids,
            'par_service'        => $serviceQuery->get(),
            'collectes_count'    => (int) $kpis->collectes_count,
            'score_conformite'   => (float) $kpis->score_conformite,
            'alertes_count'      => (int) $kpis->alertes_count,
            'destructions_count' => (int) $kpis->dest_count,
            'poids_reel_total'   => (float) $kpis->dest_poids,
        ];
    }
}
