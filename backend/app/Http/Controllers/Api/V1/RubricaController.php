<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rubrica\StoreRubricaRequest;
use App\Http\Requests\Rubrica\UpdateRubricaRequest;
use App\Http\Resources\RubricaResource;
use App\Models\Rubrica;
use App\Services\RubricaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class RubricaController extends Controller
{
    public function __construct(private RubricaService $rubricas) {}

    /** Cada docente ve solo sus rúbricas; el admin ve todas. */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $consulta = $user->esAdmin() ? Rubrica::query() : $user->rubricas();

        return RubricaResource::collection(
            $consulta->with('criterios')->withCount('tareas')->orderBy('nombre')->get()
        );
    }

    public function store(StoreRubricaRequest $request): JsonResponse
    {
        $rubrica = $this->rubricas->crear($request->user(), $request->validated());

        return (new RubricaResource($rubrica->load('criterios')->loadCount('tareas')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Rubrica $rubrica): RubricaResource
    {
        Gate::authorize('view', $rubrica);

        return new RubricaResource($rubrica->load('criterios')->loadCount('tareas'));
    }

    public function update(UpdateRubricaRequest $request, Rubrica $rubrica): RubricaResource
    {
        $rubrica = $this->rubricas->actualizar($rubrica, $request->validated());

        return new RubricaResource($rubrica->load('criterios')->loadCount('tareas'));
    }

    /** Las tareas que la usaban quedan "sin rúbrica" (nullOnDelete en la base). */
    public function destroy(Rubrica $rubrica): Response
    {
        Gate::authorize('delete', $rubrica);

        $rubrica->delete();

        return response()->noContent();
    }
}