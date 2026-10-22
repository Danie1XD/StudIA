<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EntregaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $tieneArchivo = $this->archivo_path !== null;

        return [
            'id' => $this->id,
            'tarea_id' => $this->tarea_id,
            'estado' => $this->estado->value,
            'contenido' => $this->contenido,
            'tiene_archivo' => $tieneArchivo,
            // Solo los PDF se pueden abrir en el visor; el resto se descarga
            'archivo_es_pdf' => $tieneArchivo && str_ends_with(strtolower($this->archivo_path), '.pdf'),
            'archivo_url' => $this->when($tieneArchivo, fn () => route('entregas.archivo', $this->id)),
            'entregada_at' => $this->entregada_at,
            'entregada_tarde' => $this->whenLoaded('tarea', fn () => $this->tarea->fecha_limite !== null
                && $this->entregada_at?->gt($this->tarea->fecha_limite)),
            'calificacion_final' => $this->calificacion_final !== null ? (float) $this->calificacion_final : null,
            'tiene_evaluacion_ia' => $this->whenLoaded('evaluacionIa', fn () => $this->evaluacionIa !== null),
            'retroalimentacion_final' => $this->retroalimentacion_final,
            'calificada_at' => $this->calificada_at,
            'alumno' => new UserResource($this->whenLoaded('alumno')),
        ];
    }
}