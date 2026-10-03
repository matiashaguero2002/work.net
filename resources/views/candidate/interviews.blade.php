@extends('layouts.app')

@section('title', 'Work.net - Mis entrevistas')

@section('content')
    <div class="wn-bg"><div class="wn-bg-grid"></div></div>

    @include('partials.wn-navbar')

    <main class="wn-main">

        <header class="wn-header">
            <div class="wn-header-left">
                <h1>Mis entrevistas</h1>
                <p>Tenés <strong>{{ $upcomingCount }} entrevistas próximas</strong> y {{ $pastCount }} ya realizadas.</p>
            </div>
        </header>

        {{-- Tabs --}}
        <div class="wn-tabs">
            <button class="wn-tab active" type="button">Próximas <span class="count">{{ $upcomingCount }}</span></button>
            <button class="wn-tab" type="button">Realizadas <span class="count">{{ $pastCount }}</span></button>
            <button class="wn-tab" type="button">Canceladas <span class="count">{{ $cancelledCount }}</span></button>
        </div>

        {{-- Lista --}}
        <div class="wn-interviews" data-map-url="{{ route('map.index') }}">

            @foreach($interviews as $interview)
                <article class="wn-interview-card {{ $interview['status'] !== 'scheduled' ? $interview['status'] : '' }}" data-status="{{ $interview['status'] }}">
                    <div class="wn-date-block {{ $interview['status'] !== 'scheduled' ? $interview['status'] : '' }}">
                        <span class="day">{{ $interview['day'] }}</span>
                        <span class="month">{{ $interview['month'] }}</span>
                        <span class="year">{{ $interview['year'] }}</span>
                    </div>

                    <div class="wn-interview-content">
                        <div class="wn-interview-header">
                            <h3 class="wn-interview-title">{{ $interview['title'] }}</h3>
                            <span class="wn-interview-status wn-status-{{ $interview['status'] }}">
                                <i class="bi bi-{{ $interview['statusIcon'] }}"></i> {{ $interview['statusLabel'] }}
                            </span>
                        </div>

                        <div class="wn-interview-company">
                            <div class="wn-company-logo">{{ $interview['companyInitials'] }}</div>
                            <div>
                                <p class="wn-company-name">{{ $interview['company'] }}</p>
                                <p class="wn-company-position">{{ $interview['position'] }}</p>
                            </div>
                        </div>

                        <div class="wn-interview-meta-grid">
                            <div class="wn-meta-item">
                                <i class="bi bi-clock"></i>
                                <span><strong>{{ $interview['timeStart'] }}</strong> - {{ $interview['timeEnd'] }}</span>
                            </div>
                            <div class="wn-meta-item">
                                <i class="bi bi-{{ $interview['modalityIcon'] }}"></i>
                                <span><strong>{{ $interview['modality'] }}</strong></span>
                            </div>
                            <div class="wn-meta-item">
                                <i class="bi bi-person-badge"></i>
                                <span>Entrevistador: <strong>{{ $interview['interviewer'] }}</strong></span>
                            </div>
                            <div class="wn-meta-item">
                                <i class="bi bi-hourglass-split"></i>
                                <span>Duración: <strong>{{ $interview['duration'] }}</strong></span>
                            </div>
                        </div>

                        @if($interview['notes'])
                            <div class="wn-interview-notes">
                                <strong>Notas del empleador</strong>
                                {{ $interview['notes'] }}
                            </div>
                        @endif
                    </div>

                    <div class="wn-interview-map-col">
                        <div class="wn-mini-map" data-lat="{{ $interview['coords'][0] }}" data-lng="{{ $interview['coords'][1] }}" data-offer-id="{{ $interview['offerId'] }}">
                            <div class="map-overlay">
                                <span class="map-overlay-text">
                                    <i class="bi bi-arrows-fullscreen"></i> Ver en mapa grande
                                </span>
                            </div>
                        </div>
                        <div class="wn-interview-actions">
                            <a href="{{ route('map.index', ['lat' => $interview['coords'][0], 'lng' => $interview['coords'][1], 'zoom' => 17, 'offer' => $interview['offerId']]) }}" class="wn-action-btn wn-action-btn-primary">
                                <i class="bi bi-geo-alt-fill"></i> Ver en mapa
                            </a>
                            @if($interview['status'] === 'scheduled')
                                <button class="wn-action-btn wn-action-btn-danger" type="button">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </button>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach

        </div>

    </main>
@endsection
