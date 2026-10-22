<?php

namespace Tests\Feature;

use App\Models\Materia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminUsuariosTest extends TestCase
{
    use RefreshDatabase;

    private const BASE = '/api/v1/admin/usuarios';

    private User $admin;
    private User $docente;
    private User $alumno;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->usuario('Administrador', 'admin@test.com', 'admin');
        $this->docente = $this->usuario('Docente Uno', 'docente@test.com', 'docente');
        $this->alumno = $this->usuario('Ana Pérez', 'ana@test.com', 'alumno');
    }

    private function usuario(string $nombre, string $email, string $rol): User
    {
        return User::create(['nombre' => $nombre, 'email' => $email, 'password' => '12345678', 'rol' => $rol]);
    }

    public function test_solo_el_admin_accede_a_la_gestion_de_usuarios(): void
    {
        foreach ([$this->docente, $this->alumno] as $usuario) {
            Sanctum::actingAs($usuario);

            $this->getJson(self::BASE)->assertForbidden();
            $this->postJson(self::BASE, ['nombre' => 'X', 'email' => 'x@test.com', 'password' => '12345678', 'rol' => 'admin'])
                ->assertForbidden();
        }

        $this->assertDatabaseMissing('users', ['email' => 'x@test.com']);
    }

    public function test_el_admin_lista_filtra_y_busca(): void
    {
        Sanctum::actingAs($this->admin);

        $this->getJson(self::BASE)->assertOk()->assertJsonCount(3, 'data');

        $this->getJson(self::BASE.'?rol=docente')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.email', 'docente@test.com');

        $this->getJson(self::BASE.'?buscar=ana')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nombre', 'Ana Pérez');

        $this->getJson(self::BASE.'?rol=inventado')->assertUnprocessable();
    }

    public function test_el_admin_da_de_alta_a_un_docente_que_puede_iniciar_sesion(): void
    {
        Sanctum::actingAs($this->admin);

        $this->postJson(self::BASE, [
            'nombre' => 'Profa. López',
            'email' => 'lopez@test.com',
            'password' => 'clave-segura-1',
            'rol' => 'docente',
        ])
            ->assertCreated()
            ->assertJsonPath('data.rol', 'docente')
            ->assertJsonPath('data.activo', true);

        $this->postJson('/api/v1/login', ['email' => 'lopez@test.com', 'password' => 'clave-segura-1'])
            ->assertOk()
            ->assertJsonPath('user.rol', 'docente');
    }

    public function test_la_validacion_rechaza_correo_repetido_contrasena_corta_y_rol_invalido(): void
    {
        Sanctum::actingAs($this->admin);

        $this->postJson(self::BASE, ['nombre' => 'Dup', 'email' => 'ana@test.com', 'password' => '12345678', 'rol' => 'alumno'])
            ->assertUnprocessable()->assertJsonValidationErrors('email');

        $this->postJson(self::BASE, ['nombre' => 'Corta', 'email' => 'corta@test.com', 'password' => '123', 'rol' => 'alumno'])
            ->assertUnprocessable()->assertJsonValidationErrors('password');

        $this->postJson(self::BASE, ['nombre' => 'Rol', 'email' => 'rol@test.com', 'password' => '12345678', 'rol' => 'rector'])
            ->assertUnprocessable()->assertJsonValidationErrors('rol');

        $this->postJson(self::BASE, [])
            ->assertUnprocessable()->assertJsonValidationErrors(['nombre', 'email', 'password', 'rol']);
    }

    public function test_el_admin_edita_a_un_usuario(): void
    {
        Sanctum::actingAs($this->admin);
        $url = self::BASE.'/'.$this->alumno->id;

        $this->putJson($url, ['nombre' => 'Ana María Pérez', 'email' => 'ana.maria@test.com'])
            ->assertOk()
            ->assertJsonPath('data.nombre', 'Ana María Pérez');

        $this->assertDatabaseHas('users', ['id' => $this->alumno->id, 'email' => 'ana.maria@test.com']);

        // Conservar su propio correo no cuenta como repetido
        $this->putJson($url, ['email' => 'ana.maria@test.com'])->assertOk();
    }

    public function test_el_admin_no_puede_cambiar_su_rol_ni_desactivarse_ni_restablecer_su_clave_por_aqui(): void
    {
        Sanctum::actingAs($this->admin);
        $propia = self::BASE.'/'.$this->admin->id;

        $this->putJson($propia, ['rol' => 'docente'])
            ->assertUnprocessable()->assertJsonValidationErrors('rol');

        $this->postJson($propia.'/desactivar')->assertUnprocessable();

        $this->putJson($propia, ['password' => 'otra-clave-123'])
            ->assertUnprocessable()->assertJsonValidationErrors('password');

        $this->assertDatabaseHas('users', ['id' => $this->admin->id, 'rol' => 'admin', 'activo' => true]);
    }

    public function test_no_se_cambia_el_rol_de_un_docente_con_materias(): void
    {
        Sanctum::actingAs($this->admin);
        $url = self::BASE.'/'.$this->docente->id;

        $materia = Materia::create(['docente_id' => $this->docente->id, 'nombre' => 'IA', 'codigo_acceso' => 'ABC123']);

        $this->putJson($url, ['rol' => 'alumno'])
            ->assertUnprocessable()->assertJsonValidationErrors('rol');

        $materia->delete();

        $this->putJson($url, ['rol' => 'alumno'])
            ->assertOk()
            ->assertJsonPath('data.rol', 'alumno');
    }

    public function test_desactivar_cierra_sesiones_y_bloquea_el_login_y_activar_lo_restaura(): void
    {
        $this->alumno->createToken('sesion-vieja');
        Sanctum::actingAs($this->admin);
        $url = self::BASE.'/'.$this->alumno->id;

        $this->postJson($url.'/desactivar')
            ->assertOk()
            ->assertJsonPath('data.activo', false);

        $this->assertSame(0, $this->alumno->tokens()->count());

        // Con la contraseña correcta, el mensaje explica por qué no entra
        $conClaveCorrecta = $this->postJson('/api/v1/login', ['email' => 'ana@test.com', 'password' => '12345678'])
            ->assertUnprocessable();
        $this->assertStringContainsString('desactivada', $conClaveCorrecta->json('errors.email.0'));

        // Con una incorrecta no se revela nada sobre el estado de la cuenta
        $conClaveIncorrecta = $this->postJson('/api/v1/login', ['email' => 'ana@test.com', 'password' => 'incorrecta'])
            ->assertUnprocessable();
        $this->assertStringNotContainsString('desactivada', $conClaveIncorrecta->json('errors.email.0'));

        $this->postJson($url.'/activar')->assertOk()->assertJsonPath('data.activo', true);
        $this->postJson('/api/v1/login', ['email' => 'ana@test.com', 'password' => '12345678'])->assertOk();
    }

    public function test_el_admin_restablece_la_contrasena_de_un_usuario_y_se_cierran_sus_sesiones(): void
    {
        $this->alumno->createToken('sesion-vieja');
        Sanctum::actingAs($this->admin);

        $this->putJson(self::BASE.'/'.$this->alumno->id, ['password' => 'nueva-clave-1'])->assertOk();

        $this->assertSame(0, $this->alumno->tokens()->count());

        $this->postJson('/api/v1/login', ['email' => 'ana@test.com', 'password' => '12345678'])->assertUnprocessable();
        $this->postJson('/api/v1/login', ['email' => 'ana@test.com', 'password' => 'nueva-clave-1'])->assertOk();
    }
}