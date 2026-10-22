<?php

namespace App\Services\AI\Estrategias;

interface EstrategiaEvaluacion
{
    /** Qué debe priorizar la IA al revisar este tipo de trabajo. */
    public function enfoque(): string;
}