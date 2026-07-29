<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, $role): Response
    {
        $usuario = $request->user();
        $rolUsuario = null;

        if ($usuario) {
            $rolUsuario = $usuario->rol ?? $usuario->role ?? null;
            if (is_array($rolUsuario)) {
                $rolUsuario = $rolUsuario[0] ?? null;
            }
        }

        if (! $usuario || strtolower((string) $rolUsuario) !== strtolower((string) $role)) {
            return response()->json([
                'error' => 'Acceso denegado. No tienes permisos para realizar esta acción.'
            ], 403);
        }

        return $next($request);
    }
}