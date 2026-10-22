<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Materia;
use App\Services\PromedioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PromedioController extends Controller
{
    public function __construct(private PromedioService $promedios) {}

    /** Docente dueño (o admin): todos los alumnos de la materia. */
    public function materia(Materia $materia): JsonResponse
    {
        Gate::authorize('update', $materia);

        return response()->json([
            'data' => $this->promedios->paraMateria($materia),
            'reglas' => $this->reglas(),
        ]);
    }

    /** Alumno: sus propias materias. Alimenta el simulador de promedios. */
    public function mios(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->promedios->paraAlumno($request->user()),
            'reglas' => $this->reglas(),
        ]);
    }

    /** Las reglas que se aplicaron, para que las pantallas expliquen el cálculo tal como es. */
    private function reglas(): array
    {
        return ['vencidas_como_cero' => (bool) config('studia.promedios.vencidas_como_cero')];
    }
}