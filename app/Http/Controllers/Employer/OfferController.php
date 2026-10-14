<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;

class OfferController extends Controller
{
    public function index()
    {
        // Datos mock de las ofertas del empleador
        // Copiados del prototipo specs/ofertas.html
        $offers = [
            [
                'id' => 1,
                'title' => 'Desarrollador Laravel Senior',
                'logo' => 'DL',
                'status' => 'active',
                'statusLabel' => 'Activa',
                'publishedAt' => 'Publicada el 5 de octubre de 2026',
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
                'publishedAt' => 'Publicada el 28 de septiembre de 2026',
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
                'publishedAt' => 'Pausada el 3 de octubre de 2026',
                'location' => 'Encarnación',
                'modality' => 'Remoto',
                'modalityIcon' => 'bi-house',
                'contractType' => 'Freelance',
                'applicants' => 3,
                'views' => 98,
                'daysLeft' => null,
            ],
            [
                'id' => 4,
                'title' => 'Desarrollador Full Stack',
                'logo' => 'FS',
                'status' => 'active',
                'statusLabel' => 'Activa',
                'publishedAt' => 'Publicada el 1 de octubre de 2026',
                'location' => 'Encarnación',
                'modality' => 'Remoto',
                'modalityIcon' => 'bi-house',
                'contractType' => 'Tiempo completo',
                'applicants' => 5,
                'views' => 134,
                'daysLeft' => 20,
            ],
            [
                'id' => 5,
                'title' => 'Administrador de Base de Datos',
                'logo' => 'BD',
                'status' => 'closed',
                'statusLabel' => 'Cerrada',
                'publishedAt' => 'Cerrada el 30 de septiembre de 2026',
                'location' => 'Encarnación',
                'modality' => 'Presencial',
                'modalityIcon' => 'bi-building',
                'contractType' => 'Tiempo completo',
                'applicants' => 15,
                'views' => 420,
                'daysLeft' => null,
            ],
        ];

        $stats = [
            'total' => 9,
            'active' => 4,
            'paused' => 2,
            'closed' => 3,
        ];

        return view('employer.offers.index', compact('offers', 'stats'));
    }

    public function show($id)
    {
        // Datos mock de una oferta específica
        // Copiados del prototipo specs/oferta-detalles.html
        $offer = [
            'id' => $id,
            'title' => 'Desarrollador Laravel Senior',
            'logo' => 'DL',
            'company' => 'Tech Solutions S.A.',
            'publishedLine' => 'Publicada el 5 de octubre de 2026',
            'status' => 'active',
            'statusLabel' => 'Activa',
            'location' => 'Encarnación',
            'modality' => 'Híbrido',
            'contractType' => 'Tiempo completo',
            'description' => 'Estamos buscando un Desarrollador Laravel Senior para liderar el desarrollo de nuestras aplicaciones web internas. La persona seleccionada será responsable del diseño de arquitectura, la implementación de nuevas funcionalidades, la integración con APIs de terceros, la optimización del rendimiento y la mentoría de desarrolladores junior.',
            'experience' => '5+ años en desarrollo backend con PHP y Laravel. Experiencia comprobable en proyectos de mediana a gran escala.',
            'education' => 'Licenciatura en Análisis de Sistemas, Ingeniería Informática o similar.',
            'requirements' => ['PHP 8.3+', 'Laravel 12', 'PostgreSQL', 'Docker', 'Git', 'APIs REST', 'Testing'],
            'benefits' => [
                ['icon' => 'bi-heart-pulse', 'label' => 'Seguro médico'],
                ['icon' => 'bi-mortarboard', 'label' => 'Capacitaciones'],
                ['icon' => 'bi-clock', 'label' => 'Horario flexible'],
                ['icon' => 'bi-house-door', 'label' => 'Home office'],
            ],
            'address' => 'Av. Irrazábal 1234, Encarnación, Paraguay',
            'coords' => [-27.3306, -55.8667],
            'stats' => [
                ['icon' => 'bi-inbox-fill', 'label' => 'Postulantes', 'value' => 12],
                ['icon' => 'bi-eye-fill', 'label' => 'Vistas', 'value' => 245],
                ['icon' => 'bi-bookmark-fill', 'label' => 'Guardadas', 'value' => 18],
                ['icon' => 'bi-hourglass-split', 'label' => 'Días restantes', 'value' => 15],
            ],
            'applicantsCount' => 12,
        ];

        $recentApplicants = [
            ['initials' => 'JP', 'name' => 'Juan Pérez', 'date' => 'Hace 2 horas', 'match' => 92],
            ['initials' => 'MG', 'name' => 'María González', 'date' => 'Hace 4 horas', 'match' => 85],
            ['initials' => 'CR', 'name' => 'Carlos Ramírez', 'date' => 'Hace 6 horas', 'match' => 78],
        ];

        return view('employer.offers.show', compact('offer', 'recentApplicants'));
    }

    public function create()
    {
        return view('employer.offers.create');
    }

    public function store()
    {
        // Placeholder: sin persistencia todavía (PoC frontend).
        return redirect()->route('employer.offers.index');
    }

    public function edit($id)
    {
        // Datos mock de la oferta a editar (precargan el formulario).
        $offer = [
            'id' => $id,
            'title' => 'Desarrollador Laravel Senior',
            'area' => 'informatica',
            'level' => 'senior',
            'description' => 'Estamos buscando un Desarrollador Laravel Senior para liderar el desarrollo de nuestras aplicaciones web internas.',
            'modality' => 'hibrido',
            'contract' => 'tiempo-completo',
            'schedule' => 'completa',
            'deadline' => '2026-11-05',
            'salaryMin' => '7.000.000',
            'salaryMax' => '10.000.000',
            'experience' => '5+ años en desarrollo backend con PHP y Laravel.',
            'education' => 'Licenciatura en Análisis de Sistemas, Ingeniería Informática o similar.',
            'skills' => 'PHP, Laravel, MySQL, PostgreSQL, Git, Docker, APIs REST',
            'softSkills' => 'Trabajo en equipo, comunicación efectiva, proactividad.',
            'address' => 'Av. Irrazábal 1234, Encarnación',
            'lat' => '-27.3306',
            'lng' => '-55.8667',
            'benefits' => ['seguro', 'capacitacion', 'horario', 'homeoffice'],
        ];

        return view('employer.offers.edit', compact('offer'));
    }

    public function update($id)
    {
        // Placeholder: sin persistencia todavía (PoC frontend).
        return redirect()->route('employer.offers.show', $id);
    }

    public function destroy($id)
    {
        // Placeholder: sin persistencia todavía (PoC frontend).
        return redirect()->route('employer.offers.index');
    }
}
