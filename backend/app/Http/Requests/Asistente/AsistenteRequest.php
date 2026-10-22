<?php

namespace App\Http\Requests\Asistente;

use Illuminate\Foundation\Http\FormRequest;

class AsistenteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;   // el acceso por rol lo controla el middleware de la ruta
    }

    public function rules(): array
    {
        return [
            'mensaje' => ['required', 'string', 'max:2000'],
            // El frontend manda los turnos anteriores; el servidor no guarda nada
            'historial' => ['nullable', 'array', 'max:20'],
            'historial.*.rol' => ['required', 'in:usuario,asistente'],
            'historial.*.texto' => ['required', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'mensaje.required' => 'Escribe tu duda.',
            'mensaje.max' => 'El mensaje no puede pasar de 2000 caracteres.',
            'historial.max' => 'La conversación es demasiado larga; envía solo los últimos 20 turnos.',
        ];
    }
}