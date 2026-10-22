<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Perfil\CambiarPasswordRequest;
use App\Http\Requests\Perfil\UpdatePerfilRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class PerfilController extends Controller
{
    public function update(UpdatePerfilRequest $request): UserResource
    {
        $user = $request->user();
        $user->update($request->validated());

        return new UserResource($user);
    }

    public function cambiarPassword(CambiarPasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! Hash::check($request->validated('password_actual'), $user->password)) {
            throw ValidationException::withMessages([
                'password_actual' => ['La contraseña actual no es correcta.'],
            ]);
        }

        $user->update(['password' => $request->validated('password')]);   // el cast 'hashed' la encripta

        // Se cierran las demás sesiones; la actual se conserva
        $actual = $user->currentAccessToken();
        $otras = $user->tokens();

        if ($actual instanceof PersonalAccessToken) {
            $otras->where('id', '!=', $actual->id);
        }

        $otras->delete();

        return response()->json(['message' => 'Contraseña actualizada. Se cerraron tus otras sesiones.']);
    }
}