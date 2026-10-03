<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Muestra las notificaciones del candidato.
     *
     * SOLO FRONTEND: las notificaciones son datos mock hardcodeados,
     * copiados textualmente del prototipo specs/notificaciones.html
     * (proyecto C:\Users\NB\Desktop\work-net).
     * No hay consultas a base de datos.
     */
    public function index(): View
    {
        $notifications = [
            [
                'id' => 1,
                'iconClass' => 'interview',
                'icon' => 'calendar-check-fill',
                'titleHtml' => '<strong>Nueva entrevista programada</strong>',
                'textHtml' => 'DataCorp S.A. te convocó a una entrevista técnica el 15 de octubre a las 10:00 hs. Podés ver la ubicación en el mapa.',
                'time' => 'Hace 15 minutos',
                'isUnread' => true,
                'group' => 'hoy',
            ],
            [
                'id' => 2,
                'iconClass' => 'success',
                'icon' => 'check-circle-fill',
                'titleHtml' => '<strong>¡Buenas noticias!</strong>',
                'textHtml' => 'Tu postulación a <strong>Analista de Sistemas</strong> en DataCorp S.A. fue <strong>aceptada</strong>. El equipo de RRHH se pondrá en contacto pronto.',
                'time' => 'Hace 3 horas',
                'isUnread' => true,
                'group' => 'hoy',
            ],
            [
                'id' => 3,
                'iconClass' => 'application',
                'icon' => 'send-fill',
                'titleHtml' => '<strong>Postulación enviada</strong>',
                'textHtml' => 'Te postulaste exitosamente a <strong>Desarrollador Full Stack Laravel</strong> en Concesionaria San Miguel. Tu postulación está siendo revisada.',
                'time' => 'Hace 6 horas',
                'isUnread' => true,
                'group' => 'hoy',
            ],
            [
                'id' => 4,
                'iconClass' => 'warning',
                'icon' => 'eye-fill',
                'titleHtml' => 'Tu postulación está en revisión',
                'textHtml' => '<strong>Tech Solutions S.A.</strong> está revisando tu postulación para el puesto de <strong>Desarrollador Laravel</strong>. Te notificaremos cuando haya novedades.',
                'time' => 'Ayer a las 14:30',
                'isUnread' => false,
                'group' => 'ayer',
            ],
            [
                'id' => 5,
                'iconClass' => '',
                'icon' => 'stars',
                'titleHtml' => '4 nuevas ofertas para vos',
                'textHtml' => 'Encontramos 4 ofertas nuevas que coinciden con tu perfil de desarrollador. Incluyen puestos en Banco Regional, Cloud Solutions y más.',
                'time' => 'Ayer a las 09:15',
                'isUnread' => false,
                'group' => 'ayer',
            ],
            [
                'id' => 6,
                'iconClass' => 'success',
                'icon' => 'check-circle-fill',
                'titleHtml' => 'Perfil actualizado correctamente',
                'textHtml' => 'Tu experiencia laboral fue agregada exitosamente a tu perfil. Completá tu CV para aumentar tus posibilidades.',
                'time' => 'Hace 3 días',
                'isUnread' => false,
                'group' => 'semana',
            ],
            [
                'id' => 7,
                'iconClass' => 'danger',
                'icon' => 'x-circle-fill',
                'titleHtml' => 'Postulación no seleccionada',
                'textHtml' => 'Lamentablemente, tu postulación a <strong>Soporte Técnico IT</strong> en <strong>Tech Help Paraguay</strong> no fue seleccionada. ¡Seguí intentando!',
                'time' => 'Hace 5 días',
                'isUnread' => false,
                'group' => 'semana',
            ],
            [
                'id' => 8,
                'iconClass' => '',
                'icon' => 'briefcase-fill',
                'titleHtml' => 'Nuevo empleador verificado',
                'textHtml' => '<strong>Cloud Solutions</strong> completó su proceso de verificación. Ahora podés ver sus ofertas con mayor confianza.',
                'time' => 'Hace 6 días',
                'isUnread' => false,
                'group' => 'semana',
            ],
        ];

        $unreadCount = collect($notifications)->where('isUnread', true)->count();

        $groups = [
            ['key' => 'hoy', 'label' => 'Hoy'],
            ['key' => 'ayer', 'label' => 'Ayer'],
            ['key' => 'semana', 'label' => 'Esta semana'],
        ];

        return view('candidate.notifications', compact('notifications', 'unreadCount', 'groups'));
    }
}
