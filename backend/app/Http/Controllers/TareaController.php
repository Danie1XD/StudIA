<?php

namespace App\Http\Controllers;

use App\Models\Asignatura;
use App\Models\Tarea;
use Illuminate\Http\Request;
use MongoDB\BSON\ObjectId;

class TareaController extends Controller
{
    public function index(Request $request, $asignatura_id)
    {
        $asignatura = $this->findAsignaturaById($asignatura_id);

        if (!$asignatura) {
            return response()->json(['error' => 'Materia no encontrada.'], 404);
        }

        $alumnoId = $request->input('alumno_id') ?: 'demo-alumno';
        $inscrito = in_array($alumnoId, $asignatura->alumnos_inscritos ?? []);

        if (!$inscrito) {
            return response()->json(['error' => 'No estás inscrito en esta materia.'], 403);
        }

        $tareas = Tarea::where('asignatura_id', $asignatura_id)->get();
        return response()->json($tareas, 200);
    }

    public function show(Request $request, $id)
    {
        $tarea = Tarea::find($id);

        if (!$tarea) {
            return response()->json(['error' => 'Tarea no encontrada.'], 404);
        }

        $asignatura = $this->findAsignaturaById($tarea->asignatura_id);
        $alumnoId = $request->input('alumno_id') ?: 'demo-alumno';
        $inscrito = $asignatura ? in_array($alumnoId, $asignatura->alumnos_inscritos ?? []) : false;

        if (!$inscrito) {
            return response()->json(['error' => 'No estás inscrito en esta materia.'], 403);
        }

        return response()->json($tarea, 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        if (empty($data)) {
            $rawBody = $request->getContent();
            if ($rawBody) {
                $decoded = json_decode($rawBody, true);
                if (is_array($decoded)) {
                    $data = $decoded;
                }
            }
        }

        $asignaturaId = $data['asignatura_id'] ?? $data['asignaturaId'] ?? null;
        $titulo = $data['titulo'] ?? null;
        $descripcion = $data['descripcion'] ?? null;
        $fechaEntregaLimite = $data['fecha_entrega_limite'] ?? $data['fechaEntregaLimite'] ?? null;
        $puntajeMaximo = $data['puntaje_maximo'] ?? $data['puntajeMaximo'] ?? null;
        $rubrica = $data['rubrica'] ?? null;

        if (!$asignaturaId || !$titulo || !$descripcion || !$fechaEntregaLimite || $puntajeMaximo === null || !$rubrica) {
            return response()->json(['error' => 'Faltan datos para crear la tarea.'], 422);
        }

        $asignatura = $this->findAsignaturaById($asignaturaId);

        if (!$asignatura) {
            return response()->json(['error' => 'La materia no existe.'], 404);
        }

        $tarea = Tarea::create([
            'asignatura_id' => $asignaturaId,
            'docente_id' => $request->input('docente_id') ?: 'demo-docente',
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'fecha_entrega_limite' => $fechaEntregaLimite,
            'puntaje_maximo' => $puntajeMaximo,
            'rubrica' => $rubrica,
            'estado' => 'Activa'
        ]);

        return response()->json($tarea, 201);
    }

    private function findAsignaturaById($id)
    {
        $normalizedId = $this->normalizeId($id);

        if (!$normalizedId) {
            return null;
        }

        if (preg_match('/^[0-9a-fA-F]{24}$/', $normalizedId)) {
            return Asignatura::where('_id', new ObjectId($normalizedId))->first();
        }

        return Asignatura::where('_id', $normalizedId)->first();
    }

    private function normalizeId($value)
    {
        if ($value instanceof ObjectId) {
            return (string) $value;
        }

        if (is_object($value)) {
            if (method_exists($value, '__toString')) {
                $text = (string) $value;
                if (preg_match('/([0-9a-fA-F]{24})/', $text, $matches)) {
                    return $matches[1];
                }
                return $text;
            }

            return null;
        }

        if (is_scalar($value)) {
            $text = trim((string) $value);
            if (preg_match('/([0-9a-fA-F]{24})/', $text, $matches)) {
                return $matches[1];
            }
            return $text;
        }

        return null;
    }
}