<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entrega extends Model
{
    use HasFactory;

    protected $fillable = [
        'tarea_id',
        'user_id',
        'contenido',
        'archivo_url',
        'calificacion_ia',
        'retroalimentacion_ia',
        'calificacion_final',
        'retroalimentacion_final',
        'estado',
    ];

    // Relación: Una entrega pertenece a una Tarea específica 
    public function tarea()
    {
        return $this->belongsTo(Tarea::class);
    }

    // Relación: Una entrega fue realizada por un Alumno 
    public function alumno()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}