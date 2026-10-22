<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Control de acceso por rol (candidato / empleador / admin)
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureRole::class,
        ]);

        // Las páginas de sesión nunca se cachean en el navegador
        $middleware->web(append: [
            \App\Http\Middleware\NoCache::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Si el formulario de acceso se envió con el token vencido
        // (página guardada en caché o sesión expirada), no se muestra
        // la página de error: se vuelve al login con un aviso claro.
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            $tokenVencido = $e instanceof \Illuminate\Session\TokenMismatchException
                || ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException
                    && $e->getStatusCode() === 419);

            if ($tokenVencido && ($request->routeIs('login.post') || $request->routeIs('auth.register.*.post'))) {
                return redirect()
                    ->back(fallback: route('login'))
                    ->withErrors(['email' => 'Tu formulario de ingreso venció por inactividad. Volvé a intentarlo.']);
            }
        });
    })->create();
