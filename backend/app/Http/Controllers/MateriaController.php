<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MateriaController extends Controller
{
    // 1. Obtener las materias según el rol del usuario logueado
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->rol === 'docente') {
            // El docente ve las clases que él creó
            $materias = $user->materiasCreadas()->withCount('alumnos')->get();
        } else {
            // El alumno ve las clases a las que se ha unido
            $materias = $user->materiasInscritas()->with('docente:id,nombre,email')->get();
        }

        return response()->json($materias);
    }

    // 2. Crear una nueva materia (Exclusivo para Docentes)
    public function store(Request $request)
    {
        if ($request->user()->rol !== 'docente') {
            return response()->json(['message' => 'No tienes permisos para crear materias.'], 403);
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        // Generamos un código único de 6 caracteres en mayúsculas
        do {
            $codigo = Str::upper(Str::random(6));
        } while (Materia::where('codigo_acceso', $codigo)->exists());

        $materia = Materia::create([
            'user_id' => $request->user()->id,
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'codigo_acceso' => $codigo,
        ]);

        return response()->json([
            'message' => 'Materia creada exitosamente.',
            'materia' => $materia
        ], 201);
    }

    // 3. Ver detalles de una materia en específico (con sus tareas y alumnos)
    public function show(Request $request, $id)
    {
        $materia = Materia::with(['docente:id,nombre,email', 'tareas', 'alumnos:id,nombre,email'])->findOrFail($id);
        return response()->json($materia);
    }

    // 4. Unirse a una materia mediante el código de acceso (Exclusivo para Alumnos)
    public function unirse(Request $request)
    {
        if ($request->user()->rol !== 'alumno') {
            return response()->json(['message' => 'Solo los alumnos pueden unirse con un código.'], 403);
        }

        $request->validate([
            'codigo_acceso' => 'required|string',
        ]);

        $materia = Materia::where('codigo_acceso', Str::upper($request->codigo_acceso))->first();

        if (!$materia) {
            return response()->json(['message' => 'El código de la materia no existe o es incorrecto.'], 404);
        }

        // Verificamos si el alumno ya estaba inscrito
        if ($request->user()->materiasInscritas()->where('materia_id', $materia->id)->exists()) {
            return response()->json(['message' => 'Ya estás inscrito en esta materia.'], 400);
        }

        // Inscribimos al alumno usando la tabla pivote
        $request->user()->materiasInscritas()->attach($materia->id);

        return response()->json([
            'message' => 'Te has unido a la materia con éxito.',
            'materia' => $materia
        ], 200);
    }
}