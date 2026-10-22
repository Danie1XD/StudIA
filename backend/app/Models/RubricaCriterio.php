<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RubricaCriterio extends Model
{
    protected $table = 'rubrica_criterios';

    protected $fillable = ['rubrica_id', 'nombre', 'descripcion', 'puntos', 'orden'];

    public function rubrica(): BelongsTo
    {
        return $this->belongsTo(Rubrica::class);
    }
}