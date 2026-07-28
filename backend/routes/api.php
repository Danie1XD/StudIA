<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AsignaturaController;
use App\Http\Controllers\TareaController;
use App\Http\Controllers\EntregaController;

// --- Rutas Públicas (¡Imprescindibles para poder iniciar sesión!) ---
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// --- Rutas Protegidas (Requieren token de sesión) ---
Route::middleware('auth:sanctum')->group(function () {
    
    Route::get('/user', function (Request $request) { return $request->user(); });
    Route::post('/logout', [AuthController::class, 'logout']);

    // --- Módulo de Asignaturas ---
    Route::get('/asignaturas', [AsignaturaController::class, 'index']);
    Route::middleware('role:docente')->post('/asignaturas', [AsignaturaController::class, 'store']);

    // --- Módulo de Tareas ---
    Route::get('/asignaturas/{asignatura_id}/tareas', [TareaController::class, 'index']);
    Route::middleware('role:docente')->post('/tareas', [TareaController::class, 'store']);

    // --- Módulo de Entregas ---
    Route::middleware('role:alumno')->post('/entregas', [EntregaController::class, 'store']);
    Route::middleware('role:docente')->post('/entregas/{id}/evaluar', [EntregaController::class, 'evaluar']);
    Route::middleware('role:docente')->post('/entregas/{id}/evaluar-ia', [EntregaController::class, 'solicitarEvaluacionIA']);
    
});