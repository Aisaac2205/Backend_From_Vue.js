<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tarea;
use App\Models\Usuario;

class TareaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar que existan usuarios en la base de datos
        $usuarios = Usuario::all();
        
        if ($usuarios->count() === 0) {
            $this->command->warn('No hay usuarios en la base de datos. Crea usuarios primero.');
            return;
        }

        // Crear tareas de ejemplo
        $tareas = [
            [
                'titulo' => 'Implementar autenticación',
                'descripcion' => 'Crear sistema de login con JWT y Laravel Sanctum',
                'estado' => 'completada',
                'fecha_vencimiento' => '2024-12-31',
                'usuario_id' => $usuarios->first()->id,
            ],
            [
                'titulo' => 'Diseñar interfaz de usuario',
                'descripcion' => 'Crear mockups y prototipos para la aplicación web',
                'estado' => 'en_progreso',
                'fecha_vencimiento' => '2024-11-30',
                'usuario_id' => $usuarios->count() > 1 ? $usuarios->skip(1)->first()->id : $usuarios->first()->id,
            ],
            [
                'titulo' => 'Testing completo',
                'descripcion' => 'Realizar pruebas unitarias e integración del sistema',
                'estado' => 'pendiente',
                'fecha_vencimiento' => '2024-10-15',
                'usuario_id' => $usuarios->first()->id,
            ],
            [
                'titulo' => 'Deployment en AWS',
                'descripcion' => 'Configurar y desplegar la aplicación en Amazon Web Services',
                'estado' => 'pendiente',
                'fecha_vencimiento' => '2024-12-15',
                'usuario_id' => $usuarios->count() > 1 ? $usuarios->skip(1)->first()->id : $usuarios->first()->id,
            ],
        ];

        foreach ($tareas as $tareaData) {
            Tarea::create($tareaData);
        }

        $this->command->info('Tareas de ejemplo creadas exitosamente.');
    }
}
