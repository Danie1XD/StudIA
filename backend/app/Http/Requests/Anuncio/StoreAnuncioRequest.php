<?php

namespace App\Http\Requests\Anuncio;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnuncioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('materia'));
    }

    public function rules(): array
    {
        return [
            'contenido' => ['required', 'string', 'max:5000'],
            // La sección es opcional, pero si viene debe ser de ESTA materia
            'seccion_id' => [
                'nullable',
                Rule::exists('secciones', 'id')->where('materia_id', $this->route('materia')->id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'contenido.required' => 'El anuncio no puede estar vacío.',
            'seccion_id.exists' => 'La sección no existe en esta materia.',
        ];
    }
}