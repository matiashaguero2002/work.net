<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    /**
     * Muestra las postulaciones del candidato.
     *
     * SOLO FRONTEND: los datos son mock hardcodeados,
     * copiados textualmente del prototipo specs/postulaciones.html
     * (proyecto C:\Users\NB\Desktop\work-net).
     * No hay consultas a base de datos.
     *
     * NOTA: los iconos se guardan SIN el prefijo "bi-"
     * (el Blade antepone "bi bi-").
     */
    public function index(): View
    {
        $applications = [
            [
                'id' => 1,
                'title' => 'Analista de Sistemas',
                'company' => 'DataCorp S.A.',
                'companyInitials' => 'DC',
                'location' => 'Encarnación, Paraguay',
                'appliedAt' => 'Postulado el 27 de septiembre de 2026',
                'status' => 'accepted',
                'statusLabel' => 'Aceptada',
                'statusIcon' => 'check-circle-fill',
                'statusNote' => '¡Felicitaciones! DataCorp S.A. aceptó tu postulación. Revisá tus entrevistas para más información.',
                'timeline' => [
                    ['label' => 'Registrada', 'state' => 'done', 'icon' => 'check'],
                    ['label' => 'En revisión', 'state' => 'done', 'icon' => 'check'],
                    ['label' => 'Entrevista', 'state' => 'done', 'icon' => 'check'],
                    ['label' => 'Aceptada', 'state' => 'current', 'icon' => 'check'],
                ],
                'actions' => [
                    ['kind' => 'primary', 'tag' => 'a', 'icon' => 'eye', 'label' => 'Ver oferta', 'href' => '#'],
                    ['kind' => 'secondary', 'tag' => 'a', 'icon' => 'calendar-event', 'label' => 'Ver entrevista', 'href' => 'interviews'],
                    ['kind' => 'secondary', 'tag' => 'a', 'icon' => 'building', 'label' => 'Ver empresa', 'href' => '#'],
                ],
            ],
            [
                'id' => 2,
                'title' => 'Desarrollador Laravel',
                'company' => 'Tech Solutions S.A.',
                'companyInitials' => 'TS',
                'location' => 'Encarnación, Paraguay',
                'appliedAt' => 'Postulado el 30 de septiembre de 2026',
                'status' => 'review',
                'statusLabel' => 'En revisión',
                'statusIcon' => 'hourglass-split',
                'statusNote' => 'Tech Solutions S.A. está revisando tu postulación. Te notificaremos cuando haya novedades.',
                'timeline' => [
                    ['label' => 'Registrada', 'state' => 'done', 'icon' => 'check'],
                    ['label' => 'En revisión', 'state' => 'current', 'icon' => 'hourglass-split'],
                    ['label' => 'Entrevista', 'state' => '', 'icon' => 'circle'],
                    ['label' => 'Resultado', 'state' => '', 'icon' => 'circle'],
                ],
                'actions' => [
                    ['kind' => 'primary', 'tag' => 'a', 'icon' => 'eye', 'label' => 'Ver oferta', 'href' => '#'],
                    ['kind' => 'secondary', 'tag' => 'a', 'icon' => 'building', 'label' => 'Ver empresa', 'href' => '#'],
                    ['kind' => 'danger', 'tag' => 'button', 'icon' => 'x-circle', 'label' => 'Retirar', 'href' => null],
                ],
            ],
            [
                'id' => 3,
                'title' => 'Ingeniero DevOps',
                'company' => 'Cloud Solutions',
                'companyInitials' => 'CS',
                'location' => 'Encarnación, Paraguay',
                'appliedAt' => 'Postulado el 25 de septiembre de 2026',
                'status' => 'review',
                'statusLabel' => 'En revisión',
                'statusIcon' => 'hourglass-split',
                'statusNote' => 'Cloud Solutions está revisando tu postulación. Te notificaremos cuando haya novedades.',
                'timeline' => [
                    ['label' => 'Registrada', 'state' => 'done', 'icon' => 'check'],
                    ['label' => 'En revisión', 'state' => 'current', 'icon' => 'hourglass-split'],
                    ['label' => 'Entrevista', 'state' => '', 'icon' => 'circle'],
                    ['label' => 'Resultado', 'state' => '', 'icon' => 'circle'],
                ],
                'actions' => [
                    ['kind' => 'primary', 'tag' => 'a', 'icon' => 'eye', 'label' => 'Ver oferta', 'href' => '#'],
                    ['kind' => 'secondary', 'tag' => 'a', 'icon' => 'building', 'label' => 'Ver empresa', 'href' => '#'],
                    ['kind' => 'danger', 'tag' => 'button', 'icon' => 'x-circle', 'label' => 'Retirar', 'href' => null],
                ],
            ],
            [
                'id' => 4,
                'title' => 'Desarrollador Full Stack Laravel',
                'company' => 'Concesionaria San Miguel',
                'companyInitials' => 'SM',
                'location' => 'Encarnación, Paraguay',
                'appliedAt' => 'Postulado hace 2 horas',
                'status' => 'registered',
                'statusLabel' => 'Registrada',
                'statusIcon' => 'send',
                'statusNote' => 'Tu postulación fue registrada correctamente. Pronto comenzará la revisión.',
                'timeline' => [
                    ['label' => 'Registrada', 'state' => 'current', 'icon' => 'send'],
                    ['label' => 'En revisión', 'state' => '', 'icon' => 'circle'],
                    ['label' => 'Entrevista', 'state' => '', 'icon' => 'circle'],
                    ['label' => 'Resultado', 'state' => '', 'icon' => 'circle'],
                ],
                'actions' => [
                    ['kind' => 'primary', 'tag' => 'a', 'icon' => 'eye', 'label' => 'Ver oferta', 'href' => '#'],
                    ['kind' => 'secondary', 'tag' => 'a', 'icon' => 'building', 'label' => 'Ver empresa', 'href' => '#'],
                    ['kind' => 'danger', 'tag' => 'button', 'icon' => 'x-circle', 'label' => 'Retirar', 'href' => null],
                ],
            ],
            [
                'id' => 5,
                'title' => 'Soporte Técnico IT',
                'company' => 'Tech Help Paraguay',
                'companyInitials' => 'TH',
                'location' => 'Encarnación, Paraguay',
                'appliedAt' => 'Postulado el 18 de septiembre de 2026',
                'status' => 'rejected',
                'statusLabel' => 'Rechazada',
                'statusIcon' => 'x-circle-fill',
                'statusNote' => 'Lamentablemente no fuiste seleccionado. ¡Seguí intentando con otras ofertas!',
                'timeline' => [
                    ['label' => 'Registrada', 'state' => 'done', 'icon' => 'check'],
                    ['label' => 'En revisión', 'state' => 'done', 'icon' => 'check'],
                    ['label' => 'Rechazada', 'state' => 'rejected', 'icon' => 'x'],
                ],
                'actions' => [
                    ['kind' => 'primary', 'tag' => 'a', 'icon' => 'eye', 'label' => 'Ver oferta', 'href' => '#'],
                    ['kind' => 'secondary', 'tag' => 'a', 'icon' => 'building', 'label' => 'Ver empresa', 'href' => '#'],
                ],
            ],
        ];

        // Conteo textual del prototipo (tabs del header).
        $stats = [
            'total' => 5,
            'registered' => 1,
            'in_review' => 3,
            'accepted' => 1,
            'rejected' => 1,
        ];

        return view('candidate.applications', compact('applications', 'stats'));
    }
}
