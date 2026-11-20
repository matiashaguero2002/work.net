<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

/**
 * Supervisión de ofertas (admin).
 *
 * SOLO FRONTEND: los datos son mock hardcodeados,
 * copiados del prototipo resources/views/prototypes/Administrador - Ofertas.html (listado)
 * y resources/views/prototypes/Administrador - Detalle de ofertas.html (detalle).
 */
class OfferController extends Controller
{
    public function index()
    {
        // Stats del listado
        $stats = [
            ['icon' => 'briefcase-fill', 'modifier' => '', 'value' => '87', 'label' => 'Ofertas activas'],
            ['icon' => 'flag-fill', 'modifier' => 'danger', 'value' => '2', 'label' => 'Reportadas'],
            ['icon' => 'pause-circle-fill', 'modifier' => 'warning', 'value' => '5', 'label' => 'Pausadas'],
            ['icon' => 'archive-fill', 'modifier' => 'purple', 'value' => '134', 'label' => 'Cerradas'],
        ];

        // Ofertas del listado (mock)
        $offers = [
            ['id' => 1, 'initials' => 'DL', 'title' => 'Desarrollador Laravel Senior', 'company' => 'Tech Solutions S.A.', 'status' => 'active', 'statusLabel' => 'Activa', 'category' => 'desarrollo', 'date' => '2026-10-16', 'badges' => [['icon' => 'geo-alt-fill', 'text' => 'Encarnación'], ['icon' => 'laptop', 'text' => 'Híbrido'], ['icon' => 'clock', 'text' => 'Tiempo completo']], 'reported' => null, 'description' => 'Buscamos desarrollador con experiencia en PHP 8.3 y Laravel 12 para unirse a nuestro equipo de desarrollo.', 'applicants' => 12, 'views' => 245, 'actions' => ['pause', 'delete']],
            ['id' => 2, 'initials' => 'AN', 'title' => 'Analista de Sistemas', 'company' => 'DataCorp S.A.', 'status' => 'reported', 'statusLabel' => 'Reportada', 'category' => 'analisis', 'date' => '2026-10-15', 'badges' => [['icon' => 'geo-alt-fill', 'text' => 'Encarnación'], ['icon' => 'building', 'text' => 'Presencial']], 'reported' => ['text' => '3 reportes'], 'description' => 'Analista funcional para proyectos de transformación digital con metodologías ágiles.', 'applicants' => 8, 'views' => 182, 'actions' => ['pause', 'delete']],
            ['id' => 3, 'initials' => 'ST', 'title' => 'Soporte Técnico IT', 'company' => 'Tech Help Paraguay', 'status' => 'reported', 'statusLabel' => 'Reportada', 'category' => 'soporte', 'date' => '2026-10-14', 'badges' => [['icon' => 'geo-alt-fill', 'text' => 'Encarnación'], ['icon' => 'building', 'text' => 'Presencial']], 'reported' => ['text' => '1 reporte'], 'description' => 'Soporte técnico nivel 1 y 2 para clientes corporativos. Requiere disponibilidad para viajar.', 'applicants' => 3, 'views' => 67, 'actions' => ['pause', 'delete']],
            ['id' => 4, 'initials' => 'UX', 'title' => 'Diseñador UX/UI', 'company' => 'Creative Studio', 'status' => 'paused', 'statusLabel' => 'Pausada', 'category' => 'diseno', 'date' => '2026-10-10', 'badges' => [['icon' => 'geo-alt-fill', 'text' => 'Encarnación'], ['icon' => 'house', 'text' => 'Remoto'], ['icon' => 'clock', 'text' => 'Freelance']], 'reported' => null, 'description' => 'Diseñador UX/UI para proyectos de aplicaciones móviles y web. Portfolio requerido.', 'applicants' => 5, 'views' => 98, 'actions' => ['reactivate', 'delete']],
            ['id' => 5, 'initials' => 'DB', 'title' => 'Administrador de Base de Datos', 'company' => 'Banco Regional', 'status' => 'active', 'statusLabel' => 'Activa', 'category' => 'datos', 'date' => '2026-10-12', 'badges' => [['icon' => 'geo-alt-fill', 'text' => 'Encarnación'], ['icon' => 'building', 'text' => 'Presencial'], ['icon' => 'clock', 'text' => 'Tiempo completo']], 'reported' => null, 'description' => 'DBA con experiencia en PostgreSQL, replicación y alta disponibilidad para entorno bancario.', 'applicants' => 7, 'views' => 156, 'actions' => ['pause', 'delete']],
            ['id' => 6, 'initials' => 'AF', 'title' => 'Desarrollador Mobile Flutter', 'company' => 'App Factory', 'status' => 'closed', 'statusLabel' => 'Cerrada', 'category' => 'desarrollo', 'date' => '2026-10-01', 'badges' => [['icon' => 'geo-alt-fill', 'text' => 'Encarnación'], ['icon' => 'house', 'text' => 'Remoto'], ['icon' => 'clock', 'text' => 'Tiempo completo']], 'reported' => null, 'description' => 'Desarrollo de apps móviles multiplataforma con Flutter y Firebase. Proyecto finalizado.', 'applicants' => 22, 'views' => 412, 'actions' => ['delete']],
            ['id' => 7, 'initials' => 'SM', 'title' => 'Desarrollador Full Stack Laravel', 'company' => 'Concesionaria San Miguel', 'status' => 'active', 'statusLabel' => 'Activa', 'category' => 'desarrollo', 'date' => '2026-10-15', 'badges' => [['icon' => 'geo-alt-fill', 'text' => 'Encarnación'], ['icon' => 'building', 'text' => 'Presencial'], ['icon' => 'clock', 'text' => 'Tiempo completo']], 'reported' => null, 'description' => 'Buscamos desarrollador con experiencia en PHP 8.3 y Laravel 12 para unirse a nuestro equipo.', 'applicants' => 15, 'views' => 287, 'actions' => ['pause', 'delete']],
            ['id' => 8, 'initials' => 'SF', 'title' => 'Analista Funcional', 'company' => 'SoftSolutions', 'status' => 'paused', 'statusLabel' => 'Pausada', 'category' => 'analisis', 'date' => '2026-10-08', 'badges' => [['icon' => 'geo-alt-fill', 'text' => 'Encarnación'], ['icon' => 'laptop', 'text' => 'Híbrido'], ['icon' => 'clock', 'text' => 'Tiempo completo']], 'reported' => null, 'description' => 'Analista funcional para proyectos de transformación digital con metodologías ágiles.', 'applicants' => 4, 'views' => 89, 'actions' => ['reactivate', 'delete']],
        ];

        return view('admin.offers.index', compact('stats', 'offers'));
    }

