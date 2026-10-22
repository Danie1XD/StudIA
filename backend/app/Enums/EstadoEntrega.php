<?php
// app/Enums/EstadoEntrega.php
namespace App\Enums;

enum EstadoEntrega: string
{
    case Entregado = 'entregado';
    case Calificado = 'calificado';
}