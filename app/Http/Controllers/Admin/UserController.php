<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

/**
 * Gestión de usuarios (admin).
 *
 * SOLO FRONTEND: los datos son mock hardcodeados,
 * copiados del prototipo resources/views/prototypes/Administrador - Usuarios.html (listado)
 * y resources/views/prototypes/Administrador - Detalle de usuarios.html (detalle).
 */
class UserController extends Controller
{
    public function index()
    {
        // Stats del listado
        $stats = [
            ['icon' => 'people-fill', 'modifier' => '', 'value' => '1.248', 'label' => 'Total'],
            ['icon' => 'check-circle-fill', 'modifier' => 'success', 'value' => '1.198', 'label' => 'Activos'],
            ['icon' => 'hourglass-split', 'modifier' => 'warning', 'value' => '3', 'label' => 'Pendientes'],
            ['icon' => 'x-circle-fill', 'modifier' => 'danger', 'value' => '47', 'label' => 'Suspendidos'],
        ];

        // Usuarios del listado (mock)
        $users = [
            ['id' => 1, 'initials' => 'JP', 'name' => 'Juan Pérez', 'email' => 'juan.perez@email.com', 'role' => 'candidato', 'roleLabel' => 'Candidato', 'status' => 'activo', 'statusIcon' => 'check-circle-fill', 'statusLabel' => 'Activo', 'registered' => 'Hoy', 'registeredDate' => '2026-10-15', 'actions' => ['suspend']],
            ['id' => 2, 'initials' => 'TS', 'name' => 'Tech Solutions S.A.', 'email' => 'contacto@techsolutions.com', 'role' => 'empleador', 'roleLabel' => 'Empleador', 'status' => 'activo', 'statusIcon' => 'check-circle-fill', 'statusLabel' => 'Activo', 'registered' => 'Hace 1 día', 'registeredDate' => '2026-10-14', 'actions' => ['suspend']],
            ['id' => 3, 'initials' => 'MG', 'name' => 'María González', 'email' => 'maria.gonzalez@email.com', 'role' => 'candidato', 'roleLabel' => 'Candidato', 'status' => 'pendiente', 'statusIcon' => 'hourglass-split', 'statusLabel' => 'Pendiente', 'registered' => 'Hoy', 'registeredDate' => '2026-10-15', 'actions' => ['approve', 'reject']],
            ['id' => 4, 'initials' => 'DC', 'name' => 'DataCorp S.A.', 'email' => 'rrhh@datacorp.com', 'role' => 'empleador', 'roleLabel' => 'Empleador', 'status' => 'activo', 'statusIcon' => 'check-circle-fill', 'statusLabel' => 'Activo', 'registered' => 'Hace 5 días', 'registeredDate' => '2026-10-10', 'actions' => ['suspend']],
            ['id' => 5, 'initials' => 'CR', 'name' => 'Carlos Ramírez', 'email' => 'carlos.ramirez@email.com', 'role' => 'candidato', 'roleLabel' => 'Candidato', 'status' => 'suspendido', 'statusIcon' => 'x-circle-fill', 'statusLabel' => 'Suspendido', 'registered' => 'Hace 17 días', 'registeredDate' => '2026-09-28', 'actions' => ['reactivate', 'delete']],
            ['id' => 6, 'initials' => 'LB', 'name' => 'Lucía Benítez', 'email' => 'lucia.benitez@email.com', 'role' => 'candidato', 'roleLabel' => 'Candidato', 'status' => 'activo', 'statusIcon' => 'check-circle-fill', 'statusLabel' => 'Activo', 'registered' => 'Hace 3 días', 'registeredDate' => '2026-10-12', 'actions' => ['suspend']],
            ['id' => 7, 'initials' => 'CS', 'name' => 'Creative Studio', 'email' => 'hola@creativestudio.com', 'role' => 'empleador', 'roleLabel' => 'Empleador', 'status' => 'pendiente', 'statusIcon' => 'hourglass-split', 'statusLabel' => 'Pendiente', 'registered' => 'Hoy', 'registeredDate' => '2026-10-15', 'actions' => ['approve', 'reject']],
            ['id' => 8, 'initials' => 'AD', 'name' => 'Admin General', 'email' => 'admin@work.net', 'role' => 'admin', 'roleLabel' => 'Administrador', 'status' => 'activo', 'statusIcon' => 'check-circle-fill', 'statusLabel' => 'Activo', 'registered' => 'Hace 10 meses', 'registeredDate' => '2025-01-01', 'actions' => []],
        ];

        return view('admin.users.index', compact('stats', 'users'));
    }

