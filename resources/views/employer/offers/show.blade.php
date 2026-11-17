@extends('layouts.app')

@section('title', 'Work.net - Detalle de oferta')

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
                <div class="wn-header-logo">{{ $offer['logo'] }}</div>
                <div class="wn-header-info">
                    <h1>{{ $offer['title'] }}</h1>
                    <p class="wn-header-company">{{ $offer['company'] }} · {{ $offer['publishedLine'] }}</p>
                    <div class="wn-header-badges">
                        <span class="wn-badge-pill status-{{ $offer['status'] }}"><i class="bi bi-circle-fill"></i> {{ $offer['statusLabel'] }}</span>
                        <span class="wn-badge-pill"><i class="bi bi-geo-alt-fill"></i> {{ $offer['location'] }}</span>
                        <span class="wn-badge-pill"><i class="bi bi-laptop"></i> {{ $offer['modality'] }}</span>
                        <span class="wn-badge-pill"><i class="bi bi-clock"></i> {{ $offer['contractType'] }}</span>
                        <span class="wn-badge-pill"><i class="bi bi-cash-stack"></i> {{ $offer['salary'] }}</span>
                    </div>
                </div>
            </div>
            <div class="wn-header-actions">
                <a href="{{ route('employer.offers.index') }}" class="btn-wn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
                <a href="{{ route('employer.offers.edit', $offer['id']) }}" class="btn-wn-secondary">
                    <i class="bi bi-pencil"></i> Editar
                </a>
                @if ($estadoOferta === 1)
                    <form method="POST" action="{{ route('employer.offers.pause', $offer['id']) }}" data-confirm="¿Pausar esta oferta? Dejará de aparecer en el mapa hasta que la reactives.">
                        @csrf
                        <button class="btn-wn-warning" type="submit">
                            <i class="bi bi-pause"></i> Pausar
                        </button>
                    </form>
                @elseif ($estadoOferta === 2)
                    <form method="POST" action="{{ route('employer.offers.reactivate', $offer['id']) }}">
                        @csrf
                        <button class="btn-wn-warning" type="submit">
                            <i class="bi bi-play"></i> Reactivar
                        </button>
                    </form>
                @endif
                @if ($estadoOferta !== 3)
                    <form method="POST" action="{{ route('employer.offers.close', $offer['id']) }}" data-confirm="¿Cerrar esta oferta? Ya no aparecerá en el mapa ni aceptará postulaciones.">
                        @csrf
                        <button class="btn-wn-danger" type="submit">
                            <i class="bi bi-x-lg"></i> Cerrar
                        </button>
                    </form>
                @endif
            </div>
        </header>

        <div class="wn-detail-grid">

            <div class="wn-detail-main">

                <div class="wn-card">
                    <h2 class="wn-card-title"><i class="bi bi-file-text"></i> Descripción del puesto</h2>
                    <p class="wn-card-text">{{ $offer['description'] }}</p>
                </div>

                <div class="wn-card">
                    <h2 class="wn-card-title"><i class="bi bi-list-check"></i> Requisitos</h2>
                    <p class="wn-card-text" style="margin-bottom: 16px;">
                        <strong>Experiencia:</strong> {{ $offer['experience'] }}
                    </p>
                    <p class="wn-card-text" style="margin-bottom: 16px;">
                        <strong>Formación:</strong> {{ $offer['education'] }}
                    </p>
                    <div class="wn-requirements">
                        @foreach($offer['requirements'] as $req)
                            <span class="wn-requirement"><i class="bi bi-check-circle-fill"></i> {{ $req }}</span>
                        @endforeach
                    </div>
                    @if(!empty($offer['softSkills']))
                        <p class="wn-card-text" style="margin: 16px 0 8px;"><strong>Habilidades blandas (deseables):</strong></p>
                        <div class="wn-requirements">
                            @foreach($offer['softSkills'] as $soft)
                                <span class="wn-requirement"><i class="bi bi-plus-circle"></i> {{ $soft }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if(!empty($offer['benefits']))
                    <div class="wn-card">
                        <h2 class="wn-card-title"><i class="bi bi-star"></i> Beneficios</h2>
                        <div class="wn-requirements">
                            @foreach($offer['benefits'] as $benefit)
                                <span class="wn-requirement"><i class="bi bi-{{ $benefit['icon'] }}"></i> {{ $benefit['label'] }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="wn-card">
                    <h2 class="wn-card-title"><i class="bi bi-geo-alt"></i> Ubicación</h2>
                    <p class="wn-card-text" style="margin-bottom: 12px;">
                        <i class="bi bi-pin-map-fill" style="color: var(--wn-accent);"></i>
                        {{ $offer['address'] }}
                    </p>
                    <div class="wn-detail-map" id="detailMap"></div>
                </div>

            </div>

            <aside class="wn-detail-sidebar">

                <div class="wn-sidebar-card">
                    <h3 class="wn-sidebar-card-title">Estadísticas</h3>
                    <div class="wn-sidebar-stats">
                        @foreach($offer['stats'] as $stat)
                            <div class="wn-sidebar-stat">
                                <span class="wn-sidebar-stat-label"><i class="bi {{ $stat['icon'] }}"></i> {{ $stat['label'] }}</span>
                                <span class="wn-sidebar-stat-value">{{ $stat['value'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="wn-sidebar-card">
                    <h3 class="wn-sidebar-card-title">Acciones</h3>
                    <div class="wn-sidebar-actions">
                        <a href="{{ route('employer.applicants.index', ['oferta' => $offer['id']]) }}" class="btn-wn-primary">
                            <i class="bi bi-inbox"></i> Ver todos los postulantes
                        </a>
                        <a href="{{ route('employer.offers.edit', $offer['id']) }}" class="btn-wn-secondary">
                            <i class="bi bi-pencil"></i> Editar oferta
                        </a>
                    </div>
                </div>

                <div class="wn-sidebar-card">
                    <h3 class="wn-sidebar-card-title">Postulantes recientes</h3>
                    <div class="wn-applicants-list">
                        @forelse($recentApplicants as $applicant)
                            <a href="{{ route('employer.applicants.show', $applicant['id']) }}" class="wn-applicant-item">
                                <div class="wn-applicant-avatar">{{ $applicant['initials'] }}</div>
                                <div class="wn-applicant-info">
                                    <p class="wn-applicant-name">{{ $applicant['name'] }}</p>
                                    <p class="wn-applicant-date">{{ $applicant['date'] }}</p>
                                </div>
                                <span class="wn-applicant-match">{{ $applicant['match'] }}%</span>
                            </a>
                        @empty
                            <p style="font-size: 0.8rem; color: var(--wn-text-muted); margin: 0;">
                                Todavía no hay postulaciones para esta oferta.
                            </p>
                        @endforelse
                    </div>
                    @if($offer['applicantsCount'] > 0)
                        <a href="{{ route('employer.applicants.index', ['oferta' => $offer['id']]) }}" class="btn-wn-secondary" style="width: 100%; margin-top: 12px; justify-content: center;">
                            Ver los {{ $offer['applicantsCount'] }} postulantes
                        </a>
                    @endif
                </div>

            </aside>

        </div>

    </main>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const detailCoords = [{{ $offer['coords'][0] }}, {{ $offer['coords'][1] }}];
        const detailMap = L.map('detailMap', { zoomControl: true, attributionControl: false }).setView(detailCoords, 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { subdomains: ['a', 'b', 'c'], maxZoom: 19 }).addTo(detailMap);
        L.marker(detailCoords).addTo(detailMap);

        // Confirmaciones de pausar / cerrar.
        document.querySelectorAll('form[data-confirm]').forEach((form) => {
            form.addEventListener('submit', (e) => {
                if (!window.confirm(form.dataset.confirm)) e.preventDefault();
            });
        });
    </script>
@endpush
