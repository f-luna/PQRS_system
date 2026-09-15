<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PqrsController;
use Illuminate\Support\Facades\Route;

// Auth pública
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas con Sanctum
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // PQRS
    Route::get('/pqrs', [PqrsController::class, 'index']);
    Route::post('/pqrs', [PqrsController::class, 'store']);
    Route::get('/pqrs/{pqrs}', [PqrsController::class, 'show']);

    // Acciones admin/agente
    Route::put('/pqrs/{pqrs}/asignar', [PqrsController::class, 'asignar']);
    Route::put('/pqrs/{pqrs}/responder', [PqrsController::class, 'responder']);
    Route::put('/pqrs/{pqrs}/cambiar-estado', [PqrsController::class, 'cambiarEstado']);

    // Historial
    Route::get('/pqrs/{pqrs}/historial', [PqrsController::class, 'historial']);

    // Adjuntos
    Route::post('/pqrs/{pqrs}/adjuntos', [PqrsController::class, 'subirAdjunto']);
    Route::get('/adjuntos/{adjunto}/descargar', [PqrsController::class, 'descargarAdjunto']);
});
