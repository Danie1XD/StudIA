<?php

namespace App\Http\Controllers;

use App\Models\Entrega;
use Illuminate\Http\Request;
use App\Services\GeminiEvaluationService;
use App\Models\Tarea;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Str;

class EntregaController extends Controller
{
    public function store(Request $request, GeminiEvaluationService $geminiService)
    {
        $tareaId = $request->input('tarea_id') ?: $request->input('tareaId');
        $archivo = $request->file('archivo');
        $alumnoId = $request->input('alumno_id') ?: 'demo-alumno';

        if (!$tareaId || !$archivo) {
            return response()->json(['error' => 'Faltan datos para entregar la tarea.'], 422);
        }

        $tarea = Tarea::find($tareaId);

        if (!$tarea) {
            return response()->json(['error' => 'La tarea no existe.'], 404);
        }

        $rutaArchivo = $archivo->store('entregas', 'public');
        $textoPlano = $this->extraerTextoDelPdf(storage_path('app/public/' . $rutaArchivo));

        $entrega = Entrega::create([
            'tarea_id' => $tareaId,
            'alumno_id' => $alumnoId,
            'archivo_url' => $rutaArchivo,
            'contenido_texto' => $textoPlano,
            'fecha_envio' => now(),
            'estado' => 'Pendiente',
            'pre_evaluacion_ia' => null,
            'revision_docente' => null
        ]);

        try {
            $this->evaluarConIA($entrega, $tarea, $geminiService);
        } catch (\Exception $e) {
            $entrega->estado = 'Pendiente';
            $entrega->save();
        }

        return response()->json($entrega, 201);
    }

    public function evaluar(Request $request, $id)
    {
        $calificacionFinal = $request->input('calificacion_final') ?? $request->input('calificacionFinal');
        $comentarios = $request->input('comentarios') ?? '';
        $docenteId = $request->input('docente_id') ?: 'demo-docente';

        $entrega = Entrega::findOrFail($id);

        $entrega->revision_docente = [
            'calificacion_final' => $calificacionFinal,
            'comentarios' => $comentarios,
            'validado_por_id' => $docenteId,
            'fecha_validacion' => now()
        ];

        $entrega->estado = 'Validado';
        $entrega->save();

        return response()->json($entrega, 200);
    }

    public function solicitarEvaluacionIA($id, GeminiEvaluationService $geminiService)
    {
        $entrega = Entrega::findOrFail($id);

        if ($entrega->estado === 'Validado') {
            return response()->json(['error' => 'Esta tarea ya tiene una calificación definitiva.'], 400);
        }

        $tarea = Tarea::findOrFail($entrega->tarea_id);

        try {
            $this->evaluarConIA($entrega, $tarea, $geminiService);

            return response()->json([
                'mensaje' => 'Evaluación de IA completada exitosamente',
                'datos' => $entrega
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Falló la evaluación de IA: ' . $e->getMessage()], 500);
        }
    }

    public function showEvaluation($id)
    {
        $entrega = Entrega::findOrFail($id);
        $tarea = Tarea::find($entrega->tarea_id);

        if (!$entrega->pre_evaluacion_ia) {
            return response()->json(['error' => 'Aún no hay evaluación de IA para esta entrega.'], 404);
        }

        return response()->json([
            'titulo_tarea' => $tarea?->titulo ?? 'Tarea sin título',
            'fecha_entrega' => $entrega->fecha_envio,
            'puntaje_sugerido' => $entrega->pre_evaluacion_ia['puntaje_sugerido'] ?? 0,
            'feedback' => $entrega->pre_evaluacion_ia['feedback'] ?? '',
            'estado' => $entrega->estado,
            'revision_docente' => $entrega->revision_docente
        ], 200);
    }

    /**
     * Obtener todas las entregas de un alumno con sus evaluaciones
     */
    public function misEvaluaciones($alumnoId)
    {
        $entregas = Entrega::where('alumno_id', $alumnoId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($entrega) {
                $tarea = Tarea::find($entrega->tarea_id);
                return [
                    '_id' => $entrega->_id,
                    'tarea_id' => $entrega->tarea_id,
                    'titulo_tarea' => $tarea?->titulo ?? 'Tarea eliminada',
                    'asignatura_id' => $tarea?->asignatura_id ?? null,
                    'archivo_url' => $entrega->archivo_url,
                    'fecha_envio' => $entrega->fecha_envio,
                    'estado' => $entrega->estado,
                    'puntaje_sugerido_ia' => $entrega->pre_evaluacion_ia['puntaje_sugerido'] ?? null,
                    'feedback_ia' => $entrega->pre_evaluacion_ia['feedback'] ?? null,
                    'calificacion_final' => $entrega->revision_docente['calificacion_final'] ?? null,
                    'comentarios_docente' => $entrega->revision_docente['comentarios'] ?? null,
                    'fecha_validacion' => $entrega->revision_docente['fecha_validacion'] ?? null,
                ];
            });

        return response()->json($entregas, 200);
    }

    /**
     * Obtener entregas pendientes de revisión docente (de todas las tareas)
     */
    public function entregasPendientes(Request $request)
    {
        $docenteId = $request->input('docente_id') ?: 'demo-docente';

        // Obtener todas las tareas creadas por este docente
        $tareasDocente = Tarea::where('docente_id', $docenteId)->pluck('_id')->toArray();
        $tareaIds = array_map(function ($id) {
            return (string) $id;
        }, $tareasDocente);

        // Obtener entregas de esas tareas que no estén validadas
        $entregas = Entrega::whereIn('tarea_id', $tareaIds)
            ->where('estado', '!=', 'Validado')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($entrega) {
                $tarea = Tarea::find($entrega->tarea_id);
                return [
                    '_id' => $entrega->_id,
                    'tarea_id' => $entrega->tarea_id,
                    'titulo_tarea' => $tarea?->titulo ?? 'Tarea eliminada',
                    'asignatura_id' => $tarea?->asignatura_id ?? null,
                    'alumno_id' => $entrega->alumno_id,
                    'archivo_url' => $entrega->archivo_url,
                    'fecha_envio' => $entrega->fecha_envio,
                    'estado' => $entrega->estado,
                    'tiene_evaluacion_ia' => !is_null($entrega->pre_evaluacion_ia),
                    'puntaje_sugerido_ia' => $entrega->pre_evaluacion_ia['puntaje_sugerido'] ?? null,
                    'feedback_ia' => $entrega->pre_evaluacion_ia['feedback'] ?? null,
                    'contenido_texto' => substr($entrega->contenido_texto ?? '', 0, 500),
                ];
            });

        return response()->json($entregas, 200);
    }

    private function evaluarConIA(Entrega $entrega, Tarea $tarea, GeminiEvaluationService $geminiService): void
    {
        $textoExtraidoDelPDF = $entrega->contenido_texto ?? $this->extraerTextoDelPdf(storage_path('app/public/' . $entrega->archivo_url));

        $evaluacionIA = $geminiService->evaluarEntrega($tarea, $textoExtraidoDelPDF);

        $entrega->pre_evaluacion_ia = [
            'puntaje_sugerido' => $evaluacionIA['puntaje_sugerido'],
            'feedback' => $evaluacionIA['feedback'],
            'fecha_evaluacion' => now()
        ];

        $entrega->estado = 'Evaluado por IA';
        $entrega->save();
    }

    private function extraerTextoDelPdf(string $rutaAbsoluta): string
    {
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($rutaAbsoluta);
            $texto = trim($pdf->getText());

            return Str::limit($texto, 12000, '');
        } catch (\Exception $e) {
            return 'No fue posible extraer texto del PDF.';
        }
    }
}