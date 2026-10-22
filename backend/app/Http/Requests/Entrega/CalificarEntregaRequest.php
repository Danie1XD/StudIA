<?php

namespace App\Http\Requests\Entrega;

use Illuminate\Foundation\Http\FormRequest;

class CalificarEntregaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('calificar', $this->route('entrega'));
    }

    public function rules(): array
    {
        $maximo = $this->route('entrega')->tarea->puntaje_maximo;

        return [
            'calificacion_final' => ['required', 'numeric', 'min:0', "max:{$maximo}"],
            'retroalimentacion_final' => ['required', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'calificacion_final.required' => 'La calificación es obligatoria.',
            'calificacion_final.max' => 'La calificación no puede superar el puntaje máximo de la tarea.',
            'retroalimentacion_final.required' => 'Escribe la retroalimentación para el alumno.',
        ];
    }
}