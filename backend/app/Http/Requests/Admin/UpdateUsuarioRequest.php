<?php

namespace App\Http\Requests\Admin;

use App\Enums\Rol;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->esAdmin();
    }

    public function rules(): array
    {
        return [
            'nombre' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes', 'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($this->route('usuario')),   // su propio correo no cuenta como repetido
            ],
            'password' => ['sometimes', 'required', 'string', 'min:8'],
            'rol' => ['sometimes', 'required', Rule::enum(Rol::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Ese correo ya está registrado.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'rol.enum' => 'El rol debe ser alumno, docente o admin.',
        ];
    }
}