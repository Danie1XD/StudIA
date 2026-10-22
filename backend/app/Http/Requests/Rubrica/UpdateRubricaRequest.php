<?php

namespace App\Http\Requests\Rubrica;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRubricaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('rubrica'));
    }

    public function rules(): array
    {
        return [
            'nombre' => ['sometimes', 'required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            // Si se manda, reemplaza TODA la lista de criterios
            'criterios' => ['sometimes', 'required', 'array', 'min:1', 'max:20'],
            'criterios.*.nombre' => ['required', 'string', 'max:150'],
            'criterios.*.descripcion' => ['nullable', 'string', 'max:1000'],
            'criterios.*.puntos' => ['required', 'integer', 'between:1,1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'criterios.required' => 'La rúbrica necesita al menos un criterio.',
            'criterios.min' => 'La rúbrica necesita al menos un criterio.',
            'criterios.*.nombre.required' => 'Cada criterio necesita un nombre.',
            'criterios.*.puntos.required' => 'Cada criterio necesita sus puntos.',
            'criterios.*.puntos.between' => 'Los puntos de cada criterio deben estar entre 1 y 1000.',
        ];
    }
}