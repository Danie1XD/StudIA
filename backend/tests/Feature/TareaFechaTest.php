<?php

namespace Tests\Feature;

use App\Models\Materia;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TareaFechaTest extends TestCase
{
    use RefreshDatabase;

    private User $docente;
    private Materia $materia;

    protected function setUp(): void
    {
        parent::setUp();

        $this->docente = User::create(['nombre' => 'Docente', 'email' => 'docente@test.com', 'password' => '12345678', 'rol' => 'docente']);
        $this->materia = Materia::create(['docente_id' => $this->docente->id, 'nombre' => 'IA', 'codigo_acceso' => 'ABC123']);
        Sanctum::actingAs($this->docente);
    }

    public function test_las_fechas_con_zona_horaria_se_guardan_en_utc(): void
    {
        // 23:59 en la Ciudad de México (UTC-6) = 05:59 UTC del día siguiente
        $this->postJson("/api/v1/materias/{$this->materia->id}/tareas", [
            'titulo' => 'Zona horaria',
            'instrucciones' => 'x',
            'fecha_limite' => '2030-01-01T23:59:00-06:00',
        ])
            ->assertCreated()
            ->assertJsonPath('data.fecha_limite', '2030-01-02T05:59:00.000000Z');

        $this->assertDatabaseHas('tareas', ['titulo' => 'Zona horaria', 'fecha_limite' => '2030-01-02 05:59:00']);
    }

    public function test_al_editar_tambien_se_normaliza_y_null_quita_la_fecha(): void
    {
        $tarea = Tarea::create([
            'materia_id' => $this->materia->id,
            'titulo' => 'Editable',
            'instrucciones' => 'x',
            'fecha_limite' => now()->addDay(),
        ]);

        $this->putJson("/api/v1/tareas/{$tarea->id}", ['fecha_limite' => '2030-06-15T10:00:00+02:00'])
            ->assertOk()
            ->assertJsonPath('data.fecha_limite', '2030-06-15T08:00:00.000000Z');

        $this->putJson("/api/v1/tareas/{$tarea->id}", ['fecha_limite' => null])
            ->assertOk()
            ->assertJsonPath('data.fecha_limite', null);
    }
}