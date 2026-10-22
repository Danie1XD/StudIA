<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EvaluacionIaResource;
use App\Models\Entrega;
use App\Services\EvaluacionIaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class EvaluacionIaController extends Controller
{
    public function __construct(private EvaluacionIaService $evaluaciones) {}

    /** Genera (o vuelve a generar) la pre-evaluación. Solo el docente dueño de la materia. */
    public function store(Entrega $entrega): JsonResponse
    {
        Gate::authorize('calificar', $entrega);

        set_time_limit(180);   // la llamada a la IA puede tardar más que el límite de PHP

        $evaluacion = $this->evaluaciones->evaluar($entrega);

        return (new EvaluacionIaResource($evaluacion))
            ->response()
            ->setStatusCode($evaluacion->wasRecentlyCreated ? 201 : 200);
    }

    public function show(Entrega $entrega): EvaluacionIaResource
    {
        Gate::authorize('calificar', $entrega);

        abort_unless($entrega->evaluacionIa, 404, 'Esta entrega aún no tiene pre-evaluación de la IA.');

        return new EvaluacionIaResource($entrega->evaluacionIa);
    }
}