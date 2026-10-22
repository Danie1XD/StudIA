<?php

namespace Tests\Feature;

use App\Models\Materia;
use App\Models\Rubrica;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RubricaCompartidaTest extends TestCase
{
    use RefreshDatabase;

    private User $docente;
    private User $alumno;
    private Materia $materia;
    private Rubrica $rubrica;

    protected function setUp(): void
    {
        parent::setUp();

        $this->docente = User::create(['nombre' => 'Docente', 'email' => 'docente@test.com', 'password' => '12345678', 'rol' => 'docente']);
        $this->alumno = User::create(['nombre' => 'Alumno', 'email' => 'alumno@test.com', 'password' => '12345678', 'rol' => 'alumno']);
        $this->materia = Materia::create(['docente_id' => $this->docente->id, 'nombre' => 'IA', 'codigo_acceso' => 'ABC123']);
        $this->materia->alumnos()->attach($this->alumno->id);

        $this->rubrica = Rubrica::create(['docente_id' => $this->docente->id, 'nombre' => 'Ensayo']);
        $this->rubrica->criterios()->create(['nombre' => 'Redacción', 'puntos' => 40, 'orden' => 1]);
        $this->rubrica->criterios()->create(['nombre' => 'Argumentos', 'puntos' => 60, 'orden' => 2]);
    }

    private function crearTarea(bool $visible): Tarea
    {
        return Tarea::create([
            'materia_id' => $this->materia->id,
            'rubrica_id' => $this->rubrica->id,
            'rubrica_visible' => $visible,
            'titulo' => 'Ensayo final',
            'instrucciones' => 'x',
        ]);
    }

    public function test_el_docente_puede_publicar_una_tarea_compartiendo_la_rubrica(): void
    {
        Sanctum::actingAs($this->docente);

        $this->postJson("/api/v1/materias/{$this->materia->id}/tareas", [
            'titulo' => 'Con rúbrica',
            'instrucciones' => 'x',
            'rubrica_id' => $this->rubrica->id,
            'rubrica_visible' => true,
        ])
            ->assertCreated()
            ->assertJsonPath('data.rubrica_visible', true);

        $this->assertDatabaseHas('tareas', ['titulo' => 'Con rúbrica', 'rubrica_visible' => true]);
    }

    public function test_por_defecto_la_rubrica_no_se_comparte(): void
    {
        Sanctum::actingAs($this->docente);

        $this->postJson("/api/v1/materias/{$this->materia->id}/tareas", [
            'titulo' => 'Sin compartir',
            'instrucciones' => 'x',
            'rubrica_id' => $this->rubrica->id,
        ])
            ->assertCreated()
            ->assertJsonPath('data.rubrica_visible', false);
    }

    public function test_el_alumno_ve_los_criterios_cuando_el_docente_la_comparte(): void
    {
        $tarea = $this->crearTarea(visible: true);
        Sanctum::actingAs($this->alumno);

        $this->getJson("/api/v1/tareas/{$tarea->id}")
            ->assertOk()
            ->assertJsonPath('data.rubrica.nombre', 'Ensayo')
            ->assertJsonPath('data.rubrica.puntos_total', 100)
            ->assertJsonCount(2, 'data.rubrica.criterios');
    }

    public function test_el_alumno_no_ve_la_rubrica_si_no_se_compartio(): void
    {
        $tarea = $this->crearTarea(visible: false);
        Sanctum::actingAs($this->alumno);

        $this->getJson("/api/v1/tareas/{$tarea->id}")
            ->assertOk()
            ->assertJsonMissingPath('data.rubrica');
    }

    public function test_el_docente_siempre_ve_su_rubrica(): void
    {
        $tarea = $this->crearTarea(visible: false);
        Sanctum::actingAs($this->docente);

        $this->getJson("/api/v1/tareas/{$tarea->id}")
            ->assertOk()
            ->assertJsonPath('data.rubrica.nombre', 'Ensayo');
    }

    public function test_el_docente_puede_dejar_de_compartirla_al_editar(): void
    {
        $tarea = $this->crearTarea(visible: true);
        Sanctum::actingAs($this->docente);

        $this->putJson("/api/v1/tareas/{$tarea->id}", ['rubrica_visible' => false])
            ->assertOk()
            ->assertJsonPath('data.rubrica_visible', false);
    }
}
