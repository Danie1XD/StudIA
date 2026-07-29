<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;
use MongoDB\BSON\ObjectId;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    use \MongoDB\Laravel\Eloquent\DocumentModel;

    protected $connection = 'mongodb';
    protected $collection = 'personal_access_tokens';

    /**
     * Sobrescribir el método tokenable para forzar la búsqueda
     * usando ObjectId en MongoDB.
     */
    public function tokenable()
    {
        $type = $this->tokenable_type;
        $id = $this->tokenable_id;

        if (is_string($id) && strlen($id) === 24 && ctype_xdigit($id)) {
            $id = new ObjectId($id);
        }

        // Si el tipo es el modelo User, ejecutamos la búsqueda directa por _id
        if ($type && class_exists($type)) {
            return $this->belongsTo($type, 'tokenable_id', '_id');
        }

        return $this->morphTo('tokenable', 'tokenable_type', 'tokenable_id');
    }

    /**
     * Buscar el token en MongoDB mediante hash directo
     */
    public static function findToken($token)
    {
        if (str_contains($token, '|')) {
            [$id, $token] = explode('|', $token, 2);
        }

        return static::where('token', hash('sha256', $token))->first();
    }
}