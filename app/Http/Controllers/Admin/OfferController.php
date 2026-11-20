<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Oferta;
use App\Models\Usuario;
use App\Models\Postulacion;
use App\Models\Reporte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Supervisión de ofertas (admin).
 * Conectado a la BD PostgreSQL Work_net.
 */
class OfferController extends Controller
{
    public function index(Request $request): \Illuminate\View\View
    {
        // Filtros
        $estadoFiltro = $request->query('estado');
        $categoriaFiltro = $request->query('categoria');
        $reportadoFiltro = $request->query('reportado');

        // Consulta base: ofertas con ubicación (para aparecer en mapa)
        $query = Oferta::query()->whereHas('ubicacion', fn ($q) => $q->whereNotNull('latitud')->whereNotNull('longitud'));

        // Aplicar filtros
        if ($estadoFiltro) {
            $query->where('id_estado_oferta', $estadoFiltro);
        }
        if ($categoriaFiltro) {
            $query->where('id_area', $categoriaFiltro);
        }
        // El filtro "reportado" no se aplica porque la tabla 'reporte' del esquema
        // original no tiene columna 'id_oferta' para vincular reportes con ofertas.
        // (Se omite intencionalmente para evitar error SQL.)

        // Estadísticas reales de la BD
        $totalOfertas = $query->count();
        $ofertasActivas = Oferta::where('id_estado_oferta', 1)->count();
        $reportesPendientes = Reporte::where('estado', 'pendiente')->count();
        $ofertasPausadas = Oferta::where('id_estado_oferta', 2)->count();
        $ofertasCerradas = Oferta::where('id_estado_oferta', 3)->count();

        // Cargar ofertas con relaciones (objeto paginador original)
        $offersRaw = $query
            ->with(['empleador.usuario', 'areaLaboral', 'modalidad', 'tipoContrato'])
            ->orderBy('fecha_publicacion', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Mapear ofertas Eloquent a arreglos para la vista (compatibilidad con sintaxis $offer['...'])
        $offersMapped = $offersRaw->getCollection()->map(function ($oferta) {
            $empleador = $oferta->empleador;
            $area = $oferta->areaLaboral;
            $modalidad = $oferta->modalidad;
            $contrato = $oferta->tipoContrato;
            $ubicacion = $oferta->ubicacion;

            $initials = $empleador ? substr($empleador->nombre_empresa ?? 'US', 0, 2) : 'US';

            // Mapear estados de oferta
            $statusMap = [1 => 'active', 2 => 'paused', 3 => 'closed'];
            $statusLabelMap = [1 => 'Activa', 2 => 'Pausada', 3 => 'Cerrada'];
            $estadoKey = $oferta->id_estado_oferta ?? 1;
            $status = $statusMap[$estadoKey] ?? 'active';
            $statusLabel = $statusLabelMap[$estadoKey] ?? 'Activa';

            // Badges para la tarjeta
            $badges = [];
            if ($ubicacion) {
                $badges[] = ['icon' => 'geo-alt-fill', 'text' => $ubicacion->ciudad ?? 'Encarnación'];
            }
            if ($modalidad) {
                $badges[] = ['icon' => 'laptop', 'text' => $modalidad->nombre ?? 'Presencial'];
            }
            if ($contrato) {
                $badges[] = ['icon' => 'clock', 'text' => $contrato->nombre ?? 'Tiempo completo'];
            }

            // Contar postulaciones activas (no retiradas)
            $applicants = Postulacion::where('id_oferta', $oferta->id_oferta)
                ->where('id_estado_post', '!=', 5)
                ->count();

            $views = $oferta->visualizaciones ?? 0;

            // Acciones disponibles según estado
            $actions = [];
            if ($estadoKey === 1) {
                $actions[] = 'pause';
                $actions[] = 'delete';
            } elseif ($estadoKey === 2) {
                $actions[] = 'reactivate';
                $actions[] = 'delete';
            } elseif ($estadoKey === 3) {
                $actions[] = 'delete';
            }

            // Reportes pendientes de esta oferta (usando conteo directo, sin relación)
// Nota: la tabla 'reporte' no tiene columna 'id_oferta' en el esquema original,
// por lo tanto no se pueden filtrar reportes por oferta.
            $reportesCount = 0;
            $reported = null;

            return [
                'id' => $oferta->id_oferta,
                'initials' => strtoupper($initials),
                'title' => $oferta->titulo ?? 'Sin título',
                'company' => $empleador ? $empleador->nombre_empresa : 'Empresa',
                'status' => $status,
                'statusLabel' => $statusLabel,
                'category' => $area ? strtolower(str_replace(' ', '-', $area->nombre)) : 'general',
                'date' => $oferta->fecha_publicacion ? (string) \Carbon\Carbon::parse($oferta->fecha_publicacion)->format('Y-m-d') : '',
                'badges' => $badges,
                'reported' => $reported,
                'description' => mb_strlen($oferta->descripcion ?? '') > 120
                    ? mb_substr($oferta->descripcion ?? '', 0, 120) . '...'
                    : ($oferta->descripcion ?? ''),
                'applicants' => (string) $applicants,
                'views' => (string) $views,
                'actions' => $actions,
            ];
        })->all();

        // Reconstruir paginador con arreglos mapeados
        $offers = new \Illuminate\Pagination\LengthAwarePaginator(
            $offersMapped,
            $offersRaw->total(),
            $offersRaw->perPage(),
            $offersRaw->currentPage(),
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $stats = [
            ['icon' => 'briefcase-fill', 'modifier' => '', 'value' => (string) $totalOfertas, 'label' => 'Ofertas totales'],
            ['icon' => 'briefcase-fill', 'modifier' => 'success', 'value' => (string) $ofertasActivas, 'label' => 'Activas'],
            ['icon' => 'flag-fill', 'modifier' => 'danger', 'value' => (string) $reportesPendientes, 'label' => 'Reportes pendientes'],
            ['icon' => 'pause-circle-fill', 'modifier' => 'warning', 'value' => (string) $ofertasPausadas, 'label' => 'Pausadas'],
            ['icon' => 'archive-fill', 'modifier' => 'purple', 'value' => (string) $ofertasCerradas, 'label' => 'Cerradas'],
        ];

        // Distribución por categoría (áreas laborales)
        $areas = DB::table('area_laboral')
            ->select('id_area', 'nombre')
            ->get()
            ->mapWithKeys(fn ($a) => [$a->id_area => $a->nombre])
            ->toArray();

        $categoriaDistribution = [];
        foreach ($areas as $id => $nombre) {
            $count = Oferta::where('id_area', $id)->where('id_estado_oferta', 1)->count();
            if ($count > 0) {
                $categoriaDistribution[$nombre] = (string) $count;
            }
        }

        // Roles disponibles para filtro
        $estadosDisponibles = [1 => 'Activa', 2 => 'Pausada', 3 => 'Cerrada'];
        $categoriasDisponibles = $areas;

        return view('admin.offers.index', compact('offers', 'stats', 'categoriaDistribution', 'estadosDisponibles', 'categoriasDisponibles'));
    }

    public function show($id): \Illuminate\View\View
    {
        // Traer oferta real de la BD con relaciones
        $oferta = Oferta::with(['empleador.usuario', 'areaLaboral', 'modalidad', 'tipoContrato', 'requisitos', 'beneficios', 'ubicacion'])
            ->findOrFail($id);

        $empleador = $oferta->empleador;
        $ubicacion = $oferta->ubicacion;

        // Métricas reales
        $postulantes = Postulacion::where('id_oferta', $oferta->id_oferta)
            ->where('id_estado_post', '!=', 5)
            ->count();
        $vistas = $oferta->visualizaciones ?? 0;
        $diasPublicada = $oferta->fecha_publicacion
            ? (int) now()->diffInDays(\Carbon\Carbon::parse($oferta->fecha_publicacion))
            : 0;

        // Reportes reales (tabla 'reporte' no vincula con oferta en esquema original)
        $reports = [];

        // Objeto $offer que espera la vista show.blade.php
        $estadoKey = $oferta->id_estado_oferta ?? 1;
        $statusLabelMap = [1 => 'Activa', 2 => 'Pausada', 3 => 'Cerrada'];
        $statusSlugMap = [1 => 'active', 2 => 'paused', 3 => 'closed'];
        $badgesHero = [
            ['class' => $statusSlugMap[$estadoKey] ?? 'active', 'icon' => 'geo-alt-fill', 'text' => $ubicacion?->ciudad ?? 'Encarnación'],
            ['class' => 'info', 'icon' => 'laptop', 'text' => $oferta->modalidad?->nombre ?? 'Presencial'],
            ['class' => 'info', 'icon' => 'clock', 'text' => $oferta->tipoContrato?->nombre ?? 'Tiempo completo'],
        ];

        $offer = [
            'id' => $oferta->id_oferta,
            'initials' => strtoupper($empleador ? substr($empleador->nombre_empresa ?? 'US', 0, 2) : 'US'),
            'title' => $oferta->titulo ?? 'Sin título',
            'company' => $empleador?->nombre_empresa ?? 'Empresa',
            'published' => $oferta->fecha_publicacion
                ? (string) \Carbon\Carbon::parse($oferta->fecha_publicacion)->locale('es')->diffForHumans()
                : 'Hace poco',
            'reported' => false,
            'heroBadges' => $badgesHero,
            'metrics' => [
                ['value' => (string) $postulantes, 'label' => 'Postulantes'],
                ['value' => (string) $vistas, 'label' => 'Vistas'],
                ['value' => (string) count($reports), 'label' => 'Reportes'],
                ['value' => (string) $diasPublicada, 'label' => 'Días publicada'],
            ],
        ];

        // Descripción en HTML seguro (escapamos y convertimos saltos de línea)
        $descripcionTexto = trim($oferta->descripcion ?? '');
        $requisitosTexto = $oferta->requisitos ?? '';
        $html = '<p>' . nl2br(e($descripcionTexto)) . '</p>';
        if (trim($requisitosTexto) !== '') {
            $html .= '<p><strong>Requisitos:</strong></p><p>' . nl2br(e($requisitosTexto)) . '</p>';
        }
        $description = $html;

        // Información de la oferta
        $offerInfo = [
            ['label' => 'ID de oferta', 'value' => '#O-'.$oferta->id_oferta],
            ['label' => 'Categoría', 'value' => $oferta->areaLaboral?->nombre ?? 'Sin categoría'],
            ['label' => 'Modalidad', 'value' => $oferta->modalidad?->nombre ?? 'Sin definir'],
            ['label' => 'Tipo de contrato', 'value' => $oferta->tipoContrato?->nombre ?? 'Sin definir'],
            ['label' => 'Salario', 'value' => $oferta->remuneracion_min !== null
                ? 'Gs. '.number_format($oferta->remuneracion_min, 0, ',', '.').' - '.($oferta->remuneracion_max ? number_format($oferta->remuneracion_max, 0, ',', '.') : 'a convenir')
                : 'A convenir'],
            ['label' => 'Ubicación', 'value' => $ubicacion
                ? trim(($ubicacion->direccion ?? '') . ', ' . ($ubicacion->ciudad ?? ''), ', ')
                : 'Sin ubicación'],
            ['label' => 'Publicada', 'value' => $oferta->fecha_publicacion
                ? (string) \Carbon\Carbon::parse($oferta->fecha_publicacion)->locale('es')->diffForHumans()
                : 'Hace poco'],
            ['label' => 'Última actualización', 'value' => $oferta->fecha_publicacion
                ? (string) \Carbon\Carbon::parse($oferta->fecha_publicacion)->locale('es')->diffForHumans()
                : 'Hace poco'],
        ];

        // Empleador
        $employerInfo = [
            ['label' => 'Empresa', 'value' => $empleador?->nombre_empresa ?? 'Sin empresa'],
            ['label' => 'Email', 'value' => $empleador?->usuario?->email ?? 'Sin email'],
            ['label' => 'RUC', 'value' => $empleador?->ruc ?? 'Sin RUC'],
            ['label' => 'Ofertas publicadas', 'value' => (string) Oferta::where('id_empleador', $empleador?->id_empleador)->count()],
            ['label' => 'Estado', 'value' => $empleador?->usuario?->estado ?? 'activo', 'style' => in_array($empleador?->usuario?->estado ?? 'activo', ['activo', null]) ? 'color: var(--wn-success)' : 'color: var(--wn-danger)'],
        ];

        return view('admin.offers.show', compact('offer', 'description', 'reports', 'offerInfo', 'employerInfo', 'id'));
    }

    public function pausar($id): \Illuminate\Http\JsonResponse
    {
        $oferta = Oferta::findOrFail($id);

        if ($oferta->id_estado_oferta !== 1) {
            return response()->json(['ok' => false, 'message' => 'Solo ofertas activas se pueden pausar.'], 422);
        }

        $oferta->id_estado_oferta = 2; // Pausada
        $oferta->save();

        return response()->json([
            'ok' => true,
            'message' => 'Oferta pausada correctamente.',
            'nuevoEstado' => 'pausada',
            'icon' => 'pause-circle-fill',
            'label' => 'Pausada',
        ]);
    }

    public function reactivar($id): \Illuminate\Http\JsonResponse
    {
        $oferta = Oferta::findOrFail($id);

        if ($oferta->id_estado_oferta !== 2) {
            return response()->json(['ok' => false, 'message' => 'Solo ofertas pausadas se pueden reactivar.'], 422);
        }

        $oferta->id_estado_oferta = 1; // Activa
        $oferta->save();

        return response()->json([
            'ok' => true,
            'message' => 'Oferta reactivada correctamente.',
            'nuevoEstado' => 'activa',
            'icon' => 'check-circle-fill',
            'label' => 'Activa',
        ]);
    }

    public function cerrar($id): \Illuminate\Http\JsonResponse
    {
        $oferta = Oferta::findOrFail($id);

        if ($oferta->id_estado_oferta !== 1 && $oferta->id_estado_oferta !== 2) {
            return response()->json(['ok' => false, 'message' => 'La oferta ya no está activa.'], 422);
        }

        $oferta->id_estado_oferta = 3; // Cerrada
        $oferta->fecha_cierre = now();
        $oferta->save();

        return response()->json([
            'ok' => true,
            'message' => 'Oferta cerrada correctamente.',
            'nuevoEstado' => 'cerrada',
            'icon' => 'x-circle-fill',
            'label' => 'Cerrada',
        ]);
    }
}