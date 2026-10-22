<?php
// app/Enums/EstadoTarea.php
namespace App\Enums;

enum EstadoTarea: string
{
    case Pendiente = 'pendiente';
    case SinEntregar = 'sin_entregar';
    case Entregada = 'entregada';
    case Calificada = 'calificada';
}