<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeccionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'materia_id' => $this->materia_id,
            'nombre' => $this->nombre,
            'orden' => $this->orden,
            'tareas_count' => $this->whenCounted('tareas'),
            'anuncios_count' => $this->whenCounted('anuncios'),
            'tareas' => TareaResource::collection($this->whenLoaded('tareas')),
            'anuncios' => AnuncioResource::collection($this->whenLoaded('anuncios')),
        ];
    }
}