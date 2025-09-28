<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Tenant para demostración de arquitectura multitenant
 * 
 * NOTA: Esta clase es solo para demostración académica.
 * NO está implementada en el sistema actual y NO afecta la funcionalidad existente.
 */
class Tenant extends Model
{
    use HasFactory;

    protected $table = 'tenants';

    protected $fillable = [
        'nombre',
        'subdominio',
        'database_name',
        'database_host',
        'database_port',
        'database_username',
        'database_password',
        'estado',
        'configuracion',
        'fecha_creacion',
        'fecha_expiracion'
    ];

    protected $casts = [
        'configuracion' => 'array',
        'fecha_creacion' => 'datetime',
        'fecha_expiracion' => 'datetime',
        'estado' => 'boolean'
    ];

    /**
     * Obtener todos los usuarios de este tenant
     */
    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'tenant_id');
    }

    /**
     * Obtener todas las tareas de este tenant
     */
    public function tareas()
    {
        return $this->hasMany(Tarea::class, 'tenant_id');
    }

    /**
     * Verificar si el tenant está activo
     */
    public function isActive()
    {
        return $this->estado && 
               (!$this->fecha_expiracion || $this->fecha_expiracion->isFuture());
    }

    /**
     * Obtener la configuración de base de datos para este tenant
     */
    public function getDatabaseConfig()
    {
        return [
            'driver' => 'mysql',
            'host' => $this->database_host ?? env('DB_HOST', '127.0.0.1'),
            'port' => $this->database_port ?? env('DB_PORT', '3306'),
            'database' => $this->database_name,
            'username' => $this->database_username,
            'password' => $this->database_password,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ];
    }

    /**
     * Buscar tenant por subdominio
     */
    public static function findBySubdomain($subdomain)
    {
        return static::where('subdominio', $subdomain)
                    ->where('estado', true)
                    ->first();
    }

    /**
     * Crear conexión de base de datos para este tenant
     */
    public function createDatabaseConnection()
    {
        $config = $this->getDatabaseConfig();
        
        // En una implementación real, esto configuraría la conexión
        // config(['database.connections.tenant_' . $this->id => $config]);
        
        return 'tenant_' . $this->id;
    }
}