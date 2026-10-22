<?php

namespace App\Services;

use App\Enums\EstadoEntrega;
use App\Models\Entrega;
use App\Models\Materia;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class PromedioService
{
    private const PARCIALES = [1, 2, 3];

    private const ESCALA = 100;

    /** Vista del docente: cada alumno inscrito con sus parciales y su promedio final. */
    public function paraMateria(Materia $materia): array
    {
        $tareas = $materia->tareas()->get(['id', 'parcial', 'puntaje_maximo', 'fecha_limite']);

        $entregas = Entrega::whereIn('tarea_id', $tareas->pluck('id'))->get()->groupBy('user_id');

        return $materia->alumnos()->orderBy('nombre')->get()
            ->map(fn (User $alumno) => [
                'alumno' => ['id' => $alumno->id, 'nombre' => $alumno->nombre],
                ...$this->calcular($tareas, ($entregas->get($alumno->id) ?? collect())->keyBy('tarea_id')),
            ])
            ->values()
            ->all();
    }

    /** Vista del alumno: cada una de sus materias con sus parciales y su promedio final. */
    public function paraAlumno(User $alumno): array
    {
        $materias = $alumno->materiasInscritas()
            ->with('tareas:id,materia_id,parcial,puntaje_maximo,fecha_limite')
            ->orderBy('nombre')
            ->get();

        $entregas = Entrega::where('user_id', $alumno->id)
            ->whereIn('tarea_id', $materias->flatMap(fn (Materia $m) => $m->tareas)->pluck('id'))
            ->get()
            ->keyBy('tarea_id');

        return $materias->map(fn (Materia $materia) => [
            'materia' => ['id' => $materia->id, 'nombre' => $materia->nombre],
            ...$this->calcular($materia->tareas, $entregas),
        ])->values()->all();
    }

    /**
     * Regla de tres por parcial: puntos obtenidos ÷ puntos posibles × 100. Cada tarea pesa según su
     * puntaje máximo, así que no importa cuántas tareas haya dejado el docente.
     *
     * @param  Collection<int, \App\Models\Tarea>  $tareas
     * @param  Collection<int, Entrega>  $entregas  Entregas del alumno, indexadas por tarea_id
     */
    private function calcular(Collection $tareas, Collection $entregas): array
    {
        $vencidasComoCero = (bool) config('studia.promedios.vencidas_como_cero');

        $calculos = collect(self::PARCIALES)->map(function (int $parcial) use ($tareas, $entregas, $vencidasComoCero) {
            $deParcial = $tareas->where('parcial', $parcial);
            $obtenidos = 0.0;
            $posibles = 0.0;
            $contadas = 0;

            foreach ($deParcial as $tarea) {
                $entrega = $entregas->get($tarea->id);

                if ($entrega?->estado === EstadoEntrega::Calificado) {
                    // min(): por si el docente bajó el puntaje máximo después de calificar
                    $obtenidos += min((float) $entrega->calificacion_final, (float) $tarea->puntaje_maximo);
                    $posibles += $tarea->puntaje_maximo;
                    $contadas++;
                } elseif ($entrega === null && $vencidasComoCero && $tarea->fecha_limite?->isPast()) {
                    $posibles += $tarea->puntaje_maximo;   // venció sin entregar: suma 0 puntos
                    $contadas++;
                }
                // Entregada sin calificar o todavía en plazo: aún no cuenta
            }

            $bruto = $posibles > 0 ? $obtenidos / $posibles * self::ESCALA : null;

            return [
                'parcial' => $parcial,
                'promedio' => $bruto !== null ? round($bruto, 2) : null,
                'tareas_contadas' => $contadas,
                'tareas_total' => $deParcial->count(),
                'puntos_obtenidos' => $obtenidos,
                'puntos_posibles' => $posibles,
                'bruto' => $bruto,   // sin redondear: el promedio final se calcula con esto
            ];
        });

        $brutos = $calculos->pluck('bruto')->filter(fn ($valor) => $valor !== null);

        return [
            'parciales' => $calculos->map(fn (array $c) => Arr::except($c, 'bruto'))->all(),
            'parciales_con_datos' => $brutos->count(),
            'promedio_final' => $brutos->isNotEmpty() ? round($brutos->avg(), 2) : null,
        ];
    }
}