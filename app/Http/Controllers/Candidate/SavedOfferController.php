<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\Oferta;
use App\Models\OfertaGuardada;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SavedOfferController extends Controller
{
    /**
     * Muestra las ofertas guardadas del candidato (RF-08).
     * Los datos vienen de la base de datos (oferta_guardada ->
     * oferta -> empleador/ubicacion/catálogos).
     */
    public function index(): View
    {
        $candidato = auth()->user()?->candidato;

        $savedOffers = [];
        if ($candidato) {
            $guardadas = OfertaGuardada::query()
                ->where('id_candidato', $candidato->id_candidato)
                ->orderByDesc('fecha_guardado')
                ->with(['oferta.empleador', 'oferta.ubicacion', 'oferta.modalidad', 'oferta.tipoContrato'])
                ->get();

            foreach ($guardadas as $guardada) {
                $oferta = $guardada->oferta;
                if (! $oferta) {
                    continue; // oferta eliminada (CASCADE la quita igual)
                }

                $savedOffers[] = [
                    'id' => $guardada->id_oferta_guardada,
                    'offerId' => $oferta->id_oferta,
                    'title' => $oferta->titulo,
                    'company' => $oferta->empleador->nombre_empresa ?? '—',
                    'companyInitials' => static::initials($oferta->empleador->nombre_empresa ?? ''),
                    'location' => static::locationLabel($oferta),
                    'modality' => $oferta->modalidad?->nombre ?? '—',
                    'contractType' => $oferta->tipoContrato?->nombre ?? '—',
                    'shortDescription' => Str::limit(strip_tags($oferta->descripcion), 140),
                    'salary' => static::salaryLabel($oferta),
                    'salaryMin' => $oferta->remuneracion_min ?: $oferta->remuneracion_max ?: 0,
                    'savedAtTs' => $guardada->fecha_guardado ? Carbon::parse($guardada->fecha_guardado)->getTimestamp() : 0,
                    'savedAt' => 'Guardada ' . Carbon::parse($guardada->fecha_guardado)
                        ->locale('es')->diffForHumans(),
                    'coords' => [
                        (float) $oferta->ubicacion->latitud,
                        (float) $oferta->ubicacion->longitud,
                    ],
                ];
            }
        }

        // ¿Dónde ya está postulado? Para el botón Postularme/Retirar.
        $postulatedIds = [];
        if ($candidato) {
            $postulatedIds = \App\Models\Postulacion::query()
                ->where('id_candidato', $candidato->id_candidato)
                ->where('id_estado_post', '!=', 5)
                ->pluck('id_oferta')
                ->all();
        }

        return view('candidate.saved', compact('savedOffers', 'postulatedIds'));
    }

    /**
     * Guarda una oferta (POST, AJAX desde el modal del mapa).
     * Idempotente: la clave única (id_candidato, id_oferta) evita duplicados.
     */
    public function store(Request $request, int $idOferta): JsonResponse
    {
        $candidato = auth()->user()?->candidato;

        if (! $candidato) {
            return response()->json([
                'saved' => false,
                'message' => 'Solo los candidatos pueden guardar ofertas.',
            ], 403);
        }

        $oferta = Oferta::query()->find($idOferta);
        if (! $oferta) {
            return response()->json([
                'saved' => false,
                'message' => 'La oferta no existe.',
            ], 404);
        }

        OfertaGuardada::query()->firstOrCreate([
            'id_candidato' => $candidato->id_candidato,
            'id_oferta' => $oferta->id_oferta,
        ], [
            // Explícito: evita que el default NOW() de Postgres
            // (hora local del servidor) desalinee con Carbon (UTC).
            'fecha_guardado' => now(),
        ]);

        return response()->json([
            'saved' => true,
            'message' => 'Oferta guardada correctamente.',
        ]);
    }

    /**
     * Quita una oferta de guardadas (DELETE).
     * - AJAX (mapa): responde JSON con el nuevo estado.
     * - Formulario (página Guardadas): redirige hacia atrás.
     */
    public function destroy(Request $request, int $idOferta): JsonResponse|RedirectResponse
    {
        $candidato = auth()->user()?->candidato;

        if ($candidato) {
            OfertaGuardada::query()
                ->where('id_candidato', $candidato->id_candidato)
                ->where('id_oferta', $idOferta)
                ->delete();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'saved' => false,
                'message' => 'Oferta quitada de guardadas.',
            ]);
        }

        return redirect()->route('candidate.saved.index');
    }

    // ============================================
    // Helpers de presentación (existentes para que las
    // tarjetas sigan con el formato del prototipo)
    // ============================================

    /** "Concesionaria San Miguel" -> "CS" (iniciales para el logo). */
    public static function initials(string $nombre): string
    {
        $palabras = preg_split('/\s+/', trim($nombre)) ?: [];
        $iniciales = '';
        foreach ($palabras as $palabra) {
            if ($palabra === '') continue;
            if (mb_strtolower($palabra) === 'de' || mb_strtolower($palabra) === 'y') continue;
            $iniciales .= mb_strtoupper(mb_substr($palabra, 0, 1));
            if (mb_strlen($iniciales) >= 2) break;
        }

        return $iniciales !== '' ? $iniciales : Str::substr($nombre, 0, 1);
    }

    /** Ubicación para el badge: ciudad + comuna si difiere. */
    public static function locationLabel($oferta): string
    {
        $ubicacion = $oferta->ubicacion;

        return $ubicacion?->ciudad ?? '—';
    }

    /** Remuneración tipo prototipo: "Gs. 7 - 9M" / "Gs. 9 - 12M" / "A convenir". */
    public static function salaryLabel($oferta): string
    {
        if (! $oferta->mostrar_remuneracion || (! $oferta->remuneracion_min && ! $oferta->remuneracion_max)) {
            return 'A convenir';
        }

        $fmt = static fn ($v) => rtrim(rtrim(number_format($v / 1000000, 1, '.', ''), '0'), '.');

        if ($oferta->remuneracion_min && $oferta->remuneracion_max && $oferta->remuneracion_min != $oferta->remuneracion_max) {
            return 'Gs. ' . $fmt($oferta->remuneracion_min) . ' - ' . $fmt($oferta->remuneracion_max) . 'M';
        }

        // Solo un extremo definido
        $solo = $oferta->remuneracion_min ?: $oferta->remuneracion_max;

        return 'Gs. ' . number_format($solo, 0, '', '.');
    }
}
