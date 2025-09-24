<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class IdentifyTenant
{
    /**
     * Maneja la solicitud entrante y establece el contexto del tenant según el subdominio.
     * Sistema Multi-Tenant con bases de datos separadas por tenant.
     */
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();
        
        // DNS base de AWS - Configurar desde env para flexibilidad
        $baseDomain = env('BASE_DOMAIN', 'ec2-18-219-51-191.us-east-2.compute.amazonaws.com');
        
        // Extraer subdominio con validación de seguridad
        $subdomain = $this->extractSubdomain($host, $baseDomain);
        
        if (!$subdomain) {
            // Sin subdominio, usar la aplicación principal (BD central)
            return $next($request);
        }
        
        // Validar que el subdominio sea seguro
        if (!preg_match('/^[a-zA-Z0-9_-]{1,50}$/', $subdomain)) {
            Log::warning('Subdominio inválido detectado', [
                'host' => $host,
                'subdomain' => $subdomain,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            abort(400, 'Subdominio no válido');
        }
        
        // Buscar tenant en BD central
        $tenant = Tenant::findBySubdomain($subdomain);
        
        if (!$tenant) {
            Log::warning('Tenant no encontrado', [
                'host' => $host,
                'subdomain' => $subdomain,
                'ip' => $request->ip()
            ]);
            abort(404, 'Tenant no encontrado');
        }
        
        // Configurar conexión a la BD del tenant
        try {
            Tenant::configureTenantConnection($tenant);
            
            // Establecer el tenant en el contenedor de servicios
            app()->instance('currentTenant', $tenant);
            
            // También lo guardamos en el request para fácil acceso
            $request->attributes->set('tenant', $tenant);
            $request->attributes->set('tenant_id', $tenant->tenant_id);
            
            // Log para auditoría (solo en desarrollo)
            if (app()->environment('local', 'development')) {
                Log::info('Tenant conectado', [
                    'host' => $host,
                    'tenant_id' => $tenant->tenant_id,
                    'database' => $tenant->database_name,
                    'ip' => $request->ip()
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Error configurando conexión de tenant', [
                'tenant_id' => $tenant->tenant_id,
                'error' => $e->getMessage(),
                'host' => $host
            ]);
            abort(500, 'Error de configuración del tenant');
        }

        return $next($request);
    }
    
    /**
     * Extrae el subdominio del host de forma segura
     */
    private function extractSubdomain(string $host, string $baseDomain): ?string
    {
        // Si el host es exactamente el dominio base, no hay tenant
        if ($host === $baseDomain) {
            return null;
        }
        
        // Para desarrollo local (localhost, 127.0.0.1)
        if (str_contains($host, 'localhost') || str_contains($host, '127.0.0.1')) {
            $parts = explode('.', $host);
            if (count($parts) > 1 && $parts[0] !== 'www') {
                return $parts[0];
            }
            return null;
        }
        
        // Verificar que el host termine con el dominio base
        if (!str_ends_with($host, '.' . $baseDomain)) {
            Log::warning('Host no autorizado para multitenancy', [
                'host' => $host,
                'expected_domain' => $baseDomain
            ]);
            return null;
        }
        
        // Extraer subdominio
        $subdomain = str_replace('.' . $baseDomain, '', $host);
        
        // Excluir subdominios reservados para seguridad
        $reservedSubdomains = ['www', 'api', 'admin', 'root', 'mail', 'ftp', 'ssh', 'cpanel', 'webmail'];
        if (in_array(strtolower($subdomain), $reservedSubdomains)) {
            return null;
        }
        
        return $subdomain;
    }
}
