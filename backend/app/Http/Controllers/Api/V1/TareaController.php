<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tarea\StoreTareaRequest;
use App\Http\Requests\Tarea\UpdateTareaRequest;
use App\Http\Resources\TareaResource;
use App\Models\Materia;
use App\Models\Tarea;
use App\Services\TareaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TareaController extends Controller
{
    public function __construct(private TareaService $tareas) {}

    public function index(Materia $materia): AnonymousResourceCollection
    {
        Gate::authorize('view', $materia);

        return TareaResource::collection($materia->tareas()->latest()->get());
    }

    public function store(StoreTareaRequest $request, Materia $materia): JsonResponse
    {
        $tarea = $this->tareas->crear($materia, $request->validated());

        return (new TareaResource($tarea))->response()->setStatusCode(201);
    }

    public function show(Tarea $tarea): TareaResource
    {
        Gate::authorize('view', $tarea->materia);

        return new TareaResource($tarea);
    }

    public function update(UpdateTareaRequest $request, Tarea $tarea): TareaResource
    {
        return new TareaResource(
            $this->tareas->actualizar($tarea, $request->validated())
        );
    }

    public function destroy(Tarea $tarea): Response
    {
        Gate::authorize('update', $tarea->materia);

        $this->tareas->eliminar($tarea);

        return response()->noContent();
    }

    public function archivo(Tarea $tarea): StreamedResponse
    {
        Gate::authorize('view', $tarea->materia);

        abort_unless(
            $tarea->archivo_path && Storage::disk('local')->exists($tarea->archivo_path),
            404,
            'Esta tarea no tiene archivo adjunto.'
        );

        return Storage::disk('local')->download(
            $tarea->archivo_path,
            Str::slug($tarea->titulo).'.pdf'
        );
    }
}