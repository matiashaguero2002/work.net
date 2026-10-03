@extends('layouts.app')

@section('title', 'Work.net - Mis postulaciones')

@section('content')
    <div class="wn-bg"><div class="wn-bg-grid"></div></div>

    @include('partials.wn-navbar')

    <main class="wn-main wn-page-applications">

        <header class="wn-header">
            <div class="wn-header-left">
                <h1>Mis postulaciones</h1>
                <p>Tenés <strong>{{ $stats['total'] }} postulaciones</strong> en total: {{ $stats['accepted'] }} aceptada, {{ $stats['in_review'] }} en revisión y {{ $stats['rejected'] }} rechazada.</p>
            </div>
        </header>

        {{-- TABS --}}
        <div class="wn-tabs">
            <button class="wn-tab active" type="button">Todas <span class="count">{{ $stats['total'] }}</span></button>
            <button class="wn-tab" type="button">Registradas <span class="count">{{ $stats['registered'] }}</span></button>
            <button class="wn-tab" type="button">En revisión <span class="count">{{ $stats['in_review'] }}</span></button>
            <button class="wn-tab" type="button">Aceptadas <span class="count">{{ $stats['accepted'] }}</span></button>
            <button class="wn-tab" type="button">Rechazadas <span class="count">{{ $stats['rejected'] }}</span></button>
        </div>

        {{-- LISTA --}}
        <div class="wn-applications-list">

            @foreach($applications as $application)
                <article class="wn-application-card{{ $application['status'] === 'rejected' ? ' rejected' : '' }}">
                    <div class="wn-app-main">
                        <div class="wn-app-header">
                            <div class="wn-app-logo">{{ $application['companyInitials'] }}</div>
                            <div class="wn-app-info">
                                <h3 class="wn-app-title">{{ $application['title'] }}</h3>
                                <p class="wn-app-company">{{ $application['company'] }} · {{ $application['location'] }}</p>
                                <p class="wn-app-date">
                                    <i class="bi bi-calendar3"></i> {{ $application['appliedAt'] }}
                                </p>
                            </div>
                        </div>

                        <div class="wn-app-timeline">
                            @foreach($application['timeline'] as $step)
                                <div class="wn-timeline-step{{ $step['state'] ? ' '.$step['state'] : '' }}">
                                    <div class="wn-timeline-dot"><i class="bi bi-{{ $step['icon'] }}"></i></div>
                                    <span class="wn-timeline-label">{{ $step['label'] }}</span>
                                </div>
                                @if(!$loop->last)
                                    <div class="wn-timeline-line"></div>
                                @endif
                            @endforeach
                        </div>

                        <div class="wn-app-actions">
                            @foreach($application['actions'] as $action)
                                @if($action['tag'] === 'a')
                                    <a href="{{ $action['href'] === 'interviews' ? route('interviews.index') : $action['href'] }}" class="wn-app-action {{ $action['kind'] }}">
                                        <i class="bi bi-{{ $action['icon'] }}"></i> {{ $action['label'] }}
                                    </a>
                                @else
                                    <button class="wn-app-action {{ $action['kind'] }}" type="button">
                                        <i class="bi bi-{{ $action['icon'] }}"></i> {{ $action['label'] }}
                                    </button>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <div class="wn-app-status-col">
                        <div class="wn-app-status-badge {{ $application['status'] }}">
                            <i class="bi bi-{{ $application['statusIcon'] }}"></i>
                            <div class="status-text">
                                <div class="wn-app-status-label">Estado</div>
                                <div class="wn-app-status-value">{{ $application['statusLabel'] }}</div>
                            </div>
                        </div>
                        <div class="wn-app-status-note">
                            {{ $application['statusNote'] }}
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
        document.body.classList.add('wn-page-applications');
        document.documentElement.classList.add('wn-page-applications');

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

        // Tabs (demo visual: solo alternan la clase activa).
        document.querySelectorAll('.wn-page-applications .wn-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.wn-page-applications .wn-tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
            });
        });

        // Retirar postulación (demo con confirmación).
        document.querySelectorAll('.wn-page-applications .wn-app-action.danger').forEach(btn => {
            btn.addEventListener('click', () => {
                const card = btn.closest('.wn-application-card');
                if (card && confirm('¿Querés retirar tu postulación?')) {
                    card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.98)';
                    setTimeout(() => card.remove(), 300);
                }
            });
        });
    </script>
@endpush
