<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, $role): Response
    {
        // Verificamos si el usuario está logueado y si su rol coincide con el requerido
        if (! $request->user() || $request->user()->rol !== $role) {
            // Si es un alumno intentando entrar a zona de docentes, lo bloqueamos
            return response()->json([
                'error' => 'Acceso denegado. No tienes permisos para realizar esta acción.'
            ], 403);
        }

        return $next($request);
    }
}