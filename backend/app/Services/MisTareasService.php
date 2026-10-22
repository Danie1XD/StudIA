<?php

namespace App\Services;

use App\Enums\EstadoEntrega;
use App\Enums\EstadoTarea;
use App\Http\Resources\EntregaResource;
use App\Http\Resources\TareaResource;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Support\Collection;

class MisTareasService
{
    public function paraAlumno(User $alumno, ?EstadoTarea $filtro = null): array
    {
        $tareas = Tarea::query()
            ->whereHas('materia.alumnos', fn ($q) => $q->where('users.id', $alumno->id))
            ->with([
                'materia:id,nombre',
                'entregas' => fn ($q) => $q->where('user_id', $alumno->id),
            ])
            ->orderByRaw('fecha_limite IS NULL, fecha_limite ASC')   // las sin fecha, al final
            ->get()
            ->each(fn (Tarea $t) => $t->setAttribute('estado_alumno', $this->estadoPara($t)));

        // Los conteos se calculan antes de filtrar, para los botones del filtro
        $conteos = collect(EstadoTarea::cases())
            ->mapWithKeys(fn (EstadoTarea $e) => [$e->value => $tareas->where('estado_alumno', $e)->count()])
            ->all();

        if ($filtro) {
            $tareas = $tareas->where('estado_alumno', $filtro);
        }

        $grupos = $tareas->groupBy('materia_id')->map(fn (Collection $grupo) => [
            'materia' => [
                'id' => $grupo->first()->materia->id,
                'nombre' => $grupo->first()->materia->nombre,
            ],
            'tareas' => $grupo->map(function (Tarea $t) {
                $entrega = $t->entregas->first();

                return [
                    ...(new TareaResource($t))->resolve(),
                    'estado' => $t->estado_alumno->value,
                    'entrega' => $entrega ? (new EntregaResource($entrega))->resolve() : null,
                ];
            })->values()->all(),
        ])->values()->all();

        return ['data' => $grupos, 'conteos' => $conteos];
    }

    public function paraDocente(User $user): array
    {
        $tareas = Tarea::query()
            ->when(! $user->esAdmin(), fn ($q) => $q->whereHas(
                'materia', fn ($m) => $m->where('docente_id', $user->id)
            ))
            ->with('materia:id,nombre')
            ->withCount([
                'entregas',
                'entregas as calificadas_count' => fn ($q) => $q->where('estado', EstadoEntrega::Calificado->value),
            ])
            ->orderByRaw('fecha_limite IS NULL, fecha_limite ASC')
            ->get();

        $grupos = $tareas->groupBy('materia_id')->map(fn (Collection $grupo) => [
            'materia' => [
                'id' => $grupo->first()->materia->id,
                'nombre' => $grupo->first()->materia->nombre,
            ],
            'tareas' => $grupo->map(fn (Tarea $t) => [
                ...(new TareaResource($t))->resolve(),
                'entregas_count' => $t->entregas_count,
                'calificadas_count' => $t->calificadas_count,
            ])->values()->all(),
        ])->values()->all();

        return ['data' => $grupos];
    }

    private function estadoPara(Tarea $tarea): EstadoTarea
    {
        $entrega = $tarea->entregas->first();

        if ($entrega?->estado === EstadoEntrega::Calificado) {
            return EstadoTarea::Calificada;
        }

        if ($entrega) {
            return EstadoTarea::Entregada;
        }

        if ($tarea->fecha_limite?->isPast()) {
            return EstadoTarea::SinEntregar;
        }

        return EstadoTarea::Pendiente;
    }
}