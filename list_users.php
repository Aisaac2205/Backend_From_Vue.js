<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== USUARIOS EN LA BASE DE DATOS ===\n";

try {
    $usuarios = App\Models\Usuario::all();
    
    if ($usuarios->count() > 0) {
        foreach ($usuarios as $user) {
            echo "ID: {$user->id} | ";
            echo "Nombre: {$user->name} | ";
            echo "Email: {$user->email} | ";
            echo "Role: {$user->role}\n";
        }
    } else {
        echo "No hay usuarios en la base de datos.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}