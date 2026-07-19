<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Services\CacheService;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user   = Auth::user();
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;
        $mois   = Carbon::now()->format('Y-m');
        $moisP  = Carbon::now()->subMonth()->format('Y-m');

        // Client signataire : périmètre fonctionnel réduit aux Collectes
        // et Signatures. Pas de tableau de bord pertinent → redirection
        // directe vers la liste des collectes (sa mission unique).
        if ($user->isClientSignataire()) {
            return redirect()->route('collectes.index');
        }

        // Utilisateurs globaux OU réseau-scoped sans structure sélectionnée
        // → dashboard réseau (filtré par TenantScope pour admin_reseau)
        if (($user->isGlobal() || $user->isReseauScoped()) && ! $tenant) {
            return redirect()->route('superadmin.index');
        }

        // Utilisateurs locaux (qhse, agent) sans établissement → erreur
        if (! $tenant) {
            abort(403, __('common.tenant_no_etab'));
        }

        $etabId = $tenant->id;

        // ══════════════════════════════════════════════════════════════
        // CTE cachée : KPIs multi-tables + évolution + métriques
        // Cache 5 min, invalidé par les Observers
        // ══════════════════════════════════════════════════════════════
        $cacheKey = CacheService::dashboardKey($etabId, $mois);

        $cached = Cache::remember($cacheKey, CacheService::TTL_DASHBOARD, function () use ($etabId, $mois, $moisP) {

        $mois0 = Carbon::now()->format('Y-m');
        $mois1 = Carbon::now()->subMonths(1)->format('Y-m');
        $mois2 = Carbon::now()->subMonths(2)->format('Y-m');
        $mois3 = Carbon::now()->subMonths(3)->format('Y-m');
        $mois4 = Carbon::now()->subMonths(4)->format('Y-m');
        $mois5 = Carbon::now()->subMonths(5)->format('Y-m');

        $kpis = DB::selectOne("
            WITH
            -- CTE déclarations : KPIs mois courant + statuts + évolution 6 mois
            decl_stats AS (
                SELECT
                    COUNT(*)                                    AS total_mois,
                    COALESCE(SUM(poids_estime_kg), 0)           AS poids_mois,
                    SUM(CASE WHEN statut = 'en_stock' THEN 1 ELSE 0 END)     AS en_stock,
                    SUM(CASE WHEN statut = 'en_transport' THEN 1 ELSE 0 END)  AS en_transport,
                    -- Évolution 6 mois via SUM(CASE WHEN)
                    COALESCE(SUM(CASE WHEN DATE_FORMAT(date_declaration,'%Y-%m') = ? THEN poids_estime_kg END), 0) AS evo_m0,
                    COALESCE(SUM(CASE WHEN DATE_FORMAT(date_declaration,'%Y-%m') = ? THEN poids_estime_kg END), 0) AS evo_m1,
                    COALESCE(SUM(CASE WHEN DATE_FORMAT(date_declaration,'%Y-%m') = ? THEN poids_estime_kg END), 0) AS evo_m2,
                    COALESCE(SUM(CASE WHEN DATE_FORMAT(date_declaration,'%Y-%m') = ? THEN poids_estime_kg END), 0) AS evo_m3,
                    COALESCE(SUM(CASE WHEN DATE_FORMAT(date_declaration,'%Y-%m') = ? THEN poids_estime_kg END), 0) AS evo_m4,
                    COALESCE(SUM(CASE WHEN DATE_FORMAT(date_declaration,'%Y-%m') = ? THEN poids_estime_kg END), 0) AS evo_m5
                FROM declarations
                WHERE etablissement_id = ?
            ),
            -- CTE checklists : score conformité mois courant
            check_stats AS (
                SELECT COALESCE(AVG(score_conformite), 0) AS score
                FROM checklists
                WHERE etablissement_id = ?
                  AND DATE_FORMAT(date_checklist,'%Y-%m') = ?
            ),
            -- CTE alertes : non lues
            alerte_stats AS (
                SELECT COUNT(*) AS non_lues
                FROM alertes
                WHERE etablissement_id = ? AND lu = 0
            ),
            -- CTE destructions : comparaison poids
            dest_stats AS (
                SELECT
                    COALESCE(SUM(destructions.poids_reel_kg), 0)  AS poids_reel,
                    COALESCE(SUM(collectes.poids_declare_kg), 0)  AS poids_declare
                FROM destructions
                JOIN collectes ON collectes.id = destructions.collecte_id
                WHERE collectes.etablissement_id = ?
                  AND DATE_FORMAT(destructions.date_destruction,'%Y-%m') = ?
            ),
            -- CTE coûts sacs : jaune + noir via SUM(CASE WHEN) sur les lignes
            cout_stats AS (
                SELECT
                    COALESCE(SUM(CASE WHEN tc.code LIKE 'SJ%'
                        THEN dl.nombre_contenants * tc.cout_unitaire END), 0) AS cout_jaune,
                    COALESCE(SUM(CASE WHEN tc.code LIKE 'SN%'
                        THEN dl.nombre_contenants * tc.cout_unitaire END), 0) AS cout_noir
                FROM declaration_lignes dl
                JOIN declarations d ON d.id = dl.declaration_id
                JOIN type_contenants tc ON tc.id = dl.type_contenant_id
                WHERE d.etablissement_id = ?
                  AND DATE_FORMAT(d.date_declaration,'%Y-%m') = ?
                  AND (tc.code LIKE 'SJ%' OR tc.code LIKE 'SN%')
            )
            SELECT
                ds.total_mois, ds.poids_mois, ds.en_stock, ds.en_transport,
                ds.evo_m0, ds.evo_m1, ds.evo_m2, ds.evo_m3, ds.evo_m4, ds.evo_m5,
                cs.score,
                als.non_lues,
                dts.poids_reel, dts.poids_declare,
                cts.cout_jaune, cts.cout_noir
            FROM decl_stats ds, check_stats cs, alerte_stats als, dest_stats dts, cout_stats cts
        ", [
            $mois0, $mois1, $mois2, $mois3, $mois4, $mois5,  // evo_m0..m5
            $etabId,                                            // decl WHERE
            $etabId, $mois,                                     // check WHERE
            $etabId,                                            // alerte WHERE
            $etabId, $mois,                                     // dest WHERE
            $etabId, $mois,                                     // cout WHERE
        ]);

        $declarationsMois = (int) $kpis->total_mois;
        $poidsMois        = (float) $kpis->poids_mois;
        $enStock          = (int) $kpis->en_stock;
        $enTransport      = (int) $kpis->en_transport;
        $scoreConformite  = (float) $kpis->score;
        $alertesNonLues   = (int) $kpis->non_lues;
        $sacJaune         = (float) $kpis->cout_jaune;
        $sacNoir          = (float) $kpis->cout_noir;

        $comparaisonPoids = (object) [
            'poids_reel'    => (float) $kpis->poids_reel,
            'poids_declare' => (float) $kpis->poids_declare,
        ];

        // Évolution 6 mois (déjà calculée dans le CTE)
        $evolution6mois = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = Carbon::now()->subMonths($i);
            $col = 'evo_m' . (5 - $i);
            $evolution6mois[] = [
                'mois'  => $d->format('M Y'),
                'poids' => (float) $kpis->$col,
            ];
        }

        // Production par service (agrégée sur les lignes de déclaration)
        $productionParService = DB::table('declaration_lignes')
            ->join('declarations', 'declaration_lignes.declaration_id', '=', 'declarations.id')
            ->join('services', 'declaration_lignes.service_id', '=', 'services.id')
            ->select('services.nom',
                DB::raw('SUM(declaration_lignes.poids_estime_kg) as poids_total'),
                DB::raw('COUNT(*) as nb_declarations'))
            ->where('declarations.etablissement_id', $etabId)
            ->whereRaw("DATE_FORMAT(declarations.date_declaration,'%Y-%m') = ?", [$mois])
            ->groupBy('services.id', 'services.nom')
            ->orderByDesc('poids_total')->get();

        // Répartition contenants (avec couleur du type de déchet)
        $repartitionContenants = DB::table('declaration_lignes')
            ->join('declarations', 'declaration_lignes.declaration_id', '=', 'declarations.id')
            ->join('type_contenants', 'declaration_lignes.type_contenant_id', '=', 'type_contenants.id')
            ->join('type_dechets', 'type_contenants.type_dechet_id', '=', 'type_dechets.id')
            ->select('type_contenants.nom', 'type_dechets.couleur_sac',
                DB::raw('SUM(declaration_lignes.nombre_contenants) as total'))
            ->where('declarations.etablissement_id', $etabId)
            ->whereRaw("DATE_FORMAT(declarations.date_declaration,'%Y-%m') = ?", [$mois])
            ->groupBy('type_contenants.id', 'type_contenants.nom', 'type_dechets.couleur_sac')->get();

        // Dernières activités : services/contenants concaténés par déclaration
        $derniereActivites = DB::table('declarations')
            ->leftJoin('declaration_lignes', 'declaration_lignes.declaration_id', '=', 'declarations.id')
            ->leftJoin('services', 'declaration_lignes.service_id', '=', 'services.id')
            ->leftJoin('type_contenants', 'declaration_lignes.type_contenant_id', '=', 'type_contenants.id')
            ->join('users', 'declarations.user_id', '=', 'users.id')
            ->select(
                'declarations.id',
                'declarations.nombre_contenants',
                'declarations.poids_estime_kg',
                'declarations.statut',
                'declarations.date_declaration',
                'declarations.created_at',
                DB::raw("GROUP_CONCAT(DISTINCT services.nom ORDER BY services.nom SEPARATOR ', ') as service_nom"),
                DB::raw("GROUP_CONCAT(DISTINCT type_contenants.nom ORDER BY type_contenants.nom SEPARATOR ', ') as contenant_nom"),
                DB::raw("CONCAT(users.prenom,' ',users.nom) as agent_nom"))
            ->where('declarations.etablissement_id', $etabId)
            ->groupBy('declarations.id', 'declarations.nombre_contenants',
                'declarations.poids_estime_kg', 'declarations.statut',
                'declarations.date_declaration', 'declarations.created_at',
                'users.prenom', 'users.nom')
            ->orderByDesc('declarations.created_at')->limit(8)->get();

        // Alertes récentes
        $alertesRecentes = DB::table('alertes')
            ->where('etablissement_id', $etabId)
            ->orderByDesc('created_at')->limit(5)->get();

            return compact(
                'declarationsMois', 'poidsMois', 'enStock', 'enTransport',
                'scoreConformite', 'alertesNonLues', 'productionParService', 'evolution6mois',
                'repartitionContenants', 'sacJaune', 'sacNoir', 'derniereActivites',
                'alertesRecentes', 'comparaisonPoids'
            );
        }); // fin Cache::remember

        // Extraire les variables du cache
        extract($cached);

        // Métriques établissement (dépendent du tenant, pas cachées)
        $etablissement  = $tenant;
        $nbLits         = $etablissement->nombre_lits ?? 1;
        $ratioKgLit     = $nbLits > 0 ? round($poidsMois / max($nbLits, 1), 2) : 0;
        $poidsPrecedent = $evolution6mois[4]['poids'] ?? 0; // M-1 = index 4 sur 6 mois
        $variationPoids = $poidsPrecedent > 0
            ? round((($poidsMois - $poidsPrecedent) / $poidsPrecedent) * 100, 1)
            : 0;

        return view('dashboard.index', compact(
            'etablissement', 'declarationsMois', 'poidsMois', 'enStock', 'enTransport',
            'scoreConformite', 'alertesNonLues', 'productionParService', 'evolution6mois',
            'repartitionContenants', 'sacJaune', 'sacNoir', 'derniereActivites',
            'alertesRecentes', 'ratioKgLit', 'variationPoids', 'nbLits', 'comparaisonPoids'
        ));
    }
}
