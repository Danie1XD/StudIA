<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; 
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users'; 

    protected $fillable = [
        'nombre',
        'email',
        'password',
        'rol',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    
    // Relación para Docentes: Materias que ha creado
    public function materiasCreadas()
    {
        return $this->hasMany(Materia::class, 'user_id');
    }

    // Relación para Alumnos: Materias a las que se unió con el código
    public function materiasInscritas()
    {
        return $this->belongsToMany(Materia::class, 'materia_user', 'user_id', 'materia_id')->withTimestamps();
    }

    // Relación para Alumnos: Todas las tareas que ha entregado
    public function entregas()
    {
        return $this->hasMany(Entrega::class, 'user_id');
    }
}