    public function show($id)
    {
        // Oferta mock (Analista de Sistemas, DataCorp; con reportes)
        $offer = [
            'id' => 2,
            'initials' => 'AN',
            'title' => 'Analista de Sistemas',
            'company' => 'DataCorp S.A.',
            'published' => 'Publicada hace 4 días',
            'reported' => true,
            'heroBadges' => [
                ['class' => 'reported', 'icon' => 'flag-fill', 'text' => 'Reportada'],
                ['class' => 'info', 'icon' => 'geo-alt-fill', 'text' => 'Encarnación'],
                ['class' => 'info', 'icon' => 'building', 'text' => 'Presencial'],
                ['class' => 'info', 'icon' => 'clock', 'text' => 'Tiempo completo'],
            ],
            'metrics' => [
                ['value' => '8', 'label' => 'Postulantes'],
                ['value' => '182', 'label' => 'Vistas'],
                ['value' => '3', 'label' => 'Reportes'],
                ['value' => '4', 'label' => 'Días publicada'],
            ],
        ];

        // Descripción (HTML seguro, borde sin escapar en la vista con {!! !!})
        $description = <<<'HTML'
            <p><strong>DataCorp S.A.</strong> busca un <strong>Analista de Sistemas</strong> para unirse a su equipo de transformación digital en Encarnación.</p>
            <p><strong>Responsabilidades:</strong></p>
            <ul>
                <li>Relevamiento y análisis de requerimientos funcionales.</li>
                <li>Documentación de procesos y casos de uso.</li>
                <li>Coordinación con equipos de desarrollo y QA.</li>
                <li>Participación en ceremonias ágiles (Scrum).</li>
            </ul>
            <p><strong>Requisitos:</strong></p>
            <ul>
                <li>Estudios en Análisis de Sistemas, Ingeniería Informática o afines.</li>
                <li>Experiencia mínima de 2 años en roles similares.</li>
                <li>Conocimientos en metodologías ágiles.</li>
                <li>Manejo de herramientas de documentación (Jira, Confluence).</li>
            </ul>
        HTML;

        // Reportes recibidos
        $reports = [
            ['reason' => 'Información engañosa', 'by' => 'Juan Pérez', 'when' => 'Hace 1 hora', 'text' => 'El salario publicado no coincide con el que se menciona en la entrevista.'],
            ['reason' => 'Posible estafa', 'by' => 'María González', 'when' => 'Hace 3 horas', 'text' => 'Me pidieron pagar por un curso de capacitación antes de la entrevista.'],
            ['reason' => 'Contenido inapropiado', 'by' => 'Carlos Ramírez', 'when' => 'Hace 6 horas', 'text' => 'El lenguaje utilizado en la descripción es ofensivo.'],
        ];

        // Información de la oferta
        $offerInfo = [
            ['label' => 'ID de oferta', 'value' => '#OFF-000234'],
            ['label' => 'Categoría', 'value' => 'Análisis'],
            ['label' => 'Modalidad', 'value' => 'Presencial'],
            ['label' => 'Tipo de contrato', 'value' => 'Tiempo completo'],
            ['label' => 'Salario', 'value' => 'Gs. 6 - 8M'],
            ['label' => 'Ubicación', 'value' => 'Encarnación, Paraguay'],
            ['label' => 'Publicada', 'value' => '15/10/2026'],
            ['label' => 'Última actualización', 'value' => '15/10/2026'],
        ];

        // Empleador
        $employerInfo = [
            ['label' => 'Empresa', 'value' => 'DataCorp S.A.'],
            ['label' => 'Email', 'value' => 'rrhh@datacorp.com'],
            ['label' => 'RUC', 'value' => '80012345-6'],
            ['label' => 'Ofertas publicadas', 'value' => '12'],
            ['label' => 'Estado', 'value' => 'Activo', 'style' => 'color: var(--wn-success);'],
        ];

        return view('admin.offers.show', compact('offer', 'description', 'reports', 'offerInfo', 'employerInfo', 'id'));
    }
}
