@extends('layouts.app')

@section('title', 'Work.net - Ofertas guardadas')

@section('content')
    <div class="wn-bg"><div class="wn-bg-grid"></div></div>

    @include('partials.wn-navbar')

    <main class="wn-main wn-page-saved">

        <header class="wn-header">
            <div class="wn-header-left">
                <h1>Ofertas guardadas</h1>
                <p>Tenés <strong>{{ count($savedOffers) }} ofertas guardadas</strong>. Podés postularte cuando quieras.</p>
            </div>
        </header>

        {{-- Toolbar: buscador + orden funcionan en vivo sobre las cards --}}
        <div class="wn-toolbar">
            <div class="wn-search-input">
                <i class="bi bi-search"></i>
                <input type="text" id="savedSearch" placeholder="Buscar en tus ofertas guardadas...">
            </div>
            <select class="wn-sort-select" id="savedSort">
                <option value="recent">Más recientes</option>
                <option value="old">Más antiguas</option>
                <option value="salary-desc">Mayor salario</option>
                <option value="salary-asc">Menor salario</option>
                <option value="company">Empresa (A-Z)</option>
            </select>
        </div>

        {{-- Grid --}}
        <div class="wn-offers-grid" id="savedGrid">

            @forelse($savedOffers as $offer)
                <article class="wn-offer-card" data-offer-id="{{ $offer['offerId'] }}"
                         data-search="{{ mb_strtolower($offer['title'].' '.$offer['company']) }}"
                         data-ts="{{ $offer['savedAtTs'] }}"
                         data-salary="{{ $offer['salaryMin'] }}"
                         data-company="{{ mb_strtolower($offer['company']) }}">
                    {{-- Quitar de guardadas: DELETE real (RF-08) --}}
                    <form method="POST" action="{{ route('candidate.saved.destroy', $offer['offerId']) }}"
                          class="wn-offer-remove-form" data-offer-title="{{ $offer['title'] }}">
                        @csrf
                        @method('DELETE')
                        <button class="wn-offer-remove" title="Quitar de guardadas" type="submit">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </form>
                    <div class="wn-offer-card-header">
                        <div class="wn-offer-logo">{{ $offer['companyInitials'] }}</div>
                        <div class="wn-offer-info">
                            <h3 class="wn-offer-title">{{ $offer['title'] }}</h3>
                            <p class="wn-offer-company">{{ $offer['company'] }}</p>
                            <p class="wn-offer-saved-date">
                                <i class="bi bi-bookmark-fill"></i> {{ $offer['savedAt'] }}
                            </p>
                        </div>
                    </div>
                    <div class="wn-offer-badges">
                        <span class="wn-offer-badge"><i class="bi bi-geo-alt-fill"></i> {{ $offer['location'] }}</span>
                        <span class="wn-offer-badge"><i class="bi bi-laptop"></i> {{ $offer['modality'] }}</span>
                        <span class="wn-offer-badge"><i class="bi bi-clock"></i> {{ $offer['contractType'] }}</span>
                    </div>
                    <p class="wn-offer-description">{{ $offer['shortDescription'] }}</p>
                    <div class="wn-offer-footer">
                        <span class="wn-offer-salary">
                            <i class="bi bi-cash-coin"></i> {{ $offer['salary'] }}
                        </span>
                        <div class="wn-offer-actions">
                            <a href="{{ route('map.index', ['lat' => $offer['coords'][0], 'lng' => $offer['coords'][1], 'zoom' => 17, 'offer' => $offer['offerId']]) }}" class="wn-offer-action map">
                                <i class="bi bi-geo-alt"></i> Mapa
                            </a>
                            @if(in_array($offer['offerId'], $postulatedIds))
                                {{-- Ya postulado: el botón pasa a Retirar (DELETE real) --}}
                                <form method="POST" action="{{ route('candidate.applications.destroyByOffer', $offer['offerId']) }}"
                                      class="wn-saved-withdraw-form" data-offer-title="{{ $offer['title'] }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="wn-offer-action withdraw" type="submit">
                                        <i class="bi bi-box-arrow-right"></i> Retirar
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('candidate.applications.store', $offer['offerId']) }}"
                                      class="wn-saved-apply-form" data-offer-title="{{ $offer['title'] }}">
                                    @csrf
                                    <button class="wn-offer-action primary" type="submit">
                                        <i class="bi bi-send"></i> Postularme
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="wn-empty-state">
                    <i class="bi bi-bookmark"></i>
                    <h3>Aún no guardaste ninguna oferta</h3>
                    <p>Explorá el mapa de ofertas y tocá el botón <strong>Guardar</strong> en la tarjeta para tenerla a mano acá.</p>
                    <a href="{{ route('map.index') }}" class="btn-wn-primary">
                        <i class="bi bi-map"></i> Ir al mapa de ofertas
                    </a>
                </div>
            @endforelse

                {{-- Mensaje cuando el buscador no encuentra nada --}}
                <div class="wn-empty-state" id="savedEmptySearch" hidden>
                    <i class="bi bi-search"></i>
                    <h3>No hay resultados para tu búsqueda</h3>
                    <p>Probá con otro texto o limpiá el buscador.</p>
                </div>

        </div>

    </main>
