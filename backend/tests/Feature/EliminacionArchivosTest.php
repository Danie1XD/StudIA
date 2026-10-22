<?php

namespace Tests\Feature;

use App\Models\Entrega;
use App\Models\Materia;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EliminacionArchivosTest extends TestCase
{
    use RefreshDatabase;

    private User $docente;
    private User $alumno;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->docente = User::create(['nombre' => 'Docente', 'email' => 'docente@test.com', 'password' => '12345678', 'rol' => 'docente']);
        $this->alumno = User::create(['nombre' => 'Alumno', 'email' => 'alumno@test.com', 'password' => '12345678', 'rol' => 'alumno']);
    }

    /** Crea una materia con una tarea (con PDF) y una entrega del alumno (con archivo). */
    private function materiaConArchivos(string $codigo, string $sufijo): array
    {
        $materia = Materia::create(['docente_id' => $this->docente->id, 'nombre' => "Materia {$sufijo}", 'codigo_acceso' => $codigo]);
        $materia->alumnos()->attach($this->alumno->id);

        Storage::disk('local')->put("tareas/tarea-{$sufijo}.pdf", 'pdf de la tarea');
        Storage::disk('local')->put("entregas/entrega-{$sufijo}.pdf", 'archivo de la entrega');

        $tarea = Tarea::create([
            'materia_id' => $materia->id,
            'titulo' => "Tarea {$sufijo}",
            'instrucciones' => 'x',
            'archivo_path' => "tareas/tarea-{$sufijo}.pdf",
        ]);

        $entrega = Entrega::create([
            'tarea_id' => $tarea->id,
            'user_id' => $this->alumno->id,
            'archivo_path' => "entregas/entrega-{$sufijo}.pdf",
            'estado' => 'entregado',
            'entregada_at' => now(),
        ]);

        return [$materia, $tarea, $entrega];
    }

    public function test_eliminar_una_materia_borra_los_archivos_de_sus_tareas_y_entregas_pero_no_los_de_otras(): void
    {
        [$materia] = $this->materiaConArchivos('AAA111', 'a');
        $this->materiaConArchivos('BBB222', 'b');
        Sanctum::actingAs($this->docente);

        $this->deleteJson("/api/v1/materias/{$materia->id}")->assertNoContent();

        Storage::disk('local')->assertMissing('tareas/tarea-a.pdf');
        Storage::disk('local')->assertMissing('entregas/entrega-a.pdf');
        Storage::disk('local')->assertExists('tareas/tarea-b.pdf');
        Storage::disk('local')->assertExists('entregas/entrega-b.pdf');

        $this->assertDatabaseMissing('materias', ['id' => $materia->id]);
        $this->assertDatabaseCount('entregas', 1);
    }

    public function test_eliminar_una_tarea_borra_su_archivo_y_los_de_sus_entregas(): void
    {
        [, $tarea] = $this->materiaConArchivos('AAA111', 'a');
        $this->materiaConArchivos('BBB222', 'b');
        Sanctum::actingAs($this->docente);

        $this->deleteJson("/api/v1/tareas/{$tarea->id}")->assertNoContent();

        Storage::disk('local')->assertMissing('tareas/tarea-a.pdf');
        Storage::disk('local')->assertMissing('entregas/entrega-a.pdf');
        Storage::disk('local')->assertExists('tareas/tarea-b.pdf');
        Storage::disk('local')->assertExists('entregas/entrega-b.pdf');

        $this->assertDatabaseMissing('tareas', ['id' => $tarea->id]);
    }

    public function test_un_docente_ajeno_no_puede_eliminar_la_materia_y_no_se_borra_nada(): void
    {
        [$materia] = $this->materiaConArchivos('AAA111', 'a');
        $ajeno = User::create(['nombre' => 'Ajeno', 'email' => 'ajeno@test.com', 'password' => '12345678', 'rol' => 'docente']);
        Sanctum::actingAs($ajeno);

        $this->deleteJson("/api/v1/materias/{$materia->id}")->assertForbidden();

        Storage::disk('local')->assertExists('tareas/tarea-a.pdf');
        Storage::disk('local')->assertExists('entregas/entrega-a.pdf');
        $this->assertDatabaseHas('materias', ['id' => $materia->id]);
    }
}