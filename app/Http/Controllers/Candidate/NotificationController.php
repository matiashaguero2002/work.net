<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Muestra las notificaciones REALES del usuario logueado
     * (tabla notificacion; agrupadas por fecha como el prototipo).
     */
    public function index(): View
    {
        $usuario = Auth::user();
        $rows = collect();

        if ($usuario) {
            $rows = Notificacion::query()
                ->where('id_usuario', $usuario->id_usuario)
                ->orderByDesc('fecha')
                ->with('tipoNotificacion')
                ->get()
                ->map(fn (Notificacion $n) => $this->presentacion($n))
                ->values();
        }

        $unreadCount = $rows->where('isUnread', true)->count();
        $groups = [
            ['key' => 'hoy', 'label' => 'Hoy'],
            ['key' => 'ayer', 'label' => 'Ayer'],
            ['key' => 'anteriores', 'label' => 'Anteriores'],
        ];

        return view('candidate.notifications', [
            'notifications' => $rows,
            'unreadCount' => $unreadCount,
            'groups' => $groups,
        ]);
    }

    /** Marca una notificación como leída (POST, AJAX). */
    public function markRead(int $idNotificacion): JsonResponse
    {
        $rows = $this->actualizar($idNotificacion, ['leida' => true]);

        if ($rows === 0) {
            return response()->json(['ok' => false, 'message' => 'La notificación no existe.'], 404);
        }

        return response()->json(['ok' => true, 'unreadCount' => $this->countSinLeer()]);
    }

    /** Marca todas como leídas (POST, usada desde form normal: recarga). */
    public function markAllRead(): JsonResponse
    {
        Notificacion::query()
            ->where('id_usuario', Auth::id())
            ->where('leida', false)
            ->update(['leida' => true]);

        return response()->json(['ok' => true, 'unreadCount' => 0]);
    }

    /** Elimina una notificación (DELETE, AJAX). */
    public function destroy(int $idNotificacion): JsonResponse
    {
        $rows = Notificacion::query()
            ->where('id_notificacion', $idNotificacion)
            ->where('id_usuario', Auth::id())
            ->delete();

        if ($rows === 0) {
            return response()->json(['ok' => false, 'message' => 'La notificación no existe.'], 404);
        }

        return response()->json(['ok' => true, 'unreadCount' => $this->countSinLeer()]);
    }

    // ============================================
    // Privados
    // ============================================

    private function actualizar(int $idNotificacion, array $datos): int
    {
        if (! Auth::id()) {
            return 0;
        }

        return Notificacion::query()
            ->where('id_notificacion', $idNotificacion)
            ->where('id_usuario', Auth::id())
            ->update($datos);
    }

    private function countSinLeer(): int
    {
        return Notificacion::query()
            ->where('id_usuario', Auth::id())
            ->where('leida', false)
            ->count();
    }

    /** Fila de BD -> formato de la tarjeta del prototipo. */
    private function presentacion(Notificacion $n): array
    {
        $fecha = Carbon::parse($n->fecha ?? now());
        $hoy = now()->format('Y-m-d');
        $ayer = now()->subDay()->format('Y-m-d');

        $grupo = $fecha->format('Y-m-d') === $hoy
            ? 'hoy'
            : ($fecha->format('Y-m-d') === $ayer ? 'ayer' : 'anteriores');

        // Estilo del icono según el tipo (catálogo tipo_notificacion).
        $tipo = (int) ($n->id_tipo_notif ?? 0);
        [$iconClass, $icon] = match ($tipo) {
            1 => ['application', 'send-fill'],
            2 => ['interview', 'calendar-check-fill'],
            3 => ['success', 'stars'],
            default => ['', 'bell-fill'],
        };

        return [
            'id' => $n->id_notificacion,
            'tipo' => $tipo,
            'iconClass' => $iconClass,
            'icon' => $icon,
            'titleHtml' => '<strong>' . e($n->titulo) . '</strong>',
            'textHtml' => e($n->mensaje),
            'time' => $fecha->locale('es')->diffForHumans(),
            'isUnread' => ! (bool) $n->leida,
            'group' => $grupo,
            'href' => $n->url_destino ?: '#',
        ];
    }
}
