<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Para rutas API pura: no redirigir al 'login' web (no existe).
        // Retornar null permite que Authenticate lance AuthenticationException
        // en lugar de RouteNotFoundException.
        $middleware->redirectGuestsTo(function ($request) {
            if (str_starts_with($request->path(), 'api/') || $request->expectsJson()) {
                return null;
            }
            return '/login';
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Convertir AuthenticationException en respuesta JSON 401 para la API
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || str_starts_with($request->path(), 'api/')) {
                return response()->json([
                    'ok'      => false,
                    'mensaje' => 'No autenticado. Por favor inicie sesión.',
                ], 401);
            }
        });
    })->create();
