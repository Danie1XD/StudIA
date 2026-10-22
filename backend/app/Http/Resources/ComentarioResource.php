<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComentarioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tarea_id' => $this->tarea_id,
            'mensaje' => $this->mensaje,
            // Sin correo: los compañeros de clase no deben verse los correos entre sí
            'autor' => $this->whenLoaded('autor', fn () => [
                'id' => $this->autor->id,
                'nombre' => $this->autor->nombre,
                'rol' => $this->autor->rol->value,
            ]),
            // Para que la pantalla sepa si muestra el botón de borrar
            'puede_eliminar' => $request->user()->can('delete', $this->resource),
            'created_at' => $this->created_at,
        ];
    }
}