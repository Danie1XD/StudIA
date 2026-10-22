<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AnuncioResource;
use App\Http\Resources\SeccionResource;
use App\Http\Resources\TareaResource;
use App\Models\Materia;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class MateriaContenidoController extends Controller
{
    /** La clase completa: cada sección con sus tareas y anuncios, más lo que no tiene sección. */
    public function __invoke(Materia $materia): JsonResponse
    {
        Gate::authorize('view', $materia);

        $secciones = $materia->secciones()
            ->with([
                'tareas' => fn ($q) => $q->latest(),
                'anuncios' => fn ($q) => $q->with('autor')->latest(),
            ])
            ->get();

        return response()->json([
            'data' => [
                'secciones' => SeccionResource::collection($secciones),
                'sin_seccion' => [
                    'tareas' => TareaResource::collection(
                        $materia->tareas()->whereNull('seccion_id')->latest()->get()
                    ),
                    'anuncios' => AnuncioResource::collection(
                        $materia->anuncios()->whereNull('seccion_id')->with('autor')->latest()->get()
                    ),
                ],
            ],
        ]);
    }
}