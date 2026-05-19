<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\Declaration;
use App\Models\Service;
use App\Models\TypeContenant;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DeclarationController extends Controller
{
    public function index(Request $request)
    {
        $user  = Auth::user();
        // TenantScope appliqué automatiquement via le trait BelongsToTenant
        $query = Declaration::with(['service','typeContenant','user','etablissement'])
            ->orderByDesc('date_declaration');

        if ($request->service_id)        $query->where('service_id', $request->service_id);
        if ($request->statut)            $query->where('statut', $request->statut);
        if ($request->type_contenant_id) $query->where('type_contenant_id', $request->type_contenant_id);
        if ($request->date_debut)        $query->where('date_declaration', '>=', $request->date_debut);
        if ($request->date_fin)          $query->where('date_declaration', '<=', $request->date_fin);
        if ($user->isAgent())            $query->where('user_id', $user->id);

        $declarations   = $query->paginate(10);
        $services       = Service::actif()->orderBy('nom')->get();
        $typeContenants = TypeContenant::orderBy('nom')->get();

        return view('declarations.index', compact('declarations','services','typeContenants'));
    }

    public function create()
    {
        $services       = Service::actif()->orderBy('nom')->get();
        $typeContenants = TypeContenant::orderBy('nom')->get();
        return view('declarations.create', compact('services','typeContenants'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'service_id'        => 'required|exists:services,id',
            'type_contenant_id' => 'required|exists:type_contenants,id',
            'nombre_contenants' => 'required|integer|min:1|max:999',
            'notes'             => 'nullable|string|max:500',
            'photo'             => 'nullable|image|max:5120',
        ]);

        $user      = Auth::user();
        $contenant = TypeContenant::findOrFail($request->type_contenant_id);
        $poids     = $contenant->poids_moyen_kg * $request->nombre_contenants;
        $service   = Service::findOrFail($request->service_id);

        // Pour admin global : utiliser l'établissement du service
        $etabId = $user->isGlobal() ? $service->etablissement_id : $user->etablissement_id;

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('declarations/photos','public');
        }

        $decl = Declaration::withoutGlobalScope(\App\Scopes\TenantScope::class)->create([
            'etablissement_id'  => $etabId,
            'service_id'        => $request->service_id,
            'user_id'           => $user->id,
            'type_contenant_id' => $request->type_contenant_id,
            'nombre_contenants' => $request->nombre_contenants,
            'poids_estime_kg'   => $poids,
            'statut'            => 'en_stock',
            'notes'             => $request->notes,
            'photo'             => $photoPath,
            'date_declaration'  => now()->toDateString(),
            'heure_declaration' => now()->toTimeString(),
        ]);

        // QR code — créer le dossier si absent
        $qrDir = storage_path('app/public/qrcodes');
        if (! is_dir($qrDir)) {
            mkdir($qrDir, 0755, true);
        }
        $qrPath = 'qrcodes/declaration_' . $decl->id . '.svg';
        Storage::disk('public')->put($qrPath, QrCode::format('svg')->size(200)
            ->generate(json_encode(['declaration_id'=>$decl->id,'etablissement_id'=>$etabId])));
        $decl->update(['qr_code' => $qrPath]);

        $this->detecterAnomalieVolume($etabId, $service->id, $poids);

        return redirect()->route('declarations.show', $decl->id)
            ->with('success', __('declarations.flash_created', [
                'count'  => $request->nombre_contenants,
                'weight' => $poids,
            ]));
    }

    public function show($id)
    {
        $declaration = Declaration::with(['service','typeContenant','user','etablissement'])
            ->findOrFail($id);
        $this->authorize('view', $declaration);
        return view('declarations.show', compact('declaration'));
    }

    public function edit($id)
    {
        $declaration    = Declaration::findOrFail($id);
        $this->authorize('update', $declaration);
        $services       = Service::actif()->orderBy('nom')->get();
        $typeContenants = TypeContenant::orderBy('nom')->get();
        return view('declarations.edit', compact('declaration','services','typeContenants'));
    }

    public function update(Request $request, $id)
    {
        $declaration = Declaration::findOrFail($id);
        $this->authorize('update', $declaration);

        $request->validate([
            'service_id'        => 'required|exists:services,id',
            'type_contenant_id' => 'required|exists:type_contenants,id',
            'nombre_contenants' => 'required|integer|min:1',
            'notes'             => 'nullable|string|max:500',
        ]);

        if ($declaration->statut !== 'en_stock') {
            return back()->with('error', __('declarations.errors_edit_collected'));
        }

        $contenant = TypeContenant::findOrFail($request->type_contenant_id);
        $declaration->update([
            'service_id'        => $request->service_id,
            'type_contenant_id' => $request->type_contenant_id,
            'nombre_contenants' => $request->nombre_contenants,
            'poids_estime_kg'   => $contenant->poids_moyen_kg * $request->nombre_contenants,
            'notes'             => $request->notes,
        ]);

        return redirect()->route('declarations.show', $id)->with('success', __('declarations.flash_updated'));
    }

    public function destroy($id)
    {
        $declaration = Declaration::findOrFail($id);
        $this->authorize('delete', $declaration);

        if ($declaration->statut !== 'en_stock') {
            return back()->with('error', __('declarations.errors_delete_processed'));
        }

        $declaration->delete();
        return redirect()->route('declarations.index')->with('success', __('declarations.flash_deleted'));
    }

    public function generateQr($id)
    {
        $declaration = Declaration::findOrFail($id);
        $this->authorize('view', $declaration);

        $qrPath = 'qrcodes/declaration_' . $id . '.svg';
        Storage::disk('public')->put($qrPath, QrCode::format('svg')->size(300)
            ->generate(json_encode(['declaration_id'=>$id,'etablissement_id'=>$declaration->etablissement_id])));
        $declaration->update(['qr_code' => $qrPath]);

        return back()->with('success', __('declarations.flash_qr_generated'));
    }

    private function detecterAnomalieVolume(int $etabId, int $serviceId, float $poids): void
    {
        $moyenne = Declaration::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->where('etablissement_id', $etabId)
            ->where('service_id', $serviceId)
            ->where('date_declaration', '>=', now()->subMonths(3))
            ->avg('poids_estime_kg');

        if ($moyenne && $poids > $moyenne * 1.5) {
            $service = Service::find($serviceId);
            if ($service) {
                \App\Models\Alerte::withoutGlobalScope(\App\Scopes\TenantScope::class)->create([
                    'etablissement_id' => $etabId,
                    'service_id'       => $serviceId,
                    'type'             => 'volume_anormal',
                    'niveau'           => 'warning',
                    'message'          => "Volume anormal au service «{$service->nom}» : {$poids} kg (moyenne: " . round($moyenne,1) . " kg).",
                ]);
            }
        }
    }
}
