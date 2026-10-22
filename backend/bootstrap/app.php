<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureRole::class,
        ]);

        // "password_actual" se deja tal cual, igual que "password": los espacios cuentan
        $middleware->trimStrings(except: ['password_actual']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $esApi = fn (Request $request): bool => $request->is('api/*') || $request->expectsJson();

        $exceptions->shouldRenderJsonWhen($esApi);

        $exceptions->render(function (AuthenticationException $e, Request $request) use ($esApi) {
            if ($esApi($request)) {
                return response()->json([
                    'message' => 'Tu sesión no es válida o expiró. Inicia sesión de nuevo.',
                ], 401);
            }
        });

        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) use ($esApi) {
            if ($esApi($request)) {
                $mensaje = $e->getMessage();
                $generico = $mensaje === '' || $mensaje === 'This action is unauthorized.';

                return response()->json([
                    'message' => $generico ? 'No tienes permiso para realizar esta acción.' : $mensaje,
                ], 403);
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) use ($esApi) {
            if ($esApi($request)) {
                $mensaje = $e->getMessage();
                $generico = $mensaje === ''
                    || $mensaje === 'Not Found'
                    || str_starts_with($mensaje, 'No query results')
                    || str_contains($mensaje, 'could not be found');

                return response()->json([
                    'message' => $generico ? 'No se encontró lo que buscas.' : $mensaje,
                ], 404);
            }
        });

        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) use ($esApi) {
            if ($esApi($request)) {
                return response()->json(['message' => 'Método no permitido.'], 405);
            }
        });

        $exceptions->render(function (TooManyRequestsHttpException $e, Request $request) use ($esApi) {            if ($esApi($request)) {
                $espera = $e->getHeaders()['Retry-After'] ?? null;

                return response()->json([
                    'message' => $espera
                        ? "Demasiados intentos. Espera {$espera} segundos e inténtalo de nuevo."
                        : 'Demasiados intentos. Espera un momento e inténtalo de nuevo.',
                ], 429, $e->getHeaders());
            }
        });
    })->create();