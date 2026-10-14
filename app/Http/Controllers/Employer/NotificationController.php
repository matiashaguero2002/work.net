<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    public function index()
    {
        // Datos mock copiados del prototipo specs/notificaciones_empleado.html
        $notifications = [
            [
                'id' => 1,
                'icon' => 'person-plus-fill',
                'iconClass' => 'application',
                'titleHtml' => '<strong>Nueva postulación recibida</strong>',
                'textHtml' => '<strong>Juan Pérez</strong> se postuló a <strong>Desarrollador Laravel Senior</strong>. Tiene un 92% de match con la oferta.',
                'time' => 'Hace 15 minutos',
                'isUnread' => true,
                'group' => 'hoy',
                'link' => null,
            ],
            [
                'id' => 2,
                'icon' => 'person-plus-fill',
                'iconClass' => 'application',
                'titleHtml' => '<strong>Nueva postulación recibida</strong>',
                'textHtml' => '<strong>María González</strong> se postuló a <strong>Desarrollador Laravel Senior</strong>. Tiene un 85% de match con la oferta.',
                'time' => 'Hace 4 horas',
                'isUnread' => true,
                'group' => 'hoy',
                'link' => null,
            ],
            [
                'id' => 3,
                'icon' => 'exclamation-triangle-fill',
                'iconClass' => 'warning',
                'titleHtml' => '<strong>Oferta por vencer</strong>',
                'textHtml' => 'Tu oferta <strong>Analista de Sistemas</strong> vence en <strong>8 días</strong>. Considerá renovarla o cerrarla.',
                'time' => 'Hace 6 horas',
                'isUnread' => true,
                'group' => 'hoy',
                'link' => null,
            ],
            [
                'id' => 4,
                'icon' => 'patch-check-fill',
                'iconClass' => 'success',
                'titleHtml' => '<strong>¡Tu empresa fue verificada!</strong>',
                'textHtml' => 'Tech Solutions S.A. completó el proceso de verificación. Ahora tus ofertas tienen mayor visibilidad.',
                'time' => 'Hace 8 horas',
                'isUnread' => true,
                'group' => 'hoy',
                'link' => null,
            ],
            [
                'id' => 5,
                'icon' => 'calendar-check-fill',
                'iconClass' => 'interview',
                'titleHtml' => 'Entrevista programada',
                'textHtml' => 'Confirmaste la entrevista con <strong>Carlos Ramírez</strong> para el <strong>15 de octubre a las 10:00 hs</strong>.',
                'time' => 'Ayer a las 16:45',
                'isUnread' => false,
                'group' => 'ayer',
                'link' => null,
            ],
            [
                'id' => 6,
                'icon' => 'person-check-fill',
                'iconClass' => 'application',
                'titleHtml' => 'Postulación actualizada',
                'textHtml' => 'Cambiaste el estado de la postulación de <strong>Lucía Benítez</strong> a <strong>En revisión</strong>.',
                'time' => 'Ayer a las 11:20',
                'isUnread' => false,
                'group' => 'ayer',
                'link' => null,
            ],
            [
                'id' => 7,
                'icon' => 'star-fill',
                'iconClass' => 'success',
                'titleHtml' => 'Nueva valoración recibida',
                'textHtml' => 'Un candidato valoró tu empresa con <strong>5 estrellas</strong>. Tu promedio actual es 4.8.',
                'time' => 'Hace 3 días',
                'isUnread' => false,
                'group' => 'semana',
                'link' => null,
            ],
            [
                'id' => 8,
                'icon' => 'x-circle-fill',
                'iconClass' => 'danger',
                'titleHtml' => 'Oferta cerrada',
                'textHtml' => 'Tu oferta <strong>Administrador de Base de Datos</strong> fue cerrada. Recibió <strong>15 postulaciones</strong> en total.',
                'time' => 'Hace 5 días',
                'isUnread' => false,
                'group' => 'semana',
                'link' => null,
            ],
        ];

        // Enlaces según destino del prototipo (postulantes / detalle / empresa / ofertas)
        $notifications[0]['link'] = route('employer.applicants.index');
        $notifications[1]['link'] = route('employer.applicants.index');
        $notifications[2]['link'] = route('employer.offers.index');
        $notifications[3]['link'] = route('employer.company.profile');
        $notifications[4]['link'] = route('employer.applicants.show', 3);
        $notifications[5]['link'] = route('employer.applicants.index');
        $notifications[6]['link'] = route('employer.company.profile');
        $notifications[7]['link'] = route('employer.offers.show', 5);

        $unreadCount = 4;
        $totalCount = 8;

        $groups = [
            ['key' => 'hoy', 'label' => 'Hoy'],
            ['key' => 'ayer', 'label' => 'Ayer'],
            ['key' => 'semana', 'label' => 'Esta semana'],
        ];

        $tabs = [
            ['label' => 'Todas', 'count' => 8],
            ['label' => 'Sin leer', 'count' => 4],
            ['icon' => 'bi-inbox', 'label' => 'Postulaciones'],
            ['icon' => 'bi-calendar-event', 'label' => 'Entrevistas'],
            ['icon' => 'bi-star', 'label' => 'Ofertas'],
        ];

        return view('employer.notifications.index', compact('notifications', 'unreadCount', 'totalCount', 'groups', 'tabs'));
    }
}
