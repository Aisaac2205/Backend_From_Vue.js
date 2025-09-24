<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Crea un usuario administrador por defecto en cada tenant
     */
    public function run(): void
    {
        $adminData = [
            'name' => 'Administrador',
            'email' => 'admin@admin.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ];

        try {
            // Verificar si ya existe el admin
            $existingAdmin = Usuario::where('email', 'admin@admin.com')->first();
            
            if (!$existingAdmin) {
                $admin = Usuario::create($adminData);
                
                Log::info('Usuario administrador creado', [
                    'tenant' => app('currentTenant') ? app('currentTenant')->tenant_id : 'central',
                    'admin_id' => $admin->id,
                    'email' => $admin->email
                ]);
                
                $this->command->info('✅ Usuario administrador creado: admin@admin.com / admin123');
            } else {
                $this->command->info('ℹ️ Usuario administrador ya existe');
            }
            
        } catch (\Exception $e) {
            Log::error('Error creando usuario administrador', [
                'error' => $e->getMessage(),
                'tenant' => app('currentTenant') ? app('currentTenant')->tenant_id : 'central'
            ]);
            
            $this->command->error('❌ Error creando usuario administrador: ' . $e->getMessage());
        }
    }
}
