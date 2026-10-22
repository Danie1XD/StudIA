<?php

namespace Tests\Feature;

use App\Models\Entrega;
use App\Models\Materia;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReentregaTest extends TestCase
{
    use RefreshDatabase;

    private User $alumno;
    private Tarea $tarea;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $docente = User::create(['nombre' => 'Docente', 'email' => 'docente@test.com', 'password' => '12345678', 'rol' => 'docente']);
        $this->alumno = User::create(['nombre' => 'Alumno', 'email' => 'alumno@test.com', 'password' => '12345678', 'rol' => 'alumno']);

        $materia = Materia::create(['docente_id' => $docente->id, 'nombre' => 'IA', 'codigo_acceso' => 'ABC123']);
        $materia->alumnos()->attach($this->alumno->id);

        $this->tarea = Tarea::create(['materia_id' => $materia->id, 'titulo' => 'Actividad 1', 'instrucciones' => 'x']);

        Sanctum::actingAs($this->alumno);
    }

    private function url(): string
    {
        return "/api/v1/tareas/{$this->tarea->id}/entrega";
    }

    private function entregaExistente(?string $texto, ?string $archivo): Entrega
    {
        if ($archivo) {
            Storage::disk('local')->put($archivo, 'contenido');
        }

        return Entrega::create([
            'tarea_id' => $this->tarea->id,
            'user_id' => $this->alumno->id,
            'contenido' => $texto,
            'archivo_path' => $archivo,
            'estado' => 'entregado',
            'entregada_at' => now(),
        ]);
    }

    public function test_cambiar_solo_el_texto_conserva_el_archivo(): void
    {
        $entrega = $this->entregaExistente('texto viejo', 'entregas/viejo.pdf');

        $this->postJson($this->url(), ['contenido' => 'texto nuevo'])->assertOk();

        $entrega->refresh();
        $this->assertSame('texto nuevo', $entrega->contenido);
        $this->assertSame('entregas/viejo.pdf', $entrega->archivo_path);
        Storage::disk('local')->assertExists('entregas/viejo.pdf');
    }

    public function test_se_puede_borrar_el_texto_si_queda_el_archivo(): void
    {
        $entrega = $this->entregaExistente('texto que se va', 'entregas/viejo.pdf');

        $this->postJson($this->url(), ['contenido' => ''])->assertOk();

        $this->assertNull($entrega->fresh()->contenido);
        Storage::disk('local')->assertExists('entregas/viejo.pdf');
    }

    public function test_no_se_puede_dejar_la_entrega_sin_texto_ni_archivo(): void
    {
        $entrega = $this->entregaExistente('solo texto', null);

        $this->postJson($this->url(), ['contenido' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('contenido');

        $this->assertSame('solo texto', $entrega->fresh()->contenido);
    }

    public function test_enviar_solo_un_archivo_nuevo_lo_reemplaza_y_conserva_el_texto(): void
    {
        $entrega = $this->entregaExistente('texto que se conserva', 'entregas/viejo.pdf');
        $nuevo = UploadedFile::fake()->create('nuevo.pdf', 10, 'application/pdf');

        $this->post($this->url(), ['archivo' => $nuevo], ['Accept' => 'application/json'])->assertOk();

        $entrega->refresh();
        $this->assertSame('texto que se conserva', $entrega->contenido);
        $this->assertNotSame('entregas/viejo.pdf', $entrega->archivo_path);
        Storage::disk('local')->assertMissing('entregas/viejo.pdf');
        Storage::disk('local')->assertExists($entrega->archivo_path);
    }
}