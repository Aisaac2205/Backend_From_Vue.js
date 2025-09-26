<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

echo "=== CREANDO USUARIO DE PRUEBA ===\n";

try {
    // Crear usuario con los campos correctos de la BD
    $usuario = Usuario::create([
        'nombre' => 'Admin Test',
        'email' => 'admin@test.com',
        'password' => Hash::make('123456'),
        'rol' => 'admin',
    ]);
    
    echo "✅ Usuario creado exitosamente:\n";
    echo "Email: admin@test.com\n";
    echo "Password: 123456\n";
    echo "Nombre: " . $usuario->nombre . "\n";
    echo "Rol: " . $usuario->rol . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}