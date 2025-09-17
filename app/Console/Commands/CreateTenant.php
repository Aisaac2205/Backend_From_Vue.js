<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class CreateTenant extends Command
{
    protected $signature = 'tenant:create {name} {--database=} {--user=} {--password=}';
    protected $description = 'Crea un nuevo esquema de base de datos y usuario para un tenant';

    public function handle()
    {
        $name = $this->argument('name');
        $database = $this->option('database') ?: 'tenant_' . $name;
        $user = $this->option('user') ?: 'tenant_' . $name;
        $password = $this->option('password') ?: 'secret123';

        // Crear base de datos y usuario (MySQL)
        DB::statement("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        DB::statement("CREATE USER IF NOT EXISTS '$user'@'%' IDENTIFIED BY '$password';");
        DB::statement("GRANT ALL PRIVILEGES ON `$database`.* TO '$user'@'%';");
        DB::statement("FLUSH PRIVILEGES;");

        $this->info("Base de datos y usuario creados para el tenant: $name");

        // Ejecutar migraciones en la nueva base de datos
        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => '/database/migrations',
            '--force' => true,
        ]);
        $this->info("Migraciones ejecutadas para $database");
    }
}
