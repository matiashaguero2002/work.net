@extends('layouts.app')

@section('title', 'Work.net - ' . $empresa->nombre_empresa)

@section('content')
    <div class="wn-bg"><div class="wn-bg-grid"></div></div>

    @include('partials.wn-navbar')

    <main class="wn-main wn-page-company">

        <header class="wn-header">
            <div class="wn-header-left">
                <div class="wn-company-hero">
                    <div class="wn-company-hero-logo">{{ $companyInitials }}</div>
                    <div>
                        <h1>{{ $empresa->nombre_empresa }}</h1>
                        <p>{{ $ciudades }} · {{ count($ofertas) }} {{ count($ofertas) === 1 ? 'oferta activa' : 'ofertas activas' }}</p>
                    </div>
                </div>
            </div>
        </header>

        {{-- Datos de la empresa --}}
        <section class="wn-section">
            <div class="wn-company-info-grid">
                <div class="wn-widget">
                    <div class="wn-widget-header">
                        <i class="bi bi-building"></i>
                        <span>Sobre la empresa</span>
                    </div>
                    <p class="wn-company-desc">{{ $empresa->descripcion ?: 'La empresa todavía no cargó una descripción.' }}</p>
                    <ul class="wn-company-data">
                        @if($empresa->sitio_web)
                            <li><i class="bi bi-globe"></i> <span>{{ $empresa->sitio_web }}</span></li>
                        @endif
                        @if($empresa->telefono)
                            <li><i class="bi bi-telephone"></i> <span>{{ $empresa->telefono }}</span></li>
                        @endif
                        <li><i class="bi bi-briefcase"></i> <span>{{ count($ofertas) }} {{ count($ofertas) === 1 ? 'oferta activa' : 'ofertas activas' }}</span></li>
                    </ul>
                </div>
            </div>
        </section>

        {{-- Ofertas activas de la empresa --}}
        <section class="wn-section">
            <div class="wn-section-header">
                <h2 class="wn-section-title">
                    <i class="bi bi-megaphone"></i>
                    Ofertas publicadas
                </h2>
                <a href="{{ route('map.index') }}" class="wn-link">
                    Ver el mapa <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="wn-offers-grid">
                @forelse($ofertas as $oferta)
                    <article class="wn-offer-card">
                        <div class="wn-offer-card-header">
                            <div class="wn-offer-logo">{{ $companyInitials }}</div>
                            <div class="wn-offer-info">
                                <h3 class="wn-offer-title">{{ $oferta->titulo }}</h3>
                                <p class="wn-offer-company">{{ $empresa->nombre_empresa }}</p>
                            </div>
                        </div>
                        <div class="wn-offer-badges">
                            <span class="wn-offer-badge"><i class="bi bi-geo-alt-fill"></i> {{ $oferta->ubicacion?->ciudad ?? '—' }}</span>
                            <span class="wn-offer-badge"><i class="bi bi-laptop"></i> {{ $oferta->modalidad?->nombre ?? '—' }}</span>
                            <span class="wn-offer-badge"><i class="bi bi-clock"></i> {{ $oferta->tipoContrato?->nombre ?? '—' }}</span>
                        </div>
                        <p class="wn-offer-description">{{ Str::limit(strip_tags($oferta->descripcion), 140) }}</p>
                        <div class="wn-offer-footer">
                            <span class="wn-offer-salary">
                                <i class="bi bi-cash-coin"></i> {{ \App\Http\Controllers\Candidate\SavedOfferController::salaryLabel($oferta) }}
                            </span>
                            <a href="{{ route('map.index', ['lat' => $oferta->ubicacion->latitud, 'lng' => $oferta->ubicacion->longitud, 'zoom' => 17, 'offer' => $oferta->id_oferta]) }}" class="wn-offer-map-btn">
                                <i class="bi bi-geo-alt"></i> Ver en mapa
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="wn-empty-state">
                        <i class="bi bi-inbox"></i>
                        <h3>Sin ofertas activas</h3>
                        <p>Esta empresa no tiene ofertas activas en este momento.</p>
                    </div>
                @endforelse
            </div>
        </section>

    </main>
@endsection

@push('scripts')
    <script>
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
    </script>
@endpush
