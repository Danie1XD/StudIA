<?php
// app/Services/AI/Estrategias/EstrategiaEnsayo.php
namespace App\Services\AI\Estrategias;

class EstrategiaEnsayo implements EstrategiaEvaluacion
{
    public function enfoque(): string
    {
        return 'Es un trabajo escrito (ensayo, reporte o texto argumentativo). Prioriza: idea central o tesis clara, argumentos sustentados con ejemplos o evidencia, estructura (introducción, desarrollo y conclusión), coherencia entre párrafos, redacción y ortografía, y uso de fuentes si la tarea las pide.';
    }
}