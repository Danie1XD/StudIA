<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materia extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nombre',
        'descripcion',
        'codigo_acceso',
    ];

    // Relación: Una materia le pertenece a un Docente
    public function docente()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relación: Una materia tiene muchos Alumnos inscritos
    public function alumnos()
    {
        return $this->belongsToMany(User::class, 'materia_user', 'materia_id', 'user_id')->withTimestamps();
    }

    // Relación: Una materia tiene muchas Tareas asignadas
    public function tareas()
    {
        return $this->hasMany(Tarea::class);
    }
}