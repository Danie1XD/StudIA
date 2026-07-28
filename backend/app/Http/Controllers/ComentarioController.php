<?php

namespace App\Http\Controllers;

use App\Models\Comentario;
use Illuminate\Http\Request;

class ComentarioController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'tarea_id' => 'required|exists:tareas,id',
            'mensaje' => 'required|string'
        ]);

        $comentario = Comentario::create([
            'tarea_id' => $request->tarea_id,
            'user_id' => auth()->id(),
            'mensaje' => $request->mensaje
        ]);

        return response()->json([
            'message' => 'Comentario enviado',
            'comentario' => $comentario->load('user')
        ], 201);
    }
}