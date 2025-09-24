<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'subdomain',
        'database_name',
        'db_username',
        'db_password',
        'status',
        'domain',
        'settings'
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';

    /**
     * Crear un nuevo tenant con su base de datos
     */
    public static function createWithDatabase(array $data)
    {
        // Validar datos obligatorios
        if (!isset($data['tenant_id']) || !isset($data['name']) || !isset($data['subdomain'])) {
            throw new \InvalidArgumentException('tenant_id, name y subdomain son obligatorios');
        }

        // Generar credenciales de BD únicas
        $dbName = 'tenant_' . $data['tenant_id'];
        $dbUsername = 'tenant_' . $data['tenant_id'];
        $dbPassword = self::generateSecurePassword();

        DB::beginTransaction();
        
        try {
            // 1. Crear tenant en BD central
            $tenant = self::create([
                'tenant_id' => $data['tenant_id'],
                'name' => $data['name'],
                'subdomain' => $data['subdomain'],
                'database_name' => $dbName,
                'db_username' => $dbUsername,
                'db_password' => encrypt($dbPassword), // Encriptamos la contraseña
                'status' => self::STATUS_ACTIVE,
                'domain' => $data['domain'] ?? null,
                'settings' => $data['settings'] ?? []
            ]);

            // 2. Crear base de datos física
            self::createPhysicalDatabase($dbName, $dbUsername, $dbPassword);

            // 3. Ejecutar migraciones en la nueva BD
            self::runTenantMigrations($tenant);

            DB::commit();
            
            return $tenant;
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Limpiar BD física si se creó
            try {
                self::dropPhysicalDatabase($dbName, $dbUsername);
            } catch (\Exception $cleanupException) {
                \Log::error('Error limpiando BD después de fallo', [
                    'tenant_id' => $data['tenant_id'],
                    'error' => $cleanupException->getMessage()
                ]);
            }
            
            throw $e;
        }
    }

    /**
     * Crear base de datos física y usuario
     */
    private static function createPhysicalDatabase(string $dbName, string $dbUsername, string $dbPassword)
    {
        $rootConnection = DB::connection('mysql');
        
        // Crear base de datos
        $rootConnection->statement("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // Crear usuario y otorgar permisos
        $rootConnection->statement("CREATE USER IF NOT EXISTS '{$dbUsername}'@'%' IDENTIFIED BY '{$dbPassword}'");
        $rootConnection->statement("GRANT ALL PRIVILEGES ON `{$dbName}`.* TO '{$dbUsername}'@'%'");
        $rootConnection->statement("FLUSH PRIVILEGES");
    }

    /**
     * Ejecutar migraciones específicas del tenant
     */
    private static function runTenantMigrations(Tenant $tenant)
    {
        // Configurar conexión temporal para el tenant
        self::configureTenantConnection($tenant);
        
        // Ejecutar solo las migraciones del tenant (no las de la BD central)
        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant',
            '--force' => true
        ]);
    }

    /**
     * Configurar conexión de BD para el tenant
     */
    public static function configureTenantConnection(Tenant $tenant)
    {
        $config = [
            'driver' => 'mysql',
            'host' => env('TENANT_DB_HOST', env('DB_HOST', '127.0.0.1')),
            'port' => env('TENANT_DB_PORT', env('DB_PORT', '3306')),
            'database' => $tenant->database_name,
            'username' => $tenant->db_username,
            'password' => decrypt($tenant->db_password),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ];

        Config::set('database.connections.tenant', $config);
        DB::purge('tenant');
    }

    /**
     * Eliminar base de datos física
     */
    private static function dropPhysicalDatabase(string $dbName, string $dbUsername)
    {
        $rootConnection = DB::connection('mysql');
        
        $rootConnection->statement("DROP DATABASE IF EXISTS `{$dbName}`");
        $rootConnection->statement("DROP USER IF EXISTS '{$dbUsername}'@'%'");
        $rootConnection->statement("FLUSH PRIVILEGES");
    }

    /**
     * Generar contraseña segura
     */
    private static function generateSecurePassword(int $length = 16): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        return substr(str_shuffle(str_repeat($characters, $length)), 0, $length);
    }

    /**
     * Buscar tenant por subdominio
     */
    public static function findBySubdomain(string $subdomain): ?self
    {
        return self::where('subdomain', $subdomain)
                  ->where('status', self::STATUS_ACTIVE)
                  ->first();
    }

    /**
     * Eliminar tenant completo (BD central + física)
     */
    public function deleteWithDatabase()
    {
        DB::beginTransaction();
        
        try {
            // Eliminar BD física
            self::dropPhysicalDatabase($this->database_name, $this->db_username);
            
            // Eliminar registro de BD central
            $this->delete();
            
            DB::commit();
            
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}