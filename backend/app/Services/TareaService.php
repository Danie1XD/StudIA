<?php

namespace App\Services;

use App\Models\Materia;
use App\Models\Tarea;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class TareaService
{
    public function crear(Materia $materia, array $datos): Tarea
    {
        $archivo = $datos['archivo'] ?? null;
        unset($datos['archivo']);

        return $materia->tareas()->create([
            ...$this->normalizarFecha($datos),
            'puntaje_maximo' => $datos['puntaje_maximo'] ?? 100,
            'parcial' => $datos['parcial'] ?? 1,
            'archivo_path' => $archivo?->store('tareas', 'local'),
        ]);
    }

    public function actualizar(Tarea $tarea, array $datos): Tarea
    {
        $archivo = $datos['archivo'] ?? null;
        $quitar = $datos['quitar_archivo'] ?? false;
        unset($datos['archivo'], $datos['quitar_archivo']);

        if ($archivo) {
            $this->borrarArchivo($tarea);   // el PDF viejo no se queda huérfano en el disco
            $datos['archivo_path'] = $archivo->store('tareas', 'local');
        } elseif ($quitar) {
            $this->borrarArchivo($tarea);
            $datos['archivo_path'] = null;
        }

        $tarea->update($this->normalizarFecha($datos));

        return $tarea;
    }

    public function eliminar(Tarea $tarea): void
    {
        $rutas = $tarea->entregas()->pluck('archivo_path')
            ->push($tarea->archivo_path)
            ->filter()
            ->values()
            ->all();

        $tarea->delete();   // sus entregas y comentarios se borran en cascada en la base

        Storage::disk('local')->delete($rutas);
    }

    /**
     * Las fechas se guardan en la zona de la aplicación (UTC). Laravel no convierte solo los textos
     * con zona horaria ("...-06:00"): sin esto se guardaría la hora local como si fuera UTC.
     */
    private function normalizarFecha(array $datos): array
    {
        if (! empty($datos['fecha_limite'])) {
            $datos['fecha_limite'] = Carbon::parse($datos['fecha_limite'])->setTimezone(config('app.timezone'));
        }

        return $datos;
    }

    private function borrarArchivo(Tarea $tarea): void
    {
        if ($tarea->archivo_path) {
            Storage::disk('local')->delete($tarea->archivo_path);
        }
    }
}