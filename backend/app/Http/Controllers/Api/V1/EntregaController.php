<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Entrega\CalificarEntregaRequest;
use App\Http\Requests\Entrega\StoreEntregaRequest;
use App\Http\Resources\EntregaResource;
use App\Http\Resources\UserResource;
use App\Models\Entrega;
use App\Models\Tarea;
use App\Services\EntregaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EntregaController extends Controller
{
    public function __construct(private EntregaService $entregas) {}

    /** Docente: todos los alumnos inscritos con su entrega (o "sin_entregar"). */
    public function index(Tarea $tarea): JsonResponse
    {
        Gate::authorize('update', $tarea->materia);

        $entregas = $tarea->entregas()->with(['alumno', 'tarea', 'evaluacionIa'])->get()->keyBy('user_id');

        $filas = $tarea->materia->alumnos()->orderBy('nombre')->get()
            ->map(function ($alumno) use ($entregas) {
                $entrega = $entregas->get($alumno->id);

                return [
                    'alumno' => new UserResource($alumno),
                    'estado' => $entrega ? $entrega->estado->value : 'sin_entregar',
                    'entrega' => $entrega ? new EntregaResource($entrega) : null,
                ];
            });

        return response()->json(['data' => $filas]);
    }

    /** Alumno: su propia entrega de esta tarea (data: null si aún no entrega). */
    public function miEntrega(Request $request, Tarea $tarea): JsonResponse
    {
        Gate::authorize('entregar', [Entrega::class, $tarea]);

        $entrega = $tarea->entregas()->where('user_id', $request->user()->id)->with('tarea')->first();

        return response()->json(['data' => $entrega ? new EntregaResource($entrega) : null]);
    }

    public function store(StoreEntregaRequest $request, Tarea $tarea): JsonResponse
    {
        $entrega = $this->entregas->entregar($request->user(), $tarea, $request->validated());

        return (new EntregaResource($entrega->load('tarea')))
            ->response()
            ->setStatusCode($entrega->wasRecentlyCreated ? 201 : 200);
    }

    public function destroy(Request $request, Tarea $tarea): Response
    {
        $entrega = $tarea->entregas()->where('user_id', $request->user()->id)->firstOrFail();

        Gate::authorize('anular', $entrega);

        $this->entregas->anular($entrega);

        return response()->noContent();
    }

    public function archivo(Entrega $entrega): StreamedResponse
    {
        Gate::authorize('view', $entrega);

        abort_unless(
            $entrega->archivo_path && Storage::disk('local')->exists($entrega->archivo_path),
            404,
            'Esta entrega no tiene archivo adjunto.'
        );

        return Storage::disk('local')->download(
            $entrega->archivo_path,
            'entrega-'.$entrega->id.'.'.pathinfo($entrega->archivo_path, PATHINFO_EXTENSION)
        );
    }

    public function calificar(CalificarEntregaRequest $request, Entrega $entrega): EntregaResource
    {
        $entrega = $this->entregas->calificar($entrega, $request->validated());

        return new EntregaResource($entrega->load(['alumno', 'tarea']));
    }
}