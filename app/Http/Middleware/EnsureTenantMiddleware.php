<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Etablissement;

/**
 * EnsureTenantMiddleware
 *
 * Résout le "tenant courant" pour chaque requête authentifiée.
 *
 * Logique par rôle :
 *  - superadmin              → vue globale par défaut, peut zoomer sur n'importe
 *                              quel établissement via session admin_tenant_id
 *  - admin_reseau / admin    → vue limitée à leur réseau, peuvent zoomer sur
 *                              un établissement de LEUR réseau (vérification reseau_id)
 *  - collecteur / prestataire → vue globale, peuvent zoomer sur tout établissement
 *  - qhse / agent            → tenant fixe = leur établissement
 */
class EnsureTenantMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (! Auth::check()) {
            app()->instance('currentTenant', null);
            view()->share('currentTenant', null);
            view()->share('isGlobalView', false);
            return $next($request);
        }

        $user = Auth::user();

        // Switch tenant via paramètre URL (utilisateurs autorisés)
        if ($request->has('switch_tenant') && (int) $request->switch_tenant > 0) {
            $tid = (int) $request->switch_tenant;

            // SECURITY : vérifier que l'utilisateur peut accéder à cet établissement
            if ($user->canAccessTenant($tid)) {
                $request->session()->put('admin_tenant_id', $tid);
                return redirect($request->url())
                    ->with('success', __('common.tenant_view_switched'));
            }
            abort(403, __('common.tenant_access_denied'));
        }

        // Détermination du tenant courant
        if ($user->isGlobal() || $user->isReseauScoped()) {
            $tenantId = $request->session()->get('admin_tenant_id');

            // SECURITY : si zoom session, vérifier toujours l'autorisation
            if ($tenantId && ! $user->canAccessTenant((int) $tenantId)) {
                $request->session()->forget('admin_tenant_id');
                $tenantId = null;
            }

            $tenant = $tenantId ? Etablissement::withoutGlobalScopes()->find($tenantId) : null;
        } else {
            // Utilisateurs locaux (qhse, agent) — chargement direct sans scope
            // pour éviter les filtres TenantScope (l'utilisateur EST son tenant)
            $tenant = $user->etablissement_id
                ? Etablissement::withoutGlobalScopes()->find($user->etablissement_id)
                : null;
        }

        app()->instance('currentTenant', $tenant);
        view()->share('currentTenant', $tenant);
        view()->share('isGlobalView',
            ($user->isGlobal() || $user->isReseauScoped()) && ! $tenant);

        return $next($request);
    }
}
