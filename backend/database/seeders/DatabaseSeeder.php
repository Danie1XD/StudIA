<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'nombre' => 'Daniel Alumno',
            'email' => 'alumno@studia.com',
            'password' => Hash::make('12345678'),
            'rol' => 'alumno',
            'fecha_creacion' => now(),
        ]);

        User::create([
            'nombre' => 'Profesor Docente',
            'email' => 'docente@studia.com',
            'password' => Hash::make('12345678'),
            'rol' => 'docente',
            'fecha_creacion' => now(),
        ]);
    }
}