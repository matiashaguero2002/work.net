<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Notificaciones del EMPLEADOR con datos REALES de la BD
 * (mismos endpoints AJAX que el candidato: marcar leída,
 * marcar todas y eliminar).
 */
class NotificationController extends Controller
{
    public function index(): View
    {
        $usuario = Auth::user();

        $rows = Notificacion::query()
            ->where('id_usuario', $usuario->id_usuario)
            ->orderByDesc('fecha')
            ->get()
            ->map(fn (Notificacion $n) => $this->presentacion($n))
            ->values();

        $notifications = $rows->all();
        $unreadCount = $rows->where('isUnread', true)->count();
        $totalCount = count($notifications);

        $groups = [
            ['key' => 'hoy', 'label' => 'Hoy'],
            ['key' => 'ayer', 'label' => 'Ayer'],
            ['key' => 'semana', 'label' => 'Esta semana'],
        ];

        return view('employer.notifications.index', compact('notifications', 'unreadCount', 'totalCount', 'groups'));
    }

    /** Marca una notificación como leída (POST, AJAX). */
    public function markRead(int $idNotificacion): JsonResponse
    {
        $rows = Notificacion::query()
            ->where('id_notificacion', $idNotificacion)
            ->where('id_usuario', Auth::id())
            ->update(['leida' => true]);

        if ($rows === 0) {
            return response()->json(['ok' => false, 'message' => 'La notificación no existe.'], 404);
        }

        return response()->json(['ok' => true, 'unreadCount' => $this->countSinLeer()]);
    }

    /** Marca todas como leídas (POST, AJAX). */
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

    /** Contador de sin leer (polling del badge de la barra lateral). */
    public function sinLeer(): JsonResponse
    {
        return response()->json(['ok' => true, 'unread' => $this->countSinLeer()]);
    }

    private function countSinLeer(): int
    {
        return Notificacion::query()
            ->where('id_usuario', Auth::id())
            ->where('leida', false)
            ->count();
    }

    /** Fila de BD -> formato de la tarjeta del prototipo del empleador. */
    private function presentacion(Notificacion $n): array
    {
        $fecha = Carbon::parse($n->fecha ?? now());
        $hoy = now()->format('Y-m-d');
        $ayer = now()->subDay()->format('Y-m-d');

        $grupo = $fecha->format('Y-m-d') === $hoy
            ? 'hoy'
            : ($fecha->format('Y-m-d') === $ayer ? 'ayer' : 'semana');

        // Icono según la referencia real de la notificación.
        $ref = $n->tipo_referencia ?? '';
        $titulo = mb_strtolower($n->titulo ?? '');

        if ($ref === 'entrevista') {
            [$iconClass, $icon] = ['interview', 'calendar-check-fill'];
        } elseif ($ref === 'oferta') {
            // Vencimiento/cierre → advertencia; el resto → oferta.
            if (str_contains($titulo, 'vence') || str_contains($titulo, 'vencer')) {
                [$iconClass, $icon] = ['warning', 'exclamation-triangle-fill'];
            } elseif (str_contains($titulo, 'cerrad')) {
                [$iconClass, $icon] = ['danger', 'x-circle-fill'];
            } else {
                [$iconClass, $icon] = ['success', 'briefcase-fill'];
            }
        } elseif (str_contains($titulo, 'rechazad') || str_contains($titulo, 'cancelad')) {
            [$iconClass, $icon] = ['application', 'person-check-fill'];
        } else {
            [$iconClass, $icon] = ['application', 'person-plus-fill'];
        }

        return [
            'id' => $n->id_notificacion,
            'tipo' => (int) ($n->id_tipo_notif ?? 0),
            'iconClass' => $iconClass,
            'icon' => $icon,
            'titleHtml' => '<strong>' . e($n->titulo) . '</strong>',
            'textHtml' => e($n->mensaje),
            'time' => $fecha->locale('es')->diffForHumans(),
            'isUnread' => ! (bool) $n->leida,
            'group' => $grupo,
            'link' => $n->url_destino ?: route('employer.dashboard'),
        ];
    }
}
