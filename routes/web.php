<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return file_get_contents(public_path('index.html'));
});

// Servir assets con el tipo MIME correcto
Route::get('/assets/{file}', function ($file) {
    $path = public_path('assets/' . $file);
    
    if (!file_exists($path)) {
        abort(404);
    }
    
    $extension = pathinfo($path, PATHINFO_EXTENSION);
    $mimeTypes = [
        'js' => 'application/javascript',
        'css' => 'text/css',
        'map' => 'application/json'
    ];
    
    $mimeType = $mimeTypes[$extension] ?? 'text/plain';
    
    return response()->file($path, [
        'Content-Type' => $mimeType
    ]);
})->where('file', '.*');

// Servir archivos estáticos
Route::get('/favicon.ico', function () {
    return response()->file(public_path('favicon.ico'));
});

Route::get('/vite.svg', function () {
    return response()->file(public_path('vite.svg'));
});

Route::get('/robots.txt', function () {
    return response()->file(public_path('robots.txt'));
});

// Ruta catch-all para Vue Router (debe estar al final)
Route::get('/{any}', function () {
    return file_get_contents(public_path('index.html'));
})->where('any', '.*');
