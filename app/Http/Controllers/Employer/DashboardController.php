<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;

/**
 * Muestra el dashboard del empleador.
 *
 * SOLO FRONTEND: los datos son mock hardcodeados,
 * copiados textualmente del prototipo specs/dashboard-empleador.html.
 * No hay consultas a base de datos.
 */
class DashboardController extends Controller
{
    public function index()
    {
        // Datos mock del dashboard del empleador (valores según SPEC).
        $stats = [
            ['icon' => 'briefcase-fill', 'modifier' => 'success', 'value' => 4, 'label' => 'Ofertas activas'],
            ['icon' => 'inbox-fill', 'modifier' => '', 'value' => 23, 'label' => 'Postulaciones'],
            ['icon' => 'calendar-check-fill', 'modifier' => 'purple', 'value' => 5, 'label' => 'Entrevistas'],
            ['icon' => 'pause-circle-fill', 'modifier' => 'warning', 'value' => 2, 'label' => 'Pausadas'],
        ];

        $recentOffers = [
            [
                'id' => 1,
                'title' => 'Desarrollador Laravel Senior',
                'logo' => 'DL',
                'status' => 'active',
                'statusLabel' => 'Activa',
                'publishedLine' => 'Publicada hace 3 días',
                'location' => 'Encarnación',
                'modality' => 'Híbrido',
                'modalityIcon' => 'bi-laptop',
                'contractType' => 'Tiempo completo',
                'applicants' => 12,
                'views' => 245,
                'daysLeft' => 15,
            ],
            [
                'id' => 2,
                'title' => 'Analista de Sistemas',
                'logo' => 'AN',
                'status' => 'active',
                'statusLabel' => 'Activa',
                'publishedLine' => 'Publicada hace 1 semana',
                'location' => 'Encarnación',
                'modality' => 'Presencial',
                'modalityIcon' => 'bi-building',
                'contractType' => 'Tiempo completo',
                'applicants' => 8,
                'views' => 182,
                'daysLeft' => 8,
            ],
            [
                'id' => 3,
                'title' => 'Diseñador UX/UI',
                'logo' => 'UX',
                'status' => 'paused',
                'statusLabel' => 'Pausada',
                'publishedLine' => 'Pausada hace 2 días',
                'location' => 'Encarnación',
                'modality' => 'Remoto',
                'modalityIcon' => 'bi-house',
                'contractType' => 'Freelance',
                'applicants' => 3,
                'views' => 98,
                'daysLeft' => null,
            ],
        ];

        $recentApplications = [
            [
                'initials' => 'JP',
                'name' => 'Juan Pérez',
                'positionLine' => 'Desarrollador Laravel Senior · hace 2 horas',
                'match' => 92,
            ],
            [
                'initials' => 'MG',
                'name' => 'María González',
                'positionLine' => 'Desarrollador Laravel Senior · hace 4 horas',
                'match' => 85,
            ],
            [
                'initials' => 'CR',
                'name' => 'Carlos Ramírez',
                'positionLine' => 'Analista de Sistemas · hace 6 horas',
                'match' => 78,
            ],
            [
                'initials' => 'LB',
                'name' => 'Lucía Benítez',
                'positionLine' => 'Analista de Sistemas · hace 1 día',
                'match' => 88,
            ],
        ];

        $companyProgress = [
            'percentage' => 75,
            'checklist' => [
                ['label' => 'Datos de la empresa', 'done' => true],
                ['label' => 'RUC y contacto', 'done' => true],
                ['label' => 'Logo corporativo', 'done' => true],
                ['label' => 'Foto de portada', 'done' => false],
                ['label' => 'Descripción institucional', 'done' => false],
            ],
        ];

        $upcomingInterviews = [
            [
                'day' => '15',
                'month' => 'Oct',
                'title' => 'Juan Pérez - Desarrollador Laravel',
                'time' => '10:00 hs · Presencial',
                'timeIcon' => 'clock',
            ],
            [
                'day' => '16',
                'month' => 'Oct',
                'title' => 'María González - Desarrollador Laravel',
                'time' => '15:30 hs · Videollamada',
                'timeIcon' => 'camera-video',
            ],
        ];

        return view('employer.dashboard', compact(
            'stats',
            'recentOffers',
            'recentApplications',
            'companyProgress',
            'upcomingInterviews'
        ));
    }
}
