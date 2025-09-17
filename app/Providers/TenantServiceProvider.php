<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class TenantServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Este provider puede usarse para inicializar lógica de tenant global
        // Por ejemplo, puedes cargar configuración de tenant desde BD o cache
        // Aquí solo dejamos un log para debug
        $connection = config('database.default');
        Log::info('TenantServiceProvider booted', ['connection' => $connection]);
    }
}
