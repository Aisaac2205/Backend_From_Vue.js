<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Services\TenantService;

/**
 * Middleware TenantResolver para demostración de arquitectura multitenant
 * 
 * NOTA: Este middleware es solo para demostración académica.
 * NO está registrado ni implementado en el sistema actual.
 * NO afecta la funcionalidad existente.
 */
class TenantResolver
{
    protected $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // En una implementación real, esto resolvería el tenant
        // basado en el subdominio de la petición
        
        $host = $request->getHost();
        $subdomain = $this->extractSubdomain($host);

        if ($subdomain) {
            $tenant = Tenant::findBySubdomain($subdomain);
            
            if (!$tenant) {
                return response()->json([
                    'error' => 'Tenant no encontrado',
                    'subdomain' => $subdomain
                ], 404);
            }

            if (!$tenant->isActive()) {
                return response()->json([
                    'error' => 'Tenant inactivo o expirado',
                    'subdomain' => $subdomain
                ], 403);
            }

            // Establecer el tenant actual
            $this->tenantService->setCurrentTenant($tenant);
            
            // Configurar la conexión de base de datos
            $this->tenantService->setDatabaseConnection($tenant);
            
            // Agregar tenant al request para uso posterior
            $request->attributes->set('tenant', $tenant);
        }

        return $next($request);
    }

    /**
     * Extraer subdominio del host
     */
    private function extractSubdomain($host)
    {
        // Ejemplo: empresa1.midominio.com -> empresa1
        $parts = explode('.', $host);
        
        if (count($parts) >= 3) {
            return $parts[0];
        }
        
        return null;
    }
}