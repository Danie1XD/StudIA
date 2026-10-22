<?php
// app/Services/AI/Estrategias/EstrategiaVisual.php
namespace App\Services\AI\Estrategias;

class EstrategiaVisual implements EstrategiaEvaluacion
{
    public function enfoque(): string
    {
        return 'Es un trabajo visual o de síntesis (mapa mental, infografía, presentación o cartel). Prioriza: que cubra los conceptos que pide la consigna, la organización y jerarquía de las ideas, la claridad y legibilidad, y la precisión del contenido. No califiques el diseño por gusto personal.';
    }
}