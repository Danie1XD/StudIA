<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Anuncio\StoreAnuncioRequest;
use App\Http\Requests\Anuncio\UpdateAnuncioRequest;
use App\Http\Resources\AnuncioResource;
use App\Models\Anuncio;
use App\Models\Materia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class AnuncioController extends Controller
{
    public function index(Materia $materia): AnonymousResourceCollection
    {
        Gate::authorize('view', $materia);

        return AnuncioResource::collection(
            $materia->anuncios()->with('autor')->latest()->get()
        );
    }

    public function store(StoreAnuncioRequest $request, Materia $materia): JsonResponse
    {
        $anuncio = $materia->anuncios()->create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
        ]);

        return (new AnuncioResource($anuncio->load('autor')))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateAnuncioRequest $request, Anuncio $anuncio): AnuncioResource
    {
        $anuncio->update($request->validated());

        return new AnuncioResource($anuncio->load('autor'));
    }

    public function destroy(Anuncio $anuncio): Response
    {
        Gate::authorize('update', $anuncio->materia);

        $anuncio->delete();

        return response()->noContent();
    }
}