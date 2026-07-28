<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Tarea extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'tareas';

    protected $fillable = [
        'asignatura_id',
        'titulo',
        'descripcion',
        'fecha_entrega_limite',
        'rubrica', // Objeto embebido / arreglo
        'estado'
    ];
}