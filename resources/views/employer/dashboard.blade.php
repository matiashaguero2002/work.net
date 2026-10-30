@extends('layouts.app')

@section('title', 'Work.net - Panel de empresa')

@section('content')
    <div class="wn-bg"><div class="wn-bg-grid"></div></div>

    @include('partials.wn-navbar')

    <main class="wn-main wn-page-employer">
        @if (session('error'))
            <div class="wn-flash-error"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}</div>
        @endif
        @if (session('success'))
            <div class="wn-flash-success"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
        @endif

        <header class="wn-header">
            <div class="wn-header-left">
                <h1>¡Hola, {{ auth()->user()->empleador?->nombre_empresa ?? 'Mi empresa' }}!</h1>
                <p>Tenés <strong>{{ $stats[0]['value'] }} ofertas activas</strong> con <strong>{{ $stats[1]['value'] }} postulaciones</strong> pendientes de revisión.</p>
            </div>
            <div class="wn-header-actions">
                <a href="{{ route('employer.offers.index') }}" class="btn-wn-secondary">
                    <i class="bi bi-list-ul"></i> Ver todas las ofertas
                </a>
                <a href="{{ route('employer.offers.create') }}" class="btn-wn-primary">
                    <i class="bi bi-plus-lg"></i> Publicar oferta
                </a>
            </div>
        </header>

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

        <section class="wn-section">
            <div class="wn-section-header">
                <h2 class="wn-section-title">
                    <i class="bi bi-briefcase"></i>
                    Ofertas recientes
                </h2>
                <a href="{{ route('employer.offers.index') }}" class="wn-link">
                    Ver todas <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="wn-offers-grid">
                @foreach($recentOffers as $offer)
                    <article class="wn-offer-card">
                        <span class="wn-offer-status {{ $offer['status'] }}">{{ $offer['statusLabel'] }}</span>
                        <div class="wn-offer-card-header">
                            <div class="wn-offer-logo">{{ $offer['logo'] }}</div>
                            <div class="wn-offer-info">
                                <h3 class="wn-offer-title">{{ $offer['title'] }}</h3>
                                <p class="wn-offer-company">{{ $offer['publishedLine'] }}</p>
                            </div>
                        </div>
                        <div class="wn-offer-badges">
                            <span class="wn-offer-badge"><i class="bi bi-geo-alt-fill"></i> {{ $offer['location'] }}</span>
                            <span class="wn-offer-badge"><i class="bi bi-{{ $offer['modalityIcon'] ?? 'laptop' }}"></i> {{ $offer['modality'] }}</span>
                            <span class="wn-offer-badge"><i class="bi bi-clock"></i> {{ $offer['contractType'] }}</span>
                        </div>
                        <div class="wn-offer-stats">
                            <div class="wn-offer-stat">
                                <i class="bi bi-inbox-fill"></i>
                                <strong>{{ $offer['applicants'] }}</strong> postulantes
                            </div>
                            <div class="wn-offer-stat">
                                <i class="bi bi-eye-fill"></i>
                                <strong>{{ $offer['views'] }}</strong> vistas
                            </div>
                        </div>
                        <div class="wn-offer-actions">
                            @if($offer['status'] === 'paused')
                                <a href="#" class="wn-offer-action primary">
                                    <i class="bi bi-play"></i> Reactivar
                                </a>
                            @else
                                <a href="{{ route('employer.applicants.index') }}" class="wn-offer-action primary">
                                    <i class="bi bi-inbox"></i> Ver postulantes
                                </a>
                            @endif
                            <a href="{{ route('employer.offers.edit', $offer['id']) }}" class="wn-offer-action secondary">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="wn-section">
            <div class="wn-section-header">
                <h2 class="wn-section-title">
                    <i class="bi bi-inbox"></i>
                    Postulaciones recientes
                </h2>
                <a href="{{ route('employer.applicants.index') }}" class="wn-link">
                    Ver todas <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="wn-applications">
                @foreach($recentApplications as $application)
                    <a href="#" class="wn-application-item">
                        <div class="wn-app-avatar">{{ $application['initials'] }}</div>
                        <div class="wn-app-info">
                            <p class="wn-app-name">{{ $application['name'] }}</p>
                            <p class="wn-app-position">{{ $application['positionLine'] }}</p>
                        </div>
                        <span class="wn-app-meta">
                            <i class="bi bi-star-fill"></i> {{ $application['match'] }}% match
                        </span>
                    </a>
                @endforeach
            </div>
        </section>

        <div class="wn-widgets-grid">
            <div class="wn-widget">
                <div class="wn-widget-header">
                    <i class="bi bi-building-gear"></i>
                    <span>Completá el perfil de tu empresa</span>
                </div>

                <div class="wn-profile-progress">
                    <div class="wn-progress-info">
                        <span>Progreso</span>
                        <strong>{{ $companyProgress['percentage'] }}%</strong>
                    </div>
                    <div class="wn-progress-bar">
                        <div class="wn-progress-fill" style="width: {{ $companyProgress['percentage'] }}%;"></div>
                    </div>
                </div>

                <ul class="wn-profile-checklist">
                    @foreach($companyProgress['checklist'] as $item)
                        <li class="{{ $item['done'] ? 'done' : 'pending' }}"><i class="bi bi-{{ $item['done'] ? 'check-circle-fill' : 'circle' }}"></i> <span>{{ $item['label'] }}</span></li>
                    @endforeach
                </ul>

                <a href="#" class="btn-wn-primary" style="margin-top: 4px;">
                    <i class="bi bi-pencil"></i> Completar empresa
                </a>
            </div>

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
                        </div>
                    </div>
                @endforeach

                <a href="#" class="btn-wn-secondary" style="margin-top: 4px;">
                    <i class="bi bi-calendar3"></i> Ver todas las entrevistas
                </a>
            </div>
        </div>

    </main>
@endsection
