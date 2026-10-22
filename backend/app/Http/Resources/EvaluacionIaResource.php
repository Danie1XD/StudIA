<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EvaluacionIaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'entrega_id' => $this->entrega_id,
            'calificacion_sugerida' => (float) $this->calificacion_sugerida,
            'retroalimentacion' => $this->retroalimentacion,
            'modelo' => $this->modelo,
            'generada_at' => $this->updated_at,
        ];
    }
}