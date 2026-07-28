<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MateriaController;
use App\Http\Controllers\TareaController;
use App\Http\Controllers\EntregaController;
use App\Http\Controllers\ComentarioController;

// --- Rutas Públicas (Autenticación e Inicio de Sesión) ---
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Rutas de autenticación con Google (Corregido con Route::get)
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

// --- Rutas Protegidas (Requieren token activo de Sanctum) ---
Route::middleware('auth:sanctum')->group(function () {
   
    // Usuario y Sesión
    Route::get('/user', function (Request $request) { return $request->user(); });
    Route::post('/logout', [AuthController::class, 'logout']);

    // --- Módulo de Materias (Gestión de Clases y Códigos) ---
    Route::get('/materias', [MateriaController::class, 'index']);
    Route::post('/materias', [MateriaController::class, 'store']);
    Route::get('/materias/{id}', [MateriaController::class, 'show']);
    Route::post('/materias/unirse', [MateriaController::class, 'unirse']);

    // --- Módulo de Tareas (Publicación y Detalle) ---
    Route::get('/tareas', [TareaController::class, 'misTareas']);
    Route::post('/tareas', [TareaController::class, 'store']);
    Route::get('/tareas/{id}', [TareaController::class, 'show']);

    // --- Módulo de Entregas y Pre-evaluación con Gemini AI ---
    Route::post('/entregas', [EntregaController::class, 'store']); 
    Route::post('/entregas/{id}/evaluar-ia', [EntregaController::class, 'evaluarIA']); 
    Route::post('/entregas/{id}/calificar', [EntregaController::class, 'guardarEvaluacion']); 
    Route::delete('/entregas/{id}', [EntregaController::class, 'destroy']);

    // --- Módulo de Comentarios Privados ---
    Route::post('/comentarios', [ComentarioController::class, 'store']);

});