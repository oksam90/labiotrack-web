<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Helper : restreint un query DB::table() aux établissements du
     * réseau de l'utilisateur connecté. SUPERADMIN/global → pas de filtre.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param string $col Colonne id ou etablissement_id
     */
    private function scopeReseau($query, string $col = 'id')
    {
        $user = Auth::user();
        if ($user->isSuperAdmin()) return $query;

        if ($user->isReseauScoped() && $user->reseau_id) {
            if ($col === 'id') {
                // Sur la table etablissements directement
                return $query->where('reseau_id', $user->reseau_id);
            }
            // Sur autre table → sous-requête sur établissements du réseau
            return $query->whereIn($col, function ($q) use ($user) {
                $q->select('id')->from('etablissements')->where('reseau_id', $user->reseau_id);
            });
        }
        return $query;
    }

    // ── Établissements ──────────────────────────────────────────────────────

    public function index()
    {
        $query = DB::table('etablissements')
            ->leftJoin('reseaux', 'etablissements.reseau_id', '=', 'reseaux.id')
            ->select('etablissements.*', 'reseaux.nom as reseau_nom');

        // Filtrer par réseau pour admin_reseau / admin
        $this->scopeReseau($query, 'etablissements.id');

        $etablissements = $query->orderBy('etablissements.nom')->paginate(10);
        return view('admin.etablissements', compact('etablissements'));
    }

    public function create()
    {
        $reseaux = $this->reseauxAccessibles();
        return view('admin.etablissement_form', [
            'etablissement' => null,
            'reseaux'       => $reseaux,
        ]);
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
            'reseau_id'        => 'nullable|exists:reseaux,id',
        ]);

        $user      = Auth::user();
        $reseauId  = $request->reseau_id ?: null;
        // AdminRéseau : forcer son propre réseau
        if ($user->isAdminReseau() && $user->reseau_id) {
            $reseauId = $user->reseau_id;
        }

        DB::table('etablissements')->insert(array_merge(
            $request->only(['nom','type','adresse','ville','telephone','email','responsable_qhse','nombre_lits']),
            ['reseau_id' => $reseauId, 'actif' => 1, 'created_at' => now(), 'updated_at' => now()]
        ));

        return redirect()->route('admin.index')->with('success', __('admin.flash_etab_created'));
    }

    public function edit($id)
    {
        $etablissement = DB::table('etablissements')->find($id);
        abort_if(! $etablissement, 404, __('admin.errors_etab_not_found'));

        // SECURITY : vérifier accès réseau
        $user = Auth::user();
        if (! $user->isSuperAdmin()) {
            if ($user->isAdminReseau() && $etablissement->reseau_id !== $user->reseau_id) {
                abort(403, __('admin.errors_admin_reseau_etab_not_in_network'));
            }
        }

        $reseaux = $this->reseauxAccessibles();
        return view('admin.etablissement_form', compact('etablissement', 'reseaux'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nom'       => 'required|string|max:255',
            'type'      => 'required|in:clinique,hopital,cabinet,laboratoire',
            'adresse'   => 'required|string',
            'reseau_id' => 'nullable|exists:reseaux,id',
        ]);

        $user = Auth::user();
        $data = $request->only(['nom','type','adresse','ville','telephone','email','responsable_qhse','nombre_lits']);

        // Seul le superadmin peut changer le rattachement réseau
        if ($user->isSuperAdmin()) {
            $data['reseau_id'] = $request->reseau_id ?: null;
        }
        $data['updated_at'] = now();

        DB::table('etablissements')->where('id', $id)->update($data);
        return redirect()->route('admin.index')->with('success', __('admin.flash_etab_updated'));
    }

    public function destroy($id)
    {
        // EtablissementPolicy → suppression réservée superadmin
        abort_unless(Auth::user()->isSuperAdmin(), 403, __('admin.errors_superadmin_only'));

        $usersActifs = DB::table('users')->where('etablissement_id', $id)->where('actif', 1)->count();
        if ($usersActifs > 0) {
            return back()->with('error', __('admin.flash_etab_delete_blocked', ['count' => $usersActifs]));
        }
        DB::table('etablissements')->where('id', $id)->delete();
        return redirect()->route('admin.index')->with('success', __('admin.flash_etab_deleted'));
    }

    public function toggleEtablissement($id)
    {
        // Seul le superadmin peut activer/désactiver un établissement
        abort_if(! Auth::user()->isSuperAdmin(), 403, __('admin.errors_superadmin_only'));

        $etablissement = DB::table('etablissements')->find($id);
        abort_if(! $etablissement, 404, __('admin.errors_etab_not_found'));

        DB::table('etablissements')->where('id', $id)->update([
            'actif'      => $etablissement->actif ? 0 : 1,
            'updated_at' => now(),
        ]);

        $statut = $etablissement->actif
            ? __('admin.flash_etab_status_deactivated')
            : __('admin.flash_etab_status_activated');
        return back()->with('success', __('admin.flash_etab_toggled', ['status' => $statut]));
    }

    /**
     * Liste les réseaux accessibles à l'utilisateur courant (pour les selects).
     */
    private function reseauxAccessibles()
    {
        $user = Auth::user();
        $q = DB::table('reseaux')->where('actif', 1)->orderBy('nom');
        if ($user->isAdminReseau() && $user->reseau_id) {
            $q->where('id', $user->reseau_id);
        }
        return $q->get();
    }

    // ── Utilisateurs ────────────────────────────────────────────────────────

    public function utilisateurs()
    {
        $user  = Auth::user();
        $query = DB::table('users')
            ->leftJoin('etablissements', 'users.etablissement_id', '=', 'etablissements.id')
            // Le réseau se résout soit directement via users.reseau_id (collecteur,
            // prestataire, admin_reseau — sans établissement fixe), soit via
            // l'établissement de rattachement (qhse, agent, admin local).
            ->leftJoin('reseaux', 'reseaux.id', '=', DB::raw('COALESCE(users.reseau_id, etablissements.reseau_id)'))
            ->select('users.*', 'etablissements.nom as etablissement_nom', 'reseaux.nom as reseau_nom')
            ->orderByDesc('users.created_at');

        // SUPERADMIN → tous les utilisateurs
        // ADMIN_RESEAU → utilisateurs du réseau (via etablissement.reseau_id ou users.reseau_id)
        // ADMIN local → utilisateurs de son établissement
        if ($user->isAdminReseau() && $user->reseau_id) {
            $query->where(function ($q) use ($user) {
                $q->where('users.reseau_id', $user->reseau_id)
                  ->orWhereIn('users.etablissement_id', function ($sub) use ($user) {
                      $sub->select('id')->from('etablissements')->where('reseau_id', $user->reseau_id);
                  });
            });
        } elseif (! $user->isSuperAdmin() && $user->etablissement_id) {
            $query->where('users.etablissement_id', $user->etablissement_id);
        }

        $users          = $query->paginate(10);
        $etablissements = $this->etablissementsAccessibles();
        return view('admin.utilisateurs', compact('users', 'etablissements'));
    }

    private function etablissementsAccessibles()
    {
        $user = Auth::user();
        $q = DB::table('etablissements')->where('actif', 1)->orderBy('nom');
        if ($user->isAdminReseau() && $user->reseau_id) {
            $q->where('reseau_id', $user->reseau_id);
        }
        return $q->get();
    }

    public function createUser()
    {
        $etablissements = $this->etablissementsAccessibles();
        $reseaux        = $this->reseauxAccessibles();
        return view('admin.user_form', [
            'user'           => null,
            'etablissements' => $etablissements,
            'reseaux'        => $reseaux,
        ]);
    }

    public function storeUser(Request $request)
    {
        // Messages custom centralisés dans lang/{fr,en}/validation.php
        // (sections 'custom' et 'attributes' — i18n FR/EN)
        $request->validate([
            'nom'      => 'required|string|max:100',
            'prenom'   => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'role'     => 'required|in:superadmin,admin,admin_reseau,qhse,agent,collecteur,prestataire,client_signataire',
            'reseau_id'        => 'nullable|exists:reseaux,id',
            'etablissement_id' => 'nullable|exists:etablissements,id',
        ]);

        $user = Auth::user();

        // SECURITY : seul le superadmin peut créer un AdminRéseau
        if ($request->role === 'admin_reseau' && ! $user->isSuperAdmin()) {
            abort(403, __('admin.errors_admin_reseau_create_superadmin_only'));
        }

        $etabId   = $request->etablissement_id ?: null;
        $reseauId = $request->reseau_id ?: null;

        // AdminRéseau créant un user → forcer le rattachement à son réseau
        if ($user->isAdminReseau()) {
            $reseauId = $user->reseau_id;
            // Vérifier que l'établissement choisi appartient à son réseau
            if ($etabId) {
                $etab = DB::table('etablissements')->find($etabId);
                abort_unless($etab && $etab->reseau_id === $user->reseau_id, 403,
                    __('admin.errors_admin_reseau_etab_not_in_network'));
            }
        }

        // Admin local : force son établissement pour les rôles locaux
        if ($user->isAdmin() && ! $user->isSuperAdmin() && ! $user->isAdminReseau()
            && $user->etablissement_id
            && ! in_array($request->role, ['collecteur','prestataire','admin_reseau','superadmin'])) {
            $etabId = $user->etablissement_id;
        }

        // client_signataire : SÉCURITÉ — un établissement est obligatoire
        // (sa fonction unique = signer pour son étab).
        if ($request->role === 'client_signataire' && ! $etabId) {
            abort(422, __('admin.errors_client_signataire_needs_etab'));
        }

        // collecteur / prestataire : SÉCURITÉ — rattachement réseau obligatoire.
        // Leur périmètre = tous les établissements de CE réseau ; sans réseau
        // ils ne verraient rien (fail-closed) → on bloque à la création.
        if (in_array($request->role, ['collecteur', 'prestataire'], true) && ! $reseauId) {
            abort(422, __('admin.errors_collecteur_needs_reseau'));
        }

        DB::table('users')->insert([
            'etablissement_id' => $etabId,
            'reseau_id'        => $reseauId,
            'nom'              => $request->nom,
            'prenom'           => $request->prenom,
            'email'            => $request->email,
            'password'         => Hash::make($request->password),
            'role'             => $request->role,
            'telephone'        => $request->telephone ?: null,
            'actif'            => 1,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        return redirect()->route('admin.utilisateurs.index')->with('success', __('admin.flash_user_created'));
    }

    public function editUser($id)
    {
        $user           = DB::table('users')->find($id);
        abort_if(! $user, 404);

        // SECURITY : AdminRéseau ne peut éditer que les utilisateurs de son réseau
        $current = Auth::user();
        if ($current->isAdminReseau() && $current->reseau_id) {
            $allowed = $user->reseau_id === $current->reseau_id;
            if (! $allowed && $user->etablissement_id) {
                $etab = DB::table('etablissements')->find($user->etablissement_id);
                $allowed = $etab && $etab->reseau_id === $current->reseau_id;
            }
            abort_unless($allowed, 403, __('admin.errors_user_not_in_network'));
        }

        $etablissements = $this->etablissementsAccessibles();
        $reseaux        = $this->reseauxAccessibles();
        return view('admin.user_form', compact('user', 'etablissements', 'reseaux'));
    }

    public function updateUser(Request $request, $id)
    {
        $rules = [
            'nom'      => 'required|string|max:100',
            'prenom'   => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email,'.$id,
            'role'     => 'required|in:superadmin,admin,admin_reseau,qhse,agent,collecteur,prestataire,client_signataire',
            'reseau_id'        => 'nullable|exists:reseaux,id',
            'etablissement_id' => 'nullable|exists:etablissements,id',
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'min:8|confirmed';
        }

        // Messages custom : voir lang/{fr,en}/validation.php (sections
        // 'custom' et 'attributes' — pas besoin de passer $messages ici)
        $request->validate($rules);

        // SECURITY : seul superadmin peut promouvoir admin_reseau
        if ($request->role === 'admin_reseau' && ! Auth::user()->isSuperAdmin()) {
            abort(403, __('admin.errors_admin_reseau_promote_superadmin_only'));
        }

        $data = $request->only(['nom','prenom','email','role','etablissement_id','reseau_id','telephone']);
        $data['etablissement_id'] = $data['etablissement_id'] ?: null;
        $data['reseau_id']        = $data['reseau_id'] ?: null;
        $data['telephone']        = $data['telephone'] ?: null;

        // collecteur / prestataire : rattachement réseau obligatoire (cf. storeUser)
        if (in_array($request->role, ['collecteur', 'prestataire'], true) && ! $data['reseau_id']) {
            abort(422, __('admin.errors_collecteur_needs_reseau'));
        }
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
        $data['updated_at'] = now();
        DB::table('users')->where('id', $id)->update($data);
        return redirect()->route('admin.utilisateurs.index')->with('success', __('admin.flash_user_updated'));
    }

    public function destroyUser($id)
    {
        // Ne pas supprimer son propre compte
        abort_if($id == Auth::id(), 403, __('admin.errors_cant_delete_self'));
        DB::table('users')->where('id', $id)->delete();
        return redirect()->route('admin.utilisateurs.index')->with('success', __('admin.flash_user_deleted'));
    }

    public function toggleUser($id)
    {
        abort_if($id == Auth::id(), 403, __('admin.errors_cant_deactivate_self'));
        $user = DB::table('users')->find($id);
        abort_if(! $user, 404);
        DB::table('users')->where('id', $id)->update([
            'actif'      => $user->actif ? 0 : 1,
            'updated_at' => now(),
        ]);
        return back()->with('success', __('admin.flash_user_status_changed'));
    }

    // ── Services ─────────────────────────────────────────────────────────────

    public function services()
    {
        $user  = Auth::user();

        $query = DB::table('services')
            ->join('etablissements', 'services.etablissement_id', '=', 'etablissements.id')
            ->select('services.*', 'etablissements.nom as etablissement_nom');

        if ($user->isAdminReseau() && $user->reseau_id) {
            $query->where('etablissements.reseau_id', $user->reseau_id);
        } elseif (! $user->isSuperAdmin() && ! $user->isGlobal() && $user->etablissement_id) {
            $query->where('services.etablissement_id', $user->etablissement_id);
        }

        $services       = $query->orderBy('etablissements.nom')->orderBy('services.nom')->paginate(10);
        $etablissements = $this->etablissementsAccessibles();
        return view('admin.services', compact('services', 'etablissements'));
    }

    public function storeService(Request $request)
    {
        $request->validate([
            'nom'              => 'required|string|max:255',
            'etablissement_id' => 'required|exists:etablissements,id',
        ]);

        $user   = Auth::user();
        $etabId = $request->etablissement_id;

        // SECURITY : AdminRéseau → vérifier que l'étab appartient à son réseau
        if ($user->isAdminReseau() && $user->reseau_id) {
            $etab = DB::table('etablissements')->find($etabId);
            abort_unless($etab && $etab->reseau_id === $user->reseau_id, 403,
                __('admin.errors_admin_reseau_etab_not_in_network'));
        }

        // Admin local : forcer son propre établissement
        if (! $user->isGlobal() && ! $user->isAdminReseau() && $user->etablissement_id) {
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
        return back()->with('success', __('admin.flash_service_created'));
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
        return back()->with('success', __('admin.flash_service_updated'));
    }

    public function destroyService($id)
    {
        $count = DB::table('declarations')->where('service_id', $id)->count();
        if ($count > 0) {
            return back()->with('error', __('admin.flash_service_delete_blocked', ['count' => $count]));
        }
        DB::table('services')->where('id', $id)->delete();
        return back()->with('success', __('admin.flash_service_deleted'));
    }

    public function toggleService($id)
    {
        $service = DB::table('services')->find($id);
        abort_if(! $service, 404, __('admin.errors_service_not_found'));

        DB::table('services')->where('id', $id)->update([
            'actif'      => $service->actif ? 0 : 1,
            'updated_at' => now(),
        ]);

        $statut = $service->actif
            ? __('admin.flash_etab_status_deactivated')
            : __('admin.flash_etab_status_activated');
        return back()->with('success', __('admin.flash_service_toggled', ['status' => $statut]));
    }

    // ── Contenants ─────────────────────────────────────────────────────────
    // NB : référentiel global, lecture seule pour AdminRéseau (CRUD : superadmin)

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
        // Référentiel global : seul superadmin peut créer
        abort_unless(Auth::user()->isSuperAdmin(), 403, __('admin.errors_containers_readonly'));

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
        return back()->with('success', __('admin.flash_container_created'));
    }

    public function updateContenant(Request $request, $id)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403, __('admin.errors_containers_readonly'));

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
        return back()->with('success', __('admin.flash_container_updated'));
    }

    public function destroyContenant($id)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403, __('admin.errors_containers_readonly'));

        $count = DB::table('declarations')->where('type_contenant_id', $id)->count();
        if ($count > 0) {
            return back()->with('error', __('admin.flash_container_delete_blocked', ['count' => $count]));
        }
        DB::table('type_contenants')->where('id', $id)->delete();
        return back()->with('success', __('admin.flash_container_deleted'));
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
        $perPage = min($perPage, 100);

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
