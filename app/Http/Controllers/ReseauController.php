<?php

namespace App\Http\Controllers;

use App\Models\Reseau;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * ReseauController — gestion CRUD des réseaux (superadmin uniquement).
 *
 * Le réseau est un regroupement logique d'établissements géré par
 * un AdminRéseau. Le superadmin crée le réseau, y rattache les
 * établissements, et désigne un AdminRéseau.
 */
class ReseauController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $reseaux = Reseau::withCount(['etablissements', 'users'])
            ->orderBy('nom')
            ->paginate(15);

        return view('reseaux.index', compact('reseaux'));
    }

    public function create()
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);
        return view('reseaux.form', ['reseau' => new Reseau()]);
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $data = $request->validate([
            'nom'               => 'required|string|max:150',
            'description'       => 'nullable|string',
            'contact_email'     => 'nullable|email|max:255',
            'contact_telephone' => 'nullable|string|max:30',
            'actif'             => 'nullable|boolean',
        ], [], [
            'nom' => 'nom du réseau',
        ]);

        $data['slug']  = Str::slug($data['nom']) . '-' . now()->timestamp;
        $data['actif'] = (bool) ($data['actif'] ?? true);

        Reseau::create($data);

        return redirect()->route('reseaux.index')
            ->with('success', __('reseaux.flash_created'));
    }

    public function show($id)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);
        $reseau = Reseau::with(['etablissements', 'admins'])->findOrFail($id);
        return view('reseaux.show', compact('reseau'));
    }

    public function edit($id)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);
        $reseau = Reseau::findOrFail($id);
        return view('reseaux.form', compact('reseau'));
    }

    public function update(Request $request, $id)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);
        $reseau = Reseau::findOrFail($id);

        $data = $request->validate([
            'nom'               => 'required|string|max:150',
            'description'       => 'nullable|string',
            'contact_email'     => 'nullable|email|max:255',
            'contact_telephone' => 'nullable|string|max:30',
            'actif'             => 'nullable|boolean',
        ]);

        $data['actif'] = (bool) ($data['actif'] ?? false);
        $reseau->update($data);

        return redirect()->route('reseaux.index')
            ->with('success', __('reseaux.flash_updated'));
    }

    public function destroy($id)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);
        $reseau = Reseau::findOrFail($id);

        if ($reseau->etablissements()->count() > 0) {
            return back()->with('error', __('reseaux.errors_delete_has_etabs'));
        }

        // Intégrité : des utilisateurs peuvent être rattachés directement au
        // réseau (collecteur, prestataire, admin_reseau via users.reseau_id).
        // La FK étant en ON DELETE SET NULL, les supprimer orphelinerait ces
        // comptes (reseau_id = NULL) qui, étant réseau-scopés (fail-closed),
        // n'auraient alors plus accès à rien. On bloque tant qu'ils ne sont
        // pas réaffectés à un autre réseau ou supprimés.
        if ($reseau->users()->count() > 0) {
            return back()->with('error', __('reseaux.errors_delete_has_users'));
        }

        $reseau->delete();
        return redirect()->route('reseaux.index')
            ->with('success', __('reseaux.flash_deleted'));
    }

    public function toggle($id)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);
        $reseau = Reseau::findOrFail($id);
        $reseau->update(['actif' => ! $reseau->actif]);
        return back()->with('success',
            $reseau->actif ? 'Réseau activé.' : 'Réseau désactivé.');
    }
}
