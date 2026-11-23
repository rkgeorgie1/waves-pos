<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddSecurityHeaders
{
    /**
     * Add baseline browser protections to every response.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');

        $allowedFormActions = array_values(array_unique(array_filter([
            "'self'",
            $request->getSchemeAndHttpHost(),
            rtrim((string) config('app.url'), '/'),
        ])));

        $response->headers->set('Content-Security-Policy', implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            "frame-ancestors 'self'",
            'form-action '.implode(' ', $allowedFormActions),
            "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net",
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com https://fonts.bunny.net",
            "font-src 'self' data: https://cdn.jsdelivr.net https://fonts.gstatic.com https://fonts.bunny.net",
            "img-src 'self' data: blob: https:",
            "connect-src 'self'",
        ]));

        $forwardedProtocol = strtolower((string) $request->headers->get('X-Forwarded-Proto'));
        $forwardedProtocol = trim(explode(',', $forwardedProtocol)[0]);

        if ($request->secure() || in_array($forwardedProtocol, ['https', 'on'], true)) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
