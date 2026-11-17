<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Candidate\SavedOfferController;
use App\Http\Controllers\Employer\OfferController;
use App\Models\Oferta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Mapa de ofertas laborales (RF-01 / RF-08).
 * Las ofertas vienen de la BD: las publicadas por los empleadores
 * (con la dirección de su sede) aparecen acá en tiempo real.
 */
class MapController extends Controller
{
    public function index(Request $request): View
    {
        // Solo ofertas ACTIVAS aparecen en el mapa.
        $ofertas = Oferta::query()
            ->where('id_estado_oferta', 1)
            ->whereHas('ubicacion', fn ($q) => $q->whereNotNull('latitud')->whereNotNull('longitud'))
            ->with(['empleador.usuario', 'ubicacion', 'modalidad', 'tipoContrato', 'areaLaboral', 'requisitos', 'beneficios'])
            ->orderByDesc('fecha_publicacion')
            ->get();

        // Imágenes de banner por área (igual que el prototipo, sin paquetes).
        $imagenesPorArea = [
            1 => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=1200&h=600&fit=crop',
            2 => 'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=1200&h=600&fit=crop',
            3 => 'https://images.unsplash.com/photo-1533750349088-bd997276ada8?w=1200&h=600&fit=crop',
            4 => 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=1200&h=600&fit=crop',
        ];

        $areasSlug = [1 => 'informatica', 2 => 'diseno', 3 => 'marketing', 4 => 'admin'];

        $offers = $ofertas
            ->map(function (Oferta $oferta, int $i) use ($imagenesPorArea, $areasSlug) {
                $empleador = $oferta->empleador;
                $ubicacion = $oferta->ubicacion;
                $idArea = $oferta->id_area;

                $reqRows = $oferta->requisitos()->get();
                if ($reqRows->isEmpty()) {
                    // Ofertas sembradas solo con texto: cada línea es un requisito.
                    $requirements = collect(preg_split('/\r\n|\r|\n/', $oferta->requisitos ?? ''))
                        ->map(fn ($r) => trim($r))->filter()->take(8)->values()->all();
                } else {
                    $requirements = $reqRows
                        ->sortByDesc('obligatorio')
                        ->pluck('descripcion')
                        ->filter()
                        ->take(8)
                        ->values()
                        ->all();
                }

                // Descripción corta: primera oración (o los primeros ~120 caracteres).
                $descripcion = trim($oferta->descripcion ?? '');
                $corte = mb_strpos($descripcion, '. ');
                $short = $corte !== false && $corte > 20
                    ? mb_substr($descripcion, 0, $corte + 1)
                    : mb_substr($descripcion, 0, 120) . (mb_strlen($descripcion) > 120 ? '…' : '');

                $benefRows = $oferta->beneficios()->get();
                $beneficios = $benefRows->isNotEmpty()
                    ? $benefRows->pluck('descripcion')->implode(', ')
                    : trim($oferta->beneficios ?? '');

                return [
                    'id' => $oferta->id_oferta,
                    'title' => $oferta->titulo,
                    'company' => $empleador?->nombre_empresa ?? 'Empresa',
                    'companyInitials' => SavedOfferController::initials($empleador?->nombre_empresa ?? ''),
                    'location' => trim(($ubicacion?->direccion ?? '') . ', ' . ($ubicacion?->ciudad ?? 'Encarnación'), ', '),
                    'modality' => $oferta->modalidad?->nombre ?? 'Presencial',
                    'contractType' => $oferta->tipoContrato?->nombre ?? 'Tiempo completo',
                    'shortDescription' => $short,
                    'fullDescription' => $descripcion,
                    'requirements' => $requirements,
                    'salary' => OfferController::remuneracionLabel($oferta),
                    'benefits' => $beneficios,
                    'contact' => trim(($empleador?->usuario?->email ?? '')
                        . (($empleador?->telefono) ? ' | ' . $empleador->telefono : '')),
                    'publishedAt' => (function () use ($oferta) {
                        $texto = \Carbon\Carbon::parse($oferta->fecha_publicacion ?? now())
                            ->locale('es')->diffForHumans();
                        return mb_strtoupper(mb_substr($texto, 0, 1)) . mb_substr($texto, 1);
                    })(),
                    'area' => $areasSlug[$idArea] ?? 'informatica',
                    'salaryMin' => $oferta->remuneracion_min !== null ? (int) $oferta->remuneracion_min : null,
                    'coords' => [(float) $ubicacion->latitud, (float) $ubicacion->longitud],
                    'image' => $imagenesPorArea[$idArea] ?? $imagenesPorArea[1],
                    'logoUrl' => $empleador?->logo ? Storage::url($empleador->logo) : null,
                    'isPrimary' => false,
                ];
            })
            ->values()
            ->all();

        // Focus opcional por deep-link desde entrevistas
        // (?lat=&lng=&zoom=&offer=), con validación de rangos.
        $mapFocus = null;
        $lat = $request->query('lat');
        $lng = $request->query('lng');
        $zoom = $request->query('zoom');
        $offer = $request->query('offer');

        $validLat = is_numeric($lat) && $lat >= -90 && $lat <= 90;
        $validLng = is_numeric($lng) && $lng >= -180 && $lng <= 180;
        $validZoom = is_numeric($zoom) && (int) $zoom >= 1 && (int) $zoom <= 19;
        $offerId = is_numeric($offer) ? (int) $offer : null;

        if ($validLat && $validLng) {
            $mapFocus = [
                'lat' => (float) $lat,
                'lng' => (float) $lng,
                'zoom' => $validZoom ? (int) $zoom : 15,
                'offerId' => $offerId,
            ];
        } elseif ($offerId !== null) {
            $mapFocus = [
                'lat' => null,
                'lng' => null,
                'zoom' => $validZoom ? (int) $zoom : 17,
                'offerId' => $offerId,
            ];
        }

        // RF-08: IDs de ofertas ya guardadas por el candidato actual,
        // para que el botón "Guardar" del modal refleje el estado real.
        $savedOfferIds = [];
        if (auth()->check() && auth()->user()->candidato) {
            $savedOfferIds = \App\Models\OfertaGuardada::query()
                ->where('id_candidato', auth()->user()->candidato->id_candidato)
                ->pluck('id_oferta')
                ->all();
        }

        // Postulaciones activas del candidato (estado != Retirada),
        // para que el botón "Postularme" refleje el estado real.
        $postulatedOfferIds = [];
        if (auth()->check() && auth()->user()->candidato) {
            $postulatedOfferIds = \App\Models\Postulacion::query()
                ->where('id_candidato', auth()->user()->candidato->id_candidato)
                ->where('id_estado_post', '!=', 5)
                ->pluck('id_oferta')
                ->all();
        }

        return view('map.index', compact('offers', 'mapFocus', 'savedOfferIds', 'postulatedOfferIds'));
    }

    /**
     * RF-05: incrementa el contador de visualizaciones cuando un
     * candidato abre el detalle de una oferta en el mapa (AJAX).
     */
    public function vista(int $idOferta): \Illuminate\Http\JsonResponse
    {
        $oferta = Oferta::query()->find($idOferta);

        if (! $oferta || (int) $oferta->id_estado_oferta !== 1) {
            return response()->json(['ok' => false], 404);
        }

        Oferta::query()->where('id_oferta', $idOferta)->increment('visualizaciones');

        return response()->json(['ok' => true]);
    }
}
