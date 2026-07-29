<?php

namespace Tests\Unit;

use App\Models\Tarea;
use App\Services\GeminiEvaluationService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GeminiEvaluationServiceTest extends TestCase
{
    #[Test]
    public function it_returns_a_fallback_evaluation_when_the_api_key_is_missing(): void
    {
        config()->set('services.gemini.key', null);

        $service = new GeminiEvaluationService();
        $tarea = new Tarea([
            'rubrica' => [
                'claridad' => 'Explica de forma clara',
            ],
        ]);

        $resultado = $service->evaluarEntrega($tarea, 'Texto de prueba del alumno');

        $this->assertArrayHasKey('puntaje_sugerido', $resultado);
        $this->assertArrayHasKey('feedback', $resultado);
        $this->assertSame(85, $resultado['puntaje_sugerido']);
        $this->assertStringContainsString('retroalimentación', strtolower($resultado['feedback']));
    }
}
