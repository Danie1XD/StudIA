<?php

namespace Database\Seeders;

use App\Models\Anuncio;
use App\Models\Materia;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'nombre' => 'Administrador',
            'email' => 'admin@studia.com',
            'password' => '12345678',
            'rol' => 'admin',
        ]);

        $docente = User::create([
            'nombre' => 'Profesor Agustín',
            'email' => 'docente@studia.com',
            'password' => '12345678',
            'rol' => 'docente',
        ]);

        $alumno = User::create([
            'nombre' => 'Daniel Cumplido García',
            'email' => 'alumno@studia.com',
            'password' => '12345678',
            'rol' => 'alumno',
        ]);

        // Docente que NO es dueño de la materia: sirve para probar permisos
        User::create([
            'nombre' => 'Docente Ajeno',
            'email' => 'ajeno@studia.com',
            'password' => '12345678',
            'rol' => 'docente',
        ]);

        $materia = Materia::create([
            'docente_id' => $docente->id,
            'nombre' => 'Inteligencia Artificial',
            'descripcion' => 'Bienvenidos al curso de IA.',
            'codigo_acceso' => Str::upper(Str::random(6)),
        ]);

        $materia->alumnos()->attach($alumno->id);

        $unidad1 = $materia->secciones()->create(['nombre' => 'Unidad 1: Fundamentos', 'orden' => 1]);
        $materia->secciones()->create(['nombre' => 'Unidad 2: Redes neuronales', 'orden' => 2]);

        Anuncio::create([
            'materia_id' => $materia->id,
            'seccion_id' => $unidad1->id,
            'user_id' => $docente->id,
            'contenido' => 'Bienvenidos. Revisen el material antes de la primera actividad.',
        ]);

        Tarea::create([
            'materia_id' => $materia->id,
            'seccion_id' => $unidad1->id,
            'titulo' => 'Actividad 1: ¿Qué es la IA?',
            'instrucciones' => 'Realizar un mapa mental sobre la IA en uno de estos entornos: Cuaderno, Canva o PowerPoint.',
            'fecha_limite' => now()->addWeek(),
            'puntaje_maximo' => 100,
            'parcial' => 1,
        ]);
    }
}