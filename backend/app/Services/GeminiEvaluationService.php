<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class GeminiEvaluationService
{
    public function evaluarEntrega($tarea, $textoAlumno)
    {
        $apiKey = config('services.gemini.key') ?? env('GEMINI_API_KEY');

        if (empty($apiKey)) {
            Log::error('StudIA Gemini Error: Sin API Key.');
            return $this->obtenerFallback($tarea);
        }

        // Modelo actual activo en Google AI Studio
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-lite:generateContent?key={$apiKey}";

        $titulo = $tarea->titulo ?? 'Sin título';
        $descripcion = $tarea->descripcion ?? 'Sin descripción.';
        $rubrica = is_array($tarea->rubrica) ? json_encode($tarea->rubrica, JSON_UNESCAPED_UNICODE) : ($tarea->rubrica ?? 'Evalúa el contenido.');
        $puntajeMaximo = (int) ($tarea->puntaje_maximo ?? 100);

        $prompt = "Eres un profesor universitario evaluando una tarea para StudIA.\n" .
            "DETALLES DE LA TAREA:\n" .
            "- Título: {$titulo}\n" .
            "- Instrucciones: {$descripcion}\n" .
            "- Rúbrica / Criterios: {$rubrica}\n" .
            "- Puntaje Máximo: {$puntajeMaximo}\n\n" .
            "TEXTO DEL ALUMNO (Extraído del PDF):\n\"\"\"\n{$textoAlumno}\n\"\"\"\n\n" .
            "INSTRUCCIONES DE SALIDA:\n" .
            "Devuelve ÚNICAMENTE un JSON válido con esta estructura exacta:\n" .
            "{\"puntaje_sugerido\": numero_de_0_a_{$puntajeMaximo}, \"feedback\": \"retroalimentación constructiva y detallada\"}";

        try {
            $response = Http::timeout(25)->post($url, [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'response_mime_type' => 'application/json'
                ]
            ]);

            if ($response->successful()) {
                $result = $response->json();
                $textoRespuesta = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;

                if ($textoRespuesta) {
                    $jsonLimpio = preg_replace('/```(?:json)?|\n```/', '', trim($textoRespuesta));
                    $resultado = json_decode($jsonLimpio, true);

                    if (is_array($resultado) && isset($resultado['puntaje_sugerido'], $resultado['feedback'])) {
                        Log::info("StudIA Gemini: Evaluación exitosa con gemini-2.0-flash");

                        return [
                            'puntaje_sugerido' => max(0, min($puntajeMaximo, (int) $resultado['puntaje_sugerido'])),
                            'feedback' => (string) $resultado['feedback']
                        ];
                    }
                }
            } else {
                Log::error("StudIA Gemini HTTP Error: Status " . $response->status(), [
                    'body' => $response->body()
                ]);
            }
        } catch (Exception $e) {
            Log::error("StudIA Exception Gemini: " . $e->getMessage());
        }

        return $this->obtenerFallback($tarea);
    }

    private function obtenerFallback($tarea)
    {
        $puntajeMaximo = (int) ($tarea->puntaje_maximo ?? 100);
        return [
            'puntaje_sugerido' => (int) round($puntajeMaximo * 0.85),
            'feedback' => 'La evaluación preliminar de IA se generó con un respaldo de seguridad porque la API de Gemini no respondió como se esperaba. Revisa el contenido y la rúbrica para ajustar la valoración.'
        ];
    }
}