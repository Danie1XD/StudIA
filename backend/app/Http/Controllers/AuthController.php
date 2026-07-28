<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string',
            'email' => 'required|email|unique:users,email',
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

    // --- MÉTODOS PARA GOOGLE OAUTH ---

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            
            // Busca o crea al usuario automáticamente en tu base de datos
            $usuario = User::updateOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'nombre' => $googleUser->getName(),
                    'password' => Hash::make(rand(100000, 999999)),
                    'rol' => 'alumno',
                    'fecha_creacion' => now()
                ]
            );

            $token = $usuario->createToken('auth_token')->plainTextToken;

            // Redirige al frontend pasando el token de autenticación
            return redirect("http://localhost:5173/dashboard?token={$token}");

        } catch (\Exception $e) {
            return redirect('http://localhost:5173/login?error=google_failed');
        }
    }
}