<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Evita que el navegador guarde en caché (o restauré con "atrás")
 * páginas de sesión: sin esto, al volver atrás desde un panel el
 * navegador puede mostrar el formulario de login desactualizado
 * (o un panel privado) restaurado de la caché del navegador.
 */
class NoCache
{
    public function handle(Request $request, Closure $next): mixed
    {
        /** @var \Illuminate\Http\Response $response */
        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }
}