@endsection

@push('scripts')
    <script>
        // Clase de respaldo para el CSS de la página (por si :has() no aplica).
        document.body.classList.add('wn-page-saved');
        document.documentElement.classList.add('wn-page-saved');

        // Toggle del navbar (map.js solo lo registra cuando existe #map).
        (function () {
            const navbar = document.getElementById('navbar');
            const navbarToggle = document.getElementById('navbarToggle');
            const navbarBrandTrigger = document.getElementById('navbarBrandTrigger');
            if (navbar && navbarToggle) {
                navbarToggle.addEventListener('click', () => {
                    navbar.classList.add('collapsed');
                    document.body.classList.add('navbar-collapsed');
                });
            }
            if (navbar && navbarBrandTrigger) {
                navbarBrandTrigger.addEventListener('click', () => {
                    if (navbar.classList.contains('collapsed')) {
                        navbar.classList.remove('collapsed');
                        document.body.classList.remove('navbar-collapsed');
                    }
                });
            }
        })();

        // Quitar de guardadas (RF-08): confirmación + fade, luego
        // envía el form DELETE que borra la fila en la base de datos.
        document.querySelectorAll('.wn-page-saved .wn-offer-remove-form').forEach(form => {
            const btn = form.querySelector('.wn-offer-remove');
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const title = form.dataset.offerTitle || 'esta oferta';
                if (!confirm('¿Querés quitar "' + title + '" de tus guardadas?')) return;

                const card = form.closest('.wn-offer-card');
                card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                card.style.opacity = '0';
                card.style.transform = 'scale(0.95)';
                setTimeout(() => form.submit(), 280);
            });
        });

        // ============================================
        // BUSCADOR + ORDEN (funcionales sobre las cards)
        // ============================================
        const grid = document.getElementById('savedGrid');
        const searchInput = document.getElementById('savedSearch');
        const sortSelect = document.getElementById('savedSort');
        const emptySearch = document.getElementById('savedEmptySearch');

        function applySearch() {
            const term = (searchInput ? searchInput.value : '').trim().toLowerCase();
            let visibles = 0;
            grid.querySelectorAll('.wn-offer-card').forEach((card) => {
                const match = !term || (card.dataset.search || '').includes(term);
                card.style.display = match ? '' : 'none';
                if (match) visibles++;
            });
            if (emptySearch) emptySearch.hidden = visibles !== 0;
        }

        if (searchInput) searchInput.addEventListener('input', applySearch);

        if (sortSelect && grid) {
            sortSelect.addEventListener('change', () => {
                const cards = Array.from(grid.querySelectorAll('.wn-offer-card'));
                const modo = sortSelect.value;
                cards.sort((a, b) => {
                    switch (modo) {
                        case 'old': return (a.dataset.ts || 0) - (b.dataset.ts || 0);
                        case 'salary-desc': return (b.dataset.salary || 0) - (a.dataset.salary || 0);
                        case 'salary-asc': return (a.dataset.salary || 0) - (b.dataset.salary || 0);
                        case 'company': return (a.dataset.company || '').localeCompare(b.dataset.company || '', 'es');
                        default: return (b.dataset.ts || 0) - (a.dataset.ts || 0);
                    }
                });
                cards.forEach((card) => grid.appendChild(card));
            });
        }
    </script>
@endpush
