<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class InterviewController extends Controller
{
    /**
     * Muestra las entrevistas del candidato.
     *
     * SOLO FRONTEND: las entrevistas son datos mock hardcodeados,
     * copiados textualmente del prototipo specs/entrevistas.html
     * (proyecto C:\Users\NB\Desktop\work-net).
     * No hay consultas a base de datos.
     */
    public function index(): View
    {
        $interviews = [
            [
                'id' => 1,
                'day' => '15',
                'month' => 'Oct',
                'year' => '2026',
                'title' => 'Entrevista técnica',
                'status' => 'scheduled',
                'statusLabel' => 'Programada',
                'statusIcon' => 'clock',
                'company' => 'DataCorp S.A.',
                'companyInitials' => 'DC',
                'position' => 'Analista de Sistemas',
                'timeStart' => '10:00 hs',
                'timeEnd' => '11:30 hs',
                'modality' => 'Presencial',
                'modalityIcon' => 'laptop',
                'interviewer' => 'Ing. María Fernández',
                'duration' => '90 min',
                'notes' => 'Traer CV impreso. Se realizará una prueba técnica de análisis y diseño de sistemas.',
                'offerId' => 2,
                'coords' => [-27.3306, -55.8667],
            ],
            [
                'id' => 2,
                'day' => '18',
                'month' => 'Oct',
                'year' => '2026',
                'title' => 'Entrevista RRHH',
                'status' => 'scheduled',
                'statusLabel' => 'Videollamada',
                'statusIcon' => 'camera-video',
                'company' => 'Tech Solutions S.A.',
                'companyInitials' => 'TS',
                'position' => 'Desarrollador Laravel',
                'timeStart' => '15:30 hs',
                'timeEnd' => '16:15 hs',
                'modality' => 'Videollamada',
                'modalityIcon' => 'camera-video',
                'interviewer' => 'Lic. Carlos Ruiz',
                'duration' => '45 min',
                'notes' => 'Se enviará link de Google Meet 30 minutos antes de la entrevista.',
                'offerId' => null,
                'coords' => [-27.3256, -55.8600],
            ],
            [
                'id' => 3,
                'day' => '02',
                'month' => 'Oct',
                'year' => '2026',
                'title' => 'Primera entrevista',
                'status' => 'past',
                'statusLabel' => 'Realizada',
                'statusIcon' => 'check-circle',
                'company' => 'Banco Regional',
                'companyInitials' => 'BR',
                'position' => 'Administrador de Base de Datos',
                'timeStart' => '09:00 hs',
                'timeEnd' => '10:00 hs',
                'modality' => 'Presencial',
                'modalityIcon' => 'laptop',
                'interviewer' => 'Ing. Roberto Giménez',
                'duration' => '60 min',
                'notes' => null,
                'offerId' => 5,
                'coords' => [-27.3350, -55.8700],
            ],
            [
                'id' => 4,
                'day' => '28',
                'month' => 'Sep',
                'year' => '2026',
                'title' => 'Entrevista técnica',
                'status' => 'past',
                'statusLabel' => 'Realizada',
                'statusIcon' => 'check-circle',
                'company' => 'Cloud Solutions',
                'companyInitials' => 'CS',
                'position' => 'Ingeniero DevOps',
                'timeStart' => '14:00 hs',
                'timeEnd' => '15:30 hs',
                'modality' => 'Videollamada',
                'modalityIcon' => 'camera-video',
                'interviewer' => 'Ing. Lucía Benítez',
                'duration' => '90 min',
                'notes' => null,
                'offerId' => 7,
                'coords' => [-27.3220, -55.8650],
            ],
        ];

        $upcomingCount = collect($interviews)->where('status', 'scheduled')->count();
        $pastCount = collect($interviews)->where('status', 'past')->count();
        $cancelledCount = collect($interviews)->where('status', 'cancelled')->count();

        return view('candidate.interviews', compact(
            'interviews',
            'upcomingCount',
            'pastCount',
            'cancelledCount'
        ));
    }
}
