<?php

namespace App\Services;

use App\Models\Materia;
use App\Models\Seccion;
use Illuminate\Support\Facades\DB;

class SeccionService
{
    public function crear(Materia $materia, array $datos): Seccion
    {
        $ultimoOrden = Seccion::where('materia_id', $materia->id)->max('orden');

        return $materia->secciones()->create([
            ...$datos,
            'orden' => ($ultimoOrden ?? 0) + 1,
        ]);
    }

    /** @param int[] $ids Ids de las secciones en el orden deseado */
    public function reordenar(Materia $materia, array $ids): void
    {
        DB::transaction(function () use ($materia, $ids) {
            foreach ($ids as $posicion => $id) {
                Seccion::where('materia_id', $materia->id)
                    ->whereKey($id)
                    ->update(['orden' => $posicion + 1]);
            }
        });
    }
}