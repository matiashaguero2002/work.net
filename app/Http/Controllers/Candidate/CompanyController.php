<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\Empleador;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CompanyController extends Controller
{
    /**
     * Perfil de empresa visible para el CANDIDATO ("Ver empresa").
     * Datos reales: empleador + sus ofertas activas.
     */
    public function show(int $idEmpleador): View
    {
        $empresa = Empleador::query()
            ->with(['ofertas.ubicacion', 'ofertas.modalidad', 'ofertas.tipoContrato'])
            ->find($idEmpleador);

        if (! $empresa) {
            abort(404);
        }

        // Solo ofertas activas (id_estado_oferta = 1).
        $ofertas = $empresa->ofertas
            ->filter(fn ($oferta) => (int) $oferta->id_estado_oferta === 1)
            ->sortByDesc('fecha_publicacion')
            ->values();

        $ciudades = $empresa->ofertas
            ->map(fn ($oferta) => $oferta->ubicacion?->ciudad)
            ->filter()->unique()->values()->implode(', ');

        return view('candidate.company', [
            'empresa' => $empresa,
            'companyInitials' => SavedOfferController::initials($empresa->nombre_empresa ?? ''),
            'ofertas' => $ofertas,
            'ciudades' => $ciudades ?: '—',
        ]);
    }
}
