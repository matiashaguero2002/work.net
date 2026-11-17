<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Models\Candidato;
use App\Models\EstadoPostulacion;
use App\Models\HistorialEstado;
use App\Models\Notificacion;
use App\Models\Oferta;
use App\Models\Postulacion;
use App\Services\MatchPostulante;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Módulo "Postulantes" del empleador con datos REALES:
 * postulacion -> candidato -> usuario (+ aptitudes, experiencia,
 * títulos, idiomas y CV). El % de match usa el recomendador real.
 *
 * El {id} de las rutas es el ID DE LA POSTULACIÓN (no del candidato).
 */
class ApplicantController extends Controller
{
    public function index(): View
    {
        $empleador = Auth::user()->empleador;

        $query = Postulacion::query()
            ->whereHas('oferta', fn ($q) => $q->where('id_empleador', $empleador->id_empleador))
            ->where('id_estado_post', '!=', 5) // Las retiradas no se listan
            ->with(['oferta.ubicacion', 'candidato.usuario', 'candidato.aptitudes']);

        // Filtro por oferta (botón "Postulantes" de una tarjeta concreta).
        $idOfertaFiltro = (int) request('oferta', 0);
        if ($idOfertaFiltro > 0) {
            $query->where('id_oferta', $idOfertaFiltro);
        }

        $postulaciones = $query->orderByDesc('fecha_postulacion')->get();

        $senalesCache = [];

        $applicants = $postulaciones->map(function (Postulacion $postulacion) use (&$senalesCache) {
            $candidato = $postulacion->candidato;
            $oferta = $postulacion->oferta;
            $usuario = $candidato?->usuario;

            if (! isset($senalesCache[$candidato->id_candidato])) {
                $senalesCache[$candidato->id_candidato] = \App\Services\OfertaRecomendador::senales($candidato);
            }
            $match = $senalesCache[$candidato->id_candidato] !== null
                ? \App\Services\OfertaRecomendador::scoreOferta($senalesCache[$candidato->id_candidato], $oferta)['score']
                : 0;

            // Años de experiencia (desde la experiencia más antigua).
            $anios = '';
            $expAntigua = $candidato->experiencias()->orderBy('fecha_inicio')->first();
            if ($expAntigua?->fecha_inicio) {
                $anios = max(1, (int) ceil(Carbon::parse($expAntigua->fecha_inicio)->floatDiffInYears(now()))) . ' años de experiencia';
            }

            $titulo = $candidato->titulos()->orderBy('fecha_fin', 'desc')->first();
            $skills = $candidato->aptitudes->take(4)->pluck('nombre')->all();

            $estado = (int) $postulacion->id_estado_post;
            [$status, $statusLabel, $statusIcon] = match (true) {
                $estado === 2 || $estado === 6 => ['review', 'En revisión', 'eye-fill'],
                $estado === 3 => ['accepted', 'Aceptada', 'check-circle-fill'],
                $estado === 4 => ['rejected', 'Rechazada', 'x-circle-fill'],
                default => ['registered', 'Registrada', 'send-fill'],
            };

            return [
                'id' => $postulacion->id_postulacion,
                'initials' => \App\Http\Controllers\Candidate\SavedOfferController::initials($usuario->nombre ?? ''),
                'name' => $usuario->nombre ?? '—',
                'offerTitle' => $oferta->titulo ?? '—',
                'meta' => array_values(array_filter([
                    ['icon' => 'bi-geo-alt-fill', 'text' => $oferta?->ubicacion?->ciudad ?? '—'],
                    $anios !== '' ? ['icon' => 'bi-briefcase-fill', 'text' => $anios] : null,
                    $titulo?->nombre ? ['icon' => 'bi-mortarboard-fill', 'text' => $titulo->nombre] : null,
                    ['icon' => 'bi-clock-fill', 'text' => 'Postuló ' . Carbon::parse($postulacion->fecha_postulacion ?? now())->locale('es')->diffForHumans()],
                ])),
                'appliedTs' => Carbon::parse($postulacion->fecha_postulacion ?? now())->format('Y-m-d H:i'),
                'skills' => $skills,
                'status' => $status,
                'statusLabel' => $statusLabel,
                'statusIcon' => $statusIcon,
                'match' => $match,
                'lowMatch' => $match < 50,
                'dimmed' => $estado === 4,
                'hasCv' => (bool) $candidato?->cv,
                'actions' => ['profile'],
            ];
        })->values();

        $stats = [
            'total' => $applicants->count(),
            'registered' => $applicants->where('status', 'registered')->count(),
            'in_review' => $applicants->where('status', 'review')->count(),
            'accepted' => $applicants->where('status', 'accepted')->count(),
            'rejected' => $applicants->where('status', 'rejected')->count(),
        ];

        return view('employer.applicants.index', compact('applicants', 'stats', 'idOfertaFiltro'));
    }

