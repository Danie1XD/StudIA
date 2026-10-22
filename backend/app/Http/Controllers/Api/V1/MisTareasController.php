<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\EstadoTarea;
use App\Http\Controllers\Controller;
use App\Services\MisTareasService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MisTareasController extends Controller
{
    public function __invoke(Request $request, MisTareasService $servicio): JsonResponse
    {
        $user = $request->user();

        if ($user->esAlumno()) {
            $request->validate([
                'estado' => ['nullable', Rule::enum(EstadoTarea::class)],
            ]);

            return response()->json(
                $servicio->paraAlumno($user, $request->enum('estado', EstadoTarea::class))
            );
        }

        return response()->json($servicio->paraDocente($user));
    }
}