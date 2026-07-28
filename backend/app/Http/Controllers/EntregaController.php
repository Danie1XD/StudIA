<?php

namespace App\Http\Controllers;

use App\Models\Entrega;
use Illuminate\Http\Request;
use App\Services\GeminiEvaluationService;
use App\Models\Tarea;
use Smalot\PdfParser\Parser;

class EntregaController extends Controller
{
    // Alumno sube su tarea
    public function store(Request $request)
    {
        $request->validate([
            'tarea_id' => 'required|string',
            'archivo' => 'required|mimes:pdf|max:10240' // Validamos que obligatoriamente sea PDF y máximo 10MB
        ]);

        // Guardamos el archivo físicamente en storage/app/public/entregas
        $rutaArchivo = $request->file('archivo')->store('entregas', 'public');

        $entrega = Entrega::create([
            'tarea_id' => $request->tarea_id,
            'alumno_id' => $request->user()->_id, 
            'archivo_url' => $rutaArchivo, // Guardamos la ruta relativa en la base de datos
            'fecha_envio' => now(),
            'estado' => 'Pendiente',
            'pre_evaluacion_ia' => null,
            'revision_docente' => null
        ]);

        return response()->json($entrega, 201);
    }

    // Docente valida y asigna calificación final
    public function evaluar(Request $request, $id)
    {
        $request->validate([
            'calificacion_final' => 'required|numeric',
            'comentarios' => 'nullable|string'
        ]);

        $entrega = Entrega::findOrFail($id);

        // Cumplimos con la Trazabilidad de Modificaciones (Auditoría Básica)
        $entrega->revision_docente = [
            'calificacion_final' => $request->calificacion_final,
            'comentarios' => $request->comentarios,
            'validado_por_id' => $request->user()->_id, // ID del Docente
            'fecha_validacion' => now()
        ];
        
        $entrega->estado = 'Validado';
        $entrega->save();

        return response()->json($entrega, 200);
    }
    // No olvides importar las clases arriba:
    

    public function solicitarEvaluacionIA(Request $request, $id, GeminiEvaluationService $geminiService)
    {
        $entrega = Entrega::findOrFail($id);
        
        if ($entrega->estado === 'Validado') {
            return response()->json(['error' => 'Esta tarea ya tiene una calificación definitiva.'], 400);
        }

        $tarea = Tarea::findOrFail($entrega->tarea_id);

        try {
            // 1. Instanciamos el lector de PDF
            $parser = new Parser();

            /* 
               2. Obtenemos la ruta real del archivo. 
               Asumiendo que guardaste el archivo en storage/app/public/entregas/ 
               cuando el alumno hizo el POST a /entregas
            */
            $rutaAbsoluta = storage_path('app/public/' . $entrega->archivo_url);
            
            // 3. Extraemos todo el texto del documento
            $pdf = $parser->parseFile($rutaAbsoluta);
            $textoExtraidoDelPDF = $pdf->getText();

            // 4. Enviamos la rúbrica y el texto real a Gemini
            $evaluacionIA = $geminiService->evaluarEntrega($tarea, $textoExtraidoDelPDF);

            // 5. Guardamos el resultado de la pre-calificación sugerida[cite: 1]
            $entrega->pre_evaluacion_ia = [
                'puntaje_sugerido' => $evaluacionIA['puntaje_sugerido'],
                'feedback' => $evaluacionIA['feedback'],
                'fecha_evaluacion' => now()
            ];
            
            // Cambiamos el estado para habilitar el distintivo en la interfaz[cite: 1]
            $entrega->estado = 'Evaluado por IA'; 
            $entrega->save();

            return response()->json([
                'mensaje' => 'Evaluación de IA completada exitosamente',
                'datos' => $entrega
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Falló la evaluación de IA: ' . $e->getMessage()], 500);
        }
    }

    
    
}