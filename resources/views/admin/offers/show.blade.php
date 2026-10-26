@extends('layouts.app')

@section('title', 'Work.net - Detalle de oferta')

@section('content')
    <div class="wn-bg"><div class="wn-bg-grid"></div></div>

    @include('partials.wn-navbar')

    <main class="wn-main wn-page-admin wn-page-admin-detail">

        <header class="wn-header">
            <div class="wn-header-left">
                <h1>Detalle de oferta <span class="wn-admin-badge">Supervisión</span></h1>
                <p>Revisá la información de la oferta, sus reportes y aplicá acciones de moderación.</p>
            </div>
            <div class="wn-header-actions">
                <a href="{{ route('admin.offers.index') }}" class="btn-wn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al listado
                </a>
            </div>
        </header>

        {{-- ALERTA DE REPORTES --}}
        <div class="wn-alert-danger">
            <div class="wn-alert-danger-icon"><i class="bi bi-flag-fill"></i></div>
            <div class="wn-alert-danger-body">
                <h3 class="wn-alert-danger-title">Esta oferta tiene {{ count($reports) }} reportes activos</h3>
                <p class="wn-alert-danger-text">Los usuarios han reportado esta oferta por posibles incumplimientos de las normas de la plataforma. Revisá los motivos y decidí si pausarla o eliminarla.</p>
                <div class="wn-alert-danger-actions">
                    <button type="button" class="btn-wn-warning" data-action="pause">
                        <i class="bi bi-pause-fill"></i> Pausar oferta
                    </button>
                    <button type="button" class="btn-wn-danger" data-action="delete">
                        <i class="bi bi-trash"></i> Eliminar oferta
                    </button>
                </div>
            </div>
        </div>

        {{-- HERO DE LA OFERTA --}}
        <section class="wn-offer-hero {{ $offer['reported'] ? 'status-reported' : '' }}">
            <div class="wn-offer-hero-logo">{{ $offer['initials'] }}</div>
            <div class="wn-offer-hero-info">
                <h2 class="wn-offer-hero-title">{{ $offer['title'] }}</h2>
                <p class="wn-offer-hero-company"><i class="bi bi-building"></i> {{ $offer['company'] }} · {{ $offer['published'] }}</p>
                <div class="wn-offer-hero-badges">
                    @foreach ($offer['heroBadges'] as $badge)
                        <span class="wn-role-badge {{ $badge['class'] }}"><i class="bi bi-{{ $badge['icon'] }}"></i> {{ $badge['text'] }}</span>
                    @endforeach
                </div>
            </div>
            <div class="wn-header-actions">
                <a href="#" class="btn-wn-secondary">
                    <i class="bi bi-box-arrow-up-right"></i> Ver oferta pública
                </a>
            </div>
        </section>

        {{-- STATS DE LA OFERTA --}}
        <section class="wn-info-card" style="margin-bottom: 24px;">
            <div class="wn-info-card-header">
                <i class="bi bi-bar-chart-fill"></i>
                <span>Métricas de la oferta</span>
            </div>
            <div class="wn-offer-stats">
                @foreach ($offer['metrics'] as $metric)
                    <div class="wn-offer-stat">
                        <div class="wn-offer-stat-value">{{ $metric['value'] }}</div>
                        <div class="wn-offer-stat-label">{{ $metric['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- INFO GRID --}}
        <div class="wn-info-grid">

            {{-- Columna izquierda: descripción + reportes --}}
            <div style="display: flex; flex-direction: column; gap: 16px;">

                {{-- Descripción --}}
                <div class="wn-info-card">
                    <div class="wn-info-card-header">
                        <i class="bi bi-file-text-fill"></i>
                        <span>Descripción de la oferta</span>
                    </div>
                    <div class="wn-offer-description">
                        {!! $description !!}
                    </div>
                </div>

                {{-- Reportes --}}
                <div class="wn-info-card">
                    <div class="wn-info-card-header">
                        <i class="bi bi-flag-fill" style="color: var(--wn-danger);"></i>
                        <span style="color: var(--wn-danger);">Reportes recibidos ({{ count($reports) }})</span>
                    </div>
                    <div class="wn-reports">
                        @foreach ($reports as $report)
                            <div class="wn-report-item">
                                <div class="wn-report-icon"><i class="bi bi-flag-fill"></i></div>
                                <div class="wn-report-body">
                                    <p class="wn-report-reason">{{ $report['reason'] }}</p>
                                    <p class="wn-report-meta">
                                        <span><i class="bi bi-person-fill"></i> {{ $report['by'] }}</span>
                                        <span><i class="bi bi-clock"></i> {{ $report['when'] }}</span>
                                    </p>
                                    <p class="wn-report-description">{{ $report['text'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>

            {{-- Columna derecha: info + acciones --}}
            <div style="display: flex; flex-direction: column; gap: 16px;">

                {{-- Info de la oferta --}}
                <div class="wn-info-card">
                    <div class="wn-info-card-header">
                        <i class="bi bi-info-circle-fill"></i>
                        <span>Información de la oferta</span>
                    </div>
                    @foreach ($offerInfo as $row)
                        <div class="wn-info-row">
                            <span class="wn-info-label">{{ $row['label'] }}</span>
                            <span class="wn-info-value">{{ $row['value'] }}@if(isset($row['icon'])) <i class="bi bi-{{ $row['icon'] }}" style="{{ $row['iconStyle'] }}"></i>@endif</span>
                        </div>
                    @endforeach
                </div>

                {{-- Info del empleador --}}
                <div class="wn-info-card">
                    <div class="wn-info-card-header">
                        <i class="bi bi-building-fill"></i>
                        <span>Empleador</span>
                    </div>
                    @foreach ($employerInfo as $row)
                        <div class="wn-info-row">
                            <span class="wn-info-label">{{ $row['label'] }}</span>
                            <span class="wn-info-value" @if(isset($row['style']))style="{{ $row['style'] }}"@endif>{{ $row['value'] }}@if(isset($row['icon'])) <i class="bi bi-{{ $row['icon'] }}" style="{{ $row['iconStyle'] }}"></i>@endif</span>
                        </div>
                    @endforeach
                    <a href="{{ route('admin.users.show', 2) }}" class="btn-wn-secondary" style="width: 100%; margin-top: 12px;">
                        <i class="bi bi-person-lines-fill"></i> Ver perfil del empleador
                    </a>
                </div>

                {{-- Acciones del admin --}}
                <div class="wn-info-card">
                    <div class="wn-info-card-header">
                        <i class="bi bi-shield-lock-fill"></i>
                        <span>Acciones de moderación</span>
                    </div>
                    <div class="wn-admin-actions">
                        <button type="button" class="btn-wn-secondary">
                            <i class="bi bi-eye-slash"></i> Ocultar de búsquedas
                        </button>
                        <button type="button" class="btn-wn-warning" data-action="pause">
                            <i class="bi bi-pause-fill"></i> Pausar oferta
                        </button>
                        <button type="button" class="btn-wn-danger" data-action="delete">
                            <i class="bi bi-trash"></i> Eliminar oferta
                        </button>
                    </div>
                </div>

            </div>
        </div>

        {{-- ZONA PELIGROSA --}}
        <div class="wn-danger-zone">
            <div class="wn-danger-zone-header">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span>Zona peligrosa</span>
            </div>
            <p>Las siguientes acciones son permanentes y no se pueden deshacer. Procedé con precaución.</p>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <button type="button" class="btn-wn-danger" data-action="delete">
                    <i class="bi bi-trash"></i> Eliminar oferta permanentemente
                </button>
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
                <p id="actionModalText">Esta acción se aplicará a la oferta.</p>
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
@endsection