    public function show($id)
    {
        // Usuario mock (Juan Pérez, candidato)
        $user = [
            'id' => 1,
            'initials' => 'JP',
            'name' => 'Juan Pérez',
            'email' => 'juan.perez@email.com',
            'role' => 'candidato',
            'roleLabel' => 'Candidato',
            'status' => 'activo',
            'statusIcon' => 'check-circle-fill',
            'statusLabel' => 'Activo',
        ];

        // Resumen de actividad
        $userStats = [
            ['value' => '5', 'label' => 'Postulaciones'],
            ['value' => '4', 'label' => 'Guardadas'],
            ['value' => '2', 'label' => 'Entrevistas'],
            ['value' => '1', 'label' => 'Reportes'],
        ];

        // Datos personales
        $personalRows = [
            ['label' => 'Nombre completo', 'value' => 'Juan Pérez'],
            ['label' => 'Email', 'value' => 'juan.perez@email.com'],
            ['label' => 'Teléfono', 'value' => '+595 985 123 456'],
            ['label' => 'Ciudad', 'value' => 'Encarnación, Paraguay'],
            ['label' => 'Fecha de nacimiento', 'value' => '15/03/1998'],
            ['label' => 'Fecha de registro', 'value' => 'Hoy, 10:32 hs'],
            ['label' => 'Último acceso', 'value' => 'Hoy, 14:05 hs'],
        ];

        // Información de cuenta
        $accountRows = [
            ['label' => 'ID de usuario', 'value' => '#USR-001248'],
            ['label' => 'Rol', 'value' => 'Candidato'],
            ['label' => 'Estado', 'value' => 'Activo'],
            ['label' => 'Email verificado', 'value' => 'Sí', 'icon' => 'check-circle-fill', 'iconStyle' => 'color: var(--wn-success); font-size: 0.8rem;'],
            ['label' => 'Perfil completo', 'value' => '60%'],
            ['label' => 'CV cargado', 'value' => 'No', 'empty' => true],
            ['label' => 'Verificación de identidad', 'value' => 'No', 'empty' => true],
        ];

        // Actividad reciente del usuario
        $activity = [
            ['icon' => 'person-plus-fill', 'modifier' => 'success', 'actor' => 'Cuenta creada', 'action' => 'y email verificado.', 'time' => 'Hoy, 10:32 hs'],
            ['icon' => 'file-earmark-text-fill', 'modifier' => '', 'actor' => null, 'action' => 'Desarrollador Laravel|en Tech Solutions.', 'special' => 'applied', 'time' => 'Hoy, 11:15 hs'],
            ['icon' => 'bookmark-fill', 'modifier' => '', 'actor' => null, 'action' => 'Desarrollador Full Stack|', 'special' => 'saved', 'time' => 'Hoy, 11:45 hs'],
            ['icon' => 'pencil-fill', 'modifier' => 'warning', 'actor' => 'perfil laboral', 'action' => 'actualizó su.', 'time' => 'Hoy, 13:20 hs', 'special' => 'suffix'],
            ['icon' => 'box-arrow-in-right', 'modifier' => '', 'actor' => 'Encarnación', 'action' => 'desde', 'time' => 'Hoy, 14:05 hs', 'special' => 'suffix'],
        ];

        // Últimas postulaciones (mock)
        $applications = [
            ['logo' => 'TS', 'title' => 'Desarrollador Laravel', 'meta' => 'Tech Solutions S.A. · Hace 3 horas', 'statusClass' => 'active', 'status' => 'En revisión'],
            ['logo' => 'DC', 'title' => 'Analista de Sistemas', 'meta' => 'DataCorp S.A. · Hace 5 días', 'statusClass' => 'active', 'status' => 'Aceptada'],
            ['logo' => 'CS', 'title' => 'Ingeniero DevOps', 'meta' => 'Cloud Solutions · Hace 1 semana', 'statusClass' => 'paused', 'status' => 'Registrada'],
        ];

        return view('admin.users.show', compact('user', 'userStats', 'personalRows', 'accountRows', 'activity', 'applications', 'id'));
    }
}
