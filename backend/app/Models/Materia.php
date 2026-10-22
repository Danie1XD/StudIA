<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Materia extends Model
{
    protected $table = 'materias';

    protected $fillable = ['docente_id', 'nombre', 'descripcion', 'codigo_acceso'];

    public function docente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'docente_id');
    }

    public function alumnos(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'materia_user')->withTimestamps();
    }

    public function secciones(): HasMany
    {
        return $this->hasMany(Seccion::class)->orderBy('orden');
    }

    public function tareas(): HasMany
    {
        return $this->hasMany(Tarea::class);
    }

    public function anuncios(): HasMany
    {
        return $this->hasMany(Anuncio::class);
    }
}