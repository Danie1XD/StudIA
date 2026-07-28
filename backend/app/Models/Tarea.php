<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarea extends Model
{
    use HasFactory;

    protected $fillable = [
        'materia_id',
        'titulo',
        'descripcion',
        'archivo_pdf',
        'fecha_limite',
        'puntaje_maximo',
    ];

    // Relación: Una tarea pertenece a una Materia
    public function materia()
    {
        return $this->belongsTo(Materia::class);
    }

    // Relación: Una tarea recibe muchas Entregas (una por alumno)
    public function entregas()
    {
        return $this->hasMany(Entrega::class);
    }

    public function comentarios()
    {
        return $this->hasMany(Comentario::class);
    }
}