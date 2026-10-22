<?php

namespace App\Http\Requests\Seccion;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReordenarSeccionesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('materia'));
    }

    public function rules(): array
    {
        $materia = $this->route('materia');

        return [
            'orden' => ['required', 'array', 'min:1'],
            // Cada id debe existir Y pertenecer a esta materia: nadie reordena secciones ajenas
            'orden.*' => [
                'integer',
                'distinct',
                Rule::exists('secciones', 'id')->where('materia_id', $materia->id),
            ],
        ];
    }
}