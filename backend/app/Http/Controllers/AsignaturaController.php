<?php

namespace App\Http\Controllers;

use App\Models\Asignatura;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AsignaturaController extends Controller
{
    public function index(Request $request)
    {
        $asignaturas = Asignatura::all();
        return response()->json($asignaturas, 200);
    }

    public function store(Request $request)
    {
        $nombre = $request->input('nombre_materia') ?: $request->input('nombre') ?: 'Materia';
        $descripcion = $request->input('descripcion') ?: 'Sin descripción';
        $codigo = $request->input('codigo_grupo') ?: strtoupper(Str::random(6));

        $asignatura = Asignatura::create([
            'nombre_materia' => $nombre,
            'docente_id' => $request->input('docente_id') ?: 'demo-docente',
            'codigo_grupo' => $codigo,
            'descripcion' => $descripcion,
            'alumnos_inscritos' => [],
            'estado' => 'Activa'
        ]);

        return response()->json($asignatura, 201);
    }

    public function unirse(Request $request)
    {
        $codigo = strtoupper($request->input('codigo_grupo') ?: '');

        $asignatura = Asignatura::where('codigo_grupo', $codigo)->first();

        if (!$asignatura) {
            return response()->json(['error' => 'No existe una materia con ese código.'], 404);
        }

        $alumnoId = $request->input('alumno_id') ?: 'demo-alumno';

        if (in_array($alumnoId, $asignatura->alumnos_inscritos ?? [])) {
            return response()->json(['message' => 'Ya estás inscrito en esta materia.'], 200);
        }

        $asignatura->alumnos_inscritos = array_values(array_merge($asignatura->alumnos_inscritos ?? [], [$alumnoId]));
        $asignatura->save();

        return response()->json($asignatura, 200);
    }
}