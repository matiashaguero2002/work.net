<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Models\Entrevista;
use App\Models\HistorialEstado;
use App\Models\Notificacion;
use App\Models\Postulacion;
use App\Services\MatchPostulante;
use App\Services\PropuestaEntrevista;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Entrevistas del empleador con datos REALES:
 * entrevista -> postulacion (a ofertas propias) -> candidato/oferta.
 *
 * Acciones:
 *  - Convocar (create/store): usa la SEDE del perfil de empresa.
 *  - Reprogramar: modal de chat (propuesta en entrevista.notas +
 *    estado "negociando"), espejo del flujo del candidato (RN-19).
 *  - Cancelar: estado "cancelada" + notificación al candidato.
 */
class InterviewController extends Controller
{
    public function index(): View
    {
        $entrevistas = $this->entrevistasDelEmpleador()->get();

        $hoy = now()->format('Y-m-d');
        $en7dias = now()->addDays(7)->endOfDay();

        $futuras = $entrevistas->whereIn('estado', ['pendiente', 'negociando']);
        $hoyCount = $futuras->filter(fn ($e) => Carbon::parse($e->fecha_hora)->format('Y-m-d') === $hoy)->count();
        $semanaCount = $futuras->filter(fn ($e) => Carbon::parse($e->fecha_hora)->between(now(), $en7dias))->count();

        $stats = [
            ['icon' => 'hourglass-split', 'modifier' => 'warning', 'value' => $futuras->count(), 'label' => 'Pendientes'],
            ['icon' => 'calendar-event-fill', 'modifier' => 'purple', 'value' => $hoyCount, 'label' => 'Hoy'],
            ['icon' => 'calendar-week-fill', 'modifier' => 'success', 'value' => $semanaCount, 'label' => 'Esta semana'],
            ['icon' => 'check-circle-fill', 'modifier' => '', 'value' => $entrevistas->where('estado', 'realizada')->count(), 'label' => 'Completadas'],
        ];

        $interviews = $entrevistas->map(fn ($e) => $this->buildInterview($e))->values()->all();

        // Widget: próximas (pendiente/negociando, a futuro, máx 4).
        $upcoming = collect($interviews)
            ->filter(fn ($i) => in_array($i['estado'], ['pendiente', 'negociando'], true))
            ->sortBy('date')
            ->take(4)
            ->map(fn ($i) => [
                'day' => $i['day'],
                'month' => $i['month'],
                'title' => $i['name'] . ' · ' . $i['position'],
                'icon' => $i['modality'] === 'Videollamada' ? 'camera-video' : 'clock',
                'meta' => $i['time'] . ' hs · ' . $i['modality'],
            ])->values()->all();

        // Resumen por modalidad (entrevistas no canceladas).
        $presencial = collect($interviews)->filter(fn ($i) => $i['modality'] === 'Presencial')->count();
        $video = collect($interviews)->filter(fn ($i) => $i['modality'] === 'Videollamada')->count();
        $total = max(1, $presencial + $video);
        $modalitySummary = [
            ['key' => 'presencial', 'icon' => 'building', 'label' => 'Presencial', 'count' => $presencial, 'width' => (int) round($presencial / $total * 100)],
            ['key' => 'video', 'icon' => 'camera-video', 'label' => 'Videollamada', 'count' => $video, 'width' => (int) round($video / $total * 100)],
        ];
        $modalityTotal = ($presencial + $video) . ' entrevistas';

        return view('employer.interviews.index', compact('stats', 'interviews', 'upcoming', 'modalitySummary', 'modalityTotal'));
    }

    /** Formulario "Convocar a entrevista" para una postulación concreta. */
    public function create(int $applicantId): View
    {
        $postulacion = Postulacion::query()
            ->where('id_postulacion', $applicantId)
            ->whereHas('oferta', fn ($q) => $q->where('id_empleador', Auth::user()->empleador->id_empleador ?? 0))
            ->with(['oferta', 'candidato.usuario'])
            ->first();

        if (! $postulacion) {
            abort(404);
        }

        $candidato = $postulacion->candidato;

        $applicant = [
            'id' => $postulacion->id_postulacion,
            'name' => $candidato->usuario->nombre ?? '—',
            'initials' => \App\Http\Controllers\Candidate\SavedOfferController::initials($candidato->usuario->nombre ?? ''),
            'position' => $postulacion->oferta->titulo ?? '—',
            'email' => $candidato->usuario->email ?? '—',
            'match' => MatchPostulante::puntaje($candidato, $postulacion->oferta),
        ];

        // La dirección de la entrevista es la de la sede del perfil.
        $sede = (new OfferController)->sedeDelEmpleador();

        return view('employer.interviews.create', compact('applicant', 'postulacion', 'sede'));
    }

