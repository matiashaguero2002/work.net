<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\Entrevista;
use App\Models\Notificacion;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InterviewController extends Controller
{
    /**
     * Muestra las entrevistas del candidato con datos REALES:
     * entrevista -> postulacion (del candidato) -> oferta -> empleador/ubicacion.
     */
    public function index(): View
    {
        $interviews = $this->entrevistasDelCandidato()->get();

        $data = ['upcoming' => [], 'past' => [], 'cancelled' => []];

        foreach ($interviews as $entrevista) {
            $data[$this->bucketDe($entrevista)][] = $this->buildInterview($entrevista);
        }

        // Orden: próximas por fecha ascendente; pasadas/canceladas descendente.
        $asc = fn ($a, $b) => strcmp($a['date'], $b['date']);
        $desc = fn ($a, $b) => strcmp($b['date'], $a['date']);
        usort($data['upcoming'], $asc);
        usort($data['past'], $desc);
        usort($data['cancelled'], $desc);

        return view('candidate.interviews', [
            'interviews' => array_merge($data['upcoming'], $data['past'], $data['cancelled']),
            'upcomingCount' => count($data['upcoming']),
            'pastCount' => count($data['past']),
            'cancelledCount' => count($data['cancelled']),
        ]);
    }

    /**
     * Cancela una entrevista (POST, AJAX). Pasa a estado "cancelada"
     * y notifica al empleador. Solo si estaba pendiente/negociando.
     */
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

        $this->notificarEmpleador($entrevista, 'Entrevista cancelada', 'El candidato canceló la entrevista por ' . ($entrevista->postulacion->oferta->titulo ?? 'la oferta') . '. Revisá tus entrevistas para reagendar.');

        return response()->json(['ok' => true, 'message' => 'Entrevista cancelada.']);
    }

    /**
     * Negociar horario (post del modal Reagendar):
     * registra la propuesta del candidato en las notas de la entrevista,
     * pasa a estado "negociando" (RN-19) y notifica al empleador.
     * La fecha/hora original se mantiene hasta que el empleador acepte.
     */
    public function negotiate(\Illuminate\Http\Request $request, int $idEntrevista): JsonResponse
    {
        $entrevista = $this->entrevistaPropia($idEntrevista);

        if (! $entrevista) {
            return response()->json(['ok' => false, 'message' => 'La entrevista no existe.'], 404);
        }

        if (! in_array($entrevista->estado, ['pendiente', 'negociando'], true)) {
            return response()->json(['ok' => false, 'message' => 'Esta entrevista ya no se puede reagendar.'], 422);
        }

        $validated = $request->validate([
            'fecha' => ['required', 'date', 'after:today'],
            'hora' => ['required', 'date_format:H:i'],
            'mensaje' => ['nullable', 'string', 'max:500'],
        ]);

        $propuesta = \Carbon\Carbon::parse($validated['fecha'])->locale('es')->translatedFormat('d/m/Y') . ' a las ' . $validated['hora'] . ' hs';

        $notas = trim($entrevista->notas ?? '');
        $notas .= ($notas !== '' ? "\n" : '') . '[Propuesta del candidato]: ' . $propuesta . ($validated['mensaje'] ? ' — "' . $validated['mensaje'] . '"' : '');

        $entrevista->notas = $notas;
        $entrevista->estado = 'negociando';
        $entrevista->save();

        $this->notificarEmpleador($entrevista, 'Propuesta de reagendamiento', 'El candidato propuso reagendar la entrevista por ' . ($entrevista->postulacion->oferta->titulo ?? 'la oferta') . ' para el ' . $propuesta . '.');

        return response()->json(['ok' => true, 'message' => 'Propuesta enviada. El empleador la revisará y te responderá.']);
    }

    // ============================================
    // Privados
    // ============================================

    private function entrevistasDelCandidato()
    {
        $candidato = Auth::user()?->candidato;

        return Entrevista::query()
            ->whereHas('postulacion', fn ($q) => $q->where('id_candidato', $candidato?->id_candidato ?? 0))
            ->with(['postulacion.oferta.empleador', 'postulacion.oferta.ubicacion']);
    }

    /** Entrevista que pertenece a una postulación del candidato actual. */
    private function entrevistaPropia(int $idEntrevista): ?Entrevista
    {
        $candidato = Auth::user()?->candidato;

        return Entrevista::query()
            ->where('id_entrevista', $idEntrevista)
            ->whereHas('postulacion', fn ($q) => $q->where('id_candidato', $candidato?->id_candidato ?? 0))
            ->with(['postulacion.oferta.empleador', 'postulacion.oferta.ubicacion'])
            ->first();
    }

    private function notificarEmpleador(Entrevista $entrevista, string $titulo, string $mensaje): void
    {
        $empleador = $entrevista->postulacion?->oferta?->empleador;
        if (! $empleador?->id_usuario) {
            return;
        }

        Notificacion::query()->create([
            'id_usuario' => $empleador->id_usuario,
            'id_tipo_notif' => 2, // Entrevista
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'url_destino' => '/empleador/entrevistas',
            'tipo_referencia' => 'entrevista',
            'id_referencia' => $entrevista->id_entrevista,
            'fecha' => now(),
            'leida' => false,
        ]);
    }

    /** Bucket por estado: próximas / realizadas / canceladas (fuente: BD). */
    private function bucketDe(Entrevista $entrevista): string
    {
        if ($entrevista->estado === 'cancelada') {
            return 'cancelled';
        }

        $fecha = Carbon::parse($entrevista->fecha_hora);
        if ($entrevista->estado === 'realizada' || $fecha < now()) {
            return 'past';
        }

        return 'upcoming'; // pendiente o negociando, a futuro
    }

    /** Traduce la entrevista al formato de la tarjeta del prototipo. */
    private function buildInterview(Entrevista $entrevista): array
    {
        $oferta = $entrevista->postulacion->oferta;
        $empresa = $oferta?->empleador;
        $fecha = Carbon::parse($entrevista->fecha_hora);
        $duracion = (int) ($entrevista->duracion_minutos ?? 60);

        $negociando = $entrevista->estado === 'negociando';
        $status = $entrevista->estado === 'cancelada' ? 'cancelled' : ($entrevista->estado === 'realizada' || $fecha < now() ? 'past' : 'scheduled');

        $modality = $entrevista->modalidad ?? 'Por confirmar';
        $modalityIcon = match (mb_strtolower($modality)) {
            'remoto', 'virtual', 'videollamada' => 'camera-video',
            'hibrido', 'híbrido' => 'arrow-left-right',
            default => 'geo-alt-fill',
        };

        return [
            'id' => $entrevista->id_entrevista,
            'day' => $fecha->format('d'),
            'month' => $this->mesCorto($fecha),
            'monthName' => $fecha->locale('es')->translatedFormat('F'),
            'year' => $fecha->format('Y'),
            'date' => $fecha->format('Y-m-d H:i'),
            'title' => 'Entrevista: ' . ($oferta->titulo ?? 'Postulación'),
            'status' => $status,
            'statusLabel' => match ($status) {
                'cancelled' => 'Cancelada',
                'past' => 'Realizada',
                default => $negociando ? 'Reagendando' : 'Programada',
            },
            'statusIcon' => match ($status) {
                'cancelled' => 'x-circle-fill',
                'past' => 'check-circle-fill',
                default => $negociando ? 'hourglass-split' : 'clock',
            },
            'company' => $empresa?->nombre_empresa ?? '—',
            'companyInitials' => SavedOfferController::initials($empresa?->nombre_empresa ?? ''),
            'position' => $oferta->titulo ?? '—',
            'negotiating' => $status === 'scheduled' && $negociando,
            'timeStart' => $fecha->format('H:i'),
            'timeEnd' => $fecha->copy()->addMinutes($duracion)->format('H:i'),
            'modality' => $modality,
            'modalityIcon' => $modalityIcon,
            'interviewer' => 'Equipo de RRHH',
            'duration' => $duracion >= 60 ? floor($duracion / 60) . ' h' : $duracion . ' min',
            'notes' => $entrevista->notas,
            'offerId' => $oferta?->id_oferta,
            'coords' => [
                (float) ($oferta?->ubicacion->latitud ?? 0),
                (float) ($oferta?->ubicacion->longitud ?? 0),
            ],
        ];
    }

    private function mesCorto(Carbon $fecha): string
    {
        return ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'][$fecha->month - 1];
    }
}
