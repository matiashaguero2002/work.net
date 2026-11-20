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
        if ($reportadoFiltro === 'sí') {
            $query->whereNotNull('reportes');
        } elseif ($reportadoFiltro === 'no') {
            $query->whereNull('reportes');
        }

        // Estadísticas reales de la BD
        $totalOfertas = $query->count();
        $ofertasActivas = Oferta::where('id_estado_oferta', 1)->count();
        $ofertasReportadas = Oferta::whereHas('reportes', function ($q) {
            $q->where('estado', 'pendiente');
        })->count();
        $ofertasPausadas = Oferta::where('id_estado_oferta', 2)->count();
        $ofertasCerradas = Oferta::where('id_estado_oferta', 3)->count();

        // Cargar relaciones necesarias para las tarjetas
        $ofertas = $query
            ->with(['empleador.usuario', 'areaLaboral', 'modalidad', 'tipoContrato'])
            ->orderBy('fecha_publicacion', 'desc')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            ['icon' => 'briefcase-fill', 'modifier' => '', 'value' => (string) $totalOfertas, 'label' => 'Ofertas totales'],
            ['icon' => 'briefcase-fill', 'modifier' => 'success', 'value' => (string) $ofertasActivas, 'label' => 'Activas'],
            ['icon' => 'flag-fill', 'modifier' => 'danger', 'value' => (string) $ofertasReportadas, 'label' => 'Reportadas'],
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

        return view('admin.offers.index', compact('ofertas', 'stats', 'categoriaDistribution', 'estadosDisponibles', 'categoriasDisponibles'));
    }

    public function show($id): \Illuminate\View\View
    {
        // Traer oferta real de la BD con relaciones
        $oferta = Oferta::with(['empleador.usuario', 'areaLaboral', 'modalidad', 'tipoContrato', 'requisitos', 'beneficios', 'ubicacion'])
            ->findOrFail($id);

        // Métricas reales: postulantes, vistas, reportes
        $postulantes = Postulacion::where('id_oferta', $oferta->id_oferta)
            ->where('id_estado_post', '!=', 5) // excluir retiradas
            ->count();

        $vistas = $oferta->visualizaciones ?? 0;

        $reportes = Reporte::where('id_oferta', $oferta->id_oferta)
            ->where('estado', 'pendiente')
            ->get();

        // Información de la oferta
        $offerInfo = [
            ['label' => 'ID de oferta', 'value' => '#O-'.$oferta->id_oferta],
            ['label' => 'Categoría', 'value' => $oferta->areaLaboral?->nombre ?? 'Sin categoría'],
            ['label' => 'Modalidad', 'value' => $oferta->modalidad?->nombre ?? 'Sin definir'],
            ['label' => 'Tipo de contrato', 'value' => $oferta->tipoContrato?->nombre ?? 'Sin definir'],
            ['label' => 'Salario', 'value' => $oferta->remuneracion_min !== null ? 'Gs. '.number_format($oferta->remuneracion_min, 0, ',', '.').' - '.($oferta->remuneracion_max ? number_format($oferta->remuneracion_max, 0, ',', '.') : 'a convenir') : 'A convenir'],
            ['label' => 'Ubicación', 'value' => $oferta->ubicacion?->direccion.', '.$oferta->ubicacion->ciudad ?? 'Sin ubicación'],
            ['label' => 'Publicada', 'value' => $oferta->fecha_publicacion ? (string) $oferta->fecha_publicacion->locale('es')->diffForHumans() : 'Hace poco'],
            ['label' => 'Última actualización', 'value' => $oferta->fecha_publicacion ? (string) $oferta->fecha_publicacion->locale('es')->diffForHumans() : 'Hace poco'],
        ];

        // Empleador
        $empleador = $oferta->empleador;
        $employerInfo = [
            ['label' => 'Empresa', 'value' => $empleador?->nombre_empresa ?? 'Sin empresa'],
            ['label' => 'Email', 'value' => $empleador?->usuario?->email ?? 'Sin email'],
            ['label' => 'RUC', 'value' => $empleador?->ruc ?? 'Sin RUC'],
            ['label' => 'Ofertas publicadas', 'value' => (string) Oferta::where('id_empleador', $empleador?->id_empleador)->count()],
            ['label' => 'Estado', 'value' => $empleador?->usuario?->estado ?? 'activo', 'style' => in_array($empleador->usuario->estado ?? 'activo', ['activo', null]) ? 'color: var(--wn-success)' : 'color: var(--wn-danger)'],
        ];

        // Últimas postulaciones
        $ultimasPostulaciones = Postulacion::where('id_oferta', $oferta->id_oferta)
            ->where('id_estado_post', '!=', 5)
            ->orderBy('fecha_postulacion', 'desc')
            ->take(3)
            ->get()->map(function ($p) {
                $candidato = DB::table('candidato')->where('id_candidato', $p->id_candidato)->first();
                $nombre = $candidato ? ($candidato->nombre ?: 'Anónimo') : 'Anónimo';
                $tiempo = $p->fecha_postulacion ? (string) $p->fecha_postulacion->locale('es')->diffForHumans() : 'Hace poco';
                $estado = match ($p->id_estado_post) {
                    1 => 'En revisión',
                    2 => 'Aceptada',
                    3 => 'Retirada',
                    4 => 'Rechazada',
                    default => 'Pending',
                };

                return [
                    'logo' => substr($nombre, 0, 2),
                    'title' => $nombre,
                    'meta' => $tiempo,
                    'statusClass' => 'active',
                    'status' => $estado,
                ];
            })
            ->toArray();

        // Reportes recientes de esta oferta
        $reportesRecientes = Reporte::where('id_oferta', $oferta->id_oferta)
            ->orderBy('fecha_creacion', 'desc')
            ->take(3)
            ->get()->map(function ($r) {
                $u = $r->usuario;
                return [
                    'by' => $u ? ($u->nombre ?: 'Anónimo') : 'Anónimo',
                    'when' => $r->fecha_creacion ? (string) $r->fecha_creacion->locale('es')->diffForHumans() : 'Hace poco',
                    'text' => mb_strlen($r->motivo) > 50 ? substr($r->motivo, 0, 50) . '...' : $r->motivo,
                ];
            })
            ->toArray();

        return view('admin.offers.show', compact('oferta', 'postulantes', 'vistas', 'offerInfo', 'employerInfo', 'reportesRecientes', 'ultimasPostulaciones'));
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