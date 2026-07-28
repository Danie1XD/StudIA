<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class GeminiEvaluationService
{
    public function evaluarEntrega($tarea, $textoAlumno)
    {
        $apiKey = config('services.gemini.key');
        
        // Usamos el modelo Gemini 1.5 Flash que es rapidísimo para estas tareas
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $apiKey;

        // Aquí armamos el "Prompt Maestro"
        $prompt = "Eres un asistente académico universitario evaluador para el sistema StudIA. " .
                  "Tu objetivo es leer la entrega de un alumno, compararla contra la rúbrica de la tarea y generar una evaluación estructurada. " .
                  "IMPORTANTE: Debes responder ÚNICAMENTE con un objeto JSON válido, sin formato Markdown extra, con esta estructura exacta: " .
                  "{\"puntaje_sugerido\": [número del 1 al 100], \"feedback\": \"[tu retroalimentación detallada y constructiva]\"}. \n\n" .
                  "Rúbrica de la Tarea: " . json_encode($tarea->rubrica) . "\n\n" .
                  "Contenido entregado por el alumno: " . $textoAlumno;

        // Hacemos la petición a la API de Gemini
        $response = Http::post($url, [
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ]
        ]);

        if ($response->successful()) {
            $result = $response->json();
            $textoRespuesta = $result['candidates'][0]['content']['parts'][0]['text'];

            // Limpiamos la respuesta por si Gemini le pone las comillas invertidas de Markdown (```json ... ```)
            $textoRespuesta = str_replace(['```json', '```'], '', $textoRespuesta);
            
            // Decodificamos el JSON que nos regresó la IA y lo convertimos en un arreglo de PHP
            return json_decode(trim($textoRespuesta), true);
        }

        throw new Exception('Error al conectar con Gemini: ' . $response->body());
    }
}