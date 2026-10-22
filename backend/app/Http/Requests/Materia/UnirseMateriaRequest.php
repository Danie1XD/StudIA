<?php

namespace App\Http\Requests\Materia;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class UnirseMateriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->esAlumno();
    }

    /** Acepta el código en minúsculas o con espacios. */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'codigo_acceso' => Str::upper(trim((string) $this->input('codigo_acceso'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'codigo_acceso' => ['required', 'string', 'size:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'codigo_acceso.required' => 'Escribe el código de la clase.',
            'codigo_acceso.size' => 'El código debe tener 6 caracteres.',
        ];
    }
}