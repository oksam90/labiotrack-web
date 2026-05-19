<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Checklist;
use App\Models\Service;
use App\Models\Alerte;
use App\Scopes\TenantScope;

class ChecklistController extends Controller
{
    public function index()
    {
        $checklists  = Checklist::with(['service','agent','etablissement'])
            ->orderByDesc('date_checklist')->paginate(10);
        $scoresMoyen = Checklist::whereMonth('date_checklist', now()->month)
            ->avg('score_conformite') ?? 0;

        return view('checklists.index', compact('checklists','scoresMoyen'));
    }

    public function create()
    {
        $services = Service::actif()->orderBy('nom')->get();
        return view('checklists.create', compact('services'));
    }

    public function store(Request $request)
    {
        // ✅ FIX #8 — $this->authorize('create', Checklist::class) est la syntaxe
        // correcte quand on passe la classe (pas d'instance). C'est valide dans
        // Laravel mais la ChecklistPolicy doit accepter User + null comme 2e arg.
        // On vérifie simplement le rôle ici pour éviter toute ambiguïté.
        $user = Auth::user();
        // Seuls les rôles habilités peuvent créer une checklist
        // Le prestataire n'est pas responsable des checklists internes (aligné avec ChecklistPolicy)
        if (! in_array($user->role, ['superadmin','admin','qhse'])) {
            abort(403, __('checklists.errors_access_denied'));
        }

        $items = ['boites_fermees_75','sacs_correctement_etiquetes','local_ventile',
                  'epi_port','sacs_noirs_non_contamines','contenants_integres'];
        $score = 0;
        foreach ($items as $item) { if ($request->boolean($item)) $score++; }
        $scoreConformite = round(($score / count($items)) * 100, 2);

        $service = $request->service_id ? Service::findOrFail($request->service_id) : null;
        $etabId  = $user->isGlobal()
            ? ($service?->etablissement_id ?? null)
            : $user->etablissement_id;

        Checklist::withoutGlobalScope(TenantScope::class)->create([
            'etablissement_id'            => $etabId,
            'service_id'                  => $request->service_id ?: null,
            'user_id'                     => $user->id,
            'boites_fermees_75'           => $request->boolean('boites_fermees_75'),
            'sacs_correctement_etiquetes' => $request->boolean('sacs_correctement_etiquetes'),
            'local_ventile'               => $request->boolean('local_ventile'),
            'epi_port'                    => $request->boolean('epi_port'),
            'sacs_noirs_non_contamines'   => $request->boolean('sacs_noirs_non_contamines'),
            'contenants_integres'         => $request->boolean('contenants_integres'),
            'score_conformite'            => $scoreConformite,
            'observations'                => $request->observations,
            'date_checklist'              => now()->toDateString(),
        ]);

        if ($scoreConformite < 60 && $etabId) {
            Alerte::withoutGlobalScope(TenantScope::class)->create([
                'etablissement_id' => $etabId,
                'service_id'       => $request->service_id ?: null,
                'type'             => 'mauvais_tri',
                'niveau'           => 'danger',
                'message'          => "Score de conformité bas : {$scoreConformite}% — Action corrective requise.",
            ]);
        }

        return redirect()->route('checklists.index')
            ->with('success', __('checklists.flash_created', ['score' => $scoreConformite]));
    }

    public function show($id)
    {
        $checklist = Checklist::with(['service','agent'])->findOrFail($id);
        // ✅ FIX #8 (suite) — authorize('view', $checklist) passe l'instance : correct
        $this->authorize('view', $checklist);
        return view('checklists.show', compact('checklist'));
    }

    public function historique()
    {
        $historique = Checklist::selectRaw(
                "DATE_FORMAT(date_checklist,'%Y-%m') as mois,
                 AVG(score_conformite) as score_moyen,
                 COUNT(*) as nb")
            ->groupBy('mois')->orderByDesc('mois')->limit(12)->get();
        return view('checklists.historique', compact('historique'));
    }
}
