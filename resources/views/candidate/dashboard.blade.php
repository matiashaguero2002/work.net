@extends('layouts.app')

@section('title', 'Work.net - Mi panel')

@section('content')
    <div class="wn-bg"><div class="wn-bg-grid"></div></div>

    @include('partials.wn-navbar')

    <main class="wn-main wn-page-dashboard">

        @if (session('error'))
            <div class="wn-flash-error"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}</div>
        @endif
        @if (session('success'))
            <div class="wn-flash-success"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
        @endif

        {{-- HEADER (sin botones, sin emoji) --}}
        <header class="wn-header">
            <div class="wn-header-left">
                <h1>¡Hola, {{ auth()->user()->nombre ?? 'Candidato' }}!</h1>
                <p>Tenés <strong>{{ $newOffers }} ofertas nuevas</strong> y <strong>{{ $scheduledInterviews }} entrevistas</strong> programadas.</p>
            </div>
        </header>

        {{-- STATS --}}
        <section class="wn-stats">
            @foreach($stats as $stat)
                <div class="wn-stat-card">
                    <div class="wn-stat-icon{{ $stat['modifier'] ? ' '.$stat['modifier'] : '' }}"><i class="bi bi-{{ $stat['icon'] }}"></i></div>
                    <div class="wn-stat-info">
                        <div class="wn-stat-value">{{ $stat['value'] }}</div>
                        <div class="wn-stat-label">{{ $stat['label'] }}</div>
                    </div>
                </div>
            @endforeach
        </section>

        {{-- OFERTAS RECOMENDADAS --}}
        <section class="wn-section">
            <div class="wn-section-header">
                <h2 class="wn-section-title">
                    <i class="bi bi-stars"></i>
                    Ofertas recomendadas para vos
                </h2>
                <a href="{{ route('map.index') }}" class="wn-link">
                    Ver todas <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="wn-offers-grid">
                @foreach($recommendedOffers as $offer)
                    <article class="wn-offer-card">
                        @if($offer['isNew'])
                            <span class="wn-offer-badge-new">Nueva</span>
                        @endif
                        <div class="wn-offer-card-header">
                            <div class="wn-offer-logo">{{ $offer['companyInitials'] }}</div>
                            <div class="wn-offer-info">
                                <h3 class="wn-offer-title">{{ $offer['title'] }}</h3>
                                <p class="wn-offer-company">{{ $offer['company'] }}</p>
                            </div>
                        </div>
                        @if (!is_null($offer['matchScore']))
                            <div class="wn-offer-match">
                                <div class="wn-progress-bar wn-match-bar">
                                    <div class="wn-progress-fill" style="width: {{ $offer['matchScore'] }}%;"></div>
                                </div>
                                <span class="wn-offer-match-pct" @if($offer['matchScore'] >= 60) data-high="1" @endif>
                                    <i @if($offer['matchScore'] > 0) class="bi bi-stars" @else class="bi bi-slash-circle" @endif></i>
                                    @if ($offer['matchScore'] > 0)
                                        {{ $offer['matchScore'] }}% de afinidad
                                    @else
                                        Sin coincidencias
                                    @endif
                                </span>
                            </div>
                        @endif
                        <div class="wn-offer-badges">
                            <span class="wn-offer-badge"><i class="bi bi-geo-alt-fill"></i> {{ $offer['location'] }}</span>
                            <span class="wn-offer-badge"><i class="bi bi-laptop"></i> {{ $offer['modality'] }}</span>
                            <span class="wn-offer-badge"><i class="bi bi-clock"></i> {{ $offer['contractType'] }}</span>
                        </div>
                        <p class="wn-offer-description">{{ $offer['shortDescription'] }}</p>
                        @if (!empty($offer['matchReasons']))
                            <ul class="wn-offer-reasons">
                                @foreach($offer['matchReasons'] as $razon)
                                    <li><i class="bi bi-check2-circle"></i> {{ $razon }}</li>
                                @endforeach
                            </ul>
                        @endif
                        <div class="wn-offer-footer">
                            <span class="wn-offer-salary">
                                <i class="bi bi-cash-coin"></i> {{ $offer['salary'] }}
                            </span>
                            <a href="{{ route('map.index', ['lat' => $offer['coords'][0], 'lng' => $offer['coords'][1], 'zoom' => 17, 'offer' => $offer['offerId']]) }}" class="wn-offer-map-btn">
                                <i class="bi bi-geo-alt"></i> Ver en mapa
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- POSTULACIONES RECIENTES --}}
        <section class="wn-section">
            <div class="wn-section-header">
                <h2 class="wn-section-title">
                    <i class="bi bi-clock-history"></i>
                    Mis postulaciones recientes
                </h2>
                <a href="{{ route('candidate.applications.index') }}" class="wn-link">
                    Ver todas <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="wn-applications">
                @if(count($recentApplications))
                    @foreach($recentApplications as $application)
                        <a href="{{ $application['href'] }}" class="wn-application-item">
                        <div class="wn-application-logo">{{ $application['companyInitials'] }}</div>
                        <div class="wn-application-info">
                            <p class="wn-application-title">{{ $application['title'] }}</p>
                            <p class="wn-application-company">{{ $application['companyLine'] }}</p>
                        </div>
                        <span class="wn-application-status wn-status-{{ $application['statusClass'] }}">
                            <i class="bi bi-{{ $application['statusIcon'] }}"></i> {{ $application['statusLabel'] }}
                        </span>
                    </a>
                    @endforeach
                @else
                    <div class="wn-application-item">
                        <div class="wn-application-info">
                            <p class="wn-application-title">Aún no te postulaste a ninguna oferta</p>
                            <p class="wn-application-company">Explorá el mapa para empezar.</p>
                        </div>
                    </div>
                @endif
            </div>
        </section>

        {{-- WIDGETS --}}
        <div class="wn-widgets-grid">

            {{-- Perfil completado --}}
            <div class="wn-widget">
                <div class="wn-widget-header">
                    <i class="bi bi-person-badge"></i>
                    <span>Completá tu perfil</span>
                </div>

                <div class="wn-profile-progress">
                    <div class="wn-progress-info">
                        <span>Progreso</span>
                        <strong>{{ $profileProgress['percentage'] }}%</strong>
                    </div>
                    <div class="wn-progress-bar">
                        <div class="wn-progress-fill" style="width: {{ $profileProgress['percentage'] }}%;"></div>
                    </div>
                </div>

                <ul class="wn-profile-checklist">
                    @foreach($profileProgress['checklist'] as $item)
                        <li class="{{ $item['done'] ? 'done' : 'pending' }}"><i class="bi bi-{{ $item['done'] ? 'check-circle-fill' : 'circle' }}"></i> <span>{{ $item['label'] }}</span></li>
                    @endforeach
                </ul>

                <a href="{{ route('candidate.profile') }}" class="btn-wn-primary" style="margin-top: 4px;">
                    <i class="bi bi-pencil"></i> Completar perfil
                </a>
            </div>

            {{-- Próximas entrevistas --}}
            <div class="wn-widget">
                <div class="wn-widget-header">
                    <i class="bi bi-calendar-event"></i>
                    <span>Próximas entrevistas</span>
                </div>

                @foreach($upcomingInterviews as $interview)
                    <div class="wn-interview-item">
                        <div class="wn-interview-date">
                            <span class="day">{{ $interview['day'] }}</span>
                            <span class="month">{{ $interview['month'] }}</span>
                        </div>
                        <div class="wn-interview-info">
                            <p class="wn-interview-title">{{ $interview['title'] }}</p>
                            <p class="wn-interview-meta">
                                <i class="bi bi-{{ $interview['timeIcon'] }}"></i> {{ $interview['time'] }}
                            </p>
                            <p class="wn-interview-meta">
                                <i class="bi bi-{{ $interview['placeIcon'] }}"></i> {{ $interview['place'] }}
                            </p>
                        </div>
                    </div>
                @endforeach

                <a href="{{ route('interviews.index') }}" class="btn-wn-secondary" style="margin-top: 4px;">
                    <i class="bi bi-calendar3"></i> Ver todas las entrevistas
                </a>
            </div>
        </div>

        {{-- OFERTAS GUARDADAS --}}
        <section class="wn-section" style="margin-top: 32px;">
            <div class="wn-section-header">
                <h2 class="wn-section-title">
                    <i class="bi bi-bookmark-fill"></i>
                    Ofertas guardadas
                </h2>
                <a href="{{ route('candidate.saved.index') }}" class="wn-link">
                    Ver todas <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="wn-widget">
                <div class="wn-saved-list">
                    @if(count($savedOffers))
                        @foreach($savedOffers as $saved)
                            <div class="wn-saved-item">
                                <a href="{{ route('map.index', ['lat' => $saved['coords'][0], 'lng' => $saved['coords'][1], 'zoom' => 17, 'offer' => $saved['offerId']]) }}" class="wn-saved-link">
                                    <div class="wn-saved-dot"></div>
                                    <div class="wn-saved-info">
                                        <p class="wn-saved-title">{{ $saved['title'] }}</p>
                                        <p class="wn-saved-company">{{ $saved['company'] }}</p>
                                    </div>
                                </a>
                                <form method="POST" action="{{ route('candidate.saved.destroy', $saved['offerId']) }}"
                                      class="wn-saved-remove-form" data-offer-title="{{ $saved['title'] }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="wn-saved-remove" title="Quitar de guardadas" type="submit">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    @else
                        <div class="wn-saved-item">
                            <div class="wn-saved-info">
                                <p class="wn-saved-title">Aún no guardaste ninguna oferta</p>
                                <p class="wn-saved-company">Tocá "Guardar" en el mapa.</p>
                            </div>
                        </div>
                    @endif
                </div>
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

        // Quitar de guardadas (RF-08) desde el dashboard:
        // confirmación + fade, luego el form DELETE borra la fila en la BD.
        document.querySelectorAll('.wn-page-dashboard .wn-saved-remove-form button').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const form = btn.closest('form');
                if (!confirm('¿Querés quitar "' + (form.dataset.offerTitle || 'esta oferta') + '" de tus guardadas?')) return;

                const item = form.closest('.wn-saved-item');
                item.style.transition = 'opacity 0.3s ease';
                item.style.opacity = '0';
                setTimeout(() => form.submit(), 280);
            });
        });
    </script>
@endpush
