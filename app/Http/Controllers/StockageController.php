<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StockageController extends Controller
{
    public function index(Request $request)
    {
        $user  = Auth::user();
        $query = DB::table('transferts')
            ->join('services', 'transferts.service_id', '=', 'services.id')
            ->join('users', 'transferts.user_id', '=', 'users.id')
            ->leftJoin('etablissements', 'transferts.etablissement_id', '=', 'etablissements.id')
            ->select('transferts.*',
                'services.nom as service_nom',
                'etablissements.nom as etablissement_nom',
                DB::raw("CONCAT(users.prenom,' ',users.nom) as agent_nom"))
            ->orderByDesc('transferts.date_transfert');

        $user->filtreEtab($query, 'transferts.etablissement_id');

        if ($request->statut)  $query->where('transferts.statut', $request->statut);
        if ($request->service) $query->where('transferts.service_id', $request->service);

        $transferts  = $query->paginate(10);

        $srvQuery = DB::table('services')->where('actif', 1);
        if (!$user->isGlobal()) $srvQuery->where('etablissement_id', $user->etablissement_id);
        $services = $srvQuery->get();

        $enStockQ = DB::table('declarations')->where('statut', 'en_stock');
        $user->filtreEtab($enStockQ);
        $enStock  = $enStockQ->count();

        return view('stockage.index', compact('transferts', 'services', 'enStock'));
    }

    public function create()
    {
        $user = Auth::user();

        $declarations = DB::table('declarations')
            ->join('services', 'declarations.service_id', '=', 'services.id')
            ->join('type_contenants', 'declarations.type_contenant_id', '=', 'type_contenants.id')
            ->leftJoin('transfert_declarations', 'declarations.id', '=', 'transfert_declarations.declaration_id')
            ->leftJoin('transferts', function ($join) {
                $join->on('transfert_declarations.transfert_id', '=', 'transferts.id')
                     ->whereIn('transferts.statut', ['en_attente', 'valide']);
            })
            ->whereNull('transferts.id')
            ->where('declarations.statut', 'en_stock')
            ->select('declarations.id', 'declarations.nombre_contenants',
                'declarations.poids_estime_kg', 'declarations.date_declaration',
                'services.nom as service_nom', 'type_contenants.nom as contenant_nom')
            ->orderByDesc('declarations.date_declaration');

        $user->filtreEtab($declarations, 'declarations.etablissement_id');
        $declarations = $declarations->paginate(10, ['*'], 'declarations_page');

        $srvQuery = DB::table('services')->where('actif', 1);
        if (!$user->isGlobal()) $srvQuery->where('etablissement_id', $user->etablissement_id);
        $services = $srvQuery->paginate(10, ['*'], 'services_page');

        return view('stockage.create', compact('declarations', 'services'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'service_id'           => 'required|exists:services,id',
            'declaration_ids'      => 'required|array|min:1',
            'declaration_ids.*'    => 'exists:declarations,id',
            'zone_stockage'        => 'nullable|string|max:100',
            'date_limite_collecte' => 'nullable|date|after:today',
            'notes'                => 'nullable|string|max:500',
        ]);

        $user = Auth::user();

        $declarations    = DB::table('declarations')
            ->whereIn('id', $request->declaration_ids)->get();
        $totalContenants = $declarations->sum('nombre_contenants');
        $totalPoids      = $declarations->sum('poids_estime_kg');

        $service = DB::table('services')->find($request->service_id);
        $etabId  = $user->isGlobal()
            ? ($service->etablissement_id ?? $user->etablissement_id)
            : $user->etablissement_id;

        $transfertId = DB::table('transferts')->insertGetId([
            'etablissement_id'     => $etabId,
            'service_id'           => $request->service_id,
            'user_id'              => $user->id,
            'nombre_contenants'    => $totalContenants,
            'poids_total_kg'       => $totalPoids,
            'zone_stockage'        => $request->zone_stockage,
            'date_limite_collecte' => $request->date_limite_collecte,
            'statut'               => 'en_attente',
            'notes'                => $request->notes,
            'date_transfert'       => now(),
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        foreach ($request->declaration_ids as $decId) {
            DB::table('transfert_declarations')->insert([
                'transfert_id'   => $transfertId,
                'declaration_id' => $decId,
            ]);
        }

        // ✅ FIX BUG DATES : utiliser startOfDay() pour obtenir un entier propre
        if ($request->date_limite_collecte) {
            $jours = (int) Carbon::now()->startOfDay()->diffInDays(
                Carbon::parse($request->date_limite_collecte)->startOfDay(),
                false
            );
            if ($jours <= 3) {
                DB::table('alertes')->insert([
                    'etablissement_id' => $etabId,
                    'service_id'       => $request->service_id,
                    'type'             => 'depassement_delai',
                    'niveau'           => $jours <= 1 ? 'danger' : 'warning',
                    'message'          => "Transfert #{$transfertId} : date limite de collecte dans {$jours} jour(s). Zone : " . ($request->zone_stockage ?? 'non précisée'),
                    'lu'               => 0,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }
        }

        return redirect()->route('stockage.show', $transfertId)
            ->with('success', __('stockage.flash_created', [
                'count'  => $totalContenants,
                'weight' => $totalPoids,
            ]));
    }

    public function show($id)
    {
        $user  = Auth::user();
        $query = DB::table('transferts')
            ->join('services', 'transferts.service_id', '=', 'services.id')
            ->join('users', 'transferts.user_id', '=', 'users.id')
            ->select('transferts.*', 'services.nom as service_nom',
                DB::raw("CONCAT(users.prenom,' ',users.nom) as agent_nom"))
            ->where('transferts.id', $id);
        $user->filtreEtab($query, 'transferts.etablissement_id');
        $transfert = $query->firstOrFail();

        $declarations = DB::table('transfert_declarations')
            ->join('declarations', 'transfert_declarations.declaration_id', '=', 'declarations.id')
            ->join('services', 'declarations.service_id', '=', 'services.id')
            ->join('type_contenants', 'declarations.type_contenant_id', '=', 'type_contenants.id')
            ->select('declarations.*', 'services.nom as service_nom', 'type_contenants.nom as contenant_nom')
            ->where('transfert_declarations.transfert_id', $id)->get();

        return view('stockage.show', compact('transfert', 'declarations'));
    }

    public function valider(Request $request, $id)
    {
        $request->validate([
            'zone_stockage'         => 'nullable|string|max:100',
            'signature_responsable' => 'nullable|string',
        ]);

        $user     = Auth::user();
        $query    = DB::table('transferts')->where('id', $id)->where('statut', 'en_attente');
        $user->filtreEtab($query);
        $transfert = $query->firstOrFail();

        DB::table('transferts')->where('id', $id)->update([
            'statut'                  => 'valide',
            'responsable_stockage_id' => $user->id,
            'zone_stockage'           => $request->zone_stockage ?? $transfert->zone_stockage,
            'signature_responsable'   => $request->signature_responsable,
            'updated_at'              => now(),
        ]);

        return redirect()->route('stockage.show', $id)
            ->with('success', __('stockage.flash_validated'));
    }
}
