@extends('layouts.app')

@section('title', 'Work.net - Entrevistas (empleador)')

@section('content')
    <div class="wn-bg"><div class="wn-bg-grid"></div></div>

    @include('partials.wn-navbar')

    <main class="wn-main wn-page-employer wn-page-interviews">

        @if (session('success'))
            <div class="wn-flash-success"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="wn-flash-error">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div>@foreach ($errors->all() as $error)<span>{{ $error }}</span>@endforeach</div>
            </div>
        @endif

        {{-- HEADER --}}
        <header class="wn-header">
            <div class="wn-header-left">
                <h1>Entrevistas</h1>
                <p>Tenés <strong>{{ $stats[0]['value'] }} entrevistas pendientes</strong> y {{ $stats[3]['value'] }} ya completadas.</p>
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
                    <option value="negotiating">Reagendando</option>
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
                         data-status="{{ $interview['status'] }}"
                         data-modality="{{ strtolower($interview['modality']) }}"
                         data-date="{{ $interview['date'] ?? '' }}"
                         data-name="{{ mb_strtolower($interview['name']) }}"
                         data-position="{{ mb_strtolower($interview['position']) }}">
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
                        <a href="{{ route('employer.applicants.show', $interview['idPostulacion']) }}" class="wn-action-btn primary">
                            <i class="bi bi-person-lines-fill"></i> Ver candidato
                        </a>
                        @if (in_array($interview['status'], ['pending', 'negotiating']))
                            <button type="button" class="wn-action-btn secondary"
                                data-negotiate
                                data-id="{{ $interview['id'] }}"
                                data-url="{{ route('employer.interviews.negotiate', $interview['id']) }}"
                                data-responder-url="{{ route('employer.interviews.responder', $interview['id']) }}"
                                data-visto-url="{{ route('employer.interviews.visto', $interview['id']) }}"
                                data-estado="{{ $interview['status'] }}"
                                data-candidate="{{ $interview['name'] }}"
                                data-position="{{ $interview['position'] }}"
                                data-notes="{{ $interview['notes'] ?? '' }}">
                                <i class="bi bi-pencil"></i> Reprogramar
                            </button>
                            <button type="button" class="wn-action-btn danger"
                                data-cancel
                                data-url="{{ route('employer.interviews.cancel', $interview['id']) }}"
                                data-candidate="{{ $interview['name'] }}">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </button>
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

                @forelse ($upcoming as $item)
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
                @empty
                    <p style="font-size: 0.8rem; color: var(--wn-text-muted); margin: 6px 0;">
                        No tenés entrevistas programadas. Convocá a un postulante desde el listado de postulantes.
                    </p>
                @endempty

                <a href="{{ route('employer.applicants.index') }}" class="btn-wn-secondary" style="margin-top: 4px;">
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
                    <span>Total de entrevistas</span>
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

        {{-- MODAL REPROGRAMAR (chat RN-19): la propuesta del empleador
             se guarda en entrevista.notas y el candidato la ve. --}}
        <div class="wn-modal-backdrop wn-negotiate" id="negotiateModal" role="dialog" aria-modal="true" aria-labelledby="negotiateModalTitle">
            <div class="wn-modal">

                {{-- Header --}}
                <div class="wn-modal-header">
                    <h2 class="wn-modal-title" id="negotiateModalTitle"><i class="bi bi-calendar-event"></i> Reprogramar Entrevista</h2>
                    <button class="wn-modal-close" type="button" data-negotiate-close aria-label="Cerrar">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                {{-- Body --}}
                <div class="wn-modal-body">

                    {{-- Contexto actual --}}
                    <div class="wn-alert-info">
                        <i class="bi bi-info-circle-fill"></i>
                        <div>
                            <p>Entrevista con: <strong id="negotiateCompany"></strong></p>
                            <p>Puesto: <strong id="negotiatePosition"></strong></p>
                        </div>
                    </div>

                    {{-- Formulario de propuesta --}}
                    <div>
                        <h3 class="wn-modal-subtitle">Proponer nueva fecha y hora</h3>
                        <div class="wn-form-row">
                            <div>
                                <label class="wn-label" for="negotiateDate">Nueva fecha <span class="wn-required">*</span></label>
                                <input type="date" class="wn-input" id="negotiateDate">
                            </div>
                            <div>
                                <label class="wn-label" for="negotiateTime">Nueva hora <span class="wn-required">*</span></label>
                                <input type="time" class="wn-input" id="negotiateTime">
                            </div>
                        </div>
                        <div class="wn-form-row mb-none">
                            <div>
                                <label class="wn-label" for="negotiateMessage">Mensaje (opcional)</label>
                                <textarea class="wn-textarea" id="negotiateMessage" rows="2" placeholder="Ej: ¿Podemos pasarla para el jueves en la mañana?"></textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Historial: propuesta del candidato / respuestas previas --}}
                    <div class="wn-chat-history" id="negotiateChat" hidden>
                        <h3 class="wn-modal-subtitle">Historial de la entrevista</h3>
                        <div class="wn-chat-history-items" id="negotiateChatItems"></div>
                    </div>

                </div>

                {{-- Footer --}}
                <div class="wn-modal-footer">
                    <button class="btn-wn-secondary" type="button" data-negotiate-close>Cancelar</button>
                    <button class="btn-wn-primary" type="button" id="negotiateSend">Enviar Propuesta</button>
                </div>

            </div>
        </div>

    </main>
@endsection
