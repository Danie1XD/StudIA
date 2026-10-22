<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Asistente\AsistenteRequest;
use App\Services\AsistenteService;
use Illuminate\Http\JsonResponse;

class AsistenteController extends Controller
{
    public function __invoke(AsistenteRequest $request, AsistenteService $asistente): JsonResponse
    {
        set_time_limit(120);

        $respuesta = $asistente->responder(
            $request->user(),
            $request->validated('mensaje'),
            $request->validated('historial') ?? [],
        );

        return response()->json(['data' => ['respuesta' => $respuesta]]);
    }
}