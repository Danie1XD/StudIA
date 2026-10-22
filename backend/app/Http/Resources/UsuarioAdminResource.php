<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsuarioAdminResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'email' => $this->email,
            'rol' => $this->rol->value,
            'activo' => (bool) $this->activo,
            'materias_impartidas_count' => $this->whenCounted('materiasImpartidas'),
            'materias_inscritas_count' => $this->whenCounted('materiasInscritas'),
            'created_at' => $this->created_at,
        ];
    }
}