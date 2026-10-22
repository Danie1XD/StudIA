<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Anuncio extends Model
{
    protected $table = 'anuncios';

    protected $fillable = ['materia_id', 'seccion_id', 'user_id', 'contenido'];

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class);
    }

    public function seccion(): BelongsTo
    {
        return $this->belongsTo(Seccion::class);
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}