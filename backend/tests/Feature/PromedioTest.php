<?php

namespace Tests\Feature;

use App\Models\Entrega;
use App\Models\Materia;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PromedioTest extends TestCase
{
    use RefreshDatabase;

    private User $docente;
    private User $alumno;
    private Materia $materia;

    protected function setUp(): void
    {
        parent::setUp();

        $this->docente = User::create(['nombre' => 'Docente', 'email' => 'docente@test.com', 'password' => '12345678', 'rol' => 'docente']);
        $this->alumno = User::create(['nombre' => 'Ana', 'email' => 'ana@test.com', 'password' => '12345678', 'rol' => 'alumno']);
        $this->materia = Materia::create(['docente_id' => $this->docente->id, 'nombre' => 'IA', 'codigo_acceso' => 'ABC123']);
        $this->materia->alumnos()->attach($this->alumno->id);
    }

    private function tarea(int $parcial, int $maximo, Carbon $limite): Tarea
    {
        return Tarea::create([
            'materia_id' => $this->materia->id,
            'titulo' => "Tarea P{$parcial} de {$maximo}",
            'instrucciones' => 'x',
            'puntaje_maximo' => $maximo,
            'parcial' => $parcial,
            'fecha_limite' => $limite,
        ]);
    }

    private function entrega(Tarea $tarea, User $alumno, ?float $nota = null): Entrega
    {
        return Entrega::create([
            'tarea_id' => $tarea->id,
            'user_id' => $alumno->id,
            'estado' => $nota === null ? 'entregado' : 'calificado',
            'calificacion_final' => $nota,
            'entregada_at' => now(),
        ]);
    }

    /**
     * P1: tareas de 100 y de 50 puntos, calificadas con 80 y 50  -> 130 / 150 = 86.67
     * P2: una tarea de 100 vencida sin entrega                    -> 0 / 100   = 0
     * P3: una tarea de 100 todavía en plazo                       -> aún no cuenta
     */
    private function escenarioBase(): void
    {
        $t1 = $this->tarea(1, 100, now()->subDays(10));
        $t2 = $this->tarea(1, 50, now()->subDays(9));
        $this->tarea(2, 100, now()->subDay());
        $this->tarea(3, 100, now()->addDays(5));

        $this->entrega($t1, $this->alumno, 80);
        $this->entrega($t2, $this->alumno, 50);
    }

    public function test_el_alumno_ve_su_promedio_por_regla_de_tres_sobre_los_puntos(): void
    {
        $this->escenarioBase();
        Sanctum::actingAs($this->alumno);

        $r = $this->getJson('/api/v1/mis-promedios')->assertOk();

        $this->assertSame('IA', $r->json('data.0.materia.nombre'));
        $this->assertEqualsWithDelta(86.67, $r->json('data.0.parciales.0.promedio'), 0.001);
        $this->assertSame(2, $r->json('data.0.parciales.0.tareas_contadas'));
        $this->assertEquals(0, $r->json('data.0.parciales.1.promedio'));
        $this->assertSame(1, $r->json('data.0.parciales.1.tareas_contadas'));
        $this->assertNull($r->json('data.0.parciales.2.promedio'));
        $this->assertSame(1, $r->json('data.0.parciales.2.tareas_total'));
        $this->assertSame(0, $r->json('data.0.parciales.2.tareas_contadas'));
        $this->assertEqualsWithDelta(43.33, $r->json('data.0.promedio_final'), 0.001);
        $this->assertSame(2, $r->json('data.0.parciales_con_datos'));
    }

    public function test_si_las_vencidas_no_cuentan_como_cero_ese_parcial_queda_sin_datos(): void
    {
        config(['studia.promedios.vencidas_como_cero' => false]);
        $this->escenarioBase();
        Sanctum::actingAs($this->alumno);

        $r = $this->getJson('/api/v1/mis-promedios')->assertOk();

        $this->assertNull($r->json('data.0.parciales.1.promedio'));
        $this->assertSame(1, $r->json('data.0.parciales_con_datos'));
        $this->assertEqualsWithDelta(86.67, $r->json('data.0.promedio_final'), 0.001);
    }

    public function test_el_docente_ve_a_todos_sus_alumnos_y_lo_entregado_sin_calificar_no_cuenta(): void
    {
        $this->escenarioBase();

        $beto = User::create(['nombre' => 'Beto', 'email' => 'beto@test.com', 'password' => '12345678', 'rol' => 'alumno']);
        $this->materia->alumnos()->attach($beto->id);

        // Beto entrega las dos tareas del parcial 1, pero el docente aún no las califica
        $this->entrega(Tarea::where('titulo', 'Tarea P1 de 100')->first(), $beto);
        $this->entrega(Tarea::where('titulo', 'Tarea P1 de 50')->first(), $beto);

        Sanctum::actingAs($this->docente);

        $r = $this->getJson("/api/v1/materias/{$this->materia->id}/promedios")->assertOk();

        // Ana (orden alfabético): P1 con 130/150 y P2 en 0
        $this->assertSame('Ana', $r->json('data.0.alumno.nombre'));
        $this->assertEqualsWithDelta(43.33, $r->json('data.0.promedio_final'), 0.001);

        // Beto: lo entregado sin calificar no cuenta, así que P1 queda sin datos (null, no 0).
        // En P2 la tarea venció sin entregar y cuenta 0, así que solo ese parcial tiene datos.
        $this->assertSame('Beto', $r->json('data.1.alumno.nombre'));
        $this->assertNull($r->json('data.1.parciales.0.promedio'));
        $this->assertSame(0, $r->json('data.1.parciales.0.tareas_contadas'));
        $this->assertSame(2, $r->json('data.1.parciales.0.tareas_total'));
        $this->assertSame(1, $r->json('data.1.parciales_con_datos'));
        $this->assertEquals(0, $r->json('data.1.promedio_final'));
    }

    public function test_solo_el_docente_dueno_ve_los_promedios_de_la_materia(): void
    {
        $url = "/api/v1/materias/{$this->materia->id}/promedios";
        $ajeno = User::create(['nombre' => 'Ajeno', 'email' => 'ajeno@test.com', 'password' => '12345678', 'rol' => 'docente']);

        Sanctum::actingAs($this->alumno);
        $this->getJson($url)->assertForbidden();

        Sanctum::actingAs($ajeno);
        $this->getJson($url)->assertForbidden();

        Sanctum::actingAs($this->docente);
        $this->getJson($url)->assertOk();
        $this->getJson('/api/v1/mis-promedios')->assertForbidden();   // esa ruta es solo de alumnos
    }

    public function test_si_el_docente_baja_el_puntaje_maximo_la_nota_se_limita(): void
    {
        $tarea = $this->tarea(1, 100, now()->subDay());
        $this->entrega($tarea, $this->alumno, 90);
        $tarea->update(['puntaje_maximo' => 50]);   // 90 ya no cabe en una tarea de 50

        Sanctum::actingAs($this->alumno);

        $r = $this->getJson('/api/v1/mis-promedios')->assertOk();

        $this->assertEquals(100, $r->json('data.0.parciales.0.promedio'));   // 50 / 50, no 90 / 50
    }
    public function test_la_respuesta_indica_si_las_vencidas_cuentan_como_cero(): void
    {
        config(['studia.promedios.vencidas_como_cero' => true]);
        Sanctum::actingAs($this->alumno);

        $this->getJson('/api/v1/mis-promedios')
            ->assertOk()
            ->assertJsonPath('reglas.vencidas_como_cero', true);

        config(['studia.promedios.vencidas_como_cero' => false]);

        $this->getJson('/api/v1/mis-promedios')
            ->assertOk()
            ->assertJsonPath('reglas.vencidas_como_cero', false);
    }
}