<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class IdentifyTenant
{
    /**
     * Maneja la solicitud entrante y establece el contexto del tenant según el subdominio.
     * Opción 1: Una sola BD con tenant_id para separar datos - VERSIÓN SEGURA PARA PRODUCCIÓN.
     */
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();
        
        // DNS base de AWS - Configurar desde env para flexibilidad
        $baseDomain = env('BASE_DOMAIN', 'ec2-18-219-51-191.us-east-2.compute.amazonaws.com');
        
        // Extraer subdominio con validación de seguridad
        $tenantId = $this->extractTenantId($host, $baseDomain);
        
        // Validar que el tenant_id sea seguro (solo alfanumérico, guiones y guiones bajos)
        if ($tenantId && !preg_match('/^[a-zA-Z0-9_-]{1,50}$/', $tenantId)) {
            Log::warning('Tenant ID inválido detectado', [
                'host' => $host,
                'tenant_id' => $tenantId,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            abort(400, 'Tenant no válido');
        }
        
        // Usar tenant por defecto si no se encuentra uno válido
        $finalTenantId = $tenantId ?: 'default';
        
        // Establecer el tenant en el contenedor de servicios
        app()->instance('currentTenant', $finalTenantId);
        
        // También lo guardamos en el request para fácil acceso
        $request->attributes->set('tenant_id', $finalTenantId);
        
        // Log para auditoría (solo en desarrollo, en producción solo errores)
        if (app()->environment('local', 'development')) {
            Log::info('Tenant identificado', [
                'host' => $host,
                'tenant_id' => $finalTenantId,
                'ip' => $request->ip()
            ]);
        }

        return $next($request);
    }
    
    /**
     * Extrae el tenant ID del host de forma segura
     */
    private function extractTenantId(string $host, string $baseDomain): ?string
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
