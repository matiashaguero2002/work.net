<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Muestra el dashboard del candidato.
     *
     * SOLO FRONTEND: los datos son mock hardcodeados,
     * copiados textualmente del prototipo specs/dashboard.html
     * (proyecto C:\Users\NB\Desktop\work-net).
     * No hay consultas a base de datos.
     */
    public function index(): View
    {
        $newOffers = 7;
        $scheduledInterviews = 2;

        $stats = [
            ['icon' => 'file-earmark-text', 'modifier' => '', 'value' => 5, 'label' => 'Postulaciones'],
            ['icon' => 'hourglass-split', 'modifier' => 'warning', 'value' => 3, 'label' => 'En revisión'],
            ['icon' => 'calendar-check', 'modifier' => 'success', 'value' => 2, 'label' => 'Entrevistas'],
            ['icon' => 'bookmark', 'modifier' => 'purple', 'value' => 4, 'label' => 'Guardadas'],
        ];

        // Ofertas recomendadas (offerId/coords enlazan con MapController
        // para el deep-link "Ver en mapa": centra y abre la tarjeta).
        $recommendedOffers = [
            [
                'offerId' => 1,
                'title' => 'Desarrollador Full Stack Laravel',
                'company' => 'Concesionaria San Miguel',
                'companyInitials' => 'SM',
                'location' => 'Encarnación',
                'modality' => 'Presencial',
                'contractType' => 'Tiempo completo',
                'shortDescription' => 'Buscamos desarrollador con experiencia en PHP 8.3 y Laravel 12 para unirse a nuestro equipo.',
                'salary' => 'Gs. 7 - 9M',
                'isNew' => true,
                'coords' => [-27.3306, -55.8667],
            ],
            [
                'offerId' => 2,
                'title' => 'Analista de Sistemas',
                'company' => 'DataCorp S.A.',
                'companyInitials' => 'DC',
                'location' => 'Encarnación',
                'modality' => 'Híbrido',
                'contractType' => 'Tiempo completo',
                'shortDescription' => 'Analista funcional para proyectos de transformación digital con metodologías ágiles.',
                'salary' => 'Gs. 6 - 8M',
                'isNew' => false,
                'coords' => [-27.3256, -55.8600],
            ],
            [
                'offerId' => 5,
                'title' => 'Administrador de Base de Datos',
                'company' => 'Banco Regional',
                'companyInitials' => 'BR',
                'location' => 'Encarnación',
                'modality' => 'Presencial',
                'contractType' => 'Tiempo completo',
                'shortDescription' => 'DBA con experiencia en PostgreSQL, replicación y alta disponibilidad.',
                'salary' => 'Gs. 8 - 10M',
                'isNew' => false,
                'coords' => [-27.3350, -55.8700],
            ],
            [
                'offerId' => 6,
                'title' => 'Desarrollador Mobile Flutter',
                'company' => 'App Factory',
                'companyInitials' => 'AF',
                'location' => 'Encarnación',
                'modality' => 'Remoto',
                'contractType' => 'Tiempo completo',
                'shortDescription' => 'Desarrollo de apps móviles multiplataforma con Flutter y Firebase.',
                'salary' => 'Gs. 6.5 - 8.5M',
                'isNew' => false,
                'coords' => [-27.3280, -55.8800],
            ],
        ];

        $recentApplications = [
            [
                'companyInitials' => 'TS',
                'title' => 'Desarrollador Laravel',
                'companyLine' => 'Tech Solutions S.A. · Postulado hace 2 días',
                'statusClass' => 'review',
                'statusIcon' => 'eye',
                'statusLabel' => 'En revisión',
            ],
            [
                'companyInitials' => 'DC',
                'title' => 'Analista de Sistemas',
                'companyLine' => 'DataCorp S.A. · Postulado hace 5 días',
                'statusClass' => 'accepted',
                'statusIcon' => 'check-circle',
                'statusLabel' => 'Aceptada',
            ],
            [
                'companyInitials' => 'CS',
                'title' => 'Ingeniero DevOps',
                'companyLine' => 'Cloud Solutions · Postulado hace 1 semana',
                'statusClass' => 'registered',
                'statusIcon' => 'send',
                'statusLabel' => 'Registrada',
            ],
            [
                'companyInitials' => 'TH',
                'title' => 'Soporte Técnico IT',
                'companyLine' => 'Tech Help Paraguay · Postulado hace 2 semanas',
                'statusClass' => 'rejected',
                'statusIcon' => 'x-circle',
                'statusLabel' => 'Rechazada',
            ],
        ];

        $profileProgress = [
            'percentage' => 60,
            'checklist' => [
                ['label' => 'Datos personales', 'done' => true],
                ['label' => 'Experiencia laboral', 'done' => true],
                ['label' => 'Formación académica', 'done' => true],
                ['label' => 'Cargar CV en PDF', 'done' => false],
                ['label' => 'Agregar habilidades', 'done' => false],
            ],
        ];

        $upcomingInterviews = [
            [
                'day' => '15',
                'month' => 'Oct',
                'title' => 'Entrevista técnica - DataCorp S.A.',
                'time' => '10:00 hs',
                'timeIcon' => 'clock',
                'place' => 'Av. Irrazábal 1234, Encarnación',
                'placeIcon' => 'geo-alt-fill',
            ],
            [
                'day' => '18',
                'month' => 'Oct',
                'title' => 'Entrevista RRHH - Tech Solutions',
                'time' => '15:30 hs',
                'timeIcon' => 'clock',
                'place' => 'Videollamada',
                'placeIcon' => 'camera-video',
            ],
        ];

        $savedOffers = [
            ['title' => 'Desarrollador Full Stack Laravel', 'company' => 'Concesionaria San Miguel'],
            ['title' => 'Ingeniero DevOps', 'company' => 'Cloud Solutions'],
            ['title' => 'Diseñador UX/UI', 'company' => 'Creative Studio'],
        ];

        return view('candidate.dashboard', compact(
            'newOffers',
            'scheduledInterviews',
            'stats',
            'recommendedOffers',
            'recentApplications',
            'profileProgress',
            'upcomingInterviews',
            'savedOffers'
        ));
    }
}