    public function show(int $id): View
    {
        $postulacion = $this->postulacionPropia($id);
        if (! $postulacion) {
            abort(404);
        }

        $candidato = $postulacion->candidato()->with(['usuario', 'experiencias', 'titulos', 'aptitudes'])->first();
        $oferta = $postulacion->oferta;
        $usuario = $candidato->usuario;

        $cvUrl = null;
        $cvNombre = null;
        $cvTamano = null;
        if ($candidato->cv && Storage::disk('public')->exists($candidato->cv)) {
            $cvUrl = route('employer.applicants.cv', $postulacion->id_postulacion);
            $cvNombre = basename($candidato->cv);
            $cvTamano = $this->tamanoLegible(Storage::disk('public')->size($candidato->cv));
        }

        $estado = (int) $postulacion->id_estado_post;
        [$status, $statusLabel, $statusIcon] = match (true) {
            $estado === 2 => ['review', 'En revisión', 'eye-fill'],
            $estado === 6 => ['review', 'Entrevista', 'calendar-event-fill'],
            $estado === 3 => ['accepted', 'Aceptada', 'check-circle-fill'],
            $estado === 4 => ['rejected', 'Rechazada', 'x-circle-fill'],
            $estado === 5 => ['withdrawn', 'Retirada', 'box-arrow-right'],
            default => ['registered', 'Registrada', 'send-fill'],
        };

        $applicant = [
            'id' => $postulacion->id_postulacion,
            'name' => $usuario->nombre ?? '—',
            'initials' => \App\Http\Controllers\Candidate\SavedOfferController::initials($usuario->nombre ?? ''),
            'position' => $oferta->titulo ?? '—',
            'email' => $usuario->email ?? '—',
            'location' => trim(($oferta?->ubicacion?->ciudad ?? '')) ?: '—',
            'match' => MatchPostulante::puntaje($candidato, $oferta),
            'status' => $status,
            'statusLabel' => $statusLabel,
            'statusIcon' => $statusIcon,
            'appliedAt' => Carbon::parse($postulacion->fecha_postulacion ?? now())->locale('es')->diffForHumans(),
            'experiencias' => $candidato->experiencias->map(fn ($exp) => [
                'puesto' => $exp->puesto,
                'empresa' => $exp->empresa,
                'periodo' => $this->periodo($exp->fecha_inicio, $exp->fecha_fin),
                'tareas' => $exp->tareas,
            ])->all(),
            'titulos' => $candidato->titulos->map(fn ($t) => [
                'nombre' => $t->nombre,
                'institucion' => $t->institucion,
                'periodo' => $this->periodo($t->fecha_inicio, $t->fecha_fin),
            ])->all(),
            'skills' => $candidato->aptitudes->pluck('nombre')->all(),
            'languages' => DB::table('candidato_idioma')
                ->join('idioma', 'idioma.id_idioma', '=', 'candidato_idioma.id_idioma')
                ->leftJoin('nivel_idioma', 'nivel_idioma.id_nivel', '=', 'candidato_idioma.id_nivel')
                ->where('candidato_idioma.id_candidato', $candidato->id_candidato)
                ->orderBy('idioma.id_idioma')
                ->get([
                    'idioma.nombre as name',
                    'nivel_idioma.nombre as level',
                ])
                ->map(fn ($row) => [
                    'name' => $row->name,
                    'level' => $row->level ?? 'Por confirmar',
                ])->all(),
            'cv' => $cvUrl ? ['url' => $cvUrl, 'filename' => $cvNombre, 'size' => $cvTamano] : null,
            'perfil' => $candidato->perfil,
        ];

        // Opciones del selector de estado (catálogo real, sin "Retirada":
        // retirar es una acción del candidato, no del empleador).
        $statusOptions = EstadoPostulacion::query()
            ->whereIn('id_estado_post', [1, 2, 3, 4])
            ->orderBy('id_estado_post')
            ->get()
            ->map(fn ($e) => [
                'key' => match ((int) $e->id_estado_post) {
                    2 => 'review', 3 => 'accepted', 4 => 'rejected', default => 'registered',
                },
                'id' => (int) $e->id_estado_post,
                'label' => $e->nombre,
            ])->all();

        return view('employer.applicants.show', compact('applicant', 'statusOptions', 'postulacion', 'candidato'));
    }

