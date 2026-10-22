<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comentario\StoreComentarioRequest;
use App\Http\Resources\ComentarioResource;
use App\Models\Comentario;
use App\Models\Tarea;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class ComentarioController extends Controller
{
    public function index(Tarea $tarea): AnonymousResourceCollection
    {
        Gate::authorize('view', $tarea->materia);

        return ComentarioResource::collection(
            $tarea->comentarios()->with(['autor', 'tarea.materia'])->oldest()->orderBy('id')->get()
        );
    }

    public function store(StoreComentarioRequest $request, Tarea $tarea): JsonResponse
    {
        $comentario = $tarea->comentarios()->create([
            'user_id' => $request->user()->id,
            'mensaje' => $request->validated('mensaje'),
        ]);

        return (new ComentarioResource($comentario->load(['autor', 'tarea.materia'])))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Comentario $comentario): Response
    {
        Gate::authorize('delete', $comentario);

        $comentario->delete();

        return response()->noContent();
    }
}