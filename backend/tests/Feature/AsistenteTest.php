<?php

namespace Tests\Feature;

use App\Models\Materia;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AsistenteTest extends TestCase
{
    use RefreshDatabase;

    private const URL = '/api/v1/asistente/mensajes';

    private User $alumno;
    private User $docente;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.gemini.key' => 'clave-de-prueba']);

        $this->docente = User::create(['nombre' => 'Docente', 'email' => 'docente@test.com', 'password' => '12345678', 'rol' => 'docente']);
        $this->alumno = User::create(['nombre' => 'Alumno', 'email' => 'alumno@test.com', 'password' => '12345678', 'rol' => 'alumno']);

        $materia = Materia::create(['docente_id' => $this->docente->id, 'nombre' => 'IA', 'codigo_acceso' => 'ABC123']);
        $materia->alumnos()->attach($this->alumno->id);

        Tarea::create([
            'materia_id' => $materia->id,
            'titulo' => 'Entrega de prueba',
            'instrucciones' => 'Haz algo.',
            'fecha_limite' => now()->addDays(3),
        ]);
    }

    private function fingirGemini(string $texto = 'Claro, te ayudo con eso.'): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => $texto]]]]],
            ], 200),
        ]);
    }

    public function test_el_alumno_recibe_respuesta_y_el_contexto_incluye_sus_tareas_pendientes(): void
    {
        $this->fingirGemini();
        Sanctum::actingAs($this->alumno);

        $this->postJson(self::URL, ['mensaje' => '¿Qué tengo pendiente?'])
            ->assertOk()
            ->assertJsonPath('data.respuesta', 'Claro, te ayudo con eso.');

        Http::assertSent(function (Request $request) {
            $sistema = $request['systemInstruction']['parts'][0]['text'];

            return $request->hasHeader('x-goog-api-key', 'clave-de-prueba')
                && str_contains($sistema, 'Entrega de prueba')
                && ! isset($request['generationConfig']['responseMimeType']);   // texto libre, no JSON
        });
    }

    public function test_el_saludo_inicial_del_asistente_no_se_envia_como_primer_turno(): void
    {
        $this->fingirGemini();
        Sanctum::actingAs($this->alumno);

        $this->postJson(self::URL, [
            'mensaje' => 'Dudas',
            'historial' => [
                ['rol' => 'asistente', 'texto' => '¡Hola! Soy el asistente.'],
                ['rol' => 'usuario', 'texto' => 'Pregunta 1'],
                ['rol' => 'asistente', 'texto' => 'Respuesta 1'],
            ],
        ])->assertOk();

        Http::assertSent(function (Request $request) {
            $turnos = $request['contents'];

            return count($turnos) === 3
                && $turnos[0]['role'] === 'user' && $turnos[0]['parts'][0]['text'] === 'Pregunta 1'
                && $turnos[1]['role'] === 'model'
                && $turnos[2]['role'] === 'user' && $turnos[2]['parts'][0]['text'] === 'Dudas';
        });
    }

    public function test_el_docente_tambien_puede_usarlo_sin_contexto_de_tareas(): void
    {
        $this->fingirGemini();
        Sanctum::actingAs($this->docente);

        $this->postJson(self::URL, ['mensaje' => 'Hola'])->assertOk();

        Http::assertSent(fn (Request $request) => ! str_contains($request['systemInstruction']['parts'][0]['text'], 'Entrega de prueba'));
    }

    public function test_un_mensaje_vacio_se_rechaza_sin_llamar_a_la_ia(): void
    {
        Http::fake();
        Sanctum::actingAs($this->alumno);

        $this->postJson(self::URL, [])->assertUnprocessable();
        Http::assertNothingSent();
    }

    public function test_un_historial_demasiado_largo_se_rechaza(): void
    {
        Http::fake();
        Sanctum::actingAs($this->alumno);

        $this->postJson(self::URL, [
            'mensaje' => 'Hola',
            'historial' => array_fill(0, 21, ['rol' => 'usuario', 'texto' => 'hola']),
        ])->assertUnprocessable();
        Http::assertNothingSent();
    }

    public function test_el_administrador_no_tiene_acceso(): void
    {
        Http::fake();
        $admin = User::create(['nombre' => 'Admin', 'email' => 'admin@test.com', 'password' => '12345678', 'rol' => 'admin']);
        Sanctum::actingAs($admin);

        $this->postJson(self::URL, ['mensaje' => 'Hola'])->assertForbidden();
        Http::assertNothingSent();
    }
}