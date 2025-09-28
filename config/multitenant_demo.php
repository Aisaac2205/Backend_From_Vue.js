<?php

/**
 * Configuración Multitenant - SOLO PARA DEMOSTRACIÓN
 * 
 * NOTA: Esta configuración es solo para demostración académica.
 * NO está incluida en el sistema actual y NO afecta la funcionalidad existente.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Multitenant Configuration
    |--------------------------------------------------------------------------
    |
    | Esta configuración define cómo funcionaría el sistema multitenant
    | en caso de implementarse en el futuro.
    |
    */

    // Habilitar/deshabilitar funcionalidad multitenant
    'enabled' => env('MULTITENANT_ENABLED', false),

    // Configuración de conexiones de base de datos
    'database' => [
        // Conexión para la base de datos maestra (tenants)
        'master' => env('DB_CONNECTION', 'mysql'),
        
        // Prefijo para nombres de bases de datos de tenants
        'tenant_prefix' => env('TENANT_DB_PREFIX', 'tenant_'),
        
        // Configuración por defecto para bases de datos de tenants
        'tenant_defaults' => [
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]
    ],

    // Configuración de dominios
    'domains' => [
        // Dominio principal (sin tenant)
        'main' => env('APP_DOMAIN', 'midominio.com'),
        
        // Patrón para subdominios de tenant
        'tenant_pattern' => '{subdomain}.' . env('APP_DOMAIN', 'midominio.com'),
        
        // Subdominios reservados que no pueden ser usados por tenants
        'reserved_subdomains' => [
            'www', 'api', 'admin', 'mail', 'ftp', 'blog', 'shop', 'app'
        ]
    ],

    // Configuración de cache
    'cache' => [
        // Habilitar cache de tenants
        'enabled' => env('TENANT_CACHE_ENABLED', true),
        
        // Tiempo de cache en minutos
        'ttl' => env('TENANT_CACHE_TTL', 60),
        
        // Prefijo para keys de cache
        'prefix' => 'tenant_'
    ],

    // Configuración de migraciones
    'migrations' => [
        // Directorio con migraciones específicas de tenant
        'tenant_path' => 'database/migrations/tenant',
        
        // Ejecutar migraciones automáticamente al crear tenant
        'auto_migrate' => env('TENANT_AUTO_MIGRATE', true),
        
        // Seeders a ejecutar para nuevos tenants
        'seeders' => [
            'TenantDefaultSeeder',
        ]
    ],

    // Configuración de almacenamiento
    'storage' => [
        // Separar archivos por tenant
        'separate_files' => env('TENANT_SEPARATE_FILES', true),
        
        // Directorio base para archivos de tenant
        'tenant_disk_prefix' => 'tenant_',
    ],

    // Configuración de seguridad
    'security' => [
        // Verificar que el usuario pertenece al tenant
        'enforce_tenant_isolation' => true,
        
        // Logear accesos entre tenants
        'log_cross_tenant_access' => true,
        
        // Middleware a aplicar en rutas de tenant
        'middleware' => [
            'tenant.resolve',
            'tenant.enforce',
        ]
    ]
];