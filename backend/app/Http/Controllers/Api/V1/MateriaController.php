<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Materia\StoreMateriaRequest;
use App\Http\Requests\Materia\UnirseMateriaRequest;
use App\Http\Requests\Materia\UpdateMateriaRequest;
use App\Http\Resources\MateriaResource;
use App\Models\Materia;
use App\Services\MateriaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class MateriaController extends Controller
{
    public function __construct(private MateriaService $materias) {}

    /** Cada rol ve sus materias: el docente las que imparte, el alumno en las que está inscrito. */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $consulta = match (true) {
            $user->esDocente() => $user->materiasImpartidas(),
            $user->esAlumno() => $user->materiasInscritas(),
            default => Materia::query(),   // admin: todas
        };

        return MateriaResource::collection(
            $consulta->with('docente')->withCount('alumnos')->orderBy('nombre')->get()
        );
    }

    public function store(StoreMateriaRequest $request): JsonResponse
    {
        $materia = $this->materias->crear($request->user(), $request->validated());

        return (new MateriaResource($materia->load('docente')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Materia $materia): MateriaResource
    {
        Gate::authorize('view', $materia);

        return new MateriaResource(
            $materia->load(['docente', 'secciones'])->loadCount('alumnos')
        );
    }

    public function update(UpdateMateriaRequest $request, Materia $materia): MateriaResource
    {
        $materia->update($request->validated());

        return new MateriaResource($materia->load('docente'));
    }

    public function destroy(Materia $materia): Response
    {
        Gate::authorize('delete', $materia);

        $this->materias->eliminar($materia);

        return response()->noContent();
    }

    public function unirse(UnirseMateriaRequest $request): MateriaResource
    {
        $materia = $this->materias->inscribir(
            $request->user(),
            $request->validated('codigo_acceso')
        );

        return new MateriaResource($materia->load('docente'));
    }
}