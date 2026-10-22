<?php

namespace App\Http\Requests\Materia;

use App\Models\Materia;
use Illuminate\Foundation\Http\FormRequest;

class StoreMateriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Materia::class);
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return ['nombre.required' => 'El nombre de la materia es obligatorio.'];
    }
}