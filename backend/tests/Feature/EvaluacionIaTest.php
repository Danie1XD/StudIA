<?php

namespace Tests\Feature;

use App\Models\Entrega;
use App\Models\Materia;
use App\Models\Rubrica;
use App\Models\RubricaCriterio;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EvaluacionIaTest extends TestCase
{
    use RefreshDatabase;

    private User $docente;
    private User $alumno;
    private Tarea $tarea;
    private Entrega $entrega;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.gemini.key' => 'clave-de-prueba',
            'services.gemini.model' => 'gemini-3.8-flash',
        ]);
        Storage::fake('local');

        $this->docente = User::create(['nombre' => 'Docente', 'email' => 'docente@test.com', 'password' => '12345678', 'rol' => 'docente']);
        $this->alumno = User::create(['nombre' => 'Ana Pérez López', 'email' => 'alumno@test.com', 'password' => '12345678', 'rol' => 'alumno']);

        $materia = Materia::create(['docente_id' => $this->docente->id, 'nombre' => 'IA', 'codigo_acceso' => 'ABC123']);
        $materia->alumnos()->attach($this->alumno->id);

        $this->tarea = Tarea::create([
            'materia_id' => $materia->id,
            'titulo' => 'Actividad 1',
            'instrucciones' => 'Elabora un mapa mental sobre la IA.',
            'puntaje_maximo' => 100,
            'parcial' => 1,
        ]);

        Storage::disk('local')->put('entregas/trabajo.pdf', '%PDF-1.4 contenido de prueba');

        $this->entrega = Entrega::create([
            'tarea_id' => $this->tarea->id,
            'user_id' => $this->alumno->id,
            'archivo_path' => 'entregas/trabajo.pdf',
            'estado' => 'entregado',
            'entregada_at' => now(),
        ]);
    }

    private function url(): string
    {
        return "/api/v1/entregas/{$this->entrega->id}/evaluacion-ia";
    }

    private function fingirGemini(array $evaluacion): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => json_encode($evaluacion)]]]]],
            ], 200),
        ]);
    }

    public function test_el_docente_genera_la_preevaluacion_enviando_el_pdf(): void
    {
        $this->fingirGemini([
            'puntos_fuertes' => 'Buen uso de ejemplos.',
            'areas_mejora' => 'Faltó profundidad.',
            'retroalimentacion_general' => 'Sigue así.',
            'calificacion_sugerida' => 85,
        ]);
        Sanctum::actingAs($this->docente);

        $this->postJson($this->url())
            ->assertCreated()
            ->assertJsonPath('data.modelo', 'gemini-3.8-flash')
            ->assertJsonPath('data.retroalimentacion.areas_mejora', 'Faltó profundidad.');

        $this->assertDatabaseHas('evaluaciones_ia', ['entrega_id' => $this->entrega->id, 'calificacion_sugerida' => 85]);

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('x-goog-api-key', 'clave-de-prueba')
                && str_contains($request->url(), 'gemini-3.8-flash:generateContent')
                && $request['contents'][0]['parts'][0]['inline_data']['mime_type'] === 'application/pdf'
                && ! isset($request['generationConfig']['temperature']);
        });
    }

    public function test_con_rubrica_el_servidor_calcula_la_nota_y_limita_cada_criterio(): void
    {
        $rubrica = Rubrica::create(['docente_id' => $this->docente->id, 'nombre' => 'Mapa mental']);
        RubricaCriterio::create(['rubrica_id' => $rubrica->id, 'nombre' => 'Contenido', 'puntos' => 60, 'orden' => 1]);
        RubricaCriterio::create(['rubrica_id' => $rubrica->id, 'nombre' => 'Organización', 'puntos' => 40, 'orden' => 2]);
        $this->tarea->update(['rubrica_id' => $rubrica->id]);

        $this->fingirGemini([
            'puntos_fuertes' => 'x',
            'areas_mejora' => 'y',
            'retroalimentacion_general' => 'z',
            'criterios' => [
                ['puntos_obtenidos' => 90, 'comentario' => 'Excelente'],   // excede su máximo de 60
                ['puntos_obtenidos' => 20, 'comentario' => 'Regular'],
            ],
        ]);
        Sanctum::actingAs($this->docente);

        $respuesta = $this->postJson($this->url())->assertCreated();

        $this->assertEquals(80, $respuesta->json('data.calificacion_sugerida'));
        $this->assertEquals(60, $respuesta->json('data.retroalimentacion.criterios.0.puntos_obtenidos'));
        $this->assertSame('Contenido', $respuesta->json('data.retroalimentacion.criterios.0.nombre'));
    }

    public function test_un_alumno_no_puede_pedir_la_preevaluacion(): void
    {
        Http::fake();
        Sanctum::actingAs($this->alumno);

        $this->postJson($this->url())->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_un_docente_ajeno_a_la_materia_no_puede_pedirla(): void
    {
        Http::fake();
        $ajeno = User::create(['nombre' => 'Ajeno', 'email' => 'ajeno@test.com', 'password' => '12345678', 'rol' => 'docente']);
        Sanctum::actingAs($ajeno);

        $this->postJson($this->url())->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_una_entrega_calificada_no_se_vuelve_a_preevaluar(): void
    {
        Http::fake();
        $this->entrega->update(['estado' => 'calificado']);
        Sanctum::actingAs($this->docente);

        $this->postJson($this->url())->assertUnprocessable();
        Http::assertNothingSent();
    }

    public function test_un_enlace_sin_archivo_no_se_envia_a_la_ia(): void
    {
        Http::fake();
        $this->entrega->update(['archivo_path' => null, 'contenido' => 'https://drive.google.com/mi-trabajo']);
        Sanctum::actingAs($this->docente);

        $this->postJson($this->url())->assertUnprocessable();
        Http::assertNothingSent();
    }

    public function test_si_gemini_alcanza_su_limite_el_docente_recibe_un_aviso_claro(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'quota']], 429),
        ]);
        Sanctum::actingAs($this->docente);

        $this->postJson($this->url())
            ->assertStatus(429)
            ->assertJsonPath('message', 'Se alcanzó el límite de uso de la IA. Espera unos minutos e inténtalo de nuevo.');

        $this->assertDatabaseCount('evaluaciones_ia', 0);
    }

    public function test_el_tipo_de_tarea_define_el_enfoque_y_solo_se_envia_el_nombre_de_pila(): void
    {
        $this->tarea->update(['tipo_evaluacion' => 'codigo']);
        $this->fingirGemini([
            'puntos_fuertes' => 'x',
            'areas_mejora' => 'y',
            'retroalimentacion_general' => 'z',
            'calificacion_sugerida' => 70,
        ]);
        Sanctum::actingAs($this->docente);

        $this->postJson($this->url())->assertCreated();

        Http::assertSent(function (Request $request) {
            $prompt = $request['contents'][0]['parts'][1]['text'];   // parts[0] es el PDF, parts[1] el texto

            return str_contains($prompt, 'programación')
                && str_contains($prompt, 'ALUMNO: Ana')
                && ! str_contains($prompt, 'Pérez')
                && ! str_contains($prompt, 'alumno@test.com');
        });
    }
}