<?php

namespace App\Services;

use App\Models\Rubrica;
use App\Models\RubricaCriterio;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class RubricaService
{
    public function crear(User $docente, array $datos): Rubrica
    {
        return DB::transaction(function () use ($docente, $datos) {
            $rubrica = $docente->rubricas()->create(Arr::only($datos, ['nombre', 'descripcion']));
            $this->guardarCriterios($rubrica, $datos['criterios']);

            return $rubrica;
        });
    }

    public function actualizar(Rubrica $rubrica, array $datos): Rubrica
    {
        return DB::transaction(function () use ($rubrica, $datos) {
            $rubrica->update(Arr::only($datos, ['nombre', 'descripcion']));

            if (isset($datos['criterios'])) {
                RubricaCriterio::where('rubrica_id', $rubrica->id)->delete();
                $this->guardarCriterios($rubrica, $datos['criterios']);
            }

            return $rubrica;
        });
    }

    /** El orden de la lista que manda el cliente es el orden de los criterios. */
    private function guardarCriterios(Rubrica $rubrica, array $criterios): void
    {
        foreach (array_values($criterios) as $i => $criterio) {
            $rubrica->criterios()->create([
                'nombre' => $criterio['nombre'],
                'descripcion' => $criterio['descripcion'] ?? null,
                'puntos' => $criterio['puntos'],
                'orden' => $i + 1,
            ]);
        }
    }
}