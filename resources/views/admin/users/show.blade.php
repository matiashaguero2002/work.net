@extends('layouts.app')

@section('title', 'Work.net - Detalle de usuario')

@section('content')
    <div class="wn-bg"><div class="wn-bg-grid"></div></div>

    @include('partials.wn-navbar')

    <main class="wn-main wn-page-admin">

        <header class="wn-header">
            <div class="wn-header-left">
                <h1>Detalle de usuario <span class="wn-admin-badge">Admin</span></h1>
                <p>Información completa, actividad y acciones administrativas.</p>
            </div>
            <div class="wn-header-actions">
                <a href="{{ route('admin.users.index') }}" class="btn-wn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al listado
                </a>
            </div>
        </header>

        {{-- HERO DEL USUARIO --}}
        <section class="wn-user-hero">
            <div class="wn-user-hero-avatar">{{ $user['initials'] }}</div>
            <div class="wn-user-hero-info">
                <h2 class="wn-user-hero-name">{{ $user['name'] }}</h2>
                <p class="wn-user-hero-email"><i class="bi bi-envelope-fill"></i> {{ $user['email'] }}</p>
                <div class="wn-user-hero-badges">
                    <span class="wn-role-badge {{ $user['role'] }}">{{ $user['roleLabel'] }}</span>
                    <span class="wn-status-badge {{ $user['status'] }}"><i class="bi bi-{{ $user['statusIcon'] }}"></i> {{ $user['statusLabel'] }}</span>
                </div>
            </div>
            <div class="wn-header-actions">
                <button type="button" class="btn-wn-secondary">
                    <i class="bi bi-pencil"></i> Editar
                </button>
                <button type="button" class="btn-wn-danger" data-action="suspend">
                    <i class="bi bi-slash-circle"></i> Suspender
                </button>
            </div>
        </section>

        {{-- STATS DEL USUARIO --}}
        <section class="wn-info-card" style="margin-bottom: 24px;">
            <div class="wn-info-card-header">
                <i class="bi bi-bar-chart-fill"></i>
                <span>Resumen de actividad</span>
            </div>
            <div class="wn-user-stats">
                @foreach ($userStats as $stat)
                    <div class="wn-user-stat">
                        <div class="wn-user-stat-value">{{ $stat['value'] }}</div>
                        <div class="wn-user-stat-label">{{ $stat['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- INFO GRID --}}
        <div class="wn-info-grid">

            {{-- Datos personales --}}
            <div class="wn-info-card">
                <div class="wn-info-card-header">
                    <i class="bi bi-person-fill"></i>
                    <span>Datos personales</span>
                </div>
                @foreach ($personalRows as $row)
                    <div class="wn-info-row">
                        <span class="wn-info-label">{{ $row['label'] }}</span>
                        <span class="wn-info-value {{ ($row['empty'] ?? false) ? 'empty' : '' }}">{{ $row['value'] }}@if(isset($row['icon'])) <i class="bi bi-{{ $row['icon'] }}" style="{{ $row['iconStyle'] }}"></i>@endif</span>
                    </div>
                @endforeach
            </div>

            {{-- Información de cuenta --}}
            <div class="wn-info-card">
                <div class="wn-info-card-header">
                    <i class="bi bi-shield-check"></i>
                    <span>Información de cuenta</span>
                </div>
                @foreach ($accountRows as $row)
                    <div class="wn-info-row">
                        <span class="wn-info-label">{{ $row['label'] }}</span>
                        <span class="wn-info-value {{ ($row['empty'] ?? false) ? 'empty' : '' }}">{{ $row['value'] }}@if(isset($row['icon'])) <i class="bi bi-{{ $row['icon'] }}" style="{{ $row['iconStyle'] }}"></i>@endif</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ACTIVIDAD + OFERTAS (layout 2 columnas) --}}
        <div class="wn-info-grid">

            {{-- Actividad del usuario --}}
            <div class="wn-info-card">
                <div class="wn-info-card-header">
                    <i class="bi bi-activity"></i>
                    <span>Actividad reciente</span>
                </div>
                <div class="wn-activity">
                    @foreach ($activity as $item)
                        <div class="wn-activity-item">
                            <div class="wn-activity-icon {{ $item['modifier'] }}"><i class="bi bi-{{ $item['icon'] }}"></i></div>
                            <div class="wn-activity-text">
                                @switch($item['special'] ?? '')
                                    @case('applied')
                                        Se postuló a <strong>{{ explode('|', $item['action'])[0] }}</strong> {{ explode('|', $item['action'])[1] }}
                                        @break
                                    @case('saved')
                                        Guardó la oferta <strong>{{ explode('|', $item['action'])[0] }}</strong>.
                                        @break
                                    @case('suffix')
                                        {{ $item['action'] }} <strong>{{ $item['actor'] }}</strong>.
                                        @break
                                    @default
                                        <strong>{{ $item['actor'] }}</strong> {{ $item['action'] }}
                                @endswitch
                                <div class="wn-activity-time"><i class="bi bi-clock"></i> {{ $item['time'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Últimas postulaciones --}}
            <div class="wn-info-card">
                <div class="wn-info-card-header">
                    <i class="bi bi-file-earmark-text-fill"></i>
                    <span>Últimas postulaciones</span>
                </div>
                <div class="wn-offers-list">
                    @foreach ($applications as $app)
                        <a href="#" class="wn-offer-mini">
                            <div class="wn-offer-mini-logo">{{ $app['logo'] }}</div>
                            <div class="wn-offer-mini-info">
                                <p class="wn-offer-mini-title">{{ $app['title'] }}</p>
                                <p class="wn-offer-mini-meta">{{ $app['meta'] }}</p>
                            </div>
                            <span class="wn-offer-mini-status {{ $app['statusClass'] }}">{{ $app['status'] }}</span>
                        </a>
                    @endforeach
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
                    <i class="bi bi-trash"></i> Eliminar usuario
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
                <p id="actionModalText">Esta acción se aplicará al usuario.</p>
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
