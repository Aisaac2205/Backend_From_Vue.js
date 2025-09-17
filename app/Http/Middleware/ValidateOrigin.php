<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ValidateOrigin
{
    /**
     * Maneja una solicitud entrante.
     * Permite solo el origen configurado en FRONTEND_URL.
     */
    public function handle(Request $request, Closure $next)
    {
        $allowedOrigin = env('FRONTEND_URL', 'http://localhost:5173');
        $origin = $request->headers->get('origin');

        if ($origin && $origin !== $allowedOrigin) {
            return response()->json([
                'message' => 'Origen no permitido.'
            ], 403);
        }
        return $next($request);
    }
}
