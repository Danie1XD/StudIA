<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\Rol;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUsuarioRequest;
use App\Http\Requests\Admin\UpdateUsuarioRequest;
use App\Http\Resources\UsuarioAdminResource;
use App\Models\User;
use App\Services\UsuarioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    public function __construct(private UsuarioService $usuarios) {}

    /** Filtros opcionales: ?rol=docente &activo=1 (o 0) &buscar=texto &por_pagina=15 */
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'rol' => ['nullable', Rule::enum(Rol::class)],
            'activo' => ['nullable', 'boolean'],
            'buscar' => ['nullable', 'string', 'max:100'],
            'por_pagina' => ['nullable', 'integer', 'between:1,100'],
        ]);

        $usuarios = User::query()
            ->when($request->filled('rol'), fn ($q) => $q->where('rol', $request->input('rol')))
            ->when($request->filled('activo'), fn ($q) => $q->where('activo', $request->boolean('activo')))
            ->when($request->filled('buscar'), function ($q) use ($request) {
                // Se escapan % y _ para que se busquen como texto y no como comodines
                $texto = '%'.addcslashes(trim((string) $request->input('buscar')), '%_\\').'%';
                $q->where(fn ($w) => $w->where('nombre', 'like', $texto)->orWhere('email', 'like', $texto));
            })
            ->withCount(['materiasImpartidas', 'materiasInscritas'])
            ->orderBy('nombre')
            ->paginate($request->integer('por_pagina', 15));

        return UsuarioAdminResource::collection($usuarios);
    }

    public function store(StoreUsuarioRequest $request): JsonResponse
    {
        $usuario = $this->usuarios->crear($request->validated());

        return (new UsuarioAdminResource($usuario))->response()->setStatusCode(201);
    }

    public function show(User $usuario): UsuarioAdminResource
    {
        return new UsuarioAdminResource(
            $usuario->loadCount(['materiasImpartidas', 'materiasInscritas'])
        );
    }

    public function update(UpdateUsuarioRequest $request, User $usuario): UsuarioAdminResource
    {
        return new UsuarioAdminResource(
            $this->usuarios->actualizar($request->user(), $usuario, $request->validated())
        );
    }

    public function desactivar(Request $request, User $usuario): UsuarioAdminResource
    {
        return new UsuarioAdminResource($this->usuarios->desactivar($request->user(), $usuario));
    }

    public function activar(User $usuario): UsuarioAdminResource
    {
        return new UsuarioAdminResource($this->usuarios->activar($usuario));
    }
}