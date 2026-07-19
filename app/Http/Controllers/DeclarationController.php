<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Http\Requests\StoreDeclarationRequest;
use App\Http\Requests\UpdateDeclarationRequest;
use App\Models\Declaration;
use App\Models\Service;
use App\Models\TypeContenant;
use App\Services\DeclarationService;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DeclarationController extends Controller
{
    public function index(Request $request)
    {
        $user  = Auth::user();
        // TenantScope appliqué automatiquement via le trait BelongsToTenant
        $query = Declaration::with(['lignes.service','lignes.typeContenant','user','etablissement'])
            ->orderByDesc('date_declaration');

        // Filtres service / contenant : portent sur les lignes de détail
        if ($request->service_id) {
            $query->whereHas('lignes', fn ($q) => $q->where('service_id', $request->service_id));
        }
        if ($request->type_contenant_id) {
            $query->whereHas('lignes', fn ($q) => $q->where('type_contenant_id', $request->type_contenant_id));
        }
        if ($request->statut)            $query->where('statut', $request->statut);
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

    public function store(StoreDeclarationRequest $request, DeclarationService $service)
    {
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('declarations/photos', 'public');
        }

        $decl = $service->create(
            Auth::user(),
            $request->validated()['lignes'],
            $request->input('notes'),
            $photoPath
        );

        $this->genererQrCode($decl);

        return redirect()->route('declarations.show', $decl->id)
            ->with('success', __('declarations.flash_created', [
                'count'  => $decl->nombre_contenants,
                'weight' => $decl->poids_estime_kg,
            ]));
    }

    public function show($id)
    {
        $declaration = Declaration::with(['lignes.service','lignes.typeContenant','user','etablissement'])
            ->findOrFail($id);
        $this->authorize('view', $declaration);
        return view('declarations.show', compact('declaration'));
    }

    public function edit($id)
    {
        $declaration    = Declaration::with('lignes')->findOrFail($id);
        $this->authorize('update', $declaration);
        $services       = Service::actif()->orderBy('nom')->get();
        $typeContenants = TypeContenant::orderBy('nom')->get();
        return view('declarations.edit', compact('declaration','services','typeContenants'));
    }

    public function update(UpdateDeclarationRequest $request, DeclarationService $service, $id)
    {
        $declaration = Declaration::findOrFail($id);
        $this->authorize('update', $declaration);

        if ($declaration->statut !== 'en_stock') {
            return back()->with('error', __('declarations.errors_edit_collected'));
        }

        $service->update($declaration, $request->validated()['lignes'], $request->input('notes'));

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

        $this->genererQrCode($declaration, 300);

        return back()->with('success', __('declarations.flash_qr_generated'));
    }

    /**
     * Génère (ou régénère) le QR code SVG de traçabilité d'une déclaration
     * et met à jour son champ qr_code. Factorisé entre store() et generateQr().
     */
    private function genererQrCode(Declaration $declaration, int $size = 200): void
    {
        $qrDir = storage_path('app/public/qrcodes');
        if (! is_dir($qrDir)) {
            mkdir($qrDir, 0755, true);
        }

        $qrPath = 'qrcodes/declaration_' . $declaration->id . '.svg';
        Storage::disk('public')->put($qrPath, QrCode::format('svg')->size($size)
            ->generate(json_encode([
                'declaration_id'   => $declaration->id,
                'etablissement_id' => $declaration->etablissement_id,
            ])));

        $declaration->update(['qr_code' => $qrPath]);
    }
}
