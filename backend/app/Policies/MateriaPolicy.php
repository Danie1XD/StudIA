<?php

namespace App\Policies;

use App\Models\Materia;
use App\Models\User;

class MateriaPolicy
{
    /** Se ejecuta antes que los demás métodos. */
    public function before(User $user, string $ability): ?bool
    {
        // El admin puede ver, editar y borrar cualquier materia, pero no crearlas
        if ($user->esAdmin() && $ability !== 'create') {
            return true;
        }

        return null;   // null = "que decida el método correspondiente"
    }

    public function create(User $user): bool
    {
        return $user->esDocente();
    }

    public function view(User $user, Materia $materia): bool
    {
        return $this->esDueno($user, $materia)
            || $materia->alumnos()->where('users.id', $user->id)->exists();
    }

    public function update(User $user, Materia $materia): bool
    {
        return $this->esDueno($user, $materia);
    }

    public function delete(User $user, Materia $materia): bool
    {
        return $this->esDueno($user, $materia);
    }

    private function esDueno(User $user, Materia $materia): bool
    {
        return $user->esDocente() && (int) $materia->docente_id === (int) $user->id;
    }
}