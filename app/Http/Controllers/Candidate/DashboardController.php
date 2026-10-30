<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\Entrevista;
use App\Models\Notificacion;
use App\Models\Oferta;
use App\Models\Postulacion;
use App\Models\OfertaGuardada;
use App\Services\OfertaRecomendador;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Dashboard del candidato con datos REALES de la BD:
     * contadores, ofertas recomendadas, últimas postulaciones,
     * próximas entrevistas, progreso de perfil y guardadas.
     */
    public function index(): View
    {
        $candidato = Auth::user()?->candidato;
        $idCandidato = $candidato?->id_candidato ?? 0;

        $hoy = now();

        // Ofertas nuevas: activas publicadas en los últimos 7 días.
        $newOffers = Oferta::query()
            ->where('id_estado_oferta', 1)
            ->where('fecha_publicacion', '>=', $hoy->copy()->subDays(7))
            ->count();

        // Entrevistas programadas (pendientes a futuro).
        $scheduledInterviews = Entrevista::query()
            ->whereHas('postulacion', fn ($q) => $q->where('id_candidato', $idCandidato))
            ->whereIn('estado', ['pendiente', 'negociando'])
            ->where('fecha_hora', '>=', $hoy)
            ->count();

        // ---- Stats (4 tarjetas) ----
        $counts = Postulacion::query()
            ->where('id_candidato', $idCandidato)
            ->selectRaw('count(*) as total')
            ->selectRaw("count(*) filter (where id_estado_post = 2 or id_estado_post = 6) as en_revision")
            ->first();

        $stats = [
            ['icon' => 'file-earmark-text', 'modifier' => '', 'value' => Postulacion::query()->where('id_candidato', $idCandidato)->where('id_estado_post', '!=', 5)->count(), 'label' => 'Postulaciones'],
            ['icon' => 'hourglass-split', 'modifier' => 'warning', 'value' => (int) ($counts->en_revision ?? 0), 'label' => 'En revisión'],
            ['icon' => 'calendar-check', 'modifier' => 'success', 'value' => $scheduledInterviews, 'label' => 'Entrevistas'],
            ['icon' => 'bookmark', 'modifier' => 'purple', 'value' => OfertaGuardada::query()->where('id_candidato', $idCandidato)->count(), 'label' => 'Guardadas'],
        ];

        // ---- Ofertas recomendadas: activas que aún no postulé ----
        // Con datos de perfil → ranking por afinidad (OfertaRecomendador).
        // Sin datos (cold start) → las 3 más recientes.
        $idsPostuladas = Postulacion::query()
            ->where('id_candidato', $idCandidato)
            ->where('id_estado_post', '!=', 5)
            ->pluck('id_oferta');

        $ofertasRecomendables = Oferta::query()
            ->where('id_estado_oferta', 1)
            ->whereNotIn('id_oferta', $idsPostuladas->count() ? $idsPostuladas : [0])
            ->with(['empleador', 'ubicacion', 'modalidad', 'tipoContrato'])
            ->orderByDesc('fecha_publicacion')
            ->get();

        $senales = $candidato ? OfertaRecomendador::senales($candidato) : null;

        if ($senales) {
            $rankeadas = $ofertasRecomendables
                ->map(fn (Oferta $oferta) => [
                    'oferta' => $oferta,
                    'match' => OfertaRecomendador::scoreOferta($senales, $oferta),
                ])
                ->sort(function ($a, $b) {
                    return $b['match']['score'] <=> $a['match']['score']
                        ?: ($b['oferta']->fecha_publicacion <=> $a['oferta']->fecha_publicacion);
                })
                ->take(3);
        } else {
            $rankeadas = $ofertasRecomendables
                ->take(3)
                ->map(fn (Oferta $oferta) => ['oferta' => $oferta, 'match' => null]);
        }

        $recommendedOffers = $rankeadas
            ->map(fn (array $item) => [
                'offerId' => $item['oferta']->id_oferta,
                'title' => $item['oferta']->titulo,
                'company' => $item['oferta']->empleador->nombre_empresa ?? '—',
                'companyInitials' => SavedOfferController::initials($item['oferta']->empleador->nombre_empresa ?? ''),
                'location' => SavedOfferController::locationLabel($item['oferta']),
                'modality' => $item['oferta']->modalidad?->nombre ?? '—',
                'contractType' => $item['oferta']->tipoContrato?->nombre ?? '—',
                'shortDescription' => Str::limit(strip_tags($item['oferta']->descripcion), 110),
                'salary' => SavedOfferController::salaryLabel($item['oferta']),
                'isNew' => $item['oferta']->fecha_publicacion && Carbon::parse($item['oferta']->fecha_publicacion) >= $hoy->copy()->subDays(3),
                'matchScore' => $item['match']['score'] ?? null,
                'matchReasons' => $item['match']['razones'] ?? [],
                'coords' => [
                    (float) $item['oferta']->ubicacion->latitud,
                    (float) $item['oferta']->ubicacion->longitud,
                ],
            ])
            ->all();

        // ---- Postulaciones recientes (últimas 4 activas) ----
        $recentApplications = Postulacion::query()
            ->where('id_candidato', $idCandidato)
            ->where('id_estado_post', '!=', 5)
            ->with(['oferta.empleador', 'oferta.ubicacion', 'estadoPostulacion'])
            ->orderByDesc('fecha_postulacion')
            ->limit(4)
            ->get()
            ->map(fn (Postulacion $postulacion) => $this->presentacionPostulacion($postulacion))
            ->all();

        // ---- Próximas entrevistas (widget) ----
        $upcomingInterviews = Entrevista::query()
            ->whereHas('postulacion', fn ($q) => $q->where('id_candidato', $idCandidato))
            ->whereIn('estado', ['pendiente', 'negociando'])
            ->where('fecha_hora', '>=', $hoy)
            ->with(['postulacion.oferta', 'postulacion.oferta.empleador'])
            ->orderBy('fecha_hora')
            ->limit(3)
            ->get()
            ->map(fn (Entrevista $entrevista) => [
                'day' => Carbon::parse($entrevista->fecha_hora)->format('d'),
                'month' => Carbon::parse($entrevista->fecha_hora)->locale('es')->translatedFormat('M'),
                'title' => $entrevista->postulacion->oferta->titulo ?? 'Entrevista',
                'time' => Carbon::parse($entrevista->fecha_hora)->format('H:i') . ' hs',
                'timeIcon' => 'clock',
                'place' => $entrevista->modalidad ?? 'Por confirmar',
                'placeIcon' => str_contains(strtolower($entrevista->modalidad ?? ''), 'video') || str_contains(strtolower($entrevista->modalidad ?? ''), 'remoto') ? 'camera-video' : 'geo-alt',
            ])
            ->all();

        // ---- Guardadas (widget) ----
        $savedOffers = OfertaGuardada::query()
            ->where('id_candidato', $idCandidato)
            ->with(['oferta.empleador'])
            ->orderByDesc('fecha_guardado')
            ->limit(5)
            ->get()
            ->filter(fn ($guardada) => $guardada->oferta)
            ->map(fn ($guardada) => [
                'offerId' => $guardada->oferta->id_oferta,
                'title' => $guardada->oferta->titulo,
                'company' => $guardada->oferta->empleador->nombre_empresa ?? '—',
                'coords' => [
                    (float) ($guardada->oferta->ubicacion?->latitud ?? 0),
                    (float) ($guardada->oferta->ubicacion?->longitud ?? 0),
                ],
            ])
            ->values()
            ->all();

        $profileProgress = \App\Http\Controllers\Candidate\ProfileController::progresoPerfil($candidato);

        return view('candidate.dashboard', compact(
            'newOffers', 'scheduledInterviews', 'stats',
            'recommendedOffers', 'recentApplications',
            'upcomingInterviews', 'savedOffers', 'profileProgress',
        ));
    }

    // ============================================
    // Privados
    // ============================================

    /**
     * Traduce la fila de postulacion al formato compacto del widget.
     */
    private function presentacionPostulacion(Postulacion $postulacion): array
    {
        $oferta = $postulacion->oferta;
        $empresa = $oferta?->empleador?->nombre_empresa ?? '—';
        $ciudad = $oferta?->ubicacion?->ciudad;

        [$statusClass, $statusIcon, $statusLabel] = match ((int) $postulacion->id_estado_post) {
            3 => ['accepted', 'check-circle-fill', 'Aceptada'],
            4 => ['rejected', 'x-circle-fill', 'Rechazada'],
            6 => ['review', 'calendar-event', 'Entrevista'],
            2 => ['review', 'hourglass-split', 'En revisión'],
            default => ['registered', 'send', 'Registrada'],
        };

        return [
            'title' => $oferta->titulo ?? 'Oferta eliminada',
            'companyLine' => $ciudad ? $empresa . ' · ' . $ciudad : $empresa,
            'companyInitials' => SavedOfferController::initials($oferta?->empleador->nombre_empresa ?? ''),
            'statusClass' => $statusClass,
            'statusIcon' => $statusIcon,
            'statusLabel' => $statusLabel,
            'href' => $oferta?->ubicacion ? route('map.index', [
                'lat' => $oferta->ubicacion->latitud,
                'lng' => $oferta->ubicacion->longitud,
                'zoom' => 17,
                'offer' => $oferta->id_oferta,
            ]) : route('candidate.applications.index'),
        ];
    }
}