    /** Rechaza la postulación (botón "Rechazar" del detalle). */
    public function reject(int $id): RedirectResponse
    {
        $postulacion = $this->postulacionPropia($id);
        if (! $postulacion) {
            abort(404);
        }

        if (in_array((int) $postulacion->id_estado_post, [4, 5], true)) {
            return back()->withErrors(['postulacion' => 'Esta postulación ya está cerrada.']);
        }

        $postulacion->id_estado_post = 4; // Rechazada
        $postulacion->save();

        $this->registrarHistorial($postulacion, 4, 'Postulación rechazada por el empleador.');
        $this->notificarCandidato($postulacion, 'Postulación rechazada', 'Lamentablemente no fuiste seleccionado para ' . ($postulacion->oferta->titulo ?? 'la oferta') . ' en ' . ($postulacion->oferta->empleador->nombre_empresa ?? 'la empresa') . '. ¡Seguí intentando con otras ofertas!');

        return redirect()->route('employer.applicants.index')->with('success', 'Postulación rechazada. El candidato fue notificado.');
    }

    /** Cambia el estado desde el selector del detalle. */
    public function estado(int $id): RedirectResponse
    {
        $postulacion = $this->postulacionPropia($id);
        if (! $postulacion) {
            abort(404);
        }

        $nuevo = (int) request('estado', 0);
        if (! in_array($nuevo, [1, 2, 3, 4], true)) {
            return back()->withErrors(['postulacion' => 'El estado elegido no es válido.']);
        }

        if ((int) $postulacion->id_estado_post === $nuevo) {
            return back();
        }

        $postulacion->id_estado_post = $nuevo;
        $postulacion->save();

        $ofertaTitulo = $postulacion->oferta->titulo ?? 'la oferta';
        $empresaNombre = $postulacion->oferta->empleador->nombre_empresa ?? 'la empresa';

        $this->registrarHistorial($postulacion, $nuevo, 'Estado actualizado por el empleador.');

        [$titulo, $mensaje] = match ($nuevo) {
            3 => ['¡Fuiste aceptado!', '¡Felicitaciones! ' . $empresaNombre . ' aceptó tu postulación a ' . $ofertaTitulo . '. Revisá tus entrevistas.'],
            4 => ['Postulación rechazada', 'Lamentablemente no fuiste seleccionado para ' . $ofertaTitulo . ' en ' . $empresaNombre . '. ¡Seguí intentando!'],
            2 => ['Tu postulación está en revisión', $empresaNombre . ' está revisando tu postulación a ' . $ofertaTitulo . '.'],
            default => ['Tu postulación fue actualizada', $empresaNombre . ' actualizó el estado de tu postulación a ' . $ofertaTitulo . '.'],
        };
        $this->notificarCandidato($postulacion, $titulo, $mensaje);

        return back()->with('success', 'Estado de la postulación actualizado. El candidato fue notificado.');
    }

    /** Descarga el CV del postulante (solo si postuló a una oferta propia). */
    public function cv(int $id)
    {
        $postulacion = $this->postulacionPropia($id);
        $cv = $postulacion?->candidato?->cv;

        if (! $cv || ! Storage::disk('public')->exists($cv)) {
            return back()->withErrors(['postulacion' => 'El candidato no cargó un CV.']);
        }

        return Storage::disk('public')->download($cv);
    }

    // ============================================
    // Privados
    // ============================================

    private function postulacionPropia(int $idPostulacion): ?Postulacion
    {
        return Postulacion::query()
            ->where('id_postulacion', $idPostulacion)
            ->whereHas('oferta', fn ($q) => $q->where('id_empleador', Auth::user()->empleador->id_empleador ?? 0))
            ->with(['oferta.ubicacion', 'oferta.empleador', 'candidato.usuario'])
            ->first();
    }

    private function registrarHistorial(Postulacion $postulacion, int $idEstado, string $comentario): void
    {
        HistorialEstado::query()->create([
            'id_postulacion' => $postulacion->id_postulacion,
            'id_estado_post' => $idEstado,
            'fecha_cambio' => now(),
            'comentario' => $comentario,
            'id_usuario_cambio' => Auth::id(),
        ]);
    }

    private function notificarCandidato(Postulacion $postulacion, string $titulo, string $mensaje): void
    {
        $idUsuario = $postulacion->candidato?->id_usuario;
        if (! $idUsuario) {
            return;
        }

        Notificacion::query()->create([
            'id_usuario' => $idUsuario,
            'id_tipo_notif' => 1, // Postulación
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'url_destino' => '/candidato/postulaciones',
            'tipo_referencia' => 'postulacion',
            'id_referencia' => $postulacion->id_postulacion,
            'fecha' => now(),
            'leida' => false,
        ]);
    }

    private function periodo(?string $desde, ?string $hasta): string
    {
        $fmt = fn (?string $f) => $f
            ? Carbon::parse($f)->locale('es')->translatedFormat('M Y')
            : null;

        $inicio = $fmt($desde) ?? '—';
        $fin = $hasta ? $fmt($hasta) : 'Presente';

        return $inicio . ' - ' . $fin;
    }

    private function tamanoLegible(int $bytes): string
    {
        return $bytes >= 1048576
            ? round($bytes / 1048576, 1) . ' MB'
            : max(1, (int) round($bytes / 1024)) . ' KB';
    }
}
