<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

/**
 * Moderación de reportes (admin).
 *
 * SOLO FRONTEND: los datos son mock hardcodeados,
 * copiados del prototipo specs/Administrador - Reportes.html.
 * No hay consultas a base de datos.
 */
class ReportController extends Controller
{
    public function index()
    {
        // Stats del listado
        $stats = [
            ['icon' => 'flag-fill', 'modifier' => 'danger', 'value' => '2', 'label' => 'Pendientes'],
            ['icon' => 'clock-history', 'modifier' => 'warning', 'value' => '5', 'label' => 'En revisión'],
            ['icon' => 'check-circle-fill', 'modifier' => 'success', 'value' => '142', 'label' => 'Resueltos'],
            ['icon' => 'archive-fill', 'modifier' => 'purple', 'value' => '23', 'label' => 'Archivados'],
        ];

        // Reportes del listado (mock)
        $reports = [
            [
                'avatar' => 'MG', 'title' => 'Posible estafa en oferta', 'reporter' => 'María González',
                'when' => 'Hace 3 horas', 'severity' => 'high', 'severityLabel' => 'Alta', 'severityIcon' => 'exclamation-triangle-fill',
                'reason' => 'Estafa', 'reasonKey' => 'fraud', 'status' => 'pending', 'statusLabel' => 'Pendiente', 'statusIcon' => 'hourglass-split',
                'description' => 'El empleador pide un pago por "capacitación" antes de la entrevista. Me contactaron por WhatsApp y me pidieron transferir Gs. 200.000 para "reservar el puesto". La oferta parece legítima pero el proceso es sospechoso.',
                'offer' => 'Analista de Sistemas', 'company' => 'DataCorp S.A.', 'code' => '#RPT-000145',
                'actions' => ['review', 'resolve', 'dismiss'],
            ],
            [
                'avatar' => 'CR', 'title' => 'Contenido inapropiado', 'reporter' => 'Carlos Ramírez',
                'when' => 'Hace 6 horas', 'severity' => 'high', 'severityLabel' => 'Alta', 'severityIcon' => 'exclamation-triangle-fill',
                'reason' => 'Contenido inapropiado', 'reasonKey' => 'inappropriate', 'status' => 'pending', 'statusLabel' => 'Pendiente', 'statusIcon' => 'hourglass-split',
                'description' => 'La descripción de la oferta utiliza lenguaje ofensivo y discriminatorio hacia ciertos grupos. Adjunto captura de pantalla como evidencia.',
                'offer' => 'Soporte Técnico IT', 'company' => 'Tech Help Paraguay', 'code' => '#RPT-000146',
                'actions' => ['review', 'resolve', 'dismiss'],
            ],
            [
                'avatar' => 'LB', 'title' => 'Información engañosa', 'reporter' => 'Lucía Benítez',
                'when' => 'Hace 1 día', 'severity' => 'medium', 'severityLabel' => 'Media', 'severityIcon' => 'exclamation-circle-fill',
                'reason' => 'Información engañosa', 'reasonKey' => 'misleading', 'status' => 'review', 'statusLabel' => 'En revisión', 'statusIcon' => 'search',
                'description' => 'El salario publicado no coincide con el que se menciona en la entrevista. Publican Gs. 7-9M pero en la entrevista ofrecen Gs. 5M.',
                'offer' => 'Desarrollador Full Stack Laravel', 'company' => 'Concesionaria San Miguel', 'code' => '#RPT-000144',
                'actions' => ['resolve', 'dismiss'],
            ],
            [
                'avatar' => 'PA', 'title' => 'Spam o duplicado', 'reporter' => 'Pedro Acosta',
                'when' => 'Hace 2 días', 'severity' => 'low', 'severityLabel' => 'Baja', 'severityIcon' => 'info-circle-fill',
                'reason' => 'Spam', 'reasonKey' => 'spam', 'status' => 'review', 'statusLabel' => 'En revisión', 'statusIcon' => 'search',
                'description' => 'Esta oferta es idéntica a otra publicada por la misma empresa hace una semana. Parece ser un duplicado.',
                'offer' => 'Diseñador UX/UI', 'company' => 'Creative Studio', 'code' => '#RPT-000143',
                'actions' => ['resolve', 'dismiss'],
            ],
            [
                'avatar' => 'AM', 'title' => 'Posible estafa en oferta', 'reporter' => 'Ana Martínez',
                'when' => 'Hace 5 días', 'severity' => 'high', 'severityLabel' => 'Alta', 'severityIcon' => 'exclamation-triangle-fill',
                'reason' => 'Estafa', 'reasonKey' => 'fraud', 'status' => 'resolved', 'statusLabel' => 'Resuelto', 'statusIcon' => 'check-circle-fill',
                'description' => 'La empresa pidió datos bancarios antes de la entrevista. Se verificó y la oferta fue eliminada por incumplir las normas.',
                'offer' => 'Asistente Administrativo', 'company' => 'Global Services', 'code' => '#RPT-000142',
                'resolvedBy' => 'Admin General', 'actions' => [],
            ],
            [
                'avatar' => 'DL', 'title' => 'Información engañosa', 'reporter' => 'Diego López',
                'when' => 'Hace 1 semana', 'severity' => 'medium', 'severityLabel' => 'Media', 'severityIcon' => 'exclamation-circle-fill',
                'reason' => 'Información engañosa', 'reasonKey' => 'misleading', 'status' => 'resolved', 'statusLabel' => 'Resuelto', 'statusIcon' => 'check-circle-fill',
                'description' => 'El horario publicado no coincide con el real. Se contactó al empleador y se corrigió la oferta.',
                'offer' => 'Vendedor Senior', 'company' => 'Comercial del Sur', 'code' => '#RPT-000141',
                'resolvedBy' => 'Admin General', 'actions' => [],
            ],
        ];

        return view('admin.reports.index', compact('stats', 'reports'));
    }
}
