<?php

namespace App\Http\Controllers;

use App\Models\Entrega;
use App\Models\Tarea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class EntregaController extends Controller
{
    // Enviar trabajo o entrega (Exclusivo Alumno)
    public function store(Request $request)
    {
        if ($request->user()->rol !== 'alumno') {
            return response()->json(['message' => 'Solo los alumnos pueden realizar entregas.'], 403);
        }

        $request->validate([
            'tarea_id' => 'required|exists:tareas,id',
            'contenido' => 'nullable|string', // Aquí se guarda el enlace o comentario privado
            'archivo' => 'nullable|file|max:20480', // Máx 20MB (PDFs, imágenes, zips, word, etc.)
        ]);

        // Verificamos que al menos mande un archivo o un texto/enlace
        if (!$request->hasFile('archivo') && empty($request->contenido)) {
            return response()->json(['message' => 'Debes adjuntar un archivo, un enlace o un comentario para entregar.'], 400);
        }

        $rutaArchivo = null;
        if ($request->hasFile('archivo')) {
            $rutaArchivo = $request->file('archivo')->store('entregas_archivos', 'public');
        }

        // Buscamos si ya existía una entrega previa del alumno para actualizarla o crear una nueva
        $entrega = Entrega::updateOrCreate(
            [
                'tarea_id' => $request->tarea_id,
                'user_id' => $request->user()->id,
            ],
            [
                'contenido' => $request->contenido,
                'archivo_url' => $rutaArchivo ?? ($request->hasFile('archivo') ? $rutaArchivo : null),
                'estado' => 'entregado',
            ]
        );

        return response()->json([
            'message' => '¡Trabajo entregado con éxito!',
            'entrega' => $entrega
        ], 200);
    }

    // 2. Solicitar Pre-evaluación a Google Gemini AI (Exclusivo Docente)
    public function evaluarIA(Request $request,$id)
    {
        if ($request->user()->rol !== 'docente') {
            return response()->json(['message' => 'Solo los docentes pueden evaluar entregas.'], 403);
        }

        $entrega = Entrega::with('tarea', 'alumno')->findOrFail($id);

        $prompt = "Actúa como un profesor experto, justo y empático. Necesito que evalúes el trabajo escolar de un alumno basado estrictamente en las instrucciones dadas.

        INSTRUCCIONES Y RÚBRICA DE LA TAREA:
        - Título: " . $entrega->tarea->titulo . "
        - Instrucciones: " . strip_tags($entrega->tarea->descripcion) . "
        - Puntaje Máximo Posible: " . $entrega->tarea->puntaje_maximo . " puntos.

        TRABAJO ENTREGADO POR EL ALUMNO (" . $entrega->alumno->nombre . "):
        " . (!empty($entrega->contenido) ?$entrega->contenido : "El alumno adjuntó un archivo/documento.") . "

        INSTRUCCIÓN DE RESPUESTA:
        Analiza el trabajo y devuelve tu evaluación ÚNICAMENTE en formato JSON válido, sin bloques de código Markdown (sin ```json), usando exactamente esta estructura:
        {
            \"calificacion_sugerida\": (número entero o decimal entre 0 y " . $entrega->tarea->puntaje_maximo . "),
            \"puntos_fuertes\": \"Breve párrafo mencionando los aciertos y lo que se hizo bien.\",
            \"areas_mejora\": \"Breve párrafo indicando qué faltó, errores técnicos o qué se puede mejorar.\",
            \"retroalimentacion_general\": \"Un mensaje cordial dirigiendo unas palabras motivadoras al estudiante.\"
        }";

        try {
            $apiKey = trim(env('GEMINI_API_KEY'));

            if (empty($apiKey)) {
                return response()->json([
                    'message' => 'La clave GEMINI_API_KEY está vacía en el .env.'
                ], 500);
            }

            // Apuntamos al modelo actual y activo de tu cuenta (Gemini 3.6 Flash)
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.6-flash:generateContent";

            // 2. Pasa estrictamente la variable $url como primer parámetro (NUNCA la variable $apiKey sola)
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'x-goog-api-key' => $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ]
                ]);

            if ($response->failed()) {
                $errorGoogle = $response->json();
                $mensajeExacto = $errorGoogle['error']['message'] ?? $response->body();
                return response()->json([
                    'message' => 'Google Gemini rechazó la petición: ' . $mensajeExacto
                ], 500);
            }

            $result = $response->json();
            $textoIA = $result['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
            
            $textoIA = str_replace(['```json', '```'], '', trim($textoIA));
            $datosIA = json_decode($textoIA, true);

            if (!$datosIA) {
                return response()->json([
                    'message' => 'El formato devuelto por la IA no fue un JSON válido.', 
                    'raw' => $textoIA
                ], 500);
            }

            $entrega->update([
                'calificacion_ia' => $datosIA['calificacion_sugerida'] ?? 0,
                'retroalimentacion_ia' => json_encode($datosIA),
                'estado' => 'pre_evaluado'
            ]);

            return response()->json([
                'message' => 'Pre-evaluación generada exitosamente.',
                'evaluacion_ia' => $datosIA,
                'entrega' => $entrega
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error técnico al ejecutar la evaluación: ' . $e->getMessage()
            ], 500);
        }
    }

    // 3. Guardar la evaluación final aprobada/editada por el Docente
    public function guardarEvaluacion(Request $request, $id)
    {
        if ($request->user()->rol !== 'docente') {
            return response()->json(['message' => 'Sin permisos para calificar.'], 403);
        }

        $request->validate([
            'calificacion_final' => 'required|numeric|min:0',
            'retroalimentacion_final' => 'required|string',
        ]);

        $entrega = Entrega::findOrFail($id);
        
        $entrega->update([
            'calificacion_final' => $request->calificacion_final,
            'retroalimentacion_final' => $request->retroalimentacion_final,
            'estado' => 'calificado'
        ]);

        return response()->json([
            'message' => '¡Evaluación enviada al estudiante con éxito!',
            'entrega' => $entrega
        ], 200);
    }

    public function destroy($id)
{
    try {
        $entrega = Entrega::findOrFail($id);

        // Si tiene archivo adjunto, lo eliminamos del storage de forma segura
        if ($entrega->archivo_url) {
            // Limpiamos la ruta por si incluye 'storage/'
            $rutaLimpia = str_replace('storage/', '', $entrega->archivo_url);
            
            if (Storage::disk('public')->exists($rutaLimpia)) {
                Storage::disk('public')->delete($rutaLimpia);
            }
        }

        // Eliminamos el registro de la base de datos
        $entrega->delete();

        return response()->json([
            'message' => 'Entrega anulada correctamente'
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Error al anular la entrega: ' . $e->getMessage()
        ], 500);
    }
}
}