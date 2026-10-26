<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;

class InterviewController extends Controller
{
    public function index()
    {
        // Stats del listado
        $stats = [
            ['icon' => 'hourglass-split', 'modifier' => 'warning', 'value' => '3', 'label' => 'Pendientes'],
            ['icon' => 'calendar-day', 'modifier' => '', 'value' => '2', 'label' => 'Hoy'],
            ['icon' => 'calendar-week', 'modifier' => 'purple', 'value' => '5', 'label' => 'Esta semana'],
            ['icon' => 'check-circle-fill', 'modifier' => 'success', 'value' => '12', 'label' => 'Completadas'],
        ];

        // Entrevistas del listado (mock)
        $interviews = [
            ['id' => 1, 'initials' => 'JP', 'name' => 'Juan Pérez', 'position' => 'Desarrollador Laravel Senior', 'status' => 'confirmed', 'statusIcon' => 'check-circle-fill', 'statusLabel' => 'Confirmada', 'day' => '15', 'month' => 'Oct', 'date' => '2026-10-15', 'today' => true, 'time' => '10:00', 'duration' => '1 hora', 'modality' => 'presencial', 'modalityIcon' => 'building', 'modalityLabel' => 'Presencial', 'detailIcon' => 'geo-alt-fill', 'detail' => 'Av. Irrazábal 1234, Encarnación', 'actions' => ['reprogramar', 'cancel']],
            ['id' => 3, 'initials' => 'MG', 'name' => 'María González', 'position' => 'Desarrollador Laravel Senior', 'status' => 'pending', 'statusIcon' => 'hourglass-split', 'statusLabel' => 'Pendiente', 'day' => '15', 'month' => 'Oct', 'date' => '2026-10-15', 'today' => true, 'time' => '15:30', 'duration' => '1 hora', 'modality' => 'videollamada', 'modalityIcon' => 'camera-video-fill', 'modalityLabel' => 'Videollamada', 'detailIcon' => 'link-45deg', 'detail' => 'meet.google.com/abc-defg-hij', 'actions' => ['reprogramar', 'cancel']],
            ['id' => 4, 'initials' => 'CR', 'name' => 'Carlos Ramírez', 'position' => 'Analista de Sistemas', 'status' => 'confirmed', 'statusIcon' => 'check-circle-fill', 'statusLabel' => 'Confirmada', 'day' => '18', 'month' => 'Oct', 'date' => '2026-10-18', 'today' => false, 'time' => '09:00', 'duration' => '45 minutos', 'modality' => 'presencial', 'modalityIcon' => 'building', 'modalityLabel' => 'Presencial', 'detailIcon' => 'geo-alt-fill', 'detail' => 'Av. Irrazábal 1234, Encarnación', 'actions' => ['reprogramar', 'cancel']],
            ['id' => 5, 'initials' => 'LB', 'name' => 'Lucía Benítez', 'position' => 'Analista de Sistemas', 'status' => 'pending', 'statusIcon' => 'hourglass-split', 'statusLabel' => 'Pendiente', 'day' => '20', 'month' => 'Oct', 'date' => '2026-10-20', 'today' => false, 'time' => '14:00', 'duration' => '1 hora', 'modality' => 'videollamada', 'modalityIcon' => 'camera-video-fill', 'modalityLabel' => 'Videollamada', 'detailIcon' => 'link-45deg', 'detail' => 'meet.google.com/xyz-1234-abc', 'actions' => ['reprogramar', 'cancel']],
            ['id' => 6, 'initials' => 'PA', 'name' => 'Pedro Acosta', 'position' => 'Diseñador UX/UI', 'status' => 'completed', 'statusIcon' => 'check-circle-fill', 'statusLabel' => 'Completada', 'day' => '08', 'month' => 'Oct', 'date' => '2026-10-08', 'today' => false, 'time' => '11:00', 'duration' => '1 hora', 'modality' => 'presencial', 'modalityIcon' => 'building', 'modalityLabel' => 'Presencial', 'detailIcon' => 'geo-alt-fill', 'detail' => 'Av. Irrazábal 1234, Encarnación', 'actions' => ['feedback']],
            ['id' => 7, 'initials' => 'SM', 'name' => 'Sofía Méndez', 'position' => 'Desarrollador Laravel Senior', 'status' => 'cancelled', 'statusIcon' => 'x-circle-fill', 'statusLabel' => 'Cancelada', 'day' => '05', 'month' => 'Oct', 'date' => '2026-10-05', 'today' => false, 'time' => '16:00', 'duration' => null, 'modality' => 'videollamada', 'modalityIcon' => 'camera-video-fill', 'modalityLabel' => 'Videollamada', 'detailIcon' => 'info-circle', 'detail' => 'Cancelada por el candidato', 'actions' => ['reprogramar']],
        ];

        // Próximas entrevistas (widget)
        $upcoming = [
            ['day' => '15', 'month' => 'Oct', 'title' => 'Juan Pérez · Desarrollador Laravel', 'icon' => 'clock', 'meta' => '10:00 hs · Presencial'],
            ['day' => '15', 'month' => 'Oct', 'title' => 'María González · Desarrollador Laravel', 'icon' => 'camera-video', 'meta' => '15:30 hs · Videollamada'],
        ];

        // Resumen por modalidad (widget)
        $modalitySummary = [
            ['key' => 'presencial', 'icon' => 'building', 'label' => 'Presencial', 'count' => 8, 'width' => 66],
            ['key' => 'video', 'icon' => 'camera-video', 'label' => 'Videollamada', 'count' => 4, 'width' => 34],
        ];
        $modalityTotal = '12 entrevistas';

        return view('employer.interviews.index', compact('stats', 'interviews', 'upcoming', 'modalitySummary', 'modalityTotal'));
    }

    public function create($applicantId = null)
    {
        $applicant = [
            'id' => $applicantId ?? 1,
            'name' => 'Juan Pérez',
            'initials' => 'JP',
            'position' => 'Desarrollador Laravel Senior',
            'email' => 'juan.perez@email.com',
            'phone' => '+595 985 123 456',
            'match' => 92,
        ];

        return view('employer.interviews.create', compact('applicant'));
    }
}
