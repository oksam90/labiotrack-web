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
 * - Content-Security-Policy   : toutes les ressources viennent de l'origine
 *   du site (plus aucun CDN). 'unsafe-inline' reste nécessaire tant que les
 *   vues contiennent des <script> inline et des onclick/onsubmit ; la
 *   politique bloque néanmoins le chargement de scripts tiers, l'exfiltration
 *   (connect-src 'self'), les <object>/<embed>, le détournement de <base> et
 *   l'envoi de formulaires vers un autre domaine. Étape suivante : nonces +
 *   suppression des gestionnaires inline, puis retrait de 'unsafe-inline'.
 *   Non posée pendant `npm run dev` (le serveur Vite sert depuis un autre port).
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

        if (! \Illuminate\Support\Facades\Vite::isRunningHot()) {
            $response->headers->set('Content-Security-Policy', implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline'",
                "style-src 'self' 'unsafe-inline'",
                "img-src 'self' data: blob:",
                "font-src 'self' data:",
                "connect-src 'self'",
                "object-src 'none'",
                "base-uri 'self'",
                "form-action 'self'",
                "frame-ancestors 'self'",
            ]));
        }

        if (app()->environment('production') && $request->isSecure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        return $response;
    }
}
