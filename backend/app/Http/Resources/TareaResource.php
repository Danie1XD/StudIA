<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TareaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $tieneArchivo = $this->archivo_path !== null;

        // La rúbrica completa solo se incluye al abrir una tarea (así los listados no hacen consultas de más).
        // El docente de la materia siempre la ve; el alumno solo si el docente la compartió.
        $puedeVerRubrica = $request->routeIs('tareas.show')
            && $this->rubrica_id !== null
            && ($this->rubrica_visible || $request->user()?->can('update', $this->materia));

        return [
            'id' => $this->id,
            'materia_id' => $this->materia_id,
            'seccion_id' => $this->seccion_id,
            'rubrica_id' => $this->rubrica_id,
            'rubrica_visible' => (bool) $this->rubrica_visible,
            'rubrica' => $this->when(
                $puedeVerRubrica,
                fn () => new RubricaResource($this->rubrica->loadMissing('criterios')),
            ),
            'titulo' => $this->titulo,
            'instrucciones' => $this->instrucciones,
            'fecha_limite' => $this->fecha_limite,
            'puntaje_maximo' => $this->puntaje_maximo,
            'parcial' => $this->parcial,
            'tiene_archivo' => $tieneArchivo,
            // Nunca exponemos la ruta interna: solo el endpoint protegido de descarga
            'archivo_url' => $this->when($tieneArchivo, fn () => route('tareas.archivo', $this->id)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}