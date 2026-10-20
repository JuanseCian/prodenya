<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Uso en rutas: ->middleware('rol:admin,coordinador')
 * Responde 403 si el usuario está inactivo o su rol no está en la lista.
 */
class EnsureRol
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        abort_unless($request->user()?->tieneRol(...$roles), 403, 'No tenés permiso para realizar esta acción.');

        return $next($request);
    }
}
