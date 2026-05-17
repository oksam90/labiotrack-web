<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

/**
 * SetLocaleMiddleware
 *
 * Résout la locale de l'utilisateur pour chaque requête HTTP, en suivant
 * l'ordre de priorité :
 *
 *   1. users.locale            (préférence persistée, user authentifié)
 *   2. session('locale')       (sélection en cours, ex: avant authentification)
 *   3. config('app.locale')    (locale par défaut applicative — 'fr')
 *
 * Les locales doivent figurer dans config('app.supported_locales') pour
 * être acceptées, sinon on retombe sur la locale par défaut.
 */
class SetLocaleMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        $supported = config('app.supported_locales', ['fr', 'en']);
        $default   = config('app.locale', 'fr');

        // 1. Préférence utilisateur (si authentifié et locale renseignée)
        if (Auth::check() && Auth::user()->locale && in_array(Auth::user()->locale, $supported, true)) {
            App::setLocale(Auth::user()->locale);
            return $next($request);
        }

        // 2. Locale en session (sélection avant login ou utilisateur anonyme)
        $sessionLocale = $request->session()->get('locale');
        if ($sessionLocale && in_array($sessionLocale, $supported, true)) {
            App::setLocale($sessionLocale);
            return $next($request);
        }

        // 3. Locale par défaut applicative
        App::setLocale($default);
        return $next($request);
    }
}
