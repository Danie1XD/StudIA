<?php

namespace App\Services;

use App\Models\User;
use App\Services\AI\GeminiClient;
use Illuminate\Support\Carbon;

class AsistenteService
{
    private const MAX_TAREAS_EN_CONTEXTO = 15;

    public function __construct(
        private GeminiClient $gemini,
        private MisTareasService $misTareas,
    ) {}

    /** @param array<int, array{rol: string, texto: string}> $historial */
    public function responder(User $usuario, string $mensaje, array $historial = []): string
    {
        return $this->gemini->generarTexto(
            $this->construirMensajes($historial, $mensaje),
            $this->instruccionSistema($usuario),
        );
    }

    private function construirMensajes(array $historial, string $mensaje): array
    {
        $mensajes = collect($historial)
            ->map(fn (array $turno) => [
                'role' => $turno['rol'] === 'asistente' ? 'model' : 'user',
                'parts' => [['text' => $turno['texto']]],
            ])
            // La conversación siempre empieza con un turno del usuario: así el saludo inicial
            // del asistente ("¡Hola! Soy el asistente...") no se manda a Gemini
            ->skipUntil(fn (array $turno) => $turno['role'] === 'user')
            ->values()
            ->all();

        $mensajes[] = ['role' => 'user', 'parts' => [['text' => $mensaje]]];

        return $mensajes;
    }

    /** Aquí se decide qué sabe y qué no hace el asistente. */
    private function instruccionSistema(User $usuario): string
    {
        $ahora = Carbon::now(config('studia.timezone'))->locale('es');

        $lineas = [
            'Eres el asistente virtual de StudIA, una plataforma universitaria para entregar tareas y recibir retroalimentación apoyada por IA. Conversas con un '.$usuario->rol->value.'.',
            'Ayudas con dudas básicas: cómo usar la plataforma, cómo organizar el estudio y explicaciones de conceptos.',
            '',
            'Reglas:',
            '- Responde en español, de forma clara y breve (unas 150 palabras como máximo, salvo que pidan más detalle).',
            '- No tomas decisiones académicas: calificaciones, prórrogas, revisiones o excepciones las decide el docente; si te lo piden, indícalo con amabilidad y sugiere hablar con él.',
            '- No inventes fechas, políticas ni datos de la institución. Si no aparecen en el contexto de abajo, di que no lo sabes.',
            '- Si te piden hacer una tarea, orienta y explica el tema, pero no escribas el trabajo completo para que lo entreguen.',
            '- No pidas contraseñas ni datos personales sensibles.',
            '- Si la pregunta no tiene relación con el estudio o la plataforma, redirige con amabilidad.',
            '',
            'Fecha y hora actuales: '.$ahora->translatedFormat('l j \d\e F \d\e Y, H:i').' (zona '.config('studia.timezone').').',
        ];

        if ($usuario->esAlumno()) {
            $lineas[] = '';
            $lineas[] = 'CONTEXTO (datos reales de la plataforma):';
            $lineas[] = $this->contextoDelAlumno($usuario);
        }

        return implode("\n", $lineas);
    }

    private function contextoDelAlumno(User $alumno): string
    {
        $pendientes = collect($this->misTareas->paraAlumno($alumno)['data'])
            ->flatMap(fn (array $grupo) => collect($grupo['tareas'])
                ->map(fn (array $tarea) => [...$tarea, 'materia' => $grupo['materia']['nombre']]))
            ->filter(fn (array $tarea) => in_array($tarea['estado'], ['pendiente', 'sin_entregar'], true))
            ->take(self::MAX_TAREAS_EN_CONTEXTO);

        if ($pendientes->isEmpty()) {
            return 'El alumno no tiene tareas pendientes ni vencidas sin entregar.';
        }

        $lineas = $pendientes->map(function (array $tarea) {
            $limite = $tarea['fecha_limite']
                ? Carbon::parse($tarea['fecha_limite'])->timezone(config('studia.timezone'))->format('d/m/Y H:i')
                : 'sin fecha límite';
            $estado = $tarea['estado'] === 'sin_entregar' ? 'VENCIDA sin entregar' : 'pendiente';

            return "- {$tarea['materia']}: {$tarea['titulo']} (límite: {$limite}, {$estado})";
        });

        return "Tareas del alumno pendientes o vencidas:\n".$lineas->implode("\n");
    }
}