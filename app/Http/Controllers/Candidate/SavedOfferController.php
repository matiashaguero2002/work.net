<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class SavedOfferController extends Controller
{
    /**
     * Muestra las ofertas guardadas del candidato.
     *
     * SOLO FRONTEND: los datos son mock hardcodeados,
     * copiados textualmente del prototipo specs/guardadas.html
     * (proyecto C:\Users\NB\Desktop\work-net).
     * No hay consultas a base de datos.
     */
    public function index(): View
    {
        // Ofertas guardadas (offerId/coords enlazan con MapController
        // para el deep-link "Mapa": centra y abre la tarjeta).
        $savedOffers = [
            [
                'id' => 1,
                'offerId' => 1,
                'title' => 'Desarrollador Full Stack Laravel',
                'company' => 'Concesionaria San Miguel',
                'companyInitials' => 'SM',
                'location' => 'Encarnación',
                'modality' => 'Presencial',
                'contractType' => 'Tiempo completo',
                'shortDescription' => 'Buscamos desarrollador con experiencia en PHP 8.3 y Laravel 12 para unirse a nuestro equipo de tecnología.',
                'salary' => 'Gs. 7 - 9M',
                'savedAt' => 'Guardada hace 2 días',
                'coords' => [-27.3306, -55.8667],
            ],
            [
                'id' => 7,
                'offerId' => 7,
                'title' => 'Ingeniero DevOps',
                'company' => 'Cloud Solutions',
                'companyInitials' => 'CS',
                'location' => 'Encarnación',
                'modality' => 'Híbrido',
                'contractType' => 'Tiempo completo',
                'shortDescription' => 'Automatización de pipelines CI/CD y gestión de infraestructura en la nube con Docker, Kubernetes y AWS.',
                'salary' => 'Gs. 9 - 12M',
                'savedAt' => 'Guardada hace 4 días',
                'coords' => [-27.3220, -55.8650],
            ],
            [
                'id' => 5,
                'offerId' => 5,
                'title' => 'Administrador de Base de Datos',
                'company' => 'Banco Regional',
                'companyInitials' => 'BR',
                'location' => 'Encarnación',
                'modality' => 'Presencial',
                'contractType' => 'Tiempo completo',
                'shortDescription' => 'DBA con experiencia en PostgreSQL, replicación, backups y tuning para infraestructura crítica.',
                'salary' => 'Gs. 8 - 10M',
                'savedAt' => 'Guardada hace 1 semana',
                'coords' => [-27.3350, -55.8700],
            ],
            [
                'id' => 6,
                'offerId' => 6,
                'title' => 'Desarrollador Mobile Flutter',
                'company' => 'App Factory',
                'companyInitials' => 'AF',
                'location' => 'Encarnación',
                'modality' => 'Remoto',
                'contractType' => 'Tiempo completo',
                'shortDescription' => 'Desarrollo de apps móviles multiplataforma con Flutter, Dart, Firebase y APIs REST.',
                'salary' => 'Gs. 6.5 - 8.5M',
                'savedAt' => 'Guardada hace 2 semanas',
                'coords' => [-27.3280, -55.8800],
            ],
        ];

        return view('candidate.saved', compact('savedOffers'));
    }
}
