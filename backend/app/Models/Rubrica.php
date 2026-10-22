<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rubrica extends Model
{
    protected $table = 'rubricas';

    protected $fillable = ['docente_id', 'nombre', 'descripcion'];

    public function docente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'docente_id');
    }

    public function criterios(): HasMany
    {
        return $this->hasMany(RubricaCriterio::class)->orderBy('orden');
    }

    public function tareas(): HasMany
    {
        return $this->hasMany(Tarea::class);
    }
}