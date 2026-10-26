<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

/**
 * Panel principal de administración.
 *
 * SOLO FRONTEND: los datos son mock hardcodeados,
 * copiados del prototipo specs/Administrador - Panel.html.
 * No hay consultas a base de datos.
 */
class DashboardController extends Controller
{
    public function index()
    {
        // Resumen general (stats)
        $stats = [
            ['icon' => 'people-fill', 'modifier' => '', 'value' => '1.248', 'label' => 'Usuarios totales'],
            ['icon' => 'briefcase-fill', 'modifier' => 'success', 'value' => '87', 'label' => 'Ofertas activas'],
            ['icon' => 'flag-fill', 'modifier' => 'warning', 'value' => '2', 'label' => 'Reportes pendientes'],
            ['icon' => 'clock-history', 'modifier' => 'purple', 'value' => '3', 'label' => 'Usuarios pendientes'],
        ];

        // Avisos del encabezado
        $pendingUsers = 3;
        $pendingReports = 2;

        // Distribución por rol
        $roleDistribution = [
            ['key' => 'candidate', 'icon' => 'person-fill', 'label' => 'Candidatos', 'value' => '1.087', 'share' => '87% del total'],
            ['key' => 'employer', 'icon' => 'building-fill', 'label' => 'Empleadores', 'value' => '158', 'share' => '12.7% del total'],
            ['key' => 'admin', 'icon' => 'shield-lock-fill', 'label' => 'Administradores', 'value' => '3', 'share' => '0.3% del total'],
        ];

        // Últimos usuarios registrados
        $recentUsers = [
            ['initials' => 'JP', 'name' => 'Juan Pérez', 'email' => 'juan.perez@email.com', 'role' => 'candidato', 'roleLabel' => 'Candidato', 'status' => 'activo', 'statusLabel' => 'Activo', 'statusIcon' => 'check-circle-fill'],
            ['initials' => 'TS', 'name' => 'Tech Solutions S.A.', 'email' => 'contacto@techsolutions.com', 'role' => 'empleador', 'roleLabel' => 'Empleador', 'status' => 'activo', 'statusLabel' => 'Activo', 'statusIcon' => 'check-circle-fill'],
            ['initials' => 'MG', 'name' => 'María González', 'email' => 'maria.gonzalez@email.com', 'role' => 'candidato', 'roleLabel' => 'Candidato', 'status' => 'pendiente', 'statusLabel' => 'Pendiente', 'statusIcon' => 'hourglass-split'],
            ['initials' => 'DC', 'name' => 'DataCorp S.A.', 'email' => 'rrhh@datacorp.com', 'role' => 'empleador', 'roleLabel' => 'Empleador', 'status' => 'activo', 'statusLabel' => 'Activo', 'statusIcon' => 'check-circle-fill'],
            ['initials' => 'CR', 'name' => 'Carlos Ramírez', 'email' => 'carlos.ramirez@email.com', 'role' => 'candidato', 'roleLabel' => 'Candidato', 'status' => 'suspendido', 'statusLabel' => 'Suspendido', 'statusIcon' => 'x-circle-fill'],
        ];

        // Actividad reciente
        $activity = [
            ['icon' => 'person-plus-fill', 'modifier' => 'success', 'actor' => 'Juan Pérez', 'action' => 'se registró como candidato.', 'time' => 'Hace 5 minutos'],
            ['icon' => 'briefcase-fill', 'modifier' => '', 'actor' => 'Tech Solutions', 'action' => 'publicó una nueva oferta.', 'time' => 'Hace 20 minutos'],
            ['icon' => 'flag-fill', 'modifier' => 'danger', 'actor' => null, 'action' => 'DataCorp S.A.|por contenido inapropiado', 'special' => 'report', 'time' => 'Hace 1 hora'],
            ['icon' => 'person-x-fill', 'modifier' => 'warning', 'actor' => 'Carlos Ramírez', 'action' => 'fue suspendido por incumplir términos.', 'time' => 'Hace 3 horas'],
            ['icon' => 'check-circle-fill', 'modifier' => 'success', 'actor' => 'María González', 'action' => 'fue aprobada como candidata.', 'time' => 'Hace 5 horas'],
        ];

        return view('admin.dashboard', compact('stats', 'pendingUsers', 'pendingReports', 'roleDistribution', 'recentUsers', 'activity'));
    }
}
