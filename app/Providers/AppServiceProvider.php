<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Declaration;
use App\Models\DeclarationLigne;
use App\Models\Collecte;
use App\Models\Destruction;
use App\Models\Checklist;
use App\Models\Etablissement;
use App\Models\Signature;
use App\Policies\DeclarationPolicy;
use App\Policies\CollectePolicy;
use App\Policies\DestructionPolicy;
use App\Policies\ChecklistPolicy;
use App\Policies\EtablissementPolicy;
use App\Policies\SignaturePolicy;
use App\Models\Alerte;
use App\Observers\DeclarationObserver;
use App\Observers\DeclarationLigneObserver;
use App\Observers\CollecteObserver;
use App\Observers\ChecklistObserver;
use App\Observers\AlerteObserver;
use App\Observers\DestructionObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Enregistrement des bindings du conteneur.
     * NOTE : NE PAS faire singleton('currentTenant') ici.
     * Le tenant est résolu à chaque requête par EnsureTenantMiddleware
     * via app()->instance('currentTenant', $tenant) qui écrase à chaque appel.
     * Un singleton cacherait la valeur entre les requêtes en mode Octane/queue.
     */
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ── Forcer HTTPS derrière le reverse proxy (production)
        if (config('app.env') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // ── Pagination Bootstrap 5 (le layout utilise Bootstrap, pas Tailwind)
        Paginator::useBootstrapFive();

        // ── Rate limiter « login » (anti-brute-force) ────────
        // 5 essais/min par couple (email + IP) + garde-fou 20/min par IP
        // pour ralentir le credential-stuffing distribué sur plusieurs comptes.
        RateLimiter::for('login', function (Request $request) {
            $key = Str::lower((string) $request->input('email')) . '|' . $request->ip();
            return [
                Limit::perMinute(5)->by($key),
                Limit::perMinute(20)->by($request->ip()),
            ];
        });

        // ── Observers (activites_log matérialisée) ───────────
        Declaration::observe(DeclarationObserver::class);
        DeclarationLigne::observe(DeclarationLigneObserver::class);
        Collecte::observe(CollecteObserver::class);
        Checklist::observe(ChecklistObserver::class);
        Alerte::observe(AlerteObserver::class);
        Destruction::observe(DestructionObserver::class);

        // ── Policies (modèle �� classe policy) ────────────────
        Gate::policy(Declaration::class,   DeclarationPolicy::class);
        Gate::policy(Collecte::class,      CollectePolicy::class);
        Gate::policy(Destruction::class,   DestructionPolicy::class);
        Gate::policy(Checklist::class,     ChecklistPolicy::class);
        Gate::policy(Etablissement::class, EtablissementPolicy::class);
        Gate::policy(Signature::class,     SignaturePolicy::class);

        // ── Gates simples (vérifications rapides sans modèle) ─
        Gate::define('access-admin', fn($user) =>
            $user->isAdminOrSuper()
        );

        Gate::define('access-qhse', fn($user) =>
            in_array($user->role, ['admin','superadmin','qhse','prestataire'])
        );

        Gate::define('manage-users', fn($user) =>
            $user->isAdminOrSuper()
        );

        Gate::define('switch-tenant', fn($user) =>
            $user->isGlobal()
        );
    }
}
