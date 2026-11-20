@extends('layouts.app')

@section('title', 'Work.net - Ofertas (supervisión)')

@section('content')
    <div class="wn-bg"><div class="wn-bg-grid"></div></div>

    @include('partials.wn-navbar')

    <main class="wn-main wn-page-admin wn-page-admin-list">

        <header class="wn-header">
            <div class="wn-header-left">
                <h1>Ofertas <span class="wn-admin-badge">Supervisión</span></h1>
                <p>Supervisá las ofertas publicadas. Tenés <strong>2 ofertas reportadas</strong> pendientes de revisión.</p>
            </div>
            <div class="wn-header-actions">
                <a href="{{ route('admin.reports.index') }}" class="btn-wn-secondary">
                    <i class="bi bi-flag"></i> Ver reportes
                </a>
                <button type="button" class="btn-wn-primary">
                    <i class="bi bi-download"></i> Exportar CSV
                </button>
            </div>
        </header>

        {{-- STATS --}}
        <section class="wn-stats" aria-label="Resumen de ofertas">
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
        <section class="wn-filters" aria-label="Filtros de ofertas">
            <div class="wn-filter-group wn-filter-search">
                <label class="wn-filter-label" for="filterSearch">Buscar</label>
                <i class="bi bi-search"></i>
                <input type="text" id="filterSearch" class="wn-filter-input" placeholder="Título, empresa o descripción...">
            </div>
            <div class="wn-filter-group">
                <label class="wn-filter-label" for="filterStatus">Estado</label>
                <select id="filterStatus" class="wn-filter-select">
                    <option value="">Todos</option>
                    <option value="active">Activas</option>
                    <option value="reported">Reportadas</option>
                    <option value="paused">Pausadas</option>
                    <option value="closed">Cerradas</option>
                </select>
            </div>
            <div class="wn-filter-group">
                <label class="wn-filter-label" for="filterCategory">Categoría</label>
                <select id="filterCategory" class="wn-filter-select">
                    <option value="">Todas</option>
                    <option value="desarrollo">Desarrollo</option>
                    <option value="analisis">Análisis</option>
                    <option value="diseno">Diseño</option>
                    <option value="soporte">Soporte</option>
                    <option value="datos">Datos</option>
                </select>
            </div>
            <div class="wn-filter-group">
                <label class="wn-filter-label" for="filterDate">Publicación</label>
                <select id="filterDate" class="wn-filter-select">
                    <option value="">Cualquier fecha</option>
                    <option value="today">Hoy</option>
                    <option value="week">Esta semana</option>
                    <option value="month">Este mes</option>
                </select>
            </div>
            <button type="button" class="wn-filter-clear" id="filterClear">
                <i class="bi bi-x-lg"></i> Limpiar
            </button>
        </section>

        {{-- INFO + TABS --}}
        <div class="wn-results-info">
            <div class="wn-results-count">
                <strong id="resultsCount">{{ count($offers) }}</strong> ofertas encontradas
            </div>
            <div class="wn-tabs" role="tablist" aria-label="Filtros rápidos">
                <button type="button" class="wn-tab active" data-tab="">Todas</button>
                <button type="button" class="wn-tab danger" data-tab="reported">Reportadas (2)</button>
                <button type="button" class="wn-tab" data-tab="paused">Pausadas (5)</button>
            </div>
        </div>

        {{-- GRID DE OFERTAS --}}
        <section class="wn-offers-grid" id="offersGrid" aria-label="Listado de ofertas">
            @foreach ($offers as $offer)
                <article class="wn-offer-card status-{{ $offer['status'] }}" data-status="{{ $offer['status'] }}" data-category="{{ $offer['category'] }}" data-date="{{ $offer['date'] }}" data-title="{{ $offer['title'] }}" data-company="{{ $offer['company'] }}">
                    <span class="wn-offer-status {{ $offer['status'] }}">{{ $offer['statusLabel'] }}</span>
                    <div class="wn-offer-card-header">
                        <div class="wn-offer-logo">{{ $offer['initials'] }}</div>
                        <div class="wn-offer-info">
                            <h3 class="wn-offer-title">{{ $offer['title'] }}</h3>
                            <p class="wn-offer-company"><i class="bi bi-building"></i> {{ $offer['company'] }}</p>
                        </div>
                    </div>
                    <div class="wn-offer-badges">
                        @foreach ($offer['badges'] as $badge)
                            <span class="wn-offer-badge"><i class="bi bi-{{ $badge['icon'] }}"></i> {{ $badge['text'] }}</span>
                        @endforeach
                        @if ($offer['reported'])
                            <span class="wn-offer-badge reported"><i class="bi bi-flag-fill"></i> {{ $offer['reported']['text'] }}</span>
                        @endif
                    </div>
                    <p class="wn-offer-description">{{ $offer['description'] }}</p>
                    <div class="wn-offer-stats">
                        <div class="wn-offer-stat"><i class="bi bi-inbox-fill"></i> <strong>{{ $offer['applicants'] }}</strong> postulantes</div>
                        <div class="wn-offer-stat"><i class="bi bi-eye-fill"></i> <strong>{{ $offer['views'] }}</strong> vistas</div>
                    </div>
                    <div class="wn-offer-actions">
                        <a href="{{ route('admin.offers.show', $offer['id']) }}" class="wn-offer-action primary"><i class="bi bi-eye"></i> Ver detalle</a>
                        @if (in_array('pause', $offer['actions']))
                            <button type="button" class="wn-offer-action secondary icon-only" title="Pausar" data-action="pause" data-id="{{ $offer['id'] }}"><i class="bi bi-pause-fill"></i></button>
                        @endif
                        @if (in_array('reactivate', $offer['actions']))
                            <button type="button" class="wn-offer-action secondary icon-only" title="Reactivar" data-action="reactivate" data-id="{{ $offer['id'] }}"><i class="bi bi-play-fill"></i></button>
                        @endif
                        @if (in_array('cerrar', $offer['actions']))
                            <button type="button" class="wn-offer-action secondary icon-only" title="Cerrar" data-action="cerrar" data-id="{{ $offer['id'] }}"><i class="bi bi-x-circle"></i></button>
                        @endif
                        @if (in_array('delete', $offer['actions']))
                            <button type="button" class="wn-offer-action danger icon-only" title="Eliminar" data-action="delete" data-id="{{ $offer['id'] }}"><i class="bi bi-trash"></i></button>
                        @endif
                    </div>
                </article>
            @endforeach
        </section>

        {{-- EMPTY STATE --}}
        <div class="wn-empty" id="emptyState" style="display: none;">
            <div class="wn-empty-icon"><i class="bi bi-briefcase"></i></div>
            <h3>No encontramos ofertas</h3>
            <p>Probá ajustar los filtros de búsqueda o el estado seleccionado.</p>
        </div>

        {{-- PAGINACIÓN --}}
        <div class="wn-pagination">
            <div class="wn-pagination-info">
                Mostrando <strong>1-{{ count($offers) }}</strong> de <strong>228</strong> ofertas
            </div>
            <div class="wn-pagination-controls">
                <button type="button" class="wn-page-btn" disabled><i class="bi bi-chevron-left"></i></button>
                <button type="button" class="wn-page-btn active">1</button>
                <button type="button" class="wn-page-btn">2</button>
                <button type="button" class="wn-page-btn">3</button>
                <button type="button" class="wn-page-btn">...</button>
                <button type="button" class="wn-page-btn">29</button>
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
                <p id="actionModalText">Esta acción se aplicará a la oferta seleccionada.</p>
            </div>
            <div class="wn-modal-actions">
                <button type="button" class="btn-wn-secondary" id="actionModalNo">
                    <i class="bi bi-x-lg"></i> Cancelar
                </button>
                <button type="button" class="btn-wn-danger" id="actionModalYes">
                    <i class="bi bi-check-lg"></i> Confirmar
                </button>
            </div>
        </div>
    </div>

    {{-- CONFIG PARA admin.js (acciones de ofertas) --}}
    <script>
    window.WN_ADMIN_OFFERS = {
        urls: {
            pause: "{{ route('admin.offers.pausar', [':id']) }}",
            reactivate: "{{ route('admin.offers.reactivar', [':id']) }}",
            cerrar: "{{ route('admin.offers.cerrar', [':id']) }}",
            delete: "{{ route('admin.offers.destroy', [':id']) }}"
        },
        csrf: "{{ csrf_token() }}"
    };
    </script>
@endsection
