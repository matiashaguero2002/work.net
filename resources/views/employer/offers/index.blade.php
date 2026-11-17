@extends('layouts.app')

@section('title', 'Work.net - Mis ofertas')

@section('content')
    <div class="wn-bg"><div class="wn-bg-grid"></div></div>

    @include('partials.wn-navbar')

    <main class="wn-main wn-page-employer">
        @if (session('success'))
            <div class="wn-flash-success"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="wn-flash-error">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div>@foreach ($errors->all() as $error)<span>{{ $error }}</span>@endforeach</div>
            </div>
        @endif

        <header class="wn-header">
            <div class="wn-header-left">
                <h1>Mis ofertas</h1>
                <p>Gestioná tus ofertas publicadas: <strong>{{ $stats['active'] }} activas</strong>, {{ $stats['paused'] }} pausadas y {{ $stats['closed'] }} cerradas.</p>
            </div>
            <div class="wn-header-actions">
                <a href="{{ route('employer.offers.create') }}" class="btn-wn-primary">
                    <i class="bi bi-plus-lg"></i> Publicar nueva oferta
                </a>
            </div>
        </header>

        <div class="wn-tabs" id="offersTabs">
            <button class="wn-tab active" type="button" data-filter="">Todas <span class="count">{{ $stats['total'] }}</span></button>
            <button class="wn-tab" type="button" data-filter="active">Activas <span class="count">{{ $stats['active'] }}</span></button>
            <button class="wn-tab" type="button" data-filter="paused">Pausadas <span class="count">{{ $stats['paused'] }}</span></button>
            <button class="wn-tab" type="button" data-filter="closed">Cerradas <span class="count">{{ $stats['closed'] }}</span></button>
        </div>

        <div class="wn-toolbar">
            <div class="wn-search-input">
                <i class="bi bi-search"></i>
                <input type="text" id="offersSearch" placeholder="Buscar por título, área o palabra clave...">
            </div>
            <select class="wn-sort-select" id="offersSort">
                <option value="recent">Más recientes</option>
                <option value="old">Más antiguas</option>
                <option value="applicants-desc">Más postulantes</option>
                <option value="applicants-asc">Menos postulantes</option>
                <option value="title">Título (A-Z)</option>
            </select>
        </div>

        <div class="wn-offers-grid" id="offersGrid">
            @foreach($offers as $offer)
                <article class="wn-offer-card"
                    data-status="{{ $offer['status'] }}"
                    data-title="{{ mb_strtolower($offer['title'] . ' ' . $offer['location'] . ' ' . $offer['modality'] . ' ' . $offer['contractType']) }}"
                    data-published="{{ $offer['publishedTs'] }}"
                    data-applicants="{{ $offer['applicants'] }}">
                    <span class="wn-offer-status {{ $offer['status'] }}">{{ $offer['statusLabel'] }}</span>

                    <div class="wn-offer-card-header">
                        <div class="wn-offer-logo">{{ $offer['logo'] }}</div>
                        <div class="wn-offer-info">
                            <a href="{{ route('employer.offers.show', $offer['id']) }}" class="wn-offer-title-link">
                                <h3 class="wn-offer-title">{{ $offer['title'] }}</h3>
                            </a>
                            <p class="wn-offer-company">{{ $offer['publishedAt'] }}</p>
                        </div>
                    </div>

                    <div class="wn-offer-badges">
                        <span class="wn-offer-badge"><i class="bi bi-geo-alt-fill"></i> {{ $offer['location'] }}</span>
                        <span class="wn-offer-badge"><i class="bi bi-{{ $offer['modalityIcon'] ?? 'laptop' }}"></i> {{ $offer['modality'] }}</span>
                        <span class="wn-offer-badge"><i class="bi bi-clock"></i> {{ $offer['contractType'] }}</span>
                    </div>

                    <div class="wn-offer-stats">
                        <div class="wn-offer-stat">
                            <i class="bi bi-inbox-fill"></i> <strong>{{ $offer['applicants'] }}</strong> postulantes
                        </div>
                        <div class="wn-offer-stat">
                            <i class="bi bi-eye-fill"></i> <strong>{{ $offer['views'] }}</strong> vistas
                        </div>
                        <div class="wn-offer-stat">
                            @if($offer['status'] === 'active')
                                <i class="bi bi-hourglass-split"></i> <strong>{{ $offer['daysLeft'] }}</strong> días
                            @elseif($offer['status'] === 'paused')
                                <i class="bi bi-pause-circle"></i> Pausada
                            @else
                                <i class="bi bi-check-circle"></i> Finalizada
                            @endif
                        </div>
                    </div>

                    <div class="wn-offer-actions">
                        <a href="{{ route('employer.offers.show', $offer['id']) }}" class="wn-offer-action secondary icon-only" title="Ver detalle">
                            <i class="bi bi-eye"></i>
                        </a>

                        @if($offer['status'] === 'active')
                            <a href="{{ route('employer.applicants.index', ['oferta' => $offer['id']]) }}" class="wn-offer-action primary">
                                <i class="bi bi-inbox"></i> Postulantes
                            </a>
                            <a href="{{ route('employer.offers.edit', $offer['id']) }}" class="wn-offer-action secondary icon-only" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('employer.offers.pause', $offer['id']) }}" class="wn-offer-form" data-confirm="¿Pausar esta oferta? Dejará de aparecer en el mapa y no aceptará postulaciones hasta que la reactives.">
                                @csrf
                                <button type="submit" class="wn-offer-action warning icon-only" title="Pausar">
                                    <i class="bi bi-pause"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('employer.offers.close', $offer['id']) }}" class="wn-offer-form" data-confirm="¿Cerrar esta oferta? Ya no aparecerá en el mapa ni aceptará postulaciones.">
                                @csrf
                                <button type="submit" class="wn-offer-action danger icon-only" title="Cerrar">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </form>

                        @elseif($offer['status'] === 'paused')
                            <form method="POST" action="{{ route('employer.offers.reactivate', $offer['id']) }}" class="wn-offer-form">
                                @csrf
                                <button type="submit" class="wn-offer-action primary">
                                    <i class="bi bi-play"></i> Reactivar
                                </button>
                            </form>
                            <a href="{{ route('employer.offers.edit', $offer['id']) }}" class="wn-offer-action secondary icon-only" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('employer.offers.close', $offer['id']) }}" class="wn-offer-form" data-confirm="¿Cerrar esta oferta? Ya no aparecerá en el mapa ni aceptará postulaciones.">
                                @csrf
                                <button type="submit" class="wn-offer-action danger icon-only" title="Cerrar">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </form>

                        @else
                            <a href="{{ route('employer.offers.show', $offer['id']) }}" class="wn-offer-action primary">
                                <i class="bi bi-eye"></i> Ver detalle
                            </a>
                            <form method="POST" action="{{ route('employer.offers.duplicate', $offer['id']) }}" class="wn-offer-form" data-confirm="Se creará una copia activa de esta oferta. ¿Continuar?">
                                @csrf
                                <button type="submit" class="wn-offer-action secondary icon-only" title="Duplicar">
                                    <i class="bi bi-files"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </article>
            @endforeach

            @empty($offers)
                <div class="wn-empty" style="grid-column: 1 / -1;">
                    <div class="wn-empty-icon"><i class="bi bi-briefcase"></i></div>
                    <h3>No tenés ofertas todavía</h3>
                    <p>Publicá tu primera oferta para aparecer en el mapa de candidatos.</p>
                    <a href="{{ route('employer.offers.create') }}" class="btn-wn-primary"><i class="bi bi-plus-lg"></i> Publicar oferta</a>
                </div>
            @endempty
        </div>

        <p class="wn-results-note" id="offersResults" style="margin-top: 12px; font-size: 0.8rem; color: var(--wn-text-muted);"></p>
    </main>
