<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use RuntimeException;

class GeminiException extends RuntimeException
{
    public function __construct(string $message, private int $estado = 502)
    {
        parent::__construct($message);
    }

    /** Laravel llama a este método cuando la excepción llega al manejador: así responde en JSON. */
    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], $this->estado);
    }
}