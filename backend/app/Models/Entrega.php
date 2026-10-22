<?php

namespace App\Models;

use App\Enums\EstadoEntrega;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Entrega extends Model
{
    protected $table = 'entregas';

    protected $fillable = [
        'tarea_id', 'user_id', 'contenido', 'archivo_path', 'estado',
        'entregada_at', 'calificacion_final', 'retroalimentacion_final', 'calificada_at',
    ];

    protected function casts(): array
    {
        return [
            'estado' => EstadoEntrega::class,
            'entregada_at' => 'datetime',
            'calificada_at' => 'datetime',
        ];
    }

    public function tarea(): BelongsTo
    {
        return $this->belongsTo(Tarea::class);
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function evaluacionIa(): HasOne
    {
        return $this->hasOne(EvaluacionIa::class);
    }
}