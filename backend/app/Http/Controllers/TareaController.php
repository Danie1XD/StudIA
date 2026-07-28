<?php

namespace App\Http\Controllers;

use App\Models\Tarea;
use Illuminate\Http\Request;

class TareaController extends Controller
{
    // Obtener las tareas de una asignatura específica
    public function index($asignatura_id)
    {
        $tareas = Tarea::where('asignatura_id', $asignatura_id)->get();
        return response()->json($tareas, 200);
    }

    // Crear tarea (Solo Docentes)
    public function store(Request $request)
    {
        $request->validate([
            'asignatura_id' => 'required|string',
            'titulo' => 'required|string',
            'descripcion' => 'required|string',
            'fecha_entrega_limite' => 'required|date',
            'rubrica' => 'required|array' // Aquí llegará el JSON con los criterios
        ]);

        $tarea = Tarea::create([
            'asignatura_id' => $request->asignatura_id,
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'fecha_entrega_limite' => $request->fecha_entrega_limite,
            'rubrica' => $request->rubrica,
            'estado' => 'Activa'
        ]);

        return response()->json($tarea, 201);
    }
}