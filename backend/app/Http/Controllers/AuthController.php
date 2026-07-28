<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string',
            'email' => 'required|email|unique:users,email', // Apunta a 'users' en lugar de 'usuarios'
            'password' => 'required|string|min:6',
            'rol' => 'required|in:docente,alumno'
        ]);

        $usuario = User::create([ 
            'nombre' => $request->nombre,
            'email' => $request->email,
            'password' => Hash::make($request->password), 
            'rol' => $request->rol,
            'fecha_creacion' => now()
        ]);

        $token = $usuario->createToken('auth_token')->plainTextToken;

        return response()->json(['usuario' => $usuario, 'token' => $token], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $usuario = User::where('email', $request->email)->first(); 

        // Verificación segura que evita fallos de tipo y responde con JSON limpio
        if (! $usuario || ! Hash::check($request->password, $usuario->password)) {
            return response()->json([
                'message' => 'Las credenciales son incorrectas.'
            ], 422);
        }

        $token = $usuario->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'usuario' => $usuario
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada exitosamente']);
    }
}