    public function store(Request $request, int $applicantId): RedirectResponse
    {
        $postulacion = Postulacion::query()
            ->where('id_postulacion', $applicantId)
            ->whereHas('oferta', fn ($q) => $q->where('id_empleador', Auth::user()->empleador->id_empleador ?? 0))
            ->with(['oferta', 'candidato'])
            ->first();

        if (! $postulacion) {
            abort(404);
        }

        $validated = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => ['required', 'date_format:H:i'],
            'duration' => ['required', 'integer', 'in:30,45,60,90,120'],
            'modality' => ['required', 'in:presencial,videollamada'],
            'video_url' => ['nullable', 'url', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'date.required' => 'La fecha de la entrevista es obligatoria.',
            'date.after_or_equal' => 'La fecha no puede ser anterior a hoy.',
            'time.required' => 'La hora de inicio es obligatoria.',
            'duration.required' => 'Elegí la duración estimada.',
            'modality.required' => 'Elegí la modalidad de la entrevista.',
            'video_url.url' => 'El enlace de la videollamada debe ser una URL válida.',
        ]);

        if ($validated['modality'] === 'videollamada' && empty($validated['video_url'])) {
            return back()->withErrors(['video_url' => 'Si la entrevista es por videollamada, cargá el enlace.'])
                ->withInput();
        }

        // Presencial → la dirección es la de la sede del perfil de empresa.
        $sede = null;
        if ($validated['modality'] === 'presencial') {
            $sede = (new OfferController)->sedeDelEmpleador();
            if (! $sede) {
                return redirect()->route('employer.company.profile')
                    ->withErrors(['sede' => 'Primero cargá la ubicación de tu sede en "Mi empresa".']);
            }
        }

        $fechaHora = Carbon::parse($validated['date'] . ' ' . $validated['time']);

        DB::transaction(function () use ($validated, $postulacion, $sede, $fechaHora) {
            Entrevista::query()->create([
                'id_postulacion' => $postulacion->id_postulacion,
                'id_sede' => $sede?->id_sede,
                'fecha_hora' => $fechaHora,
                'duracion_minutos' => (int) $validated['duration'],
                'modalidad' => $validated['modality'] === 'presencial' ? 'Presencial' : 'Videollamada',
                'enlace_virtual' => $validated['video_url'] ?? null,
                'notas' => $validated['notes'] ?? null,
                'estado' => 'pendiente',
                'fecha_creacion' => now(),
            ]);

            // La postulación pasa a "Entrevista" (6).
            $postulacion->id_estado_post = 6;
            $postulacion->save();

            HistorialEstado::query()->create([
                'id_postulacion' => $postulacion->id_postulacion,
                'id_estado_post' => 6,
                'fecha_cambio' => now(),
                'comentario' => 'Convocado a entrevista por el empleador.',
                'id_usuario_cambio' => Auth::id(),
            ]);

            $empresa = $postulacion->oferta->empleador->nombre_empresa ?? 'la empresa';
            Notificacion::query()->create([
                'id_usuario' => $postulacion->candidato->id_usuario,
                'id_tipo_notif' => 2, // Entrevista
                'titulo' => 'Entrevista programada',
                'mensaje' => $empresa . ' te convocó a una entrevista por ' . $postulacion->oferta->titulo . ' para el '
                    . $fechaHora->locale('es')->translatedFormat('d/m/Y \a \l\a\s H:i') . ' hs ('
                    . ($validated['modality'] === 'presencial' ? 'presencial en ' . ($sede?->ubicacion?->ciudad ?? 'la sede') : 'videollamada') . ').',
                'url_destino' => '/entrevistas',
                'tipo_referencia' => 'entrevista',
                'fecha' => now(),
                'leida' => false,
            ]);
        });

