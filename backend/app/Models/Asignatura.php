<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Asignatura extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'asignaturas';

    protected $fillable = [
        'nombre_materia',
        'docente_id',
        'codigo_grupo',
        'alumnos_inscritos',
        'descripcion',
        'estado'
    ];
}