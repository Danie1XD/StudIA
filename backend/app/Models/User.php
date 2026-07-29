<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\PersonalAccessToken;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $connection = 'mongodb';
    protected $collection = 'users';

    // Le indicamos a Eloquent que la clave primaria de Mongo es de tipo string/ObjectId
    protected $primaryKey = '_id';
    protected $keyType = 'string';

    protected $fillable = [
        'nombre',
        'email',
        'password',
        'rol',
        'fecha_creacion',
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

    /**
     * Sobrescribe la relación de tokens para MongoDB indicando campos explícitos
     */
    public function tokens()
    {
        return $this->morphMany(PersonalAccessToken::class, 'tokenable', 'tokenable_type', 'tokenable_id');
    }
}