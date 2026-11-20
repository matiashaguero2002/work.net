<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Oferta;
use App\Models\Reporte;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

/**
 * Panel principal de administración.
 * Conectado a la BD PostgreSQL Work_net.
 */
class DashboardController extends Controller
{
    public function index()
    {
        // Resumen general (stats) - datos reales de la BD
        $usuariosTotales = Usuario::count();
        $ofertasActivas = Oferta::where('id_estado_oferta', 1)->count();
        $reportesPendientes = Reporte::where('estado', 'pendiente')->count();

        // Users con estado 'pendiente' (registrados pero sin activar aún)
        $usuariosPendientes = Usuario::where('estado', 'pendiente')->count();

        $stats = [
            ['icon' => 'people-fill', 'modifier' => '', 'value' => (string) $usuariosTotales, 'label' => 'Usuarios totales'],
            ['icon' => 'briefcase-fill', 'modifier' => 'success', 'value' => (string) $ofertasActivas, 'label' => 'Ofertas activas'],
            ['icon' => 'flag-fill', 'modifier' => 'warning', 'value' => (string) $reportesPendientes, 'label' => 'Reportes pendientes'],
            ['icon' => 'clock-history', 'modifier' => 'purple', 'value' => (string) $usuariosPendientes, 'label' => 'Usuarios pendientes'],
        ];

        // Avisos del encabezado - datos reales
        $pendingUsers = $usuariosPendientes;
        $pendingReports = $reportesPendientes;

        // Distribución por rol - usando datos reales de la BD
        $candidatos = Usuario::where('rol', 'candidato')->count();
        $empleadores = Usuario::where('rol', 'empleador')->count();
        $administradores = Usuario::where('rol', 'admin')->count();
        $usuariosTotal = $usuariosTotales > 0 ? $usuariosTotales : 1; // evitar división por cero

        $roleDistribution = [
            ['key' => 'candidate', 'icon' => 'person-fill', 'label' => 'Candidatos', 'value' => (string) $candidatos, 'share' => number_format(($candidatos / $usuariosTotal) * 100, 1) . ' % del total'],
            ['key' => 'employer', 'icon' => 'building-fill', 'label' => 'Empleadores', 'value' => (string) $empleadores, 'share' => number_format(($empleadores / $usuariosTotal) * 100, 1) . ' % del total'],
            ['key' => 'admin', 'icon' => 'shield-lock-fill', 'label' => 'Administradores', 'value' => (string) $administradores, 'share' => number_format(($administradores / $usuariosTotal) * 100, 1) . ' % del total'],
        ];

        // Últimos usuarios registrados - reales de la BD
        $recentUsers = Usuario::orderBy('fecha_registro', 'desc')
            ->take(5)
            ->get()->map(function ($user) {
                return [
                    'initials' => substr($user->nombre ?? 'US', 0, 2),
                    'name' => $user->nombre ?? 'Sin nombre',
                    'email' => $user->email,
                    'role' => $user->rol,
                    'roleLabel' => ucfirst($user->rol),
                    'status' => $user->estado ?? 'activo',
                    'statusLabel' => ucfirst($user->estado ?? 'activo'),
                    'statusIcon' => in_array($user->estado, ['activo', null]) ? 'check-circle-fill' : 'hourglass-split',
                ];
            })
            ->toArray();

        // Actividad reciente - ofertas publicadas y reportes
        $ofertasRecientes = Oferta::where('id_estado_oferta', 1)
            ->orderBy('fecha_publicacion', 'desc')
            ->take(3)
            ->get()->map(function ($oferta) {
                $empleador = $oferta->empleador;
                return [
                    'icon' => 'briefcase-fill',
                    'modifier' => '',
                    'actor' => $empleador ? $empleador->nombre_empresa : 'Empresa',
                    'action' => 'publicó una nueva oferta',
                    'time' => $oferta->fecha_publicacion ? (string) (is_object($oferta->fecha_publicacion) ? $oferta->fecha_publicacion->locale('es')->diffForHumans() : \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $oferta->fecha_publicacion)->locale('es')->diffForHumans()) : 'Hace poco',
                ];
            });

        $reportesRecientes = Reporte::with('usuario', 'oferta')
            ->orderBy('fecha_creacion', 'desc')
            ->take(2)
            ->get()->map(function ($reporte) {
                $usuario = $reporte->usuario;
                $oferta = $reporte->oferta;
                $actor = $usuario ? ($usuario->nombre ?? 'Anónimo') : 'Anónimo';
                $accion = 'reportó';
                $tiempo = $reporte->fecha_creacion ? (string) \Carbon\Carbon::parse($reporte->fecha_creacion)->locale('es')->diffForHumans() : 'Hace poco';
                $ofertaTitulo = $oferta ? $oferta->titulo : 'Oferta';

                return [
                    'icon' => 'flag-fill',
                    'modifier' => 'danger',
                    'actor' => $actor,
                    'action' => $accion . ' la oferta "' . $ofertaTitulo . '"',
                    'time' => $tiempo,
                ];
            });

        // Merge de actividad reciente (lo más reciente primero)
        $activity = array_merge(
            $ofertasRecientes->toArray(),
            $reportesRecientes->toArray()
        );
        // Ordenar por tiempo (el más reciente primero)
        usort($activity, function ($a, $b) {
            $timeA = strtotime(str_replace('hace ', '', $a['time']));
            $timeB = strtotime(str_replace('hace ', '', $b['time']));
            return $timeB <=> $timeA;
        });

        return view('admin.dashboard', compact('stats', 'pendingUsers', 'pendingReports', 'roleDistribution', 'recentUsers', 'activity'));
    }
}
