<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;

class ApplicantController extends Controller
{
    public function index()
    {
        // Datos mock de postulantes
        // Copiados del prototipo specs/postulantes.html
        $applicants = [
            [
                'id' => 1,
                'initials' => 'JP',
                'name' => 'Juan Pérez',
                'offerTitle' => 'Desarrollador Laravel Senior',
                'meta' => [
                    ['icon' => 'bi-geo-alt-fill', 'text' => 'Encarnación'],
                    ['icon' => 'bi-briefcase-fill', 'text' => '5 años de experiencia'],
                    ['icon' => 'bi-mortarboard-fill', 'text' => 'Lic. en Sistemas'],
                    ['icon' => 'bi-clock-fill', 'text' => 'Postuló hace 2 horas'],
                ],
                'skills' => ['PHP', 'Laravel', 'PostgreSQL', 'Docker', 'Git'],
                'status' => 'review',
                'statusLabel' => 'En revisión',
                'statusIcon' => 'bi-eye-fill',
                'match' => 92,
                'lowMatch' => false,
                'dimmed' => false,
                'actions' => ['profile', 'cv'],
            ],
            [
                'id' => 2,
                'initials' => 'MG',
                'name' => 'María González',
                'offerTitle' => 'Desarrollador Laravel Senior',
                'meta' => [
                    ['icon' => 'bi-geo-alt-fill', 'text' => 'Encarnación'],
                    ['icon' => 'bi-briefcase-fill', 'text' => '4 años de experiencia'],
                    ['icon' => 'bi-mortarboard-fill', 'text' => 'Ing. Informática'],
                    ['icon' => 'bi-clock-fill', 'text' => 'Postuló hace 4 horas'],
                ],
                'skills' => ['Laravel', 'Vue.js', 'MySQL', 'Redis'],
                'status' => 'accepted',
                'statusLabel' => 'Aceptada',
                'statusIcon' => 'bi-check-circle-fill',
                'match' => 85,
                'lowMatch' => false,
                'dimmed' => false,
                'actions' => ['profile', 'cv'],
            ],
            [
                'id' => 3,
                'initials' => 'CR',
                'name' => 'Carlos Ramírez',
                'offerTitle' => 'Analista de Sistemas',
                'meta' => [
                    ['icon' => 'bi-geo-alt-fill', 'text' => 'Encarnación'],
                    ['icon' => 'bi-briefcase-fill', 'text' => '3 años de experiencia'],
                    ['icon' => 'bi-mortarboard-fill', 'text' => 'Lic. en Sistemas'],
                    ['icon' => 'bi-clock-fill', 'text' => 'Postuló hace 6 horas'],
                ],
                'skills' => ['Análisis', 'UML', 'SQL', 'Scrum'],
                'status' => 'registered',
                'statusLabel' => 'Registrada',
                'statusIcon' => 'bi-send-fill',
                'match' => 78,
                'lowMatch' => false,
                'dimmed' => false,
                'actions' => ['profile', 'cv'],
            ],
            [
                'id' => 4,
                'initials' => 'LB',
                'name' => 'Lucía Benítez',
                'offerTitle' => 'Analista de Sistemas',
                'meta' => [
                    ['icon' => 'bi-geo-alt-fill', 'text' => 'Encarnación'],
                    ['icon' => 'bi-briefcase-fill', 'text' => '6 años de experiencia'],
                    ['icon' => 'bi-mortarboard-fill', 'text' => 'Ing. Informática'],
                    ['icon' => 'bi-clock-fill', 'text' => 'Postuló hace 1 día'],
                ],
                'skills' => ['Análisis funcional', 'BPMN', 'SQL', 'Jira'],
                'status' => 'review',
                'statusLabel' => 'En revisión',
                'statusIcon' => 'bi-eye-fill',
                'match' => 88,
                'lowMatch' => false,
                'dimmed' => false,
                'actions' => ['profile', 'cv'],
            ],
            [
                'id' => 5,
                'initials' => 'PA',
                'name' => 'Pedro Almirón',
                'offerTitle' => 'Desarrollador Laravel Senior',
                'meta' => [
                    ['icon' => 'bi-geo-alt-fill', 'text' => 'Encarnación'],
                    ['icon' => 'bi-briefcase-fill', 'text' => '2 años de experiencia'],
                    ['icon' => 'bi-clock-fill', 'text' => 'Postuló hace 3 días'],
                ],
                'skills' => ['PHP', 'CodeIgniter'],
                'status' => 'rejected',
                'statusLabel' => 'Rechazada',
                'statusIcon' => 'bi-x-circle-fill',
                'match' => 45,
                'lowMatch' => true,
                'dimmed' => true,
                'actions' => ['profile', 'cv'],
            ],
        ];

        $stats = [
            'total' => 23,
            'registered' => 8,
            'in_review' => 10,
            'accepted' => 3,
            'rejected' => 2,
        ];

        return view('employer.applicants.index', compact('applicants', 'stats'));
    }

    public function show($id)
    {
        // Datos mock del postulante (detalle).
        // Estructura según el prototipo specs/postulaciones_empleado.html
        $applicant = [
            'id' => $id,
            'name' => 'Juan Pérez',
            'initials' => 'JP',
            'position' => 'Desarrollador Laravel Senior',
            'email' => 'juan.perez@email.com',
            'phone' => '+595 985 123 456',
            'location' => 'Encarnación, Paraguay',
            'match' => 92,
            'status' => 'review',
            'statusLabel' => 'En revisión',
            'statusIcon' => 'eye-fill',
            'appliedAt' => 'Hace 2 horas',
            'experience' => '5 años de experiencia en desarrollo backend con PHP y Laravel.',
            'education' => 'Licenciatura en Análisis de Sistemas, Universidad Autónoma de Encarnación.',
            'skills' => ['PHP', 'Laravel', 'PostgreSQL', 'Docker', 'Git'],
            'languages' => [
                ['name' => 'Español', 'level' => 'Nativo'],
                ['name' => 'Inglés', 'level' => 'Avanzado'],
            ],
            'cv' => [
                'filename' => 'juan_perez_cv.pdf',
                'size' => '245 KB',
            ],
        ];

        $statusOptions = [
            ['key' => 'registered', 'label' => 'Registrada', 'icon' => 'send-fill'],
            ['key' => 'review', 'label' => 'En revisión', 'icon' => 'eye-fill'],
            ['key' => 'accepted', 'label' => 'Aceptada', 'icon' => 'check-circle-fill'],
            ['key' => 'rejected', 'label' => 'Rechazada', 'icon' => 'x-circle-fill'],
        ];

        return view('employer.applicants.show', compact('applicant', 'statusOptions'));
    }
}
