<?php

namespace App\Enums;

use App\Services\AI\Estrategias\EstrategiaCodigo;
use App\Services\AI\Estrategias\EstrategiaEnsayo;
use App\Services\AI\Estrategias\EstrategiaEvaluacion;
use App\Services\AI\Estrategias\EstrategiaGeneral;
use App\Services\AI\Estrategias\EstrategiaVisual;

enum TipoEvaluacion: string
{
    case General = 'general';
    case Ensayo = 'ensayo';
    case Codigo = 'codigo';
    case Visual = 'visual';

    public function estrategia(): EstrategiaEvaluacion
    {
        return match ($this) {
            self::General => new EstrategiaGeneral,
            self::Ensayo => new EstrategiaEnsayo,
            self::Codigo => new EstrategiaCodigo,
            self::Visual => new EstrategiaVisual,
        };
    }
}