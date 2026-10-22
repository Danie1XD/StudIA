<?php

namespace App\Policies;

use App\Models\Rubrica;
use App\Models\User;

class RubricaPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        // El admin puede ver, editar y borrar cualquier rúbrica, pero no crearlas
        if ($user->esAdmin() && $ability !== 'create') {
            return true;
        }

        return null;
    }

    public function create(User $user): bool
    {
        return $user->esDocente();
    }

    public function view(User $user, Rubrica $rubrica): bool
    {
        return $this->esDueno($user, $rubrica);
    }

    public function update(User $user, Rubrica $rubrica): bool
    {
        return $this->esDueno($user, $rubrica);
    }

    public function delete(User $user, Rubrica $rubrica): bool
    {
        return $this->esDueno($user, $rubrica);
    }

    private function esDueno(User $user, Rubrica $rubrica): bool
    {
        return $user->esDocente() && (int) $rubrica->docente_id === (int) $user->id;
    }
}