@endsection

@push('scripts')
    <script>
        (function () {
            const grid = document.getElementById('offersGrid');
            if (!grid) return;
            const cards = Array.from(grid.querySelectorAll('.wn-offer-card'));
            const tabs = document.querySelectorAll('#offersTabs .wn-tab');
            const search = document.getElementById('offersSearch');
            const sort = document.getElementById('offersSort');
            const results = document.getElementById('offersResults');
            let activeFilter = '';

            function applyFilters() {
                const q = (search.value || '').toLowerCase().trim();
                let visible = 0;
                cards.forEach((card) => {
                    let match = (!activeFilter || card.dataset.status === activeFilter)
                        && (!q || card.dataset.title.includes(q));
                    card.style.display = match ? '' : 'none';
                    if (match) visible++;
                });
                if (results) results.textContent = visible + ' de ' + cards.length + ' ofertas visibles';
            }

            tabs.forEach((tab) => {
                tab.addEventListener('click', () => {
                    tabs.forEach((t) => t.classList.remove('active'));
                    tab.classList.add('active');
                    activeFilter = tab.dataset.filter;
                    applyFilters();
                });
            });

            if (search) search.addEventListener('input', applyFilters);

            if (sort) {
                sort.addEventListener('change', () => {
                    const mode = sort.value;
                    const sorted = cards.slice().sort((a, b) => {
                        if (mode === 'recent') return new Date(b.dataset.published) - new Date(a.dataset.published);
                        if (mode === 'old') return new Date(a.dataset.published) - new Date(b.dataset.published);
                        if (mode === 'applicants-desc') return b.dataset.applicants - a.dataset.applicants;
                        if (mode === 'applicants-asc') return a.dataset.applicants - b.dataset.applicants;
                        if (mode === 'title') return a.dataset.title.localeCompare(b.dataset.title);
                        return 0;
                    });
                    sorted.forEach((card) => grid.appendChild(card));
                });
            }

            // Confirmaciones de pausar / cerrar / duplicar.
            grid.querySelectorAll('form[data-confirm]').forEach((form) => {
                form.addEventListener('submit', (e) => {
                    if (!window.confirm(form.dataset.confirm)) e.preventDefault();
                });
            });
        })();
    </script>
@endpush
