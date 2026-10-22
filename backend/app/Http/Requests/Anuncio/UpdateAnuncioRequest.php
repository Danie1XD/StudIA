<?php

namespace App\Http\Requests\Anuncio;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAnuncioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('anuncio')->materia);
    }

    public function rules(): array
    {
        return [
            'contenido' => ['sometimes', 'required', 'string', 'max:5000'],
            'seccion_id' => [
                'nullable',
                Rule::exists('secciones', 'id')->where('materia_id', $this->route('anuncio')->materia_id),
            ],
        ];
    }

    public function messages(): array
    {
        return ['seccion_id.exists' => 'La sección no existe en esta materia.'];
    }
}