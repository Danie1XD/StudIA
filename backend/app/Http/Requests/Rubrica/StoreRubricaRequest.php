<?php

namespace App\Http\Requests\Rubrica;

use App\Models\Rubrica;
use Illuminate\Foundation\Http\FormRequest;

class StoreRubricaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Rubrica::class);
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'criterios' => ['required', 'array', 'min:1', 'max:20'],
            'criterios.*.nombre' => ['required', 'string', 'max:150'],
            'criterios.*.descripcion' => ['nullable', 'string', 'max:1000'],
            'criterios.*.puntos' => ['required', 'integer', 'between:1,1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la rúbrica es obligatorio.',
            'criterios.required' => 'La rúbrica necesita al menos un criterio.',
            'criterios.min' => 'La rúbrica necesita al menos un criterio.',
            'criterios.max' => 'Una rúbrica no puede tener más de 20 criterios.',
            'criterios.*.nombre.required' => 'Cada criterio necesita un nombre.',
            'criterios.*.puntos.required' => 'Cada criterio necesita sus puntos.',
            'criterios.*.puntos.between' => 'Los puntos de cada criterio deben estar entre 1 y 1000.',
        ];
    }
}