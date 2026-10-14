@extends('layouts.app')

@section('title', 'Work.net - Mis ofertas')

@section('content')
    <div class="wn-bg"><div class="wn-bg-grid"></div></div>

    @include('partials.wn-navbar')

    <main class="wn-main wn-page-employer">
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

        <div class="wn-tabs">
            <button class="wn-tab active" type="button">Todas <span class="count">{{ $stats['total'] }}</span></button>
            <button class="wn-tab" type="button">Activas <span class="count">{{ $stats['active'] }}</span></button>
            <button class="wn-tab" type="button">Pausadas <span class="count">{{ $stats['paused'] }}</span></button>
            <button class="wn-tab" type="button">Cerradas <span class="count">{{ $stats['closed'] }}</span></button>
        </div>

        <div class="wn-toolbar">
            <div class="wn-search-input">
                <i class="bi bi-search"></i>
                <input type="text" placeholder="Buscar por título, área o palabra clave...">
            </div>
            <select class="wn-sort-select">
                <option>Más recientes</option>
                <option>Más antiguas</option>
                <option>Más postulantes</option>
                <option>Menos postulantes</option>
                <option>Título (A-Z)</option>
            </select>
        </div>

        <div class="wn-offers-grid">
            @foreach($offers as $offer)
                <article class="wn-offer-card">
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
                            <a href="{{ route('employer.applicants.index') }}" class="wn-offer-action primary">
                                <i class="bi bi-inbox"></i> Postulantes
                            </a>
                            <a href="{{ route('employer.offers.edit', $offer['id']) }}" class="wn-offer-action secondary icon-only" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="#" class="wn-offer-action warning icon-only" title="Pausar">
                                <i class="bi bi-pause"></i>
                            </a>
                            <a href="#" class="wn-offer-action danger icon-only" title="Cerrar">
                                <i class="bi bi-x-lg"></i>
                            </a>

                        @elseif($offer['status'] === 'paused')
                            <a href="#" class="wn-offer-action primary">
                                <i class="bi bi-play"></i> Reactivar
                            </a>
                            <a href="{{ route('employer.offers.edit', $offer['id']) }}" class="wn-offer-action secondary icon-only" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="#" class="wn-offer-action danger icon-only" title="Cerrar">
                                <i class="bi bi-x-lg"></i>
                            </a>

                        @else
                            <a href="{{ route('employer.offers.show', $offer['id']) }}" class="wn-offer-action primary">
                                <i class="bi bi-eye"></i> Ver detalle
                            </a>
                            <a href="#" class="wn-offer-action secondary icon-only" title="Duplicar">
                                <i class="bi bi-files"></i>
                            </a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </main>
@endsection

@push('scripts')
    <script>
        // Tabs
        document.querySelectorAll('.wn-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.wn-tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
            });
        });
    </script>
@endpush
