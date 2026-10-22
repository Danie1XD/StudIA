<?php

namespace App\Services\AI;

use App\Exceptions\GeminiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GeminiClient
{
    private const URL_BASE = 'https://generativelanguage.googleapis.com/v1beta/models/';

    /** gemini-3.8-flash no acepta el nivel "minimal". */
    private const NIVELES = ['low', 'medium', 'high'];

    private const INTENTOS = 2;

    public function __construct(
        private string $apiKey,
        private string $modelo,
        private string $nivelPensamiento,
        private int $timeout,
    ) {}

    public function modelo(): string
    {
        return $this->modelo;
    }

    /**
     * Respuesta estructurada: Gemini debe cumplir el JSON Schema recibido.
     *
     * @param  array<int, array<string, mixed>>  $partes  Partes del mensaje (archivos y texto)
     * @param  array<string, mixed>  $esquema
     * @return array<string, mixed>
     */
    public function generarJson(array $partes, string $instruccionSistema, array $esquema): array
    {
        $respuesta = $this->enviar([
            'systemInstruction' => $this->instruccion($instruccionSistema),
            'contents' => [['role' => 'user', 'parts' => $partes]],
            'generationConfig' => [
                'thinkingConfig' => ['thinkingLevel' => $this->nivelValido()],
                'responseMimeType' => 'application/json',
                'responseJsonSchema' => $esquema,
            ],
        ]);

        $datos = json_decode($this->textoDe($respuesta), true);

        if (! is_array($datos)) {
            throw new GeminiException('La IA devolvió una respuesta que no se pudo interpretar. Inténtalo de nuevo.', 502);
        }

        return $datos;
    }

    /**
     * Conversación en texto libre. El último turno debe ser del usuario.
     *
     * @param  array<int, array{role: string, parts: array<int, array<string, string>>}>  $mensajes
     */
    public function generarTexto(array $mensajes, string $instruccionSistema): string
    {
        $respuesta = $this->enviar([
            'systemInstruction' => $this->instruccion($instruccionSistema),
            'contents' => $mensajes,
            'generationConfig' => [
                'thinkingConfig' => ['thinkingLevel' => $this->nivelValido()],
            ],
        ]);

        return $this->textoDe($respuesta);
    }

    private function instruccion(string $texto): array
    {
        return ['parts' => [['text' => $texto]]];
    }

    private function enviar(array $cuerpo): Response
    {
        if ($this->apiKey === '') {
            throw new GeminiException('La IA no está configurada: falta GEMINI_API_KEY en el .env.', 503);
        }

        $url = self::URL_BASE.$this->modelo.':generateContent';

        for ($intento = 1; $intento <= self::INTENTOS; $intento++) {
            try {
                // La clave va en un encabezado, nunca en la URL (así no queda en logs ni historiales)
                $respuesta = Http::withHeaders(['x-goog-api-key' => $this->apiKey])
                    ->timeout($this->timeout)
                    ->acceptJson()
                    ->post($url, $cuerpo);
            } catch (ConnectionException $e) {
                if ($intento === self::INTENTOS) {
                    Log::warning('Gemini: sin conexión', ['error' => $e->getMessage()]);

                    throw new GeminiException('No se pudo conectar con el servicio de IA. Revisa la conexión e inténtalo de nuevo.', 503);
                }
                usleep(1_500_000);

                continue;
            }

            if ($respuesta->successful()) {
                return $respuesta;
            }

            // Solo se reintentan los fallos temporales del servidor
            if (in_array($respuesta->status(), [500, 503], true) && $intento < self::INTENTOS) {
                usleep(1_500_000);

                continue;
            }

            $this->lanzarPorError($respuesta);
        }

        throw new GeminiException('El servicio de IA no respondió.', 503);
    }

    private function lanzarPorError(Response $respuesta): never
    {
        // El detalle técnico va al log; al usuario solo le llega un mensaje claro
        Log::warning('Gemini rechazó la petición', [
            'estado' => $respuesta->status(),
            'modelo' => $this->modelo,
            'detalle' => Str::limit(preg_replace('/\s+/', ' ', $respuesta->body()), 1200),
        ]);

        throw match (true) {
            $respuesta->status() === 429 => new GeminiException('Se alcanzó el límite de uso de la IA. Espera unos minutos e inténtalo de nuevo.', 429),
            in_array($respuesta->status(), [401, 403], true) => new GeminiException('La clave de la IA no es válida o no tiene permiso para este modelo.', 502),
            $respuesta->status() === 404 => new GeminiException("El modelo «{$this->modelo}» no está disponible. Cambia GEMINI_MODEL en el .env.", 502),
            $respuesta->status() === 400 => new GeminiException('La IA rechazó la solicitud (archivo o formato no válido). Revisa el documento del alumno.', 502),
            default => new GeminiException('El servicio de IA no está disponible en este momento. Inténtalo más tarde.', 503),
        };
    }

    private function textoDe(Response $respuesta): string
    {
        $texto = collect($respuesta->json('candidates.0.content.parts') ?? [])
            ->reject(fn ($parte) => ! empty($parte['thought']))
            ->pluck('text')
            ->filter()
            ->implode('');

        if (trim($texto) === '') {
            Log::warning('Gemini devolvió una respuesta vacía', [
                'finishReason' => $respuesta->json('candidates.0.finishReason'),
                'bloqueo' => $respuesta->json('promptFeedback.blockReason'),
            ]);

            throw new GeminiException('La IA no devolvió una respuesta (posible bloqueo de contenido). Inténtalo de nuevo.', 502);
        }

        return trim($texto);
    }

    private function nivelValido(): string
    {
        return in_array($this->nivelPensamiento, self::NIVELES, true) ? $this->nivelPensamiento : 'low';
    }
}