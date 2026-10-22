<?php
// app/Services/AI/Estrategias/EstrategiaCodigo.php
namespace App\Services\AI\Estrategias;

class EstrategiaCodigo implements EstrategiaEvaluacion
{
    public function enfoque(): string
    {
        return 'Es un trabajo de programación. Prioriza: que cumpla lo que pide el enunciado, corrección lógica, legibilidad y nombres claros, manejo de errores y casos límite, y buenas prácticas del lenguaje. No asumas que el código compila o funciona: evalúa solo lo que ves y señala fallos concretos indicando la parte del código.';
    }
}