<?php

namespace App\Policies;

use App\Models\Comentario;
use App\Models\User;

class ComentarioPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        // El admin puede moderar (borrar) cualquier comentario
        return $user->esAdmin() && $ability === 'delete' ? true : null;
    }

    /** Lo borra su autor o el docente dueño de la materia. */
    public function delete(User $user, Comentario $comentario): bool
    {
        return (int) $comentario->user_id === (int) $user->id
            || $user->can('update', $comentario->tarea->materia);
    }
}