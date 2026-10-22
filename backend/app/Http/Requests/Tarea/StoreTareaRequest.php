<?php

namespace App\Http\Requests\Tarea;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\TipoEvaluacion;

class StoreTareaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('materia'));
    }

    public function rules(): array
    {
        $materia = $this->route('materia');

        return [
            'titulo' => ['required', 'string', 'max:255'],
            'instrucciones' => ['required', 'string', 'max:10000'],
            'fecha_limite' => ['nullable', 'date', 'after:now'],
            'puntaje_maximo' => ['nullable', 'integer', 'between:1,1000'],
            'parcial' => ['nullable', 'integer', 'between:1,3'],
            'tipo_evaluacion' => ['sometimes', Rule::enum(TipoEvaluacion::class)],
            // La sección debe ser de ESTA materia
            'seccion_id' => [
                'nullable',
                Rule::exists('secciones', 'id')->where('materia_id', $materia->id),
            ],
            // La rúbrica debe ser del docente dueño de la materia
            'rubrica_id' => [
                'nullable',
                Rule::exists('rubricas', 'id')->where('docente_id', $materia->docente_id),
            ],
            // Si los alumnos pueden ver la rúbrica de la tarea
            'rubrica_visible' => ['sometimes', 'boolean'],
            'archivo' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],   // 10 MB
        ];
    }

    public function messages(): array
    {
        return [
            'titulo.required' => 'El título es obligatorio.',
            'instrucciones.required' => 'Las instrucciones son obligatorias.',
            'fecha_limite.after' => 'La fecha límite debe ser futura.',
            'parcial.between' => 'El parcial debe ser 1, 2 o 3.',
            'tipo_evaluacion.enum' => 'El tipo de evaluación no es válido.',
            'seccion_id.exists' => 'La sección no existe en esta materia.',
            'rubrica_id.exists' => 'La rúbrica no existe o no es tuya.',
            'archivo.mimes' => 'El archivo debe ser un PDF.',
            'archivo.max' => 'El PDF no puede pesar más de 10 MB.',
        ];
    }
}