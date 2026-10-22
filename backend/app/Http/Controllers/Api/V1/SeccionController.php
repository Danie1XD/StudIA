<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seccion\ReordenarSeccionesRequest;
use App\Http\Requests\Seccion\StoreSeccionRequest;
use App\Http\Requests\Seccion\UpdateSeccionRequest;
use App\Http\Resources\SeccionResource;
use App\Models\Materia;
use App\Models\Seccion;
use App\Services\SeccionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class SeccionController extends Controller
{
    public function __construct(private SeccionService $secciones) {}

    public function index(Materia $materia): AnonymousResourceCollection
    {
        Gate::authorize('view', $materia);

        return SeccionResource::collection(
            $materia->secciones()->withCount(['tareas', 'anuncios'])->get()
        );
    }

    public function store(StoreSeccionRequest $request, Materia $materia): JsonResponse
    {
        $seccion = $this->secciones->crear($materia, $request->validated());

        return (new SeccionResource($seccion))->response()->setStatusCode(201);
    }

    public function update(UpdateSeccionRequest $request, Seccion $seccion): SeccionResource
    {
        $seccion->update($request->validated());

        return new SeccionResource($seccion);
    }

    public function destroy(Seccion $seccion): Response
    {
        Gate::authorize('update', $seccion->materia);

        $seccion->delete();   // su contenido queda "sin sección" gracias a nullOnDelete

        return response()->noContent();
    }

    public function reordenar(ReordenarSeccionesRequest $request, Materia $materia): AnonymousResourceCollection
    {
        $this->secciones->reordenar($materia, $request->validated('orden'));

        return SeccionResource::collection($materia->secciones()->get());
    }
}