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
        //
    })->create();
