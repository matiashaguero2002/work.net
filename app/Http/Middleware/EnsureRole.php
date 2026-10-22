<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * RN-01 / RN-02: restringe el acceso a vistas según el rol del usuario.
 *
 * Uso: ->middleware('role:candidato') o 'role:empleador,admin'.
 * Si el rol no coincide, redirige al panel propio del usuario
 * (nunca muestra un panel ajeno, ni por URL directa).
 */
class EnsureRole
{
    /** Panel al que pertenece cada rol. */
    private const PANEL_BY_ROLE = [
        'candidato' => 'candidate.dashboard',
        'empleador' => 'employer.dashboard',
        'admin'     => 'admin.dashboard',
    ];

    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        if ($roles !== [] && ! in_array(Auth::user()->rol, $roles, true)) {
            $propio = self::PANEL_BY_ROLE[Auth::user()->rol] ?? 'login';

            return redirect()
                ->route($propio)
                ->with('error', 'No tenés acceso a esa sección.');
        }

        return $next($request);
    }
}
