@extends('layouts.app')

@section('title', 'Work.net - Reportes (moderación)')

@section('content')
    <div class="wn-bg"><div class="wn-bg-grid"></div></div>

    @include('partials.wn-navbar')

    <main class="wn-main wn-page-admin wn-page-admin-reports">

        <header class="wn-header">
            <div class="wn-header-left">
                <h1>Reportes <span class="wn-admin-badge">Moderación</span></h1>
                <p>Revisá los reportes enviados por los usuarios. Tenés <strong>2 pendientes</strong> de resolución.</p>
            </div>
            <div class="wn-header-actions">
                <a href="{{ route('admin.dashboard') }}" class="btn-wn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al panel
                </a>
                <button type="button" class="btn-wn-primary">
                    <i class="bi bi-download"></i> Exportar CSV
                </button>
            </div>
        </header>

        {{-- STATS --}}
        <section class="wn-stats" aria-label="Resumen de reportes">
            @foreach ($stats as $stat)
                <div class="wn-stat-card">
                    <div class="wn-stat-icon {{ $stat['modifier'] }}"><i class="bi bi-{{ $stat['icon'] }}"></i></div>
                    <div class="wn-stat-info">
                        <div class="wn-stat-value">{{ $stat['value'] }}</div>
                        <div class="wn-stat-label">{{ $stat['label'] }}</div>
                    </div>
                </div>
            @endforeach
        </section>

        {{-- FILTROS --}}
        <section class="wn-filters" aria-label="Filtros de reportes">
            <div class="wn-filter-group wn-filter-search">
                <label class="wn-filter-label" for="filterSearch">Buscar</label>
                <i class="bi bi-search"></i>
                <input type="text" id="filterSearch" class="wn-filter-input" placeholder="Motivo, usuario u oferta...">
            </div>
            <div class="wn-filter-group">
                <label class="wn-filter-label" for="filterSeverity">Severidad</label>
                <select id="filterSeverity" class="wn-filter-select">
                    <option value="">Todas</option>
                    <option value="high">Alta</option>
                    <option value="medium">Media</option>
                    <option value="low">Baja</option>
                </select>
            </div>
            <div class="wn-filter-group">
                <label class="wn-filter-label" for="filterStatus">Estado</label>
                <select id="filterStatus" class="wn-filter-select">
                    <option value="">Todos</option>
                    <option value="pending">Pendientes</option>
                    <option value="review">En revisión</option>
                    <option value="resolved">Resueltos</option>
                </select>
            </div>
            <div class="wn-filter-group">
                <label class="wn-filter-label" for="filterReason">Motivo</label>
                <select id="filterReason" class="wn-filter-select">
                    <option value="">Todos</option>
                    <option value="spam">Spam</option>
                    <option value="fraud">Estafa</option>
                    <option value="inappropriate">Contenido inapropiado</option>
                    <option value="misleading">Información engañosa</option>
                    <option value="other">Otro</option>
                </select>
            </div>
            <button type="button" class="wn-filter-clear" id="filterClear">
                <i class="bi bi-x-lg"></i> Limpiar
            </button>
        </section>

        {{-- INFO + TABS --}}
        <div class="wn-results-info">
            <div class="wn-results-count">
                <strong id="resultsCount">{{ count($reports) }}</strong> reportes encontrados
            </div>
            <div class="wn-tabs" role="tablist" aria-label="Filtros rápidos">
                <button type="button" class="wn-tab active" data-tab="">Todos</button>
                <button type="button" class="wn-tab danger" data-tab="pending">Pendientes (2)</button>
                <button type="button" class="wn-tab" data-tab="review">En revisión (5)</button>
                <button type="button" class="wn-tab" data-tab="resolved">Resueltos (142)</button>
            </div>
        </div>

        {{-- LISTA DE REPORTES --}}
        <section class="wn-reports-list" id="reportsList" aria-label="Listado de reportes">
            @foreach ($reports as $report)
                <article class="wn-report-card severity-{{ $report['severity'] }} {{ $report['status'] === 'resolved' ? 'resolved' : '' }}"
                         data-status="{{ $report['status'] }}" data-severity="{{ $report['severity'] }}"
                         data-reason="{{ $report['reasonKey'] }}" data-name="{{ $report['reporter'] }}"
                         data-offer="{{ $report['offer'] }}">
                    <div class="wn-report-header">
                        <div class="wn-report-header-left">
                            <div class="wn-report-avatar">{{ $report['avatar'] }}</div>
                            <div class="wn-report-info">
                                <h3 class="wn-report-title">{{ $report['title'] }}</h3>
                                <p class="wn-report-subtitle">Reportado por {{ $report['reporter'] }} · {{ $report['when'] }}</p>
                            </div>
                        </div>
                        <div class="wn-report-badges">
                            <span class="wn-report-badge {{ $report['severity'] }}"><i class="bi bi-{{ $report['severityIcon'] }}"></i> {{ $report['severityLabel'] }}</span>
                            <span class="wn-report-badge reason"><i class="bi bi-flag-fill"></i> {{ $report['reason'] }}</span>
                            <span class="wn-report-badge {{ $report['status'] }}">
                                <i class="bi bi-{{ $report['statusIcon'] }}"></i> {{ $report['statusLabel'] }}
                            </span>
                        </div>
                    </div>
                    <div class="wn-report-body">
                        <strong>Descripción:</strong> {{ $report['description'] }}
                    </div>
                    <div class="wn-report-meta">
                        <span class="wn-report-meta-item"><i class="bi bi-briefcase-fill"></i> Oferta: <strong>{{ $report['offer'] }}</strong></span>
                        <span class="wn-report-meta-item"><i class="bi bi-building"></i> Empresa: <strong>{{ $report['company'] }}</strong></span>
                        <span class="wn-report-meta-item"><i class="bi bi-hash"></i> <strong>{{ $report['code'] }}</strong></span>
                        @if (isset($report['resolvedBy']))
                            <span class="wn-report-meta-item"><i class="bi bi-check-circle-fill" style="color: var(--wn-success);"></i> Resuelto por <strong>{{ $report['resolvedBy'] }}</strong></span>
                        @endif
                    </div>
                    <div class="wn-report-actions">
                        @if ($report['status'] === 'resolved')
                            <a href="#" class="wn-report-action secondary">
                                <i class="bi bi-eye"></i> Ver detalle de resolución
                            </a>
                        @else
                            <a href="{{ route('admin.offers.show', 2) }}" class="wn-report-action primary">
                                <i class="bi bi-eye"></i> Ver oferta
                            </a>
                            @if (in_array('review', $report['actions']))
                                <button type="button" class="wn-report-action secondary" data-action="review">
                                    <i class="bi bi-search"></i> Marcar en revisión
                                </button>
                            @endif
                            <button type="button" class="wn-report-action success" data-action="resolve">
                                <i class="bi bi-check-lg"></i> Resolver
                            </button>
                            <button type="button" class="wn-report-action danger" data-action="dismiss">
                                <i class="bi bi-x-lg"></i> Desestimar
                            </button>
                        @endif
                    </div>
                </article>
            @endforeach
        </section>

        {{-- EMPTY STATE --}}
        <div class="wn-empty" id="emptyState" style="display: none;">
            <div class="wn-empty-icon"><i class="bi bi-flag"></i></div>
            <h3>No encontramos reportes</h3>
            <p>Probá ajustar los filtros o el estado seleccionado.</p>
        </div>

        {{-- PAGINACIÓN --}}
        <div class="wn-pagination">
            <div class="wn-pagination-info">
                Mostrando <strong>1-{{ count($reports) }}</strong> de <strong>172</strong> reportes
            </div>
            <div class="wn-pagination-controls">
                <button type="button" class="wn-page-btn" disabled><i class="bi bi-chevron-left"></i></button>
                <button type="button" class="wn-page-btn active">1</button>
                <button type="button" class="wn-page-btn">2</button>
                <button type="button" class="wn-page-btn">3</button>
                <button type="button" class="wn-page-btn">...</button>
                <button type="button" class="wn-page-btn">22</button>
                <button type="button" class="wn-page-btn"><i class="bi bi-chevron-right"></i></button>
            </div>
        </div>

    </main>

    {{-- MODAL ACCIÓN --}}
    <div class="wn-modal-backdrop wn-page-admin" id="actionModal" role="dialog" aria-modal="true" aria-labelledby="actionModalTitle">
        <div class="wn-modal">
            <div class="wn-modal-icon" id="actionModalIcon">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <div>
                <h2 id="actionModalTitle">¿Confirmar acción?</h2>
                <p id="actionModalText">Esta acción se aplicará al reporte seleccionado.</p>
            </div>
            <div class="wn-modal-actions">
                <button type="button" class="btn-wn-secondary" id="actionModalNo">
                    <i class="bi bi-x-lg"></i> Cancelar
                </button>
                <button type="button" class="btn-wn-primary" id="actionModalYes">
                    <i class="bi bi-check-lg"></i> Confirmar
                </button>
            </div>
        </div>
    </div>
@endsection
