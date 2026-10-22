<?php

namespace App\Models;

use App\Enums\TipoEvaluacion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tarea extends Model
{
    protected $table = 'tareas';

    protected $fillable = [
        'materia_id', 'seccion_id', 'rubrica_id', 'rubrica_visible', 'titulo', 'instrucciones',
        'archivo_path', 'fecha_limite', 'puntaje_maximo', 'parcial', 'tipo_evaluacion',
    ];

    protected $attributes = [
        'tipo_evaluacion' => 'general',
        'rubrica_visible' => false,
    ];

    protected function casts(): array
    {
        return [
            'fecha_limite' => 'datetime',
            'rubrica_visible' => 'boolean',
            'tipo_evaluacion' => TipoEvaluacion::class,
        ];
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class);
    }

    public function seccion(): BelongsTo
    {
        return $this->belongsTo(Seccion::class);
    }

    public function rubrica(): BelongsTo
    {
        return $this->belongsTo(Rubrica::class);
    }

    public function entregas(): HasMany
    {
        return $this->hasMany(Entrega::class);
    }

    public function comentarios(): HasMany
    {
        return $this->hasMany(Comentario::class);
    }
}