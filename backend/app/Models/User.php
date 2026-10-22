<?php

namespace App\Models;

use App\Enums\Rol;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['nombre', 'email', 'password', 'rol'];

    protected $hidden = ['password', 'remember_token'];

    protected $attributes = [
        'activo' => true,
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',   // se encripta sola al asignarla
            'rol' => Rol::class,      // $user->rol devuelve un Enum, no un texto
            'activo' => 'boolean',
        ];
    }

    public function esAlumno(): bool  { return $this->rol === Rol::Alumno; }
    public function esDocente(): bool { return $this->rol === Rol::Docente; }
    public function esAdmin(): bool   { return $this->rol === Rol::Admin; }

    /** Materias que imparte (si es docente) */
    public function materiasImpartidas(): HasMany
    {
        return $this->hasMany(Materia::class, 'docente_id');
    }

    /** Materias en las que está inscrito (si es alumno) */
    public function materiasInscritas(): BelongsToMany
    {
        return $this->belongsToMany(Materia::class, 'materia_user')->withTimestamps();
    }

    public function rubricas(): HasMany
    {
        return $this->hasMany(Rubrica::class, 'docente_id');
    }

    public function entregas(): HasMany
    {
        return $this->hasMany(Entrega::class);
    }
}