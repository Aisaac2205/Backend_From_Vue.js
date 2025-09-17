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
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
// Rutas para el controlador de usuarios, asignando nombres personalizados


// Proteger todas las rutas de usuarios con auth:sanctum excepto el formulario de testing
Route::prefix('usuarios')->middleware(['auth:sanctum', 'validate.origin'])->group(function () {
    Route::get('/listUsers', [UsuarioController::class, 'index']);
    Route::post('/addUser', [UsuarioController::class, 'store']);
    Route::get('/getUser/{id}', [UsuarioController::class, 'show']);
    Route::put('/updateUser/{id}', [UsuarioController::class, 'update']);
    Route::delete('/deleteUser/{id}', [UsuarioController::class, 'destroy']);
});
// Mantener pública solo la ruta de formulario de testing
Route::get('/usuarios/addUser', [UsuarioController::class, 'createUserForm']);

Route::post('/register', [AuthController::class, 'register']);
// Limitar a 5 intentos por minuto para evitar fuerza bruta
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::get('/login', [AuthController::class, 'loginForm']); // Para testing desde navegador
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);


// Proteger todas las rutas de tareas con auth:sanctum
Route::prefix('tareas')->middleware(['auth:sanctum', 'validate.origin'])->group(function () {
    Route::get('/', [TareaController::class, 'index']);
    Route::post('/', [TareaController::class, 'store']);
    Route::get('/usuarios', [TareaController::class, 'getUsers']); // Para obtener usuarios para el selector
    Route::get('/report-pendientes', [TareaController::class, 'downloadPendingReport']); // Descargar reporte Excel
    Route::get('/{id}', [TareaController::class, 'show']);
    Route::put('/{id}', [TareaController::class, 'update']);
    Route::delete('/{id}', [TareaController::class, 'destroy']);
});
