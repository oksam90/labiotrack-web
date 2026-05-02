<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ✅ FIX #5 — EnsureTenantMiddleware était enregistré deux fois :
        //   1) En global via web(append:[...]) → s'exécutait sur TOUTES les routes web
        //      y compris /login et /logout, avant même Auth::check()
        //   2) En alias 'tenant' → utilisé dans les routes protégées
        //
        // Correction : on le retire du stack global web et on le garde UNIQUEMENT
        // comme alias. Il est déjà appliqué via Route::middleware(['auth','tenant'])
        // dans web.php, ce qui est l'endroit correct (après auth).

        $middleware->alias([
            'role'   => \App\Http\Middleware\RoleMiddleware::class,
            'tenant' => \App\Http\Middleware\EnsureTenantMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // ── 419 Page Expired ──────────────────────────────────────
        // Laravel jette TokenMismatchException quand le CSRF expire :
        //  - onglet de login resté ouvert > SESSION_LIFETIME
        //  - bouton « précédent » du navigateur après un logout
        //  - multi-onglets (regenerate() invalide les autres tabs)
        //
        // ATTENTION : Laravel convertit TokenMismatchException en
        // HttpException(419) via prepareException() AVANT les renderables.
        // Il faut donc matcher HttpException et filtrer sur status 419.
        //
        // Au lieu d'afficher la page brute « 419 PAGE EXPIRED », on
        // redirige vers /login avec un message explicite.
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            if ($e->getStatusCode() !== 419) {
                return null; // laisse Laravel gérer les autres HttpException
            }
            return redirect()
                ->route('login')
                ->withInput($request->except(['password', '_token']))
                ->with('warning', 'Votre session a expiré pour des raisons de sécurité. Veuillez vous reconnecter.');
        });
    })->create();
