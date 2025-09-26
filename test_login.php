<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== PROBANDO LOGIN ===\n";

// Simular una petición POST a /api/test/login
$request = new \Illuminate\Http\Request();
$request->merge([
    'email' => 'admin@test.com',
    'password' => '123456'
]);

try {
    $authController = new \App\Http\Controllers\Api\AuthController();
    $response = $authController->login($request);
    
    echo "✅ Login exitoso!\n";
    echo "Response: " . $response->getContent() . "\n";
    
} catch (Exception $e) {
    echo "❌ Error en login: " . $e->getMessage() . "\n";
}