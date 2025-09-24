<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;

class CreateTenantCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:create 
                           {tenant_id : ID único del tenant} 
                           {name : Nombre del tenant} 
                           {subdomain : Subdominio para el tenant} 
                           {--domain= : Dominio personalizado (opcional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crear un nuevo tenant con su base de datos exclusiva';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantId = $this->argument('tenant_id');
        $name = $this->argument('name');
        $subdomain = $this->argument('subdomain');
        $domain = $this->option('domain');

        $this->info("Creando tenant: {$name} ({$tenantId})...");

        try {
            // Validar que no exista
            if (Tenant::where('tenant_id', $tenantId)->exists()) {
                $this->error("❌ El tenant '{$tenantId}' ya existe.");
                return 1;
            }

            if (Tenant::where('subdomain', $subdomain)->exists()) {
                $this->error("❌ El subdominio '{$subdomain}' ya existe.");
                return 1;
            }

            // Crear tenant
            $tenant = Tenant::createWithDatabase([
                'tenant_id' => $tenantId,
                'name' => $name,
                'subdomain' => $subdomain,
                'domain' => $domain
            ]);

            $this->info("✅ Tenant creado exitosamente:");
            $this->table(
                ['Campo', 'Valor'],
                [
                    ['Tenant ID', $tenant->tenant_id],
                    ['Nombre', $tenant->name],
                    ['Subdominio', $tenant->subdomain],
                    ['Base de Datos', $tenant->database_name],
                    ['Usuario BD', $tenant->db_username],
                    ['URL de Acceso', "https://{$tenant->subdomain}." . env('BASE_DOMAIN')],
                    ['Estado', $tenant->status]
                ]
            );

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error creando tenant: " . $e->getMessage());
            return 1;
        }
    }
}
