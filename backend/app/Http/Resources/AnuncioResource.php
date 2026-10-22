<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnuncioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'materia_id' => $this->materia_id,
            'seccion_id' => $this->seccion_id,
            'contenido' => $this->contenido,
            'autor' => new UserResource($this->whenLoaded('autor')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}