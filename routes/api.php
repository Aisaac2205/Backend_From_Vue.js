<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TareaController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
| All routes are protected with Sanctum authentication middleware
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    $user = $request->user();
    if (!$user) {
        return response()->json([
            'message' => 'Token inválido o expirado',
            'error' => 'Unauthorized',
            'status' => false
        ], 401);
    }
    return response()->json([
        'user' => $user,
        'authenticated' => true,
        'status' => true
    ]);
});

// Ruta para verificar si el token es válido
Route::middleware('auth:sanctum')->get('/verify-token', function (Request $request) {
    $user = $request->user();
    if (!$user) {
        return response()->json([
            'message' => 'Token inválido o expirado',
            'valid' => false,
            'status' => false
        ], 401);
    }
    return response()->json([
        'message' => 'Token válido',
        'valid' => true,
        'user' => $user,
        'status' => true
    ]);
});
// Rutas para el controlador de usuarios, asignando nombres personalizados

// Rutas simples para usuarios (lo que espera el frontend) - Protegidas con auth
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/usuarios', [UsuarioController::class, 'index']);
    Route::post('/usuarios', [UsuarioController::class, 'store']);
    Route::get('/usuarios/{id}', [UsuarioController::class, 'show']);
    Route::put('/usuarios/{id}', [UsuarioController::class, 'update']);
    Route::delete('/usuarios/{id}', [UsuarioController::class, 'destroy']);
});

// Ruta de debug simple
Route::get('/test', function () {
    return response()->json(['message' => 'API funcionando', 'timestamp' => now()]);
});

// Rutas con nombres descriptivos (compatibilidad) - Protegidas con auth
Route::middleware('auth:sanctum')->prefix('usuarios')->group(function () {
    Route::get('/listUsers', [UsuarioController::class, 'index']);
    Route::post('/addUser', [UsuarioController::class, 'store']);
    Route::get('/getUser/{id}', [UsuarioController::class, 'show']);
    Route::put('/updateUser/{id}', [UsuarioController::class, 'update']);
    Route::delete('/deleteUser/{id}', [UsuarioController::class, 'destroy']);
});

// Rutas para tareas - Protegidas con auth
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/tareas', [TareaController::class, 'index']);
    Route::get('/tareas/reporte-excel', [TareaController::class, 'reporteExcel']); // Debe ir antes de las rutas con parámetros
    Route::post('/tareas', [TareaController::class, 'store']);
    Route::get('/tareas/{id}', [TareaController::class, 'show']);
    Route::put('/tareas/{id}', [TareaController::class, 'update']);
    Route::delete('/tareas/{id}', [TareaController::class, 'destroy']);
    Route::patch('/tareas/{id}/status', [TareaController::class, 'updateStatus']);
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
