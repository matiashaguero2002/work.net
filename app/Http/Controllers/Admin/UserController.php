<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Gestión de usuarios (admin).
 * Conectado a la BD PostgreSQL Work_net.
 */
class UserController extends Controller
{
    public function index(Request $request): \Illuminate\View\View
    {
        // Filtros
        $rolFiltro = $request->query('rol');
        $estadoFiltro = $request->query('estado');

        // Consulta base
        $query = Usuario::query();

        // Aplicar filtros
        if ($rolFiltro) {
            $query->where('rol', $rolFiltro);
        }
        if ($estadoFiltro) {
            $query->where('estado', $estadoFiltro);
        }

        // Usuarios con paginación (10 por página) - datos reales
        $users = $query->orderBy('fecha_registro', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Stats reales de la BD
        $totalUsuarios = Usuario::count();
        $usuariosActivos = Usuario::where('estado', 'activo')->count();
        $usuariosPendientes = Usuario::where('estado', 'pendiente')->count();
        $usuariosSuspendidos = Usuario::where('estado', 'suspendido')->count();

        $stats = [
            ['icon' => 'people-fill', 'modifier' => '', 'value' => (string) $totalUsuarios, 'label' => 'Total'],
            ['icon' => 'check-circle-fill', 'modifier' => 'success', 'value' => (string) $usuariosActivos, 'label' => 'Activos'],
            ['icon' => 'hourglass-split', 'modifier' => 'warning', 'value' => (string) $usuariosPendientes, 'label' => 'Pendientes'],
            ['icon' => 'x-circle-fill', 'modifier' => 'danger', 'value' => (string) $usuariosSuspendidos, 'label' => 'Suspendidos'],
        ];

        // Distribución por rol (datos reales)
        $candidatos = Usuario::where('rol', 'candidato')->count();
        $empleadores = Usuario::where('rol', 'empleador')->count();
        $administradores = Usuario::where('rol', 'admin')->count();

        $roleDistribution = [
            ['key' => 'candidate', 'icon' => 'person-fill', 'label' => 'Candidatos', 'value' => (string) $candidatos, 'share' => number_format(($candidatos / max($totalUsuarios, 1)) * 100, 1) . ' % del total'],
            ['key' => 'employer', 'icon' => 'building-fill', 'label' => 'Empleadores', 'value' => (string) $empleadores, 'share' => number_format(($empleadores / max($totalUsuarios, 1)) * 100, 1) . ' % del total'],
            ['key' => 'admin', 'icon' => 'shield-lock-fill', 'label' => 'Administradores', 'value' => (string) $administradores, 'share' => number_format(($administradores / max($totalUsuarios, 1)) * 100, 1) . ' % del total'],
        ];

        // Roles disponibles para el filtro
        $rolesDisponibles = Usuario::distinct()->pluck('rol', 'rol')->values()->toArray();

        // Estados disponibles para el filtro
        $estadosDisponibles = ['activo', 'pendiente', 'suspendido'];

        return view('admin.users.index', compact('users', 'stats', 'roleDistribution', 'rolesDisponibles', 'estadosDisponibles'));
    }

    public function show($id): \Illuminate\View\View
    {
        // Traer usuario real de la BD
        $user = Usuario::findOrFail($id);

        // Datos personales
        $personalRows = [
            ['label' => 'Nombre completo', 'value' => $user->nombre ?? 'Sin nombre'],
            ['label' => 'Email', 'value' => $user->email],
            ['label' => 'Teléfono', 'value' => $user->telefono ?? 'No registrado'],
            ['label' => 'Ciudad', 'value' => $user->ciudad ?? 'No registrada'],
            ['label' => 'Fecha de nacimiento', 'value' => $user->fecha_nacimiento ?? 'No registrada'],
            ['label' => 'Fecha de registro', 'value' => $user->fecha_registro ? (string) $user->fecha_registro->locale('es')->diffForHumans() : 'No registrada'],
            ['label' => 'Último acceso', 'value' => $user->ultimo_acceso ? (string) $user->ultimo_acceso->locale('es')->diffForHumans() : 'Nunca'],
        ];

        // Información de cuenta
        $accountRows = [
            ['label' => 'ID de usuario', 'value' => '#' . str_pad($user->id_usuario, 6, '0', STR_PAD_LEFT)],
            ['label' => 'Rol', 'value' => ucfirst($user->rol)],
            ['label' => 'Estado', 'value' => ucfirst($user->estado ?? 'activo'), 'icon' => in_array($user->estado, ['activo', null]) ? 'check-circle-fill' : 'hourglass-split', 'iconStyle' => in_array($user->estado, ['activo', null]) ? 'color: var(--wn-success)' : 'color: var(--wn-danger)'],
            ['label' => 'Email verificado', 'value' => 'Sí'],
            ['label' => 'Perfil completo', 'value' => '60%'],
            ['label' => 'CV cargado', 'value' => 'No'],
            ['label' => 'Verificación de identidad', 'value' => 'No'],
        ];

        // Estadísticas del usuario (postulaciones, guardadas, entrevistas)
        $userStats = DB::table('postulacion')
            ->where('id_candidato', $user->candidato ? $user->candidato->id_candidato : null)
            ->where('id_estado_post', '!=', 5) // excluir retiradas
            ->count();

        $guardadas = DB::table('oferta_guardada')
            ->where('id_candidato', $user->candidato ? $user->candidato->id_candidato : null)
            ->count();

        $entrevistas = DB::table('entrevista')
            ->where('id_postulacion', function ($q) use ($user) {
                $q->select('id_postulacion')
                    ->from('postulacion')
                    ->whereRaw('postulacion.id_candidato = ' . ($user->candidato ? $user->candidato->id_candidato : 0));
            })
            ->count();

        $statsData = [
            ['value' => (string) $userStats, 'label' => 'Postulaciones'],
            ['value' => (string) $guardadas, 'label' => 'Guardadas'],
            ['value' => (string) $entrevistas, 'label' => 'Entrevistas'],
        ];

        // Actividad reciente del usuario (postulaciones)
        $actividad = DB::table('postulacion')
            ->where('id_candidato', $user->candidato ? $user->candidato->id_candidato : null)
            ->where('id_estado_post', '!=', 5)
            ->orderBy('fecha_postulacion', 'desc')
            ->take(5)
            ->get()->map(function ($p) {
                $oferta = DB::table('oferta')->where('id_oferta', $p->id_oferta)->first();
                $empresa = $oferta ? ($oferta->empleador ? $oferta->empleador->nombre_empresa : 'Empresa') : 'Empresa';
                $tiempo = $p->fecha_postulacion ? (string) $p->fecha_postulacion->locale('es')->diffForHumans() : 'Hace poco';

                return [
                    'icon' => 'file-earmark-text-fill',
                    'modifier' => '',
                    'actor' => $empresa,
                    'action' => 'se postuló a',
                    'time' => $tiempo,
                    'ofertaTitulo' => $oferta ? $oferta->titulo : 'Oferta',
                ];
            })
            ->toArray();

        // Últimas postulaciones vistas en el sistema
        $ultimasPostulaciones = DB::table('postulacion as p')
            ->join('oferta as o', 'p.id_oferta', '=', 'o.id_oferta')
            ->join('empleador as e', 'o.id_empleador', '=', 'e.id_empleador')
            ->where('p.id_candidato', $user->candidato ? $user->candidato->id_candidato : null)
            ->where('p.id_estado_post', '!=', 5)
            ->orderBy('p.fecha_postulacion', 'desc')
            ->take(3)
            ->get()->map(function ($p) {
                $oferta = DB::table('oferta')->where('id_oferta', $p->id_oferta)->first();
                $empresa = $oferta ? ($oferta->empleador ? $oferta->empleador->nombre_empresa : 'Empresa') : 'Empresa';
                return [
                    'logo' => $empresa ? substr($empresa, 0, 2) : 'US',
                    'title' => $oferta ? $oferta->titulo : 'Oferta',
                    'meta' => $empresa . ' · ' . ($p->fecha_postulacion ? (string) $p->fecha_postulacion->locale('es')->diffForHumans() : 'Hace poco'),
                    'statusClass' => 'active',
                    'status' => $p->id_estado_post === 1 ? 'En revisión' : ($p->id_estado_post === 2 ? 'Aceptada' : ($p->id_estado_post === 3 ? 'Retirada' : 'Rechazada')),
                ];
            })
            ->toArray();

        return view('admin.users.show', compact('user', 'personalRows', 'accountRows', 'statsData', 'actividad', 'ultimasPostulaciones', 'id'));
    }

    public function suspender($id): \Illuminate\Http\JsonResponse
    {
        $usuario = Usuario::findOrFail($id);

        // No se puede suspender al propio admin ni si ya está suspendido
        if ($usuario->id_usuario === auth()->id() || $usuario->estado === 'suspendido') {
            return response()->json(['ok' => false, 'message' => 'No se puede realizar esta acción.'], 422);
        }

        $usuario->estado = 'suspendido';
        $usuario->save();

        return response()->json([
            'ok' => true,
            'message' => 'Usuario suspendido correctamente.',
            'nuevoEstado' => 'suspendido',
            'icon' => 'hourglass-split',
            'label' => 'Suspendido',
        ]);
    }

    public function reactivar($id): \Illuminate\Http\JsonResponse
    {
        $usuario = Usuario::findOrFail($id);

        // No se puede reactivar si no está suspendido
        if ($usuario->estado !== 'suspendido') {
            return response()->json(['ok' => false, 'message' => 'El usuario ya está activo.'], 422);
        }

        $usuario->estado = 'activo';
        $usuario->save();

        return response()->json([
            'ok' => true,
            'message' => 'Usuario reactivado correctamente.',
            'nuevoEstado' => 'activo',
            'icon' => 'check-circle-fill',
            'label' => 'Activo',
        ]);
    }
}