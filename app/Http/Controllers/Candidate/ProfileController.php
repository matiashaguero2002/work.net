<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    public function index()
    {
        // Datos mock del perfil del candidato
        // Copiar EXACTAMENTE del prototipo specs/perfil.html
        $profile = [
            'name' => 'Juan Pérez',
            'initials' => 'JP',
            'headline' => 'Desarrollador Full Stack Laravel',
            'email' => 'juan.perez@email.com',
            'phone' => '+595 985 123 456',
            'location' => 'Encarnación, Paraguay',
            'document' => '4.123.456',
            'birthdate' => '15/03/1998',
            'completion' => 70,
        ];

        $experiences = [
            [
                'id' => 1,
                'position' => 'Desarrollador Full Stack',
                'company' => 'Tech Solutions S.A.',
                'period' => 'Marzo 2023 - Presente',
                'description' => 'Desarrollo de aplicaciones web con Laravel y Vue.js. Mantenimiento de APIs REST y optimización de base de datos.',
            ],
            [
                'id' => 2,
                'position' => 'Desarrollador Backend Junior',
                'company' => 'DataCorp S.A.',
                'period' => 'Enero 2021 - Febrero 2023',
                'description' => 'Desarrollo de APIs con Laravel, integración con servicios externos y testing con PHPUnit.',
            ],
        ];

        $educations = [
            [
                'id' => 1,
                'degree' => 'Licenciatura en Análisis de Sistemas',
                'institution' => 'Universidad Autónoma de Encarnación',
                'period' => '2018 - 2024',
                'description' => 'Formación en ingeniería de software, bases de datos, redes y análisis de sistemas.',
            ],
            [
                'id' => 2,
                'degree' => 'Técnico en Informática',
                'institution' => 'Colegio Técnico Nacional',
                'period' => '2015 - 2017',
                'description' => null,
            ],
        ];

        $skills = ['PHP', 'Laravel', 'MySQL', 'PostgreSQL', 'JavaScript', 'Vue.js', 'Git', 'Docker'];

        $languages = [
            ['name' => 'Español', 'level' => 'Nativo'],
            ['name' => 'Inglés', 'level' => 'Avanzado'],
            ['name' => 'Guaraní', 'level' => 'Intermedio'],
        ];

        $cv = [
            'filename' => 'juan_perez_cv.pdf',
            'uploaded_at' => '15 de septiembre de 2026',
            'size' => '245 KB',
        ];

        $preferences = [
            ['icon' => 'bi-geo-alt-fill', 'label' => 'Ubicación preferida', 'value' => 'Encarnación, Paraguay'],
            ['icon' => 'bi-laptop', 'label' => 'Modalidad', 'value' => 'Híbrido / Remoto'],
            ['icon' => 'bi-clock', 'label' => 'Jornada', 'value' => 'Tiempo completo'],
            ['icon' => 'bi-cash-coin', 'label' => 'Salario esperado', 'value' => 'Gs. 7 - 10M'],
        ];

        return view('candidate.profile', compact(
            'profile',
            'experiences',
            'educations',
            'skills',
            'languages',
            'cv',
            'preferences'
        ));
    }
}
