<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminController extends Controller
{
    // ── Établissements ──────────────────────────────────────────────────────

    public function index()
    {
        $etablissements = DB::table('etablissements')->orderBy('nom')->paginate(10);
        return view('admin.etablissements', compact('etablissements'));
    }

    public function create()
    {
        return view('admin.etablissement_form', ['etablissement' => null]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom'              => 'required|string|max:255',
            'type'             => 'required|in:clinique,hopital,cabinet,laboratoire',
            'adresse'          => 'required|string',
            'ville'            => 'nullable|string|max:255',
            'responsable_qhse' => 'nullable|string|max:255',
            'nombre_lits'      => 'nullable|integer|min:0',
            'email'            => 'nullable|email|unique:etablissements,email',
            'telephone'        => 'nullable|string|max:30',
        ]);

        DB::table('etablissements')->insert(array_merge(
            $request->only(['nom','type','adresse','ville','telephone','email','responsable_qhse','nombre_lits']),
            ['actif' => 1, 'created_at' => now(), 'updated_at' => now()]
        ));

        return redirect()->route('admin.index')->with('success', 'Établissement créé avec succès.');
    }

    public function edit($id)
    {
        $etablissement = DB::table('etablissements')->find($id);
        abort_if(! $etablissement, 404, 'Établissement introuvable.');
        return view('admin.etablissement_form', compact('etablissement'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nom'     => 'required|string|max:255',
            'type'    => 'required|in:clinique,hopital,cabinet,laboratoire',
            'adresse' => 'required|string',
        ]);
        DB::table('etablissements')->where('id', $id)->update(array_merge(
            $request->only(['nom','type','adresse','ville','telephone','email','responsable_qhse','nombre_lits']),
            ['updated_at' => now()]
        ));
        return redirect()->route('admin.index')->with('success', 'Établissement mis à jour.');
    }

    public function destroy($id)
    {
        // Vérification qu'aucun utilisateur actif n'est rattaché
        $usersActifs = DB::table('users')->where('etablissement_id', $id)->where('actif', 1)->count();
        if ($usersActifs > 0) {
            return back()->with('error', "Impossible : {$usersActifs} utilisateur(s) actif(s) rattaché(s). Désactivez-les d'abord.");
        }
        DB::table('etablissements')->where('id', $id)->delete();
        return redirect()->route('admin.index')->with('success', 'Établissement supprimé.');
    }

    public function toggleEtablissement($id)
    {
        // Seul le superadmin peut activer/désactiver un établissement
        abort_if(! Auth::user()->isSuperAdmin(), 403, 'Action réservée au superadmin.');

        $etablissement = DB::table('etablissements')->find($id);
        abort_if(! $etablissement, 404, 'Établissement introuvable.');

        DB::table('etablissements')->where('id', $id)->update([
            'actif'      => $etablissement->actif ? 0 : 1,
            'updated_at' => now(),
        ]);

        $statut = $etablissement->actif ? 'désactivé' : 'activé';
        return back()->with('success', "Établissement {$statut} avec succès.");
    }

    // ── Utilisateurs ────────────────────────────────────────────────────────

    public function utilisateurs()
    {
        $user  = Auth::user();
        $query = DB::table('users')
            ->leftJoin('etablissements', 'users.etablissement_id', '=', 'etablissements.id')
            ->select('users.*', 'etablissements.nom as etablissement_nom')
            ->orderByDesc('users.created_at');

        // Admin local : ne voit que les utilisateurs de son établissement
        if (! $user->isAdminOrSuper() || $user->etablissement_id) {
            $query->where('users.etablissement_id', $user->etablissement_id);
        }

        $users          = $query->paginate(10);
        $etablissements = DB::table('etablissements')->where('actif', 1)->orderBy('nom')->get();
        return view('admin.utilisateurs', compact('users', 'etablissements'));
    }

    public function createUser()
    {
        $etablissements = DB::table('etablissements')->where('actif', 1)->orderBy('nom')->get();
        return view('admin.user_form', ['user' => null, 'etablissements' => $etablissements]);
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'nom'     => 'required|string|max:100',
            'prenom'  => 'required|string|max:100',
            'email'   => 'required|email|unique:users,email',
            'password'=> 'required|min:8|confirmed',
            'role'    => 'required|in:superadmin,admin,qhse,agent,collecteur,prestataire',
        ], [
            'password.required'  => 'Le mot de passe est obligatoire.',
            'password.min'       => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'email.unique'       => 'Cet email est déjà utilisé par un autre compte.',
        ]);

        $user = Auth::user();

        // Admin local : force son propre établissement pour les rôles locaux
        // Admin réseau (sans établissement) et superadmin peuvent choisir librement
        $etabId = $request->etablissement_id ?: null;
        if ($user->etablissement_id && ! in_array($request->role, ['collecteur','prestataire'])) {
            $etabId = $user->etablissement_id;
        }

        DB::table('users')->insert([
            'etablissement_id' => $etabId,
            'nom'              => $request->nom,
            'prenom'           => $request->prenom,
            'email'            => $request->email,
            'password'         => Hash::make($request->password),
            'role'             => $request->role,
            'actif'            => 1,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        return redirect()->route('admin.utilisateurs.index')->with('success', 'Utilisateur créé.');
    }

    public function editUser($id)
    {
        $user           = DB::table('users')->find($id);
        abort_if(! $user, 404);
        $etablissements = DB::table('etablissements')->where('actif', 1)->orderBy('nom')->get();
        return view('admin.user_form', compact('user', 'etablissements'));
    }

    public function updateUser(Request $request, $id)
    {
        $rules = [
            'nom'    => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email'  => 'required|email|unique:users,email,'.$id,
            'role'   => 'required|in:superadmin,admin,qhse,agent,collecteur,prestataire',
        ];

        $messages = [];

        if ($request->filled('password')) {
            $rules['password']              = 'min:8|confirmed';
            $messages['password.min']       = 'Le mot de passe doit contenir au moins 8 caractères.';
            $messages['password.confirmed'] = 'La confirmation du mot de passe ne correspond pas.';
        }

        $request->validate($rules, $messages);

        $data = $request->only(['nom','prenom','email','role','etablissement_id','telephone']);
        $data['etablissement_id'] = $data['etablissement_id'] ?: null;
        $data['telephone']        = $data['telephone'] ?: null;
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
        $data['updated_at'] = now();
        DB::table('users')->where('id', $id)->update($data);
        return redirect()->route('admin.utilisateurs.index')->with('success', 'Utilisateur mis à jour.');
    }

    public function destroyUser($id)
    {
        // Ne pas supprimer son propre compte
        abort_if($id == Auth::id(), 403, 'Vous ne pouvez pas supprimer votre propre compte.');
        DB::table('users')->where('id', $id)->delete();
        return redirect()->route('admin.utilisateurs.index')->with('success', 'Utilisateur supprimé.');
    }

    public function toggleUser($id)
    {
        abort_if($id == Auth::id(), 403, 'Vous ne pouvez pas désactiver votre propre compte.');
        $user = DB::table('users')->find($id);
        abort_if(! $user, 404);
        DB::table('users')->where('id', $id)->update([
            'actif'      => $user->actif ? 0 : 1,
            'updated_at' => now(),
        ]);
        return back()->with('success', 'Statut utilisateur modifié.');
    }

    // ── Services ─────────────────────────────────────────────────────────────

    public function services()
    {
        $user  = Auth::user();

        // Utilisateur global sans établissement → tous les services avec leur établissement
        if ($user->isGlobal() && ! $user->etablissement_id) {
            $services = DB::table('services')
                ->join('etablissements', 'services.etablissement_id', '=', 'etablissements.id')
                ->select('services.*', 'etablissements.nom as etablissement_nom')
                ->orderBy('etablissements.nom')->orderBy('services.nom')
                ->paginate(10);
        } else {
            $etabId   = $user->etablissement_id;
            $services = DB::table('services')
                ->where('etablissement_id', $etabId)
                ->orderBy('nom')->paginate(10);
        }

        $etablissements = DB::table('etablissements')->where('actif', 1)->orderBy('nom')->get();
        return view('admin.services', compact('services', 'etablissements'));
    }

    public function storeService(Request $request)
    {
        $request->validate([
            'nom'            => 'required|string|max:255',
            'etablissement_id' => 'required|exists:etablissements,id',
        ]);

        $user   = Auth::user();
        $etabId = $request->etablissement_id;

        // Admin local : forcer son propre établissement
        if (! $user->isGlobal() && $user->etablissement_id) {
            $etabId = $user->etablissement_id;
        }

        DB::table('services')->insert([
            'etablissement_id' => $etabId,
            'nom'              => $request->nom,
            'description'      => $request->description,
            'responsable'      => $request->responsable,
            'actif'            => 1,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
        return back()->with('success', 'Service créé.');
    }

    public function updateService(Request $request, $id)
    {
        $request->validate(['nom' => 'required|string|max:255']);
        DB::table('services')->where('id', $id)->update([
            'nom'         => $request->nom,
            'description' => $request->description,
            'responsable' => $request->responsable,
            'updated_at'  => now(),
        ]);
        return back()->with('success', 'Service mis à jour.');
    }

    public function destroyService($id)
    {
        // Vérifier qu'aucune déclaration n'est liée
        $count = DB::table('declarations')->where('service_id', $id)->count();
        if ($count > 0) {
            return back()->with('error', "Impossible : {$count} déclaration(s) liée(s) à ce service.");
        }
        DB::table('services')->where('id', $id)->delete();
        return back()->with('success', 'Service supprimé.');
    }

    public function toggleService($id)
    {
        $service = DB::table('services')->find($id);
        abort_if(! $service, 404, 'Service introuvable.');

        DB::table('services')->where('id', $id)->update([
            'actif'      => $service->actif ? 0 : 1,
            'updated_at' => now(),
        ]);

        $statut = $service->actif ? 'désactivé' : 'activé';
        return back()->with('success', "Service {$statut} avec succès.");
    }

    // ── Contenants ───────────────────────────────────────────────────────────

    public function contenants()
    {
        $contenants  = DB::table('type_contenants')
            ->leftJoin('type_dechets','type_contenants.type_dechet_id','=','type_dechets.id')
            ->select('type_contenants.*','type_dechets.nom as type_dechet_nom')
            ->orderBy('type_contenants.nom')->paginate(10);
        $typeDechets = DB::table('type_dechets')->orderBy('nom')->get();
        return view('admin.contenants', compact('contenants', 'typeDechets'));
    }

    public function storeContenant(Request $request)
    {
        $request->validate([
            'nom'            => 'required|string|max:255',
            'code'           => 'required|string|max:50|unique:type_contenants,code',
            'type_dechet_id' => 'required|exists:type_dechets,id',
            'poids_moyen_kg' => 'required|numeric|min:0.01',
            'capacite_litres'=> 'nullable|numeric|min:0',
            'cout_unitaire'  => 'nullable|numeric|min:0',
            'description'    => 'nullable|string|max:1000',
        ]);
        DB::table('type_contenants')->insert(array_merge(
            $request->only(['nom','code','type_dechet_id','poids_moyen_kg','capacite_litres','cout_unitaire','description']),
            ['created_at' => now(), 'updated_at' => now()]
        ));
        return back()->with('success', 'Type de contenant créé.');
    }

    public function updateContenant(Request $request, $id)
    {
        $request->validate([
            'nom'            => 'required|string|max:255',
            'poids_moyen_kg' => 'required|numeric|min:0.01',
            'capacite_litres'=> 'nullable|numeric|min:0',
            'cout_unitaire'  => 'nullable|numeric|min:0',
            'description'    => 'nullable|string|max:1000',
        ]);
        DB::table('type_contenants')->where('id', $id)->update(array_merge(
            $request->only(['nom','poids_moyen_kg','capacite_litres','cout_unitaire','description']),
            ['updated_at' => now()]
        ));
        return back()->with('success', 'Contenant mis à jour.');
    }

    public function destroyContenant($id)
    {
        // Vérifier qu'aucune déclaration n'utilise ce contenant
        $count = DB::table('declarations')->where('type_contenant_id', $id)->count();
        if ($count > 0) {
            return back()->with('error', "Impossible : {$count} déclaration(s) utilisent ce contenant.");
        }
        DB::table('type_contenants')->where('id', $id)->delete();
        return back()->with('success', 'Contenant supprimé avec succès.');
    }

    // ── Activités temps réel ─────────────────────────────────────────────────

    public function activites()
    {
        $stats = [
            'etablissements'     => DB::table('etablissements')->where('actif', 1)->count(),
            'users_actifs'       => DB::table('users')->where('actif', 1)->count(),
            'declarations_today' => DB::table('declarations')->whereDate('created_at', today())->count(),
            'alertes_nonlues'    => DB::table('alertes')->where('lu', 0)->count(),
            'collectes_today'    => DB::table('collectes')->whereDate('created_at', today())->count(),
            'checklists_today'   => DB::table('checklists')->whereDate('created_at', today())->count(),
        ];
        return view('admin.activites', compact('stats'));
    }

    public function activitesData()
    {
        $page    = (int) request('page', 1);
        $perPage = (int) request('per_page', 30);
        $perPage = min($perPage, 100); // Garde-fou : max 100 par page

        // ── Flux d'activités avec pagination LIMIT/OFFSET côté SQL ──
        $source = DB::table('activites_log')->count() > 0
            ? 'activites_log'
            : 'activites_feed';

        $total = DB::table($source)->count();

        $flux = DB::table($source)
            ->select('type', 'moment', 'acteur', 'etablissement',
                     'description', 'niveau', 'user_id', 'etablissement_id')
            ->orderByDesc('moment')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        // ── Stats live : 1 requête agrégée au lieu de 5 ─────────────
        $today = now()->toDateString();
        $statsRaw = DB::selectOne("
            SELECT
                (SELECT COUNT(*) FROM declarations  WHERE DATE(created_at) = ?) AS declarations_today,
                (SELECT COUNT(*) FROM alertes       WHERE lu = 0)               AS alertes_nonlues,
                (SELECT COUNT(*) FROM collectes     WHERE DATE(created_at) = ?) AS collectes_today,
                (SELECT COUNT(*) FROM checklists    WHERE DATE(created_at) = ?) AS checklists_today,
                (SELECT COUNT(*) FROM sessions      WHERE last_activity >= ?)   AS users_connectes
        ", [$today, $today, $today, now()->subMinutes(15)->timestamp]);

        $statsLive = (array) $statsRaw;

        return response()->json([
            'flux'  => $flux,
            'stats' => $statsLive,
            'pagination' => [
                'current_page' => $page,
                'per_page'     => $perPage,
                'total'        => $total,
                'last_page'    => (int) ceil($total / $perPage),
            ],
        ]);
    }
}
