@extends('layouts.app')

@section('title', 'Work.net - Panel de administración')

@section('content')
    <div class="wn-bg"><div class="wn-bg-grid"></div></div>

    @include('partials.wn-navbar')

    <main class="wn-main wn-page-admin">

        <header class="wn-header">
            <div class="wn-header-left">
                <h1>Panel de administración <span class="wn-admin-badge">Admin</span></h1>
                <p>Tenés <strong>{{ $pendingUsers }} usuarios pendientes</strong> de aprobación y <strong>{{ $pendingReports }} reportes</strong> sin revisar.</p>
            </div>
            <div class="wn-header-actions">
                <a href="{{ route('admin.users.index') }}" class="btn-wn-secondary">
                    <i class="bi bi-people"></i> Gestionar usuarios
                </a>
                <a href="{{ route('admin.reports.index') }}" class="btn-wn-primary">
                    <i class="bi bi-flag"></i> Ver reportes
                </a>
            </div>
        </header>

        {{-- STATS --}}
        <section class="wn-stats" aria-label="Resumen general">
            @foreach ($stats as $stat)
                <div class="wn-stat-card">
                    <div class="wn-stat-icon {{ $stat['modifier'] }}"><i class="bi bi-{{ $stat['icon'] }}"></i></div>
                    <div class="wn-stat-info">
                        <div class="wn-stat-value">{{ $stat['value'] }}</div>
                        <div class="wn-stat-label">{{ $stat['label'] }}</div>
                    </div>
                </div>
            @endforeach
        </section>

        {{-- DISTRIBUCIÓN POR ROL --}}
        <section class="wn-section">
            <div class="wn-section-header">
                <h2 class="wn-section-title">
                    <i class="bi bi-pie-chart"></i>
                    Distribución por rol
                </h2>
            </div>
            <div class="wn-role-grid">
                @foreach ($roleDistribution as $role)
                    <div class="wn-role-card">
                        <div class="wn-role-header {{ $role['key'] }}">
                            <i class="bi bi-{{ $role['icon'] }}"></i>
                            <span>{{ $role['label'] }}</span>
                        </div>
                        <div class="wn-role-value">{{ $role['value'] }}</div>
                        <div class="wn-role-label">{{ $role['share'] }}</div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- WIDGETS: ÚLTIMOS USUARIOS + ACTIVIDAD --}}
        <div class="wn-widgets-grid">

            {{-- Últimos usuarios registrados --}}
            <div class="wn-widget">
                <div class="wn-widget-header">
                    <i class="bi bi-person-plus-fill"></i>
                    <span>Últimos usuarios registrados</span>
                </div>

                <div class="wn-table-wrap">
                    <table class="wn-table">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentUsers as $user)
                                <tr>
                                    <td>
                                        <div class="wn-table-user">
                                            <div class="wn-table-avatar">{{ $user['initials'] }}</div>
                                            <div>
                                                <p class="wn-table-name">{{ $user['name'] }}</p>
                                                <p class="wn-table-email">{{ $user['email'] }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="wn-role-badge {{ $user['role'] }}">{{ $user['roleLabel'] }}</span></td>
                                    <td><span class="wn-status-badge {{ $user['status'] }}"><i class="bi bi-{{ $user['statusIcon'] }}"></i> {{ $user['statusLabel'] }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <a href="{{ route('admin.users.index') }}" class="btn-wn-secondary" style="align-self: flex-start;">
                    <i class="bi bi-people"></i> Ver todos los usuarios
                </a>
            </div>

            {{-- Actividad reciente --}}
            <div class="wn-widget">
                <div class="wn-widget-header">
                    <i class="bi bi-activity"></i>
                    <span>Actividad reciente</span>
                </div>

                <div class="wn-activity">
                    @foreach ($activity as $item)
                        <div class="wn-activity-item">
                            <div class="wn-activity-icon {{ $item['modifier'] }}"><i class="bi bi-{{ $item['icon'] }}"></i></div>
                            <div class="wn-activity-text">
                                @if (($item['special'] ?? null) === 'report')
                                    Se reportó una oferta de <strong>{{ explode('|', $item['action'])[0] }}</strong> {{ explode('|', $item['action'])[1] }}.
                                @else
                                    <strong>{{ $item['actor'] }}</strong> {{ $item['action'] }}
                                @endif
                                <div class="wn-activity-time"><i class="bi bi-clock"></i> {{ $item['time'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </main>
@endsection
