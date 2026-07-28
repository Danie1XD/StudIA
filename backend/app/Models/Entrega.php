<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Entrega extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'entregas';

    protected $fillable = [
        'tarea_id',
        'alumno_id',
        'archivo_url',
        'fecha_envio',
        'estado',
        'pre_evaluacion_ia', // Objeto embebido con JSON de Gemini
        'revision_docente'   // Objeto embebido con validación humana
    ];
}