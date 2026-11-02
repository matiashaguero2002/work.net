@extends('layouts.app')

@section('title', 'Work.net - Entrevistas (empleador)')

@section('content')
    <div class="wn-bg"><div class="wn-bg-grid"></div></div>

    @include('partials.wn-navbar')

    <main class="wn-main wn-page-employer wn-page-interviews">

        {{-- HEADER --}}
        <header class="wn-header">
            <div class="wn-header-left">
                <h1>Entrevistas</h1>
                <p>Tenés <strong>2 entrevistas hoy</strong> y <strong>5 programadas</strong> esta semana.</p>
            </div>
            <div class="wn-header-actions">
                <a href="{{ route('employer.applicants.index') }}" class="btn-wn-secondary">
                    <i class="bi bi-inbox"></i> Ver postulantes
                </a>
                <a href="{{ route('employer.interviews.create') }}" class="btn-wn-primary">
                    <i class="bi bi-plus-lg"></i> Convocar entrevista
                </a>
            </div>
        </header>

        {{-- STATS --}}
        <section class="wn-stats" aria-label="Resumen de entrevistas">
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
        <section class="wn-filters" aria-label="Filtros de entrevistas">
            <div class="wn-filter-group wn-filter-search">
                <label class="wn-filter-label" for="filterSearch">Buscar</label>
                <i class="bi bi-search"></i>
                <input type="text" id="filterSearch" class="wn-filter-input" placeholder="Candidato u oferta...">
            </div>
            <div class="wn-filter-group">
                <label class="wn-filter-label" for="filterStatus">Estado</label>
                <select id="filterStatus" class="wn-filter-select">
                    <option value="">Todos</option>
                    <option value="pending">Pendientes</option>
                    <option value="confirmed">Confirmadas</option>
                    <option value="completed">Completadas</option>
                    <option value="cancelled">Canceladas</option>
                </select>
            </div>
            <div class="wn-filter-group">
                <label class="wn-filter-label" for="filterModality">Modalidad</label>
                <select id="filterModality" class="wn-filter-select">
                    <option value="">Todas</option>
                    <option value="presencial">Presencial</option>
                    <option value="videollamada">Videollamada</option>
                </select>
            </div>
            <div class="wn-filter-group">
                <label class="wn-filter-label" for="filterDate">Fecha</label>
                <select id="filterDate" class="wn-filter-select">
                    <option value="">Todas</option>
                    <option value="today">Hoy</option>
                    <option value="week">Esta semana</option>
                    <option value="month">Este mes</option>
                </select>
            </div>
            <button type="button" class="wn-filter-clear" id="filterClear">
                <i class="bi bi-x-lg"></i> Limpiar
            </button>
        </section>

        {{-- INFO RESULTADOS --}}
        <div class="wn-results-info">
            <div class="wn-results-count">
                <strong id="resultsCount">{{ count($interviews) }}</strong> entrevistas encontradas
            </div>
            <div class="wn-results-sort">
                <label for="sortBy">Ordenar por:</label>
                <select id="sortBy">
                    <option value="date-asc">Fecha (más próximas)</option>
                    <option value="date-desc">Fecha (más lejanas)</option>
                    <option value="candidate">Candidato (A-Z)</option>
                </select>
            </div>
        </div>

        {{-- LISTA DE ENTREVISTAS --}}
        <section class="wn-interviews-list" id="interviewsList" aria-label="Listado de entrevistas">
            @foreach ($interviews as $interview)
                <article class="wn-interview-card status-{{ $interview['status'] }}"
                         data-status="{{ $interview['status'] }}" data-modality="{{ $interview['modality'] }}"
                         data-date="{{ $interview['date'] ?? '' }}" data-name="{{ $interview['name'] }}">
                    <div class="wn-interview-date-block {{ $interview['today'] ? 'today' : '' }}">
                        <span class="day">{{ $interview['day'] }}</span>
                        <span class="month">{{ $interview['month'] }}</span>
                    </div>
                    <div class="wn-interview-main">
                        <div class="wn-interview-top">
                            <div class="wn-interview-candidate">
                                <div class="wn-interview-avatar">{{ $interview['initials'] }}</div>
                                <div>
                                    <p class="wn-interview-name">{{ $interview['name'] }}</p>
                                    <p class="wn-interview-position">{{ $interview['position'] }}</p>
                                </div>
                            </div>
                            <span class="wn-interview-status wn-status-{{ $interview['status'] }}">
                                <i class="bi bi-{{ $interview['statusIcon'] }}"></i> {{ $interview['statusLabel'] }}
                            </span>
                        </div>
                        <div class="wn-interview-meta">
                            <span class="wn-interview-meta-item">
                                <i class="bi bi-clock-fill"></i>
                                <strong>{{ $interview['time'] }}</strong> hs @if($interview['duration'])· {{ $interview['duration'] }}@endif
                            </span>
                            <span class="wn-interview-meta-item">
                                <i class="bi bi-{{ $interview['modalityIcon'] }}"></i> {{ $interview['modalityLabel'] }}
                            </span>
                            <span class="wn-interview-meta-item">
                                <i class="bi bi-{{ $interview['detailIcon'] }}"></i> {{ $interview['detail'] }}
                            </span>
                        </div>
                    </div>
                    <div class="wn-interview-actions">
                        <a href="{{ route('employer.applicants.show', $interview['id']) }}" class="wn-action-btn primary">
                            <i class="bi bi-person-lines-fill"></i> Ver candidato
                        </a>
                        @if ($interview['status'] === 'completed')
                            <button type="button" class="wn-action-btn secondary">
                                <i class="bi bi-chat-left-text"></i> Ver feedback
                            </button>
                        @else
                            <button type="button" class="wn-action-btn secondary">
                                <i class="bi bi-{{ $interview['status'] === 'cancelled' ? 'arrow-clockwise' : 'pencil' }}"></i> Reprogramar
                            </button>
                            @if ($interview['status'] !== 'cancelled')
                                <button type="button" class="wn-action-btn danger" data-cancel="{{ $interview['name'] }}">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </button>
                            @endif
                        @endif
                    </div>
                </article>
            @endforeach
        </section>

        {{-- EMPTY STATE --}}
        <div class="wn-empty" id="emptyState" style="display: none;">
            <div class="wn-empty-icon"><i class="bi bi-calendar-x"></i></div>
            <h3>No encontramos entrevistas</h3>
            <p>Probá ajustar los filtros o convocá a un nuevo candidato desde el listado de postulantes.</p>
            <a href="{{ route('employer.applicants.index') }}" class="btn-wn-primary" style="margin-top: 6px;">
                <i class="bi bi-inbox"></i> Ver postulantes
            </a>
        </div>

        {{-- WIDGETS --}}
        <div class="wn-widgets-grid">

            {{-- Próximas entrevistas --}}
            <div class="wn-widget">
                <div class="wn-widget-header">
                    <i class="bi bi-calendar-event-fill"></i>
                    <span>Próximas entrevistas</span>
                </div>

                @foreach ($upcoming as $item)
                    <div class="wn-interview-mini">
                        <div class="wn-interview-mini-date">
                            <span class="day">{{ $item['day'] }}</span>
                            <span class="month">{{ $item['month'] }}</span>
                        </div>
                        <div class="wn-interview-mini-info">
                            <p class="wn-interview-mini-title">{{ $item['title'] }}</p>
                            <p class="wn-interview-mini-meta">
                                <i class="bi bi-{{ $item['icon'] }}"></i> {{ $item['meta'] }}
                            </p>
                        </div>
                    </div>
                @endforeach

                <a href="{{ route('employer.interviews.create') }}" class="btn-wn-secondary" style="margin-top: 4px;">
                    <i class="bi bi-plus-lg"></i> Convocar nueva
                </a>
            </div>

            {{-- Resumen por modalidad --}}
            <div class="wn-widget">
                <div class="wn-widget-header">
                    <i class="bi bi-pie-chart-fill"></i>
                    <span>Resumen por modalidad</span>
                </div>

                <div class="wn-modality-summary">
                    @foreach ($modalitySummary as $m)
                        <div>
                            <div class="wn-modality-row">
                                <div class="wn-modality-label">
                                    <i class="bi bi-{{ $m['icon'] }}"></i>
                                    <span>{{ $m['label'] }}</span>
                                </div>
                                <span class="wn-modality-count">{{ $m['count'] }}</span>
                            </div>
                            <div class="wn-modality-bar">
                                <div class="wn-modality-bar-fill {{ $m['key'] }}" style="width: {{ $m['width'] }}%;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div style="padding-top: 8px; border-top: 1px solid var(--wn-border); font-size: 0.78rem; color: var(--wn-text-muted); display: flex; justify-content: space-between;">
                    <span>Total del mes</span>
                    <strong style="color: var(--wn-text);">{{ $modalityTotal }}</strong>
                </div>
            </div>
        </div>

        {{-- MODAL CANCELAR --}}
        <div class="wn-modal-backdrop wn-page-employer" id="cancelModal" role="dialog" aria-modal="true" aria-labelledby="cancelModalTitle">
            <div class="wn-modal">
                <div class="wn-modal-icon">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div>
                    <h2 id="cancelModalTitle">¿Cancelar esta entrevista?</h2>
                    <p id="cancelModalText">Se notificará al candidato y la entrevista quedará marcada como cancelada. Esta acción no se puede deshacer.</p>
                </div>
                <div class="wn-modal-actions">
                    <button type="button" class="btn-wn-secondary" id="cancelModalNo">
                        <i class="bi bi-x-lg"></i> Volver
                    </button>
                    <button type="button" class="btn-wn-danger" id="cancelModalYes">
                        <i class="bi bi-check-lg"></i> Sí, cancelar
                    </button>
                </div>
            </div>
        </div>

    </main>
@endsection
