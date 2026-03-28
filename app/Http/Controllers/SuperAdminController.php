<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Services\CacheService;
use Carbon\Carbon;
use App\Models\Etablissement;

class SuperAdminController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $mois = Carbon::now()->format('Y-m');

        // KPIs réseau global — CTE cachée 5 min
        $kpis = Cache::remember(CacheService::superadminKpisKey($mois), CacheService::TTL_DASHBOARD, fn () => DB::selectOne("
            WITH
            etab_stats AS (
                SELECT
                    COUNT(*)                                  AS total_structures,
                    SUM(CASE WHEN actif = 1 THEN 1 ELSE 0 END) AS structures_actives
                FROM etablissements
            ),
            user_stats AS (
                SELECT COUNT(*) AS total_users
                FROM users WHERE actif = 1
            ),
            alerte_stats AS (
                SELECT COUNT(*) AS alertes_nonlues
                FROM alertes WHERE lu = 0
            ),
            decl_stats AS (
                SELECT
                    COUNT(*)                          AS declarations_mois,
                    COALESCE(SUM(poids_estime_kg), 0) AS poids_mois_kg
                FROM declarations
                WHERE DATE_FORMAT(date_declaration,'%Y-%m') = ?
            ),
            dest_stats AS (
                SELECT COUNT(*) AS destructions_mois
                FROM destructions
                WHERE DATE_FORMAT(date_destruction,'%Y-%m') = ?
            ),
            check_stats AS (
                SELECT COALESCE(AVG(score_conformite), 0) AS score_moyen
                FROM checklists
                WHERE DATE_FORMAT(date_checklist,'%Y-%m') = ?
            )
            SELECT es.*, us.*, als.*, ds.*, dts.*, cs.*
            FROM etab_stats es, user_stats us, alerte_stats als,
                 decl_stats ds, dest_stats dts, check_stats cs
        ", [$mois, $mois, $mois]));

        $stats = [
            'total_structures'   => (int) $kpis->total_structures,
            'structures_actives' => (int) $kpis->structures_actives,
            'total_users'        => (int) $kpis->total_users,
            'alertes_nonlues'    => (int) $kpis->alertes_nonlues,
            'declarations_mois'  => (int) $kpis->declarations_mois,
            'poids_mois_kg'      => (float) $kpis->poids_mois_kg,
            'destructions_mois'  => (int) $kpis->destructions_mois,
            'score_moyen'        => (float) $kpis->score_moyen,
        ];

        // Performance par structure
        $parStructure = Etablissement::actif()
            ->withCount(['declarations as declarations_mois' => fn($q) =>
                $q->whereRaw("DATE_FORMAT(date_declaration,'%Y-%m') = ?", [$mois])
            ])
            ->addSelect([
                'poids_mois' => DB::table('declarations')
                    ->selectRaw('COALESCE(SUM(poids_estime_kg), 0)')
                    ->whereColumn('declarations.etablissement_id', 'etablissements.id')
                    ->whereRaw("DATE_FORMAT(date_declaration,'%Y-%m') = ?", [$mois]),
                'score_conformite' => DB::table('checklists')
                    ->selectRaw('COALESCE(AVG(score_conformite), 0)')
                    ->whereColumn('checklists.etablissement_id', 'etablissements.id')
                    ->whereRaw("DATE_FORMAT(date_checklist,'%Y-%m') = ?", [$mois]),
                'alertes_nonlues' => DB::table('alertes')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('alertes.etablissement_id', 'etablissements.id')
                    ->where('alertes.lu', 0),
            ])
            ->paginate(10, ['*'], 'structures_page');

        // Évolution réseau 6 mois
        $evolution = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = Carbon::now()->subMonths($i);
            $evolution[] = [
                'mois'  => $d->format('M Y'),
                'poids' => (float)(DB::table('declarations')
                    ->whereRaw("DATE_FORMAT(date_declaration,'%Y-%m') = ?", [$d->format('Y-m')])
                    ->sum('poids_estime_kg') ?? 0),
                'count' => DB::table('declarations')
                    ->whereRaw("DATE_FORMAT(date_declaration,'%Y-%m') = ?", [$d->format('Y-m')])
                    ->count(),
            ];
        }

        // Alertes réseau récentes
        $alertesReseau = DB::table('alertes')
            ->join('etablissements', 'alertes.etablissement_id', '=', 'etablissements.id')
            ->select('alertes.*', 'etablissements.nom as etab_nom')
            ->where('alertes.lu', 0)
            ->orderByDesc('alertes.created_at')
            ->limit(10)->get();

        $currentTenant  = app()->bound('currentTenant') ? app('currentTenant') : null;
        $etablissements = Etablissement::actif()->orderBy('nom')->get();

        // Collectes récentes (pour le dashboard collecteur)
        $collectesRecentes = null;
        if ($user->isCollecteur()) {
            $collectesRecentes = DB::table('collectes')
                ->leftJoin('etablissements', 'collectes.etablissement_id', '=', 'etablissements.id')
                ->select('collectes.*', 'etablissements.nom as etablissement_nom')
                ->where('collectes.collecteur_id', $user->id)
                ->orderByDesc('collectes.date_collecte')
                ->limit(10)->get();
        }

        // Destructions récentes (pour le dashboard prestataire)
        $destructionsRecentes = null;
        if ($user->isPrestataire()) {
            $destructionsRecentes = DB::table('destructions')
                ->join('collectes', 'destructions.collecte_id', '=', 'collectes.id')
                ->leftJoin('etablissements', 'collectes.etablissement_id', '=', 'etablissements.id')
                ->select('destructions.*', 'collectes.numero_bordereau', 'etablissements.nom as etablissement_nom')
                ->where('destructions.prestataire_id', $user->id)
                ->orderByDesc('destructions.date_destruction')
                ->limit(10)->get();
        }

        return view('superadmin.dashboard', compact(
            'stats', 'parStructure', 'evolution', 'alertesReseau',
            'currentTenant', 'etablissements', 'collectesRecentes', 'destructionsRecentes'
        ));
    }

    public function etablissements()
    {
        $etablissements = Etablissement::withCount(['users', 'services', 'declarations'])
            ->orderBy('nom')->paginate(10);
        return view('superadmin.etablissements', compact('etablissements'));
    }

    public function etablissement($id)
    {
        $etab = Etablissement::findOrFail($id);
        $mois = Carbon::now()->format('Y-m');

        // KPIs établissement — CTE cachée 5 min
        $raw = Cache::remember(CacheService::superadminEtabKey($id, $mois), CacheService::TTL_DASHBOARD, fn () => DB::selectOne("
            WITH
            decl_kpi AS (
                SELECT COUNT(*) AS declarations,
                       COALESCE(SUM(poids_estime_kg), 0) AS poids
                FROM declarations
                WHERE etablissement_id = ?
                  AND DATE_FORMAT(date_declaration,'%Y-%m') = ?
            ),
            check_kpi AS (
                SELECT COALESCE(AVG(score_conformite), 0) AS score
                FROM checklists
                WHERE etablissement_id = ?
                  AND DATE_FORMAT(date_checklist,'%Y-%m') = ?
            ),
            alerte_kpi AS (
                SELECT COUNT(*) AS alertes
                FROM alertes
                WHERE etablissement_id = ? AND lu = 0
            ),
            user_kpi AS (
                SELECT COUNT(*) AS users
                FROM users
                WHERE etablissement_id = ? AND actif = 1
            )
            SELECT d.*, c.score, a.alertes, u.users
            FROM decl_kpi d, check_kpi c, alerte_kpi a, user_kpi u
        ", [$id, $mois, $id, $mois, $id, $id]));

        $kpis = [
            'declarations' => (int) $raw->declarations,
            'poids'        => (float) $raw->poids,
            'score'        => (float) $raw->score,
            'alertes'      => (int) $raw->alertes,
            'users'        => (int) $raw->users,
        ];

        $services = DB::table('services')->where('etablissement_id', $id)->paginate(10, ['*'], 'services_page');
        $users    = DB::table('users')->where('etablissement_id', $id)->orderBy('role')->paginate(10, ['*'], 'users_page');

        return view('superadmin.etablissement_detail', compact('etab', 'kpis', 'services', 'users'));
    }

    public function switchTenant(Request $request, $id)
    {
        abort_unless(Auth::user()->isGlobal(), 403, 'Accès non autorisé.');
        $etab = Etablissement::findOrFail($id);
        $request->session()->put('admin_tenant_id', $id);
        return redirect()->route('dashboard')
            ->with('success', "Vous visualisez maintenant : {$etab->nom}");
    }

    public function resetTenant(Request $request)
    {
        $request->session()->forget('admin_tenant_id');
        return redirect()->route('superadmin.index')
            ->with('success', 'Retour à la vue réseau globale.');
    }

    public function statsReseau()
    {
        $mois = now()->format('Y-m');
        $data = Etablissement::actif()
            ->addSelect([
                'poids' => DB::table('declarations')
                    ->selectRaw('COALESCE(SUM(poids_estime_kg), 0)')
                    ->whereColumn('declarations.etablissement_id', 'etablissements.id')
                    ->whereRaw("DATE_FORMAT(date_declaration,'%Y-%m') = ?", [$mois]),
                'score' => DB::table('checklists')
                    ->selectRaw('COALESCE(AVG(score_conformite), 0)')
                    ->whereColumn('checklists.etablissement_id', 'etablissements.id')
                    ->whereRaw("DATE_FORMAT(date_checklist,'%Y-%m') = ?", [$mois]),
                'alertes' => DB::table('alertes')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('alertes.etablissement_id', 'etablissements.id')
                    ->where('alertes.lu', 0),
            ])
            ->paginate((int) request('per_page', 20));

        $data->getCollection()->transform(fn($e) => [
            'nom'     => $e->nom,
            'type'    => $e->type,
            'poids'   => (float) $e->poids,
            'score'   => round((float) $e->score, 1),
            'alertes' => (int) $e->alertes,
        ]);

        return response()->json($data);
    }

    public function comparatif()
    {
        $mois    = now()->format('Y-m');
        $etabs   = Etablissement::actif()
            ->addSelect([
                'poids' => DB::table('declarations')
                    ->selectRaw('COALESCE(SUM(poids_estime_kg), 0)')
                    ->whereColumn('declarations.etablissement_id', 'etablissements.id')
                    ->whereRaw("DATE_FORMAT(date_declaration,'%Y-%m') = ?", [$mois]),
                'score' => DB::table('checklists')
                    ->selectRaw('COALESCE(AVG(score_conformite), 0)')
                    ->whereColumn('checklists.etablissement_id', 'etablissements.id')
                    ->whereRaw("DATE_FORMAT(date_checklist,'%Y-%m') = ?", [$mois]),
                'cout' => DB::table('declarations')
                    ->selectRaw('COALESCE(SUM(declarations.nombre_contenants * type_contenants.cout_unitaire), 0)')
                    ->join('type_contenants', 'declarations.type_contenant_id', '=', 'type_contenants.id')
                    ->whereColumn('declarations.etablissement_id', 'etablissements.id')
                    ->whereRaw("DATE_FORMAT(date_declaration,'%Y-%m') = ?", [$mois]),
            ])
            ->paginate(10);

        return view('superadmin.comparatif', compact('etabs', 'mois'));
    }
}
