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

        {{-- Toolbar --}}
        <div class="wn-toolbar">
            <div class="wn-search-input">
                <i class="bi bi-search"></i>
                <input type="text" placeholder="Buscar en tus ofertas guardadas...">
            </div>
            <select class="wn-sort-select">
                <option>Más recientes</option>
                <option>Más antiguas</option>
                <option>Mayor salario</option>
                <option>Menor salario</option>
                <option>Empresa (A-Z)</option>
            </select>
        </div>

        {{-- Grid --}}
        <div class="wn-offers-grid">

            @foreach($savedOffers as $offer)
                <article class="wn-offer-card">
                    <button class="wn-offer-remove" title="Quitar de guardadas" type="button">
                        <i class="bi bi-x-lg"></i>
                    </button>
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
                            <a href="#" class="wn-offer-action primary">
                                <i class="bi bi-send"></i> Postularme
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach

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

        // Quitar de guardadas (demo con confirmación).
        document.querySelectorAll('.wn-page-saved .wn-offer-remove').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const card = btn.closest('.wn-offer-card');
                if (card && confirm('¿Querés quitar esta oferta de tus guardadas?')) {
                    card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.95)';
                    setTimeout(() => card.remove(), 300);
                }
            });
        });
    </script>
@endpush
