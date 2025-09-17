<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class IdentifyTenant
{
    /**
     * Maneja la solicitud entrante y configura la conexión de base de datos según el subdominio.
     */
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost(); // ejemplo: empresa1.midominio.com
        $subdomain = explode('.', $host)[0];

        // Puedes personalizar la lógica para extraer el subdominio según tu dominio base
        if (!$subdomain || $subdomain === 'www' || $subdomain === 'midominio') {
            Log::warning('Tenant no especificado o inválido', ['host' => $host]);
            abort(404, 'Tenant no encontrado');
        }

        // Configura la conexión dinámica
        $connectionName = 'tenant_' . $subdomain;
        config(['database.default' => $connectionName]);

        // Opcional: log para debug
        Log::info('Tenant detectado', ['tenant' => $subdomain, 'connection' => $connectionName]);

        return $next($request);
    }
}
