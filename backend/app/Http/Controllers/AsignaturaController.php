<?php

namespace App\Http\Controllers;

use App\Models\Asignatura;
use Illuminate\Http\Request;

class AsignaturaController extends Controller
{
    // Ver todas las asignaturas
    public function index()
    {
        $asignaturas = Asignatura::all();
        return response()->json($asignaturas, 200);
    }

    // Crear una nueva asignatura (Solo Docentes deberían poder hacer esto)
    public function store(Request $request)
    {
        $request->validate([
            'nombre_materia' => 'required|string',
            'codigo_grupo' => 'required|string'
        ]);

        $asignatura = Asignatura::create([
            'nombre_materia' => $request->nombre_materia,
            // El ID del docente se toma automáticamente del usuario que inició sesión
            'docente_id' => $request->user()->_id, 
            'codigo_grupo' => $request->codigo_grupo,
            'alumnos_inscritos' => [] // Inicia vacío
        ]);

        return response()->json($asignatura, 201);
    }
}