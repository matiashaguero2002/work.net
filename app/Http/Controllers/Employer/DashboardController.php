<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Models\Entrevista;
use App\Models\Oferta;
use App\Models\Postulacion;
use App\Services\OfertaRecomendador;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Dashboard del empleador con datos REALES de la BD:
 * estadísticas, ofertas recientes, postulaciones recientes
 * (con match del recomendador), progreso del perfil de
 * empresa y próximas entrevistas.
 */
class DashboardController extends Controller
{
    public function index(): View
    {
        $empleador = Auth::user()->empleador;

        // Builder "virgen" para subqueries de ids (otro queda contaminado
        // con orderBy/limit tras el ->get() de ofertas recientes).
        $ofertasIds = Oferta::query()->where('id_empleador', $empleador->id_empleador);

        $ofertasQuery = Oferta::query()->where('id_empleador', $empleador->id_empleador);

        // ---------- Stats ----------
        $activas = (clone $ofertasQuery)->where('id_estado_oferta', 1)->count();
        $pausadas = (clone $ofertasQuery)->where('id_estado_oferta', 2)->count();
        $postulaciones = Postulacion::query()
            ->whereIn('id_oferta', (clone $ofertasIds)->select('id_oferta'))
            ->count();
        $entrevistasProximas = Entrevista::query()
            ->whereIn('id_postulacion', Postulacion::query()
                ->whereIn('id_oferta', (clone $ofertasIds)->select('id_oferta'))
                ->select('id_postulacion'))
            ->whereIn('estado', ['pendiente', 'negociando'])
            ->count();

        $stats = [
            ['icon' => 'briefcase-fill', 'modifier' => 'success', 'value' => $activas, 'label' => 'Ofertas activas'],
            ['icon' => 'inbox-fill', 'modifier' => '', 'value' => $postulaciones, 'label' => 'Postulaciones'],
            ['icon' => 'calendar-check-fill', 'modifier' => 'purple', 'value' => $entrevistasProximas, 'label' => 'Entrevistas próximas'],
            ['icon' => 'pause-circle-fill', 'modifier' => 'warning', 'value' => $pausadas, 'label' => 'Pausadas'],
        ];

        // ---------- Ofertas recientes (últimas 3) ----------
        $ofertas = $ofertasQuery
            ->with(['ubicacion', 'modalidad', 'tipoContrato'])
            ->withCount('postulaciones')
            ->orderByDesc('fecha_publicacion')
            ->limit(3)
            ->get();

        $recentOffers = $ofertas->map(fn (Oferta $oferta) => (new OfferController)->presentacionIndex($oferta) + [
            'publishedLine' => 'Publicada ' . Carbon::parse($oferta->fecha_publicacion ?? now())
                ->locale('es')->diffForHumans(),
        ])->values()->all();

        // ---------- Postulaciones recientes (últimas 4, con match real) ----------
        $senalesCache = [];
        $recentApplications = Postulacion::query()
            ->whereIn('id_oferta', (clone $ofertasIds)->select('id_oferta'))
            ->where('id_estado_post', '!=', 5)
            ->with(['candidato.usuario', 'oferta'])
            ->orderByDesc('fecha_postulacion')
            ->limit(4)
            ->get()
            ->map(function (Postulacion $postulacion) use (&$senalesCache) {
                $candidato = $postulacion->candidato;
                $oferta = $postulacion->oferta;

                if (! isset($senalesCache[$candidato->id_candidato])) {
                    $senalesCache[$candidato->id_candidato] = OfertaRecomendador::senales($candidato);
                }
                $match = $senalesCache[$candidato->id_candidato] !== null
                    ? OfertaRecomendador::scoreOferta($senalesCache[$candidato->id_candidato], $oferta)['score']
                    : 0;

                return [
                    'id' => $postulacion->id_postulacion,
                    'initials' => \App\Http\Controllers\Candidate\SavedOfferController::initials($candidato->usuario->nombre ?? ''),
                    'name' => $candidato->usuario->nombre ?? '—',
                    'positionLine' => ($oferta->titulo ?? '—') . ' · ' . Carbon::parse($postulacion->fecha_postulacion ?? now())->locale('es')->diffForHumans(),
                    'match' => $match,
                ];
            })->values()->all();

        // ---------- Progreso del perfil de empresa (helper real) ----------
        $companyProgress = CompanyController::progresoEmpresa($empleador);

        // ---------- Próximas entrevistas (widget) ----------
        $upcomingInterviews = Entrevista::query()
            ->whereIn('id_postulacion', Postulacion::query()
                ->whereIn('id_oferta', (clone $ofertasIds)->select('id_oferta'))
                ->select('id_postulacion'))
            ->whereIn('estado', ['pendiente', 'negociando'])
            ->with(['postulacion.oferta', 'postulacion.candidato.usuario'])
            ->orderBy('fecha_hora')
            ->limit(3)
            ->get()
            ->map(fn (Entrevista $e) => [
                'day' => Carbon::parse($e->fecha_hora)->format('d'),
                'month' => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'][Carbon::parse($e->fecha_hora)->month - 1],
                'title' => ($e->postulacion->candidato->usuario->nombre ?? '—') . ' - ' . ($e->postulacion->oferta->titulo ?? '—'),
                'time' => Carbon::parse($e->fecha_hora)->format('H:i') . ' hs · ' . (mb_strtolower($e->modalidad ?? '') === 'videollamada' ? 'Videollamada' : 'Presencial'),
                'timeIcon' => mb_strtolower($e->modalidad ?? '') === 'videollamada' ? 'camera-video' : 'clock',
            ])->values()->all();

        return view('employer.dashboard', compact(
            'stats',
            'recentOffers',
            'recentApplications',
            'companyProgress',
            'upcomingInterviews'
        ));
    }
}
