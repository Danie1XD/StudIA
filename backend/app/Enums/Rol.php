<?php
// app/Enums/Rol.php
namespace App\Enums;

enum Rol: string
{
    case Alumno = 'alumno';
    case Docente = 'docente';
    case Admin = 'admin';
}