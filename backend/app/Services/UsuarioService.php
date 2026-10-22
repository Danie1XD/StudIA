<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class UsuarioService
{
    public function crear(array $datos): User
    {
        return User::create(Arr::only($datos, ['nombre', 'email', 'password', 'rol']));
    }

    public function actualizar(User $actor, User $usuario, array $datos): User
    {
        if (isset($datos['password']) && $actor->is($usuario)) {
            throw ValidationException::withMessages([
                'password' => ['Tu propia contraseña se cambia desde tu perfil.'],
            ]);
        }

        if (isset($datos['rol']) && $datos['rol'] !== $usuario->rol->value) {
            if ($actor->is($usuario)) {
                throw ValidationException::withMessages(['rol' => ['No puedes cambiar tu propio rol.']]);
            }

            // Quedarían huérfanas: las políticas exigen que su dueño sea docente
            if ($usuario->materiasImpartidas()->exists() || $usuario->rubricas()->exists()) {
                throw ValidationException::withMessages([
                    'rol' => ['Este usuario tiene materias o rúbricas a su nombre; elimínalas o reasígnalas antes de cambiar su rol.'],
                ]);
            }
        }

        $usuario->fill(Arr::only($datos, ['nombre', 'email', 'password', 'rol']))->save();

        if (isset($datos['password'])) {
            $usuario->tokens()->delete();   // la contraseña cambió: se cierran sus sesiones abiertas
        }

        return $usuario;
    }

    public function desactivar(User $actor, User $usuario): User
    {
        if ($actor->is($usuario)) {
            throw ValidationException::withMessages([
                'usuario' => ['No puedes desactivar tu propia cuenta.'],
            ]);
        }

        $usuario->forceFill(['activo' => false])->save();
        $usuario->tokens()->delete();   // sus sesiones dejan de valer en el acto

        return $usuario;
    }

    public function activar(User $usuario): User
    {
        $usuario->forceFill(['activo' => true])->save();

        return $usuario;
    }
}