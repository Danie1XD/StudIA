<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluacionIa extends Model
{
    protected $table = 'evaluaciones_ia';

    protected $fillable = ['entrega_id', 'calificacion_sugerida', 'retroalimentacion', 'modelo'];

    protected function casts(): array
    {
        return ['retroalimentacion' => 'array'];   // la columna JSON se lee como arreglo PHP
    }

    public function entrega(): BelongsTo
    {
        return $this->belongsTo(Entrega::class);
    }
}