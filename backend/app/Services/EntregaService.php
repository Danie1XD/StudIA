<?php

namespace App\Services;

use App\Enums\EstadoEntrega;
use App\Models\Entrega;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class EntregaService
{
    /** Crea la entrega del alumno o reemplaza la que ya tenía (una por alumno y tarea). */
    public function entregar(User $alumno, Tarea $tarea, array $datos): Entrega
    {
        $entrega = Entrega::firstOrNew([
            'tarea_id' => $tarea->id,
            'user_id' => $alumno->id,
        ]);

        if ($entrega->estado === EstadoEntrega::Calificado) {
            throw ValidationException::withMessages([
                'entrega' => ['Esta entrega ya fue calificada y no se puede modificar.'],
            ]);
        }

        if (isset($datos['archivo'])) {
            $this->borrarArchivo($entrega);
            $entrega->archivo_path = $datos['archivo']->store('entregas', 'local');
        }

        // Si el texto viene en la petición (aunque venga vacío) es el texto nuevo; si no, se conserva
        if (array_key_exists('contenido', $datos)) {
            $entrega->contenido = $datos['contenido'];
        }        $entrega->estado = EstadoEntrega::Entregado;
        $entrega->entregada_at = now();
        $entrega->save();

        // Si el trabajo cambió, la evaluación previa de la IA ya no corresponde
        $entrega->evaluacionIa()->delete();

        return $entrega;
    }

    public function anular(Entrega $entrega): void
    {
        $this->borrarArchivo($entrega);
        $entrega->delete();
    }

    public function calificar(Entrega $entrega, array $datos): Entrega
    {
        $entrega->update([
            'calificacion_final' => $datos['calificacion_final'],
            'retroalimentacion_final' => $datos['retroalimentacion_final'],
            'estado' => EstadoEntrega::Calificado,
            'calificada_at' => now(),
        ]);

        return $entrega;
    }

    private function borrarArchivo(Entrega $entrega): void
    {
        if ($entrega->archivo_path) {
            Storage::disk('local')->delete($entrega->archivo_path);
        }
    }
}