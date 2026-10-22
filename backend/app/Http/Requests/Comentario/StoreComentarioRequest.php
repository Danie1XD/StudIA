<?php

namespace App\Http\Requests\Comentario;

use Illuminate\Foundation\Http\FormRequest;

class StoreComentarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Comenta quien puede ver la materia de la tarea
        return $this->user()->can('view', $this->route('tarea')->materia);
    }

    public function rules(): array
    {
        return [
            'mensaje' => ['required', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'mensaje.required' => 'Escribe un comentario.',
            'mensaje.max' => 'El comentario no puede pasar de 2000 caracteres.',
        ];
    }
}