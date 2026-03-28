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
 *  - superadmin / admin     → vue globale par défaut, peuvent zoomer via session
 *  - collecteur / prestataire → vue globale également (sans établissement fixe),
 *                               peuvent zoomer sur une structure via session
 *  - qhse / agent           → tenant fixe = leur établissement
 */
class EnsureTenantMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        // Pas d'utilisateur connecté → lier null pour éviter BindingResolutionException
        if (! Auth::check()) {
            app()->instance('currentTenant', null);
            view()->share('currentTenant', null);
            view()->share('isGlobalView', false);
            return $next($request);
        }

        $user = Auth::user();

        if ($user->isGlobal()) {
            // Utilisateurs globaux : peuvent switcher vers une structure via paramètre URL
            if ($request->has('switch_tenant') && (int) $request->switch_tenant > 0) {
                $tid = (int) $request->switch_tenant;
                $request->session()->put('admin_tenant_id', $tid);
                return redirect($request->url())
                    ->with('success', 'Vous visualisez maintenant les données de cette structure.');
            }

            $tenantId = $request->session()->get('admin_tenant_id');
            $tenant   = $tenantId ? Etablissement::find($tenantId) : null;
        } else {
            // Utilisateurs locaux (qhse, agent) → tenant = leur établissement
            $tenant = $user->etablissement;
        }

        // Toujours lier dans le conteneur IoC (même si null)
        app()->instance('currentTenant', $tenant);
        view()->share('currentTenant', $tenant);
        view()->share('isGlobalView', $user->isGlobal() && ! $tenant);

        return $next($request);
    }
}
