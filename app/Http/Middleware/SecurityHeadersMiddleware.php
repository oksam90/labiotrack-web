<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * En-têtes de sécurité HTTP appliqués à toutes les réponses web.
 *
 * - Strict-Transport-Security : force HTTPS côté navigateur (prod uniquement,
 *   sur requête sécurisée) — complète URL::forceScheme('https').
 * - X-Content-Type-Options    : bloque le MIME-sniffing.
 * - X-Frame-Options           : anti-clickjacking (pas d'embarquement tiers).
 * - Referrer-Policy           : limite la fuite d'URL vers les tiers.
 *
 * NOTE : la Content-Security-Policy n'est volontairement PAS posée ici. Elle
 * nécessite d'abord la sortie du CDN (jsdelivr) et la suppression des styles/
 * scripts inline (migration Vite — cf. roadmap J+60). L'ajouter maintenant avec
 * 'unsafe-inline' apporterait peu et risquerait de casser l'UI.
 */
class SecurityHeadersMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        if (app()->environment('production') && $request->isSecure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        return $response;
    }
}
