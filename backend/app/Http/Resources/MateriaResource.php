<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MateriaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            // El código solo lo ve quien administra la materia, nunca los alumnos
            'codigo_acceso' => $this->when(
                $request->user()->can('update', $this->resource),
                $this->codigo_acceso
            ),
            'docente' => new UserResource($this->whenLoaded('docente')),
            'alumnos_count' => $this->whenCounted('alumnos'),
            'secciones' => SeccionResource::collection($this->whenLoaded('secciones')),
            'created_at' => $this->created_at,
        ];
    }
}