        return redirect()->route('employer.interviews.index')
            ->with('success', 'Entrevista convocada. El candidato recibió la notificación con los detalles.');
    }

    /**
     * Reprogramar (modal de chat, espejo del candidato):
     * registra la propuesta del EMPLEADOR en entrevista.notas,
     * pasa a "negociando" y notifica al candidato.
     */
    public function negotiate(Request $request, int $idEntrevista): JsonResponse
    {
        $entrevista = $this->entrevistaPropia($idEntrevista);

        if (! $entrevista) {
            return response()->json(['ok' => false, 'message' => 'La entrevista no existe.'], 404);
        }

        if (! in_array($entrevista->estado, ['pendiente', 'negociando'], true)) {
            return response()->json(['ok' => false, 'message' => 'Esta entrevista ya no se puede reagendar.'], 422);
        }

        // No se puede encadenar otra propuesta mientras la última
        // (propia) sigue esperando respuesta del candidato.
        if (PropuestaEntrevista::propuestaPendiente($entrevista->notas, PropuestaEntrevista::EMPLEADOR)) {
            return response()->json(['ok' => false, 'message' => 'Ya enviaste una propuesta; esperá la respuesta del candidato antes de enviar otra.'], 422);
        }

        $validated = $request->validate([
            'fecha' => ['required', 'date', 'after:today'],
            'hora' => ['required', 'date_format:H:i'],
            'mensaje' => ['nullable', 'string', 'max:500'],
        ]);

        $propuesta = Carbon::parse($validated['fecha'])->locale('es')->translatedFormat('d/m/Y') . ' a las ' . $validated['hora'] . ' hs';

        // Timestamp de envío (punto 4): queda registrado en la propia línea.
        $enviado = ' (enviado ' . now()->locale('es')->translatedFormat('d/m/Y H:i') . ')';

        $notas = trim($entrevista->notas ?? '');
        $notas .= ($notas !== '' ? "\n" : '') . '[Propuesta del empleador]: ' . $propuesta . ($validated['mensaje'] ? ' — "' . $validated['mensaje'] . '"' : '') . $enviado;

        $entrevista->notas = $notas;
        $entrevista->estado = 'negociando';
        $entrevista->save();

        $candidato = $entrevista->postulacion?->candidato;
        if ($candidato?->id_usuario) {
            Notificacion::query()->create([
                'id_usuario' => $candidato->id_usuario,
                'id_tipo_notif' => 2, // Entrevista
                'titulo' => 'Propuesta de reagendamiento',
                'mensaje' => ($entrevista->postulacion->oferta->empleador->nombre_empresa ?? 'La empresa')
                    . ' te propuso reagendar la entrevista por ' . ($entrevista->postulacion->oferta->titulo ?? 'la oferta')
                    . ' para el ' . $propuesta . '. Abre "Mis entrevistas" para responder.',
                'url_destino' => '/entrevistas',
                'tipo_referencia' => 'entrevista',
                'id_referencia' => $entrevista->id_entrevista,
                'fecha' => now(),
                'leida' => false,
            ]);
        }

        return response()->json(['ok' => true, 'message' => 'Propuesta enviada. El candidato podrá responderla desde sus entrevistas.']);
    }

    /**
     * Aceptar / Rechazar la última propuesta del CANDIDATO (chat RN-19).
     */
    public function responderPropuesta(Request $request, int $idEntrevista): JsonResponse
    {
        $entrevista = $this->entrevistaPropia($idEntrevista);

        if (! $entrevista) {
            return response()->json(['ok' => false, 'message' => 'La entrevista no existe.'], 404);
        }

        if ($entrevista->estado !== 'negociando') {
            return response()->json(['ok' => false, 'message' => 'No hay ninguna propuesta pendiente de respuesta.'], 422);
        }

        $accion = $request->validate(['accion' => ['required', 'in:aceptar,rechazar']])['accion'];

        $ultima = PropuestaEntrevista::ultima($entrevista->notas);
        if ($ultima === null || $ultima['autor'] !== PropuestaEntrevista::CANDIDATO) {
            return response()->json(['ok' => false, 'message' => 'No hay ninguna propuesta del candidato pendiente de respuesta.'], 422);
        }

        $resultado = $accion === 'aceptar'
            ? PropuestaEntrevista::aceptar($entrevista, PropuestaEntrevista::EMPLEADOR)
            : PropuestaEntrevista::rechazar($entrevista, PropuestaEntrevista::EMPLEADOR);

        return response()->json(['ok' => $resultado['ok'], 'message' => $resultado['message']], $resultado['ok'] ? 200 : 422);
    }

    /**
     * Recibo de lectura (punto 4): marca la última propuesta del candidato
     * como vista cuando el empleador abre el chat. Idempotente.
     */
    public function visto(int $idEntrevista): JsonResponse
    {
        $entrevista = $this->entrevistaPropia($idEntrevista);

        if (! $entrevista) {
            return response()->json(['ok' => false], 404);
        }

        PropuestaEntrevista::marcarVisto($entrevista, PropuestaEntrevista::EMPLEADOR);

        return response()->json(['ok' => true]);
    }

    /** Cancela una entrevista (AJAX) + notificación al candidato. */
    public function cancel(int $idEntrevista): JsonResponse
    {
        $entrevista = $this->entrevistaPropia($idEntrevista);

        if (! $entrevista) {
            return response()->json(['ok' => false, 'message' => 'La entrevista no existe.'], 404);
        }

        if (! in_array($entrevista->estado, ['pendiente', 'negociando'], true)) {
            return response()->json(['ok' => false, 'message' => 'Esta entrevista ya no se puede cancelar.'], 422);
        }

        $entrevista->estado = 'cancelada';
        $entrevista->save();

        $candidato = $entrevista->postulacion?->candidato;
        if ($candidato?->id_usuario) {
            Notificacion::query()->create([
                'id_usuario' => $candidato->id_usuario,
                'id_tipo_notif' => 2, // Entrevista
                'titulo' => 'Entrevista cancelada',
                'mensaje' => ($entrevista->postulacion->oferta->empleador->nombre_empresa ?? 'La empresa')
                    . ' canceló la entrevista por ' . ($entrevista->postulacion->oferta->titulo ?? 'la oferta') . '.',
                'url_destino' => '/entrevistas',
                'tipo_referencia' => 'entrevista',
                'id_referencia' => $entrevista->id_entrevista,
                'fecha' => now(),
                'leida' => false,
            ]);
        }

        return response()->json(['ok' => true, 'message' => 'Entrevista cancelada.']);
    }

    // ============================================
    // Privados
    // ============================================

    private function entrevistasDelEmpleador()
    {
        return Entrevista::query()
            ->whereHas('postulacion.oferta', fn ($q) => $q->where('id_empleador', Auth::user()->empleador->id_empleador ?? 0))
            ->with(['postulacion.oferta.ubicacion', 'postulacion.oferta.empleador', 'postulacion.candidato.usuario', 'sede.ubicacion']);
    }

    private function entrevistaPropia(int $idEntrevista): ?Entrevista
    {
        return Entrevista::query()
            ->where('id_entrevista', $idEntrevista)
            ->whereHas('postulacion.oferta', fn ($q) => $q->where('id_empleador', Auth::user()->empleador->id_empleador ?? 0))
            ->with(['postulacion.oferta.ubicacion', 'postulacion.oferta.empleador', 'postulacion.candidato.usuario', 'sede.ubicacion'])
            ->first();
    }

    /** Traduce la entrevista al formato de la tarjeta del prototipo. */
    private function buildInterview(Entrevista $e): array
    {
        $oferta = $e->postulacion->oferta;
        $candidato = $e->postulacion->candidato;
        $fecha = Carbon::parse($e->fecha_hora);
        $duracion = (int) ($e->duracion_minutos ?? 60);
        $hoy = now()->format('Y-m-d');

        $estado = $e->estado ?? 'pendiente';
        $esPasada = $fecha < now();

        // El estado real manda: pendiente/negociando/realizada/cancelada.
        [$status, $statusLabel, $statusIcon] = match (true) {
            $estado === 'cancelada' => ['cancelled', 'Cancelada', 'x-circle-fill'],
            $estado === 'realizada' || ($esPasada && $estado === 'pendiente') => ['completed', 'Completada', 'check-circle-fill'],
            $estado === 'negociando' => ['negotiating', 'Reagendando', 'hourglass-split'],
            default => ['pending', 'Pendiente', 'hourglass-split'],
        };

        $esVideollamada = mb_strtolower($e->modalidad ?? '') === 'videollamada';
        $detail = $esVideollamada
            ? ($e->enlace_virtual ?: 'Sin enlace cargado')
            : trim(($e->sede?->ubicacion?->direccion ?? $oferta?->ubicacion?->direccion ?? '') . ', ' . ($e->sede?->ubicacion?->ciudad ?? $oferta?->ubicacion?->ciudad ?? ''));

        return [
            'id' => $e->id_entrevista,
            'idPostulacion' => $e->id_postulacion,
            'initials' => \App\Http\Controllers\Candidate\SavedOfferController::initials($candidato?->usuario->nombre ?? ''),
            'name' => $candidato?->usuario->nombre ?? '—',
            'position' => $oferta->titulo ?? '—',
            'estado' => $estado,
            'status' => $status,
            'statusIcon' => $statusIcon,
            'statusLabel' => $statusLabel,
            'day' => $fecha->format('d'),
            'month' => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'][$fecha->month - 1],
            'date' => $fecha->format('Y-m-d'),
            'today' => $fecha->format('Y-m-d') === $hoy,
            'time' => $fecha->format('H:i'),
            'duration' => $duracion >= 60 ? floor($duracion / 60) . ' hora' . ($duracion > 60 ? 's' : '') : $duracion . ' minutos',
            'modality' => $esVideollamada ? 'Videollamada' : 'Presencial',
            'modalityIcon' => $esVideollamada ? 'camera-video-fill' : 'building',
            'modalityLabel' => $esVideollamada ? 'Videollamada' : 'Presencial',
            'detailIcon' => $esVideollamada ? 'link-45deg' : 'geo-alt-fill',
            'detail' => $detail,
            'notes' => $e->notas,
        ];
    }
}
