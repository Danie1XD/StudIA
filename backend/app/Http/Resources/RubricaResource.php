<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RubricaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'puntos_total' => $this->whenLoaded('criterios', fn () => $this->criterios->sum('puntos')),
            'criterios' => $this->whenLoaded('criterios', fn () => $this->criterios->map(fn ($c) => [
                'id' => $c->id,
                'nombre' => $c->nombre,
                'descripcion' => $c->descripcion,
                'puntos' => $c->puntos,
                'orden' => $c->orden,
            ])->values()),
            'tareas_count' => $this->whenCounted('tareas'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}