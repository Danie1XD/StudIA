<?php

namespace App\Policies;

use App\Enums\EstadoEntrega;
use App\Models\Entrega;
use App\Models\Tarea;
use App\Models\User;

class EntregaPolicy
{
    /** Solo un alumno inscrito en la materia de la tarea puede entregar. */
    public function entregar(User $user, Tarea $tarea): bool
    {
        return $user->esAlumno()
            && $tarea->materia->alumnos()->where('users.id', $user->id)->exists();
    }

    /** La ve su dueño y quien administra la materia. */
    public function view(User $user, Entrega $entrega): bool
    {
        return (int) $entrega->user_id === (int) $user->id
            || $user->can('update', $entrega->tarea->materia);
    }

    /** Califica solo el docente dueño de la materia. */
    public function calificar(User $user, Entrega $entrega): bool
    {
        return $user->esDocente()
            && (int) $entrega->tarea->materia->docente_id === (int) $user->id;
    }

    /** El alumno puede anular su entrega mientras no esté calificada. */
    public function anular(User $user, Entrega $entrega): bool
    {
        return (int) $entrega->user_id === (int) $user->id
            && $entrega->estado !== EstadoEntrega::Calificado;
    }
}