<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;

class ApiAuthentication
{
    public function handle(Request $request, Closure $next)
    {
        try {
            return $next($request);
        } catch (AuthenticationException $e) {
            return response()->json([
                'message' => 'Token de acceso requerido o inválido',
                'error' => 'Unauthorized',
                'status' => false,
                'code' => 401
            ], 401);
        }
    }
}