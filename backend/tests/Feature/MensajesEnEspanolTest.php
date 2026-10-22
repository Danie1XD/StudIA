<?php

namespace Tests\Feature;

use App\Models\Materia;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MensajesEnEspanolTest extends TestCase
{
    use RefreshDatabase;

    private User $docente;
    private Materia $materia;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('es');

        $this->docente = User::create(['nombre' => 'Docente', 'email' => 'docente@test.com', 'password' => '12345678', 'rol' => 'docente']);
        $this->materia = Materia::create(['docente_id' => $this->docente->id, 'nombre' => 'IA', 'codigo_acceso' => 'ABC123']);
    }

    public function test_las_validaciones_sin_mensaje_propio_salen_en_espanol(): void
    {
        Sanctum::actingAs($this->docente);

        $this->putJson("/api/v1/materias/{$this->materia->id}", ['nombre' => ''])
            ->assertUnprocessable()
            ->assertJsonPath('errors.nombre.0', 'El campo nombre es obligatorio.');
    }

    public function test_un_recurso_que_no_existe_responde_404_en_espanol(): void
    {
        Sanctum::actingAs($this->docente);

        $this->getJson('/api/v1/materias/9999')
            ->assertNotFound()
            ->assertJsonPath('message', 'No se encontró lo que buscas.');
    }

    public function test_un_404_con_mensaje_propio_conserva_su_texto(): void
    {
        $tarea = Tarea::create(['materia_id' => $this->materia->id, 'titulo' => 'Sin PDF', 'instrucciones' => 'x']);
        Sanctum::actingAs($this->docente);

        $this->getJson("/api/v1/tareas/{$tarea->id}/archivo")
            ->assertNotFound()
            ->assertJsonPath('message', 'Esta tarea no tiene archivo adjunto.');
    }

    public function test_un_permiso_denegado_responde_403_en_espanol(): void
    {
        $alumno = User::create(['nombre' => 'Alumno', 'email' => 'alumno@test.com', 'password' => '12345678', 'rol' => 'alumno']);
        Sanctum::actingAs($alumno);

        $this->getJson("/api/v1/materias/{$this->materia->id}")
            ->assertForbidden()
            ->assertJsonPath('message', 'No tienes permiso para realizar esta acción.');
    }

    public function test_sin_sesion_responde_401_en_espanol(): void
    {
        $this->getJson('/api/v1/me')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Tu sesión no es válida o expiró. Inicia sesión de nuevo.');
    }

    public function test_un_metodo_no_permitido_responde_405_en_espanol(): void
    {
        $this->deleteJson('/api/v1/login')
            ->assertStatus(405)
            ->assertJsonPath('message', 'Método no permitido.');
    }

    public function test_el_limite_de_intentos_del_login_dice_cuanto_esperar(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/login', ['email' => 'nadie@test.com', 'password' => 'incorrecta'])
                ->assertUnprocessable();
        }

        $respuesta = $this->postJson('/api/v1/login', ['email' => 'nadie@test.com', 'password' => 'incorrecta'])
            ->assertStatus(429);

        $this->assertStringContainsString('Demasiados intentos. Espera', $respuesta->json('message'));
        $this->assertNotNull($respuesta->headers->get('Retry-After'));
    }
}