<?php

namespace App\Services;

use App\Models\Entrega;
use App\Models\Materia;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MateriaService
{
    public function crear(User $docente, array $datos): Materia
    {
        return Materia::create([
            ...$datos,
            'docente_id' => $docente->id,
            'codigo_acceso' => $this->generarCodigo(),
        ]);
    }

    public function inscribir(User $alumno, string $codigo): Materia
    {
        $materia = Materia::where('codigo_acceso', $codigo)->first();

        if (! $materia) {
            throw ValidationException::withMessages([
                'codigo_acceso' => ['El código no existe.'],
            ]);
        }

        if ($materia->alumnos()->where('users.id', $alumno->id)->exists()) {
            throw ValidationException::withMessages([
                'codigo_acceso' => ['Ya estás inscrito en esta materia.'],
            ]);
        }

        $materia->alumnos()->attach($alumno->id);

        return $materia;
    }

    /** Borra la materia y, con ella, los archivos de sus tareas y de las entregas de los alumnos. */
    public function eliminar(Materia $materia): void
    {
        $tareas = $materia->tareas()->get(['id', 'archivo_path']);

        $rutas = $tareas->pluck('archivo_path')
            ->merge(Entrega::whereIn('tarea_id', $tareas->pluck('id'))->pluck('archivo_path'))
            ->filter()
            ->values()
            ->all();

        $materia->delete();   // tareas, entregas y comentarios se borran en cascada en la base de datos

        // Primero la base y después los archivos: si falla el borrado de un archivo no queda un registro roto
        Storage::disk('local')->delete($rutas);
    }

    private function generarCodigo(): string
    {
        do {
            $codigo = Str::upper(Str::random(6));
        } while (Materia::where('codigo_acceso', $codigo)->exists());

        return $codigo;
    }
}