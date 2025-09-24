<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     * Agrega headers de seguridad para producción.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Solo aplicar headers de seguridad si está habilitado
        if (env('SECURITY_HEADERS', false)) {
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('X-Frame-Options', 'DENY');
            $response->headers->set('X-XSS-Protection', '1; mode=block');
            $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
            $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
            
            // Content Security Policy para APIs
            $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'none'; object-src 'none';");
            
            // HSTS solo si HTTPS está forzado
            if (env('FORCE_HTTPS', false)) {
                $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
            }
        }
        
        return $response;
    }
}
