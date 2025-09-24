<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Ejecuta todos los seeders necesarios para un tenant nuevo
     */
    public function run(): void
    {
        $this->command->info('🌱 Ejecutando seeders para tenant...');
        
        // Crear usuario administrador
        $this->call(AdminUserSeeder::class);
        
        // Crear algunos usuarios de ejemplo (opcional)
        $this->createSampleUsers();
        
        $this->command->info('✅ Seeders de tenant completados');
    }
    
    /**
     * Crear usuarios de ejemplo para demostración
     */
    private function createSampleUsers()
    {
        $sampleUsers = [
            [
                'name' => 'Usuario Demo',
                'email' => 'usuario@demo.com',
                'password' => Hash::make('demo123'),
                'role' => 'user',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Editor Demo',
                'email' => 'editor@demo.com',
                'password' => Hash::make('editor123'),
                'role' => 'editor',
                'email_verified_at' => now(),
            ]
        ];
        
        foreach ($sampleUsers as $userData) {
            try {
                $existingUser = Usuario::where('email', $userData['email'])->first();
                
                if (!$existingUser) {
                    Usuario::create($userData);
                    $this->command->info("✅ Usuario creado: {$userData['email']} / " . str_replace(Hash::make(''), '', $userData['email']));
                }
            } catch (\Exception $e) {
                $this->command->warn("⚠️ No se pudo crear usuario: {$userData['email']}");
            }
        }
    }
}