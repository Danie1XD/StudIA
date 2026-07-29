<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AsignaturaController;
use App\Http\Controllers\TareaController;
use App\Http\Controllers\EntregaController;
use App\Models\PersonalAccessToken;

// --- Rutas Públicas ---
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register']);

// --- Ruta Temporal de Diagnóstico para MongoDB ---
Route::post('/test-token', function (Request $request) {
    $rawToken = $request->input('token');

    if (!$rawToken) {
        return response()->json(['error' => 'Por favor envía el campo "token" en el body'], 400);
    }

    if (str_contains($rawToken, '|')) {
        [$id, $plainTextToken] = explode('|', $rawToken, 2);
    } else {
        $plainTextToken = $rawToken;
    }

    $hashed = hash('sha256', $plainTextToken);
    $tokenDoc = PersonalAccessToken::where('token', $hashed)->first();

    if (!$tokenDoc) {
        return response()->json([
            'status' => 'error',
            'message' => 'Token no encontrado en la BD con ese hash',
            'hash_buscado' => $hashed
        ], 404);
    }

    $user = $tokenDoc->tokenable;

    return response()->json([
        'status' => 'success',
        'token_id' => $tokenDoc->_id ?? $tokenDoc->id,
        'tokenable_id_en_bd' => $tokenDoc->tokenable_id,
        'tipo_tokenable_id' => gettype($tokenDoc->tokenable_id),
        'usuario_encontrado' => $user
    ]);
});

// --- Rutas básicas sin middleware ---
Route::get('/user', function (Request $request) {
    return response()->json($request->user() ?: ['name' => 'Demo']);
});

Route::post('/logout', [AuthController::class, 'logout']);

// --- Módulo de Asignaturas ---
Route::get('/asignaturas', [AsignaturaController::class, 'index']);
Route::post('/asignaturas', [AsignaturaController::class, 'store']);
Route::post('/asignaturas/unirse', [AsignaturaController::class, 'unirse']);

// --- Módulo de Tareas ---
Route::get('/tareas/{id}', [TareaController::class, 'show']);
Route::get('/asignaturas/{asignatura_id}/tareas', [TareaController::class, 'index']);
Route::post('/tareas', [TareaController::class, 'store']);

// --- Módulo de Entregas ---
Route::get('/entregas/{id}/evaluacion-ia', [EntregaController::class, 'showEvaluation']);
Route::get('/entregas/alumno/{alumno_id}', [EntregaController::class, 'misEvaluaciones']);
Route::get('/entregas/docente/pendientes', [EntregaController::class, 'entregasPendientes']);
Route::post('/entregas', [EntregaController::class, 'store']);
Route::post('/entregas/{id}/evaluar', [EntregaController::class, 'evaluar']);
Route::post('/entregas/{id}/evaluar-ia', [EntregaController::class, 'solicitarEvaluacionIA']);
