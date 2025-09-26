<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

echo "=== CREANDO USUARIO ADMINISTRADOR ===\n";

try {
    $admin = Usuario::create([
        'name' => 'Administrador',
        'email' => 'admin@admin.com',
        'password' => Hash::make('admin123'),
        'role' => 'admin',
        'email_verified_at' => now(),
    ]);
    
    echo "✅ Usuario administrador creado exitosamente:\n";
    echo "Email: admin@admin.com\n";
    echo "Password: admin123\n";
    echo "Role: admin\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}