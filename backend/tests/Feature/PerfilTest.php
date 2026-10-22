<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PerfilTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD_URL = '/api/v1/perfil/password';

    private User $alumno;

    protected function setUp(): void
    {
        parent::setUp();

        $this->alumno = User::create(['nombre' => 'Ana Pérez', 'email' => 'ana@test.com', 'password' => '12345678', 'rol' => 'alumno']);
        User::create(['nombre' => 'Otro', 'email' => 'otro@test.com', 'password' => '12345678', 'rol' => 'alumno']);

        Sanctum::actingAs($this->alumno);
    }

    public function test_la_contrasena_actual_no_se_recorta(): void
    {
        $this->alumno->update(['password' => '  con espacios  ']);

        $this->putJson(self::PASSWORD_URL, [
            'password_actual' => '  con espacios  ',
            'password' => 'nueva-clave-1',
            'password_confirmation' => 'nueva-clave-1',
        ])->assertOk();
    }

    public function test_el_usuario_edita_su_nombre_y_no_puede_cambiarse_el_rol(): void
    {
        $this->putJson('/api/v1/perfil', ['nombre' => 'Ana María', 'rol' => 'admin'])
            ->assertOk()
            ->assertJsonPath('data.nombre', 'Ana María')
            ->assertJsonPath('data.rol', 'alumno');

        $this->assertDatabaseHas('users', ['id' => $this->alumno->id, 'nombre' => 'Ana María', 'rol' => 'alumno']);
    }

    public function test_el_correo_no_puede_repetirse_pero_puede_conservar_el_propio(): void
    {
        $this->putJson('/api/v1/perfil', ['email' => 'otro@test.com'])
            ->assertUnprocessable()->assertJsonValidationErrors('email');

        $this->putJson('/api/v1/perfil', ['email' => 'ana@test.com'])->assertOk();
    }

    public function test_el_cambio_de_contrasena_valida_los_datos(): void
    {
        $this->putJson(self::PASSWORD_URL, ['password_actual' => 'incorrecta', 'password' => 'nueva-clave-1', 'password_confirmation' => 'nueva-clave-1'])
            ->assertUnprocessable()->assertJsonValidationErrors('password_actual');

        $this->putJson(self::PASSWORD_URL, ['password_actual' => '12345678', 'password' => 'nueva-clave-1', 'password_confirmation' => 'distinta-123'])
            ->assertUnprocessable()->assertJsonValidationErrors('password');

        $this->putJson(self::PASSWORD_URL, ['password_actual' => '12345678', 'password' => '12345678', 'password_confirmation' => '12345678'])
            ->assertUnprocessable()->assertJsonValidationErrors('password');

        $this->putJson(self::PASSWORD_URL, ['password_actual' => '12345678', 'password' => 'corta', 'password_confirmation' => 'corta'])
            ->assertUnprocessable()->assertJsonValidationErrors('password');

        $this->assertTrue(Hash::check('12345678', $this->alumno->fresh()->password));
    }

    public function test_cambiar_la_contrasena_la_actualiza_y_cierra_las_otras_sesiones(): void
    {
        $this->alumno->createToken('celular');
        $this->alumno->createToken('tablet');

        $this->putJson(self::PASSWORD_URL, ['password_actual' => '12345678', 'password' => 'nueva-clave-1', 'password_confirmation' => 'nueva-clave-1'])
            ->assertOk();

        $this->assertTrue(Hash::check('nueva-clave-1', $this->alumno->fresh()->password));
        $this->assertSame(0, $this->alumno->tokens()->count());
    }
}