<?php

namespace App\Http\Controllers;

use App\Models\Tarea;
use Illuminate\Http\Request;

class TareaController extends Controller
{
    // 1. Obtener todas las tareas del usuario conectado (para TareasView.vue)
    public function misTareas(Request $request)
    {
        $user = $request->user();

        if ($user->rol === 'docente') {
            // Tareas creadas en las materias del docente
            $tareas = Tarea::whereHas('materia', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->with('materia:id,nombre')->orderBy('fecha_limite', 'desc')->get();
        } else {
            // Tareas de las materias donde el alumno está inscrito
            $tareas = Tarea::whereHas('materia.alumnos', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })->with(['materia:id,nombre', 'entregas' => function ($q) use ($user) {
                $q->where('user_id', $user->id);
            }])->orderBy('fecha_limite', 'desc')->get();
        }

        return response()->json($tareas);
    }

    // 2. Crear nueva tarea con PDF (Exclusivo Docente)
    public function store(Request $request)
    {
        if ($request->user()->rol !== 'docente') {
            return response()->json(['message' => 'Sin permisos para crear tareas.'], 403);
        }

        $request->validate([
            'materia_id' => 'required|exists:materias,id',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'fecha_limite' => 'required|date',
            'puntaje_maximo' => 'integer|min:1',
            'archivo_pdf' => 'nullable|file|mimes:pdf|max:10240', // Máx 10MB
        ]);

        $rutaPdf = null;
        if ($request->hasFile('archivo_pdf')) {
            $rutaPdf = $request->file('archivo_pdf')->store('tareas_pdfs', 'public');
        }

        $tarea = Tarea::create([
            'materia_id' => $request->materia_id,
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'archivo_pdf' => $rutaPdf,
            'fecha_limite' => $request->fecha_limite,
            'puntaje_maximo' => $request->puntaje_maximo ?? 100,
        ]);

        return response()->json(['message' => 'Tarea publicada con éxito.', 'tarea' => $tarea], 201);
    }

    // 3. Ver detalle de una tarea específica con sus entregas (para TareaDetalleView.vue)
    public function show(Request $request, $id)
    {
        $tarea = Tarea::with([
            'materia.docente:id,nombre,email',
            'entregas.alumno:id,nombre,email',
            'comentarios.user:id,nombre,email'
        ])->findOrFail($id);

        return response()->json($tarea);
    }
}