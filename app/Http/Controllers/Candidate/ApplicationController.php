<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\Entrevista;
use App\Models\EstadoPostulacion;
use App\Models\HistorialEstado;
use App\Models\Notificacion;
use App\Models\Oferta;
use App\Models\Postulacion;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    /**
     * Muestra las postulaciones del candidato con datos REALES
     * de la base de datos (postulacion -> oferta -> empleador).
     * El estado actual (id_estado_post) es la columna "verdad".
     *
     * NOTA: los iconos se guardan SIN el prefijo "bi-"
     * (el Blade antepone "bi bi-").
     */
    public function index(): View
    {
        $postulaciones = $this->postulacionesDelCandidato()->get();

        $applications = [];
        $stats = ['total' => 0, 'registered' => 0, 'in_review' => 0, 'accepted' => 0, 'rejected' => 0, 'withdrawn' => 0];

        foreach ($postulaciones as $postulacion) {
            $app = $this->buildApplication($postulacion);

            $applications[] = $app;
            $stats['total']++;
            // La clave de stats es distinta del status de la tarjeta
            // ('review' -> 'in_review').
            $statsKey = $app['status'] === 'review' ? 'in_review' : $app['status'];
            $stats[$statsKey] = ($stats[$statsKey] ?? 0) + 1;
        }

        return view('candidate.applications', compact('applications', 'stats'));
    }

    /**
     * Postula al candidato a una oferta (POST).
     * - AJAX (modal del mapa / botón Guardar): responde JSON.
     * - Formulario (página Guardadas): redirige hacia atrás.
     * Crea historial_estado y una notificación "Postulación enviada".
     */
    public function store(Request $request, int $idOferta): JsonResponse|RedirectResponse
    {
        $candidato = auth()->user()?->candidato;

        if (! $candidato) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => 'Solo los candidatos pueden postularse.'], 403);
            }

            return back()->withErrors(['postulacion' => 'Solo los candidatos pueden postularse.']);
        }

        $oferta = Oferta::query()->find($idOferta);
        if (! $oferta) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => 'La oferta no existe.'], 404);
            }

            return back()->withErrors(['postulacion' => 'La oferta no existe.']);
        }

        // Ya postulado (y no retirada = sigue activa) → no duplicar.
        $existente = Postulacion::query()
            ->where('id_candidato', $candidato->id_candidato)
            ->where('id_oferta', $oferta->id_oferta)
            ->first();

        if ($existente && $existente->id_estado_post != 5) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => true, 'already' => true, 'message' => 'Ya estás postulado a esta oferta.']);
            }

            return back();
        }

        $estado = EstadoPostulacion::query()->where('id_estado_post', 1)->first();

        $postulacion = Postulacion::query()->create([
            'id_candidato' => $candidato->id_candidato,
            'id_oferta' => $oferta->id_oferta,
            // Explícito: evita el desfase de zona horaria del NOW() de Postgres.
            'fecha_postulacion' => now(),
            'id_estado_post' => $estado?->id_estado_post ?? 1,
        ]);

        $this->registrarHistorial($postulacion, 1, 'Postulación enviada por el candidato.');

        $empresa = $oferta->empleador->nombre_empresa ?? 'la empresa';
        Notificacion::query()->create([
            'id_usuario' => auth()->id(),
            'id_tipo_notif' => 1, // Postulación
            'titulo' => 'Postulación enviada',
            'mensaje' => 'Te postulaste exitosamente a ' . $oferta->titulo . ' en ' . $empresa . '.',
            'url_destino' => '/candidato/postulaciones',
            'tipo_referencia' => 'postulacion',
            'id_referencia' => $postulacion->id_postulacion,
            // Explícito (mismo motivo que fecha_postulacion).
            'fecha' => now(),
            'leida' => false,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => '¡Postulación enviada correctamente!']);
        }

        return back()->with('success', '¡Postulación enviada correctamente!');
    }

    /**
     * Retira una postulación concreta (Mis postulaciones).
     * No borra la fila: queda como estado "Retirada"
     * (trazabilidad en historial_estado, RN del diseño).
     */
    public function destroy(Request $request, int $idPostulacion): JsonResponse|RedirectResponse
    {
        $postulacion = $this->retirar($idPostulacion);

        if (! $postulacion) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => 'La postulación no existe.'], 404);
            }

            return back()->withErrors(['postulacion' => 'La postulación no existe.']);
        }

        return $this->respuestaRetiro($request, 'Postulación retirada correctamente.');
    }

    /**
     * Retira la postulación del candidato a una oferta (por id de oferta).
     * Usado por: botón "Retirar" de Guardadas y del modal/popup del mapa.
     */
    public function destroyByOffer(Request $request, int $idOferta): JsonResponse|RedirectResponse
    {
        $candidato = auth()->user()?->candidato;

        $postulacion = $candidato
            ? Postulacion::query()
                ->where('id_candidato', $candidato->id_candidato)
                ->where('id_oferta', $idOferta)
                ->first()
            : null;

        if (! $postulacion || !$this->retirar($postulacion->id_postulacion)) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => 'No tenés una postulación activa en esta oferta.'], 404);
            }

            return back()->withErrors(['postulacion' => 'No tenés una postulación activa en esta oferta.']);
        }

        return $this->respuestaRetiro($request, 'Postulación retirada correctamente.');
    }

    // ============================================
    // Privados
    // ============================================

    private function postulacionesDelCandidato()
    {
        $candidato = auth()->user()?->candidato;

        return Postulacion::query()
            ->where('id_candidato', $candidato?->id_candidato ?? 0)
            ->where('id_estado_post', '!=', 5)
            ->with(['oferta.empleador', 'oferta.ubicacion', 'oferta.modalidad', 'oferta.tipoContrato', 'entrevistas']);
    }

    /** Cambia el estado a "Retirada" + historial + notificación. */
    private function retirar(int $idPostulacion): ?Postulacion
    {
        $candidato = auth()->user()?->candidato;

        $postulacion = Postulacion::query()
            ->where('id_postulacion', $idPostulacion)
            ->where('id_candidato', $candidato?->id_candidato ?? 0)
            ->first();

        if (! $postulacion) {
            return null;
        }

        $postulacion->id_estado_post = 5; // Retirada
        $postulacion->save();

        $this->registrarHistorial($postulacion, 5, 'Postulación retirada por el candidato.');

        $oferta = $postulacion->oferta;
        Notificacion::query()->create([
            'id_usuario' => auth()->id(),
            'id_tipo_notif' => 1,
            'titulo' => 'Postulación retirada',
            'mensaje' => 'Retiraste tu postulación a ' . ($oferta->titulo ?? 'la oferta') . ' en ' . ($oferta->empleador->nombre_empresa ?? 'la empresa') . '.',
            'url_destino' => '/candidato/postulaciones',
            'tipo_referencia' => 'postulacion',
            'id_referencia' => $postulacion->id_postulacion,
            'fecha' => now(),
            'leida' => false,
        ]);

        return $postulacion;
    }

    private function respuestaRetiro(Request $request, string $mensaje): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => $mensaje]);
        }

        return redirect()->route('candidate.applications.index')->with('success', $mensaje);
    }

    private function registrarHistorial(Postulacion $postulacion, int $idEstado, string $comentario): void
    {
        HistorialEstado::query()->create([
            'id_postulacion' => $postulacion->id_postulacion,
            'id_estado_post' => $idEstado,
            // Explícito: NOW() de Postgres desalinea con Carbon (UTC).
            'fecha_cambio' => now(),
            'comentario' => $comentario,
            'id_usuario_cambio' => auth()->id(),
        ]);
    }

    /**
     * Traduce una fila de postulacion al formato de la tarjeta
     * (mismas claves que usa el prototipo).
     */
    private function buildApplication(Postulacion $postulacion): array
    {
        $oferta = $postulacion->oferta;
        $empresa = $oferta->empleador;

        $applied = Carbon::parse($postulacion->fecha_postulacion ?? now());

        // Mapa id_estado_post -> presentacion (clases del CSS).
        // 2 (En revisión) y 6 (Entrevista) comparten grupo "review" en las tabs.
        $presentacion = match ((int) $postulacion->id_estado_post) {
            2 => ['status' => 'review', 'label' => 'En revisión', 'icon' => 'hourglass-split'],
            6 => ['status' => 'review', 'label' => 'Entrevista', 'icon' => 'calendar-event'],
            3 => ['status' => 'accepted', 'label' => 'Aceptada', 'icon' => 'check-circle-fill'],
            4 => ['status' => 'rejected', 'label' => 'Rechazada', 'icon' => 'x-circle-fill'],
            5 => ['status' => 'withdrawn', 'label' => 'Retirada', 'icon' => 'box-arrow-right'],
            default => ['status' => 'registered', 'label' => 'Registrada', 'icon' => 'send'],
        };

        $estado = (int) $postulacion->id_estado_post;
        $empresaNombre = $empresa?->nombre_empresa ?? '—';

        $notas = [
            1 => 'Tu postulación fue registrada correctamente. Pronto comenzará la revisión.',
            2 => $empresaNombre . ' está revisando tu postulación. Te notificaremos cuando haya novedades.',
            6 => $empresaNombre . ' te convocó a una entrevista. Revisá "Mis entrevistas" para más información.',
            3 => '¡Felicitaciones! ' . $empresaNombre . ' aceptó tu postulación. Revisá tus entrevistas para más información.',
            4 => 'Lamentablemente no fuiste seleccionado. ¡Seguí intentando con otras ofertas!',
            5 => 'Retiraste tu postulación de este proceso de selección.',
        ];

        $timeline = $this->buildTimeline($estado, $postulacion->entrevistas->isNotEmpty());

        $actions = [];
        // Ver oferta → deep-link al mapa con la ubicación real.
        if ($oferta?->ubicacion) {
            $actions[] = [
                'kind' => 'primary', 'tag' => 'a', 'icon' => 'map',
                'label' => 'Ver oferta',
                'href' => route('map.index', [
                    'lat' => $oferta->ubicacion->latitud,
                    'lng' => $oferta->ubicacion->longitud,
                    'zoom' => 17,
                    'offer' => $oferta->id_oferta,
                ]),
            ];
        }
        // Ver entrevistas solo si esta postulación tiene alguna.
        if ($postulacion->entrevistas->isNotEmpty()) {
            $actions[] = ['kind' => 'secondary', 'tag' => 'a', 'icon' => 'calendar-event', 'label' => 'Ver entrevista', 'href' => 'interviews'];
        }
        // Ver empresa con datos reales de la BD.
        if ($empresa) {
            $actions[] = ['kind' => 'secondary', 'tag' => 'a', 'icon' => 'building', 'label' => 'Ver empresa', 'href' => 'company:' . $empresa->id_empleador];
        }
        // Se puede retirar mientras el proceso no esté cerrado.
        if (in_array($estado, [1, 2, 6], true)) {
            $actions[] = ['kind' => 'danger', 'tag' => 'button', 'icon' => 'x-circle', 'label' => 'Retirar', 'href' => null];
        }

        return [
            'id' => $postulacion->id_postulacion,
            'offerId' => $oferta?->id_oferta,
            'title' => $oferta->titulo ?? 'Oferta eliminada',
            'company' => $empresaNombre,
            'companyInitials' => SavedOfferController::initials($empresaNombre),
            'location' => SavedOfferController::locationLabel($oferta),
            'appliedAt' => 'Postulada ' . $applied->locale('es')->diffForHumans(),
            'status' => $presentacion['status'],
            'statusLabel' => $presentacion['label'],
            'statusIcon' => $presentacion['icon'],
            'statusNote' => $notas[$estado],
            'timeline' => $timeline,
            'actions' => $actions,
        ];
    }

    /** Pasos del timeline según el estado actual (clases done/current/rejected). */
    private function buildTimeline(int $estado, bool $tieneEntrevista): array
    {
        $chequear = fn (string $label) => ['label' => $label, 'state' => 'done', 'icon' => 'check'];
        $pendiente = fn (string $label, string $icon = 'circle') => ['label' => $label, 'state' => '', 'icon' => $icon];

        return match ($estado) {
            3 => [ // Aceptada
                $chequear('Registrada'),
                $chequear('En revisión'),
                ['label' => 'Entrevista', 'state' => $tieneEntrevista ? 'done' : '', 'icon' => $tieneEntrevista ? 'check' : 'circle'],
                ['label' => 'Aceptada', 'state' => 'current', 'icon' => 'check'],
            ],
            4 => [ // Rechazada
                $chequear('Registrada'),
                $chequear('En revisión'),
                ['label' => 'Rechazada', 'state' => 'rejected', 'icon' => 'x'],
            ],
            5 => [ // Retirada
                $chequear('Registrada'),
                ['label' => 'Retirada', 'state' => 'rejected', 'icon' => 'x'],
            ],
            6 => [ // En entrevista
                $chequear('Registrada'),
                $chequear('En revisión'),
                ['label' => 'Entrevista', 'state' => 'current', 'icon' => 'calendar-event'],
                $pendiente('Resultado'),
            ],
            2 => [ // En revisión
                $chequear('Registrada'),
                ['label' => 'En revisión', 'state' => 'current', 'icon' => 'hourglass-split'],
                $pendiente('Entrevista'),
                $pendiente('Resultado'),
            ],
            default => [ // Registrada
                ['label' => 'Registrada', 'state' => 'current', 'icon' => 'send'],
                $pendiente('En revisión'),
                $pendiente('Entrevista'),
                $pendiente('Resultado'),
            ],
        };
    }
}
