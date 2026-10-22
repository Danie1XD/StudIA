<?php
// app/Services/AI/Estrategias/EstrategiaGeneral.php
namespace App\Services\AI\Estrategias;

class EstrategiaGeneral implements EstrategiaEvaluacion
{
    public function enfoque(): string
    {
        return 'Es un trabajo general. Prioriza: que cumpla lo que pide la consigna, la claridad, la completitud y la calidad del contenido.';
    }
}