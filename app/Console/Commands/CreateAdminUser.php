<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create 
                           {--name= : Nombre del administrador}
                           {--email= : Email del administrador}
                           {--password= : Contraseña del administrador}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crear un usuario administrador en el tenant actual';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔐 Creando usuario administrador...');

        // Obtener datos del usuario  
        $name = $this->option('name') ?: $this->ask('Nombre del administrador');
        $email = $this->option('email') ?: $this->ask('Email del administrador');
        $password = $this->option('password') ?: $this->secret('Contraseña del administrador');

        // Validar datos
        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuarios',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            $this->error('❌ Datos inválidos:');
            foreach ($validator->errors()->all() as $error) {
                $this->error('  • ' . $error);
            }
            return 1;
        }

        try {
            // Crear usuario administrador
            $admin = Usuario::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]);

            $this->info('✅ Usuario administrador creado exitosamente:');
            $this->table(
                ['Campo', 'Valor'],
                [
                    ['ID', $admin->id],
                    ['Nombre', $admin->name],
                    ['Email', $admin->email],
                    ['Rol', $admin->role],
                    ['Creado', $admin->created_at],
                ]
            );

            $this->warn('🔑 Credenciales de acceso:');
            $this->warn("   Email: {$admin->email}");
            $this->warn("   Contraseña: {$password}");

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error creando administrador: ' . $e->getMessage());
            return 1;
        }
    }
}
