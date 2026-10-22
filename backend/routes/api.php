<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\MateriaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AnuncioController;
use App\Http\Controllers\Api\V1\SeccionController;
use App\Http\Controllers\Api\V1\MateriaContenidoController;
use App\Http\Controllers\Api\V1\TareaController;
use App\Http\Controllers\Api\V1\EntregaController;
use App\Http\Controllers\Api\V1\MisTareasController;
use App\Http\Controllers\Api\V1\EvaluacionIaController;
use App\Http\Controllers\Api\V1\RubricaController;
use App\Http\Controllers\Api\V1\AsistenteController;
use App\Http\Controllers\Api\V1\ComentarioController;
use App\Http\Controllers\Api\V1\PromedioController;
use App\Http\Controllers\Api\V1\Admin\UsuarioController;
use App\Http\Controllers\Api\V1\PerfilController;

Route::prefix('v1')->group(function () {

    // Públicas
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

    // Protegidas: requieren token
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);

        // Materias (unirse va antes del apiResource para que nunca se confunda con {materia})
        Route::post('/materias/unirse', [MateriaController::class, 'unirse']);
        Route::apiResource('materias', MateriaController::class)
            ->parameters(['materias' => 'materia']);

        // Secciones (los apartados de la clase)
        Route::post('materias/{materia}/secciones/reordenar', [SeccionController::class, 'reordenar']);
        Route::apiResource('materias.secciones', SeccionController::class)
            ->shallow()
            ->except('show')
            ->parameters(['materias' => 'materia', 'secciones' => 'seccion']);

        // Anuncios
        Route::apiResource('materias.anuncios', AnuncioController::class)
            ->shallow()
            ->except('show')
            ->parameters(['materias' => 'materia', 'anuncios' => 'anuncio']);

        // Contenido de la clase agrupado por sección
        Route::get('materias/{materia}/contenido', MateriaContenidoController::class);

        // Tareas
        Route::get('tareas/{tarea}/archivo', [TareaController::class, 'archivo'])->name('tareas.archivo');
        Route::apiResource('materias.tareas', TareaController::class)
            ->shallow()
            ->parameters(['materias' => 'materia', 'tareas' => 'tarea']);

        // Mis tareas (agrupadas por materia, con filtro por estado para el alumno)
        Route::get('mis-tareas', MisTareasController::class);

        // Entregas
        Route::get('tareas/{tarea}/entregas', [EntregaController::class, 'index']);
        Route::get('tareas/{tarea}/mi-entrega', [EntregaController::class, 'miEntrega']);
        Route::post('tareas/{tarea}/entrega', [EntregaController::class, 'store']);
        Route::delete('tareas/{tarea}/entrega', [EntregaController::class, 'destroy']);
        Route::get('entregas/{entrega}/archivo', [EntregaController::class, 'archivo'])->name('entregas.archivo');
        Route::put('entregas/{entrega}/calificacion', [EntregaController::class, 'calificar']);

        // Pre-evaluación con IA (solo el docente de la materia). Máx. 10 por minuto por usuario.
        Route::get('entregas/{entrega}/evaluacion-ia', [EvaluacionIaController::class, 'show']);
        Route::post('entregas/{entrega}/evaluacion-ia', [EvaluacionIaController::class, 'store'])
            ->middleware('throttle:10,1');

        // Rúbricas (solo docentes y admin; los alumnos reciben 403 por el rol)
        Route::middleware('role:docente,admin')->group(function () {
            Route::apiResource('rubricas', RubricaController::class)
                ->parameters(['rubricas' => 'rubrica']);
        });
            
        // Foro de la clase (comentarios de cada tarea)
        Route::get('tareas/{tarea}/comentarios', [ComentarioController::class, 'index']);
        Route::post('tareas/{tarea}/comentarios', [ComentarioController::class, 'store'])
            ->middleware('throttle:30,1');
        Route::delete('comentarios/{comentario}', [ComentarioController::class, 'destroy']);

        // Asistente virtual (alumnos y docentes). Máx. 20 mensajes por minuto por usuario.
        Route::post('asistente/mensajes', AsistenteController::class)
            ->middleware(['role:alumno,docente', 'throttle:20,1']);
        
        // Promedios por parcial (regla de tres sobre los puntos de cada tarea)
        Route::get('materias/{materia}/promedios', [PromedioController::class, 'materia']);
        Route::get('mis-promedios', [PromedioController::class, 'mios'])->middleware('role:alumno');

        // Perfil propio (cualquier rol)
        Route::put('perfil', [PerfilController::class, 'update']);
        Route::put('perfil/password', [PerfilController::class, 'cambiarPassword'])
            ->middleware('throttle:5,1');

        // Administración de usuarios (solo admin)
        Route::middleware('role:admin')->prefix('admin')->group(function () {
            Route::get('usuarios', [UsuarioController::class, 'index']);
            Route::post('usuarios', [UsuarioController::class, 'store']);
            Route::get('usuarios/{usuario}', [UsuarioController::class, 'show']);
            Route::put('usuarios/{usuario}', [UsuarioController::class, 'update']);
            Route::post('usuarios/{usuario}/desactivar', [UsuarioController::class, 'desactivar']);
            Route::post('usuarios/{usuario}/activar', [UsuarioController::class, 'activar']); });
        });
});