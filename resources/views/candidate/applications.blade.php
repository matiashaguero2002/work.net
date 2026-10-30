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

        @if(session('success'))
            <div class="wn-flash-success"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
        @endif

        {{-- TABS (filtran las tarjetas por estado) --}}
        <div class="wn-tabs">
            <button class="wn-tab active" type="button" data-filter="all">Todas <span class="count">{{ $stats['total'] }}</span></button>
            <button class="wn-tab" type="button" data-filter="registered">Registradas <span class="count">{{ $stats['registered'] }}</span></button>
            <button class="wn-tab" type="button" data-filter="review">En revisión <span class="count">{{ $stats['in_review'] }}</span></button>
            <button class="wn-tab" type="button" data-filter="accepted">Aceptadas <span class="count">{{ $stats['accepted'] }}</span></button>
            <button class="wn-tab" type="button" data-filter="rejected">Rechazadas <span class="count">{{ $stats['rejected'] }}</span></button>
        </div>

        {{-- LISTA --}}
        <div class="wn-applications-list">

            @forelse($applications as $application)
                <article class="wn-application-card{{ $application['status'] === 'rejected' ? ' rejected' : '' }}{{ $application['status'] === 'withdrawn' ? ' withdrawn' : '' }}"
                         data-status="{{ $application['status'] }}">
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
                                    @if(str_starts_with($action['href'], 'company:'))
                                        <a href="{{ route('candidate.company.show', (int) substr($action['href'], 8)) }}" class="wn-app-action {{ $action['kind'] }}">
                                            <i class="bi bi-{{ $action['icon'] }}"></i> {{ $action['label'] }}
                                        </a>
                                    @elseif($action['href'] === 'interviews')
                                        <a href="{{ route('interviews.index') }}" class="wn-app-action {{ $action['kind'] }}">
                                            <i class="bi bi-{{ $action['icon'] }}"></i> {{ $action['label'] }}
                                        </a>
                                    @else
                                        <a href="{{ $action['href'] }}" class="wn-app-action {{ $action['kind'] }}">
                                            <i class="bi bi-{{ $action['icon'] }}"></i> {{ $action['label'] }}
                                        </a>
                                    @endif
                                @else
                                    {{-- Retirar: DELETE real con confirmación --}}
                                    <form method="POST" action="{{ route('candidate.applications.destroy', $application['id']) }}"
                                          class="wn-app-withdraw-form" data-offer-title="{{ $application['title'] }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="wn-app-action {{ $action['kind'] }}" type="submit">
                                            <i class="bi bi-{{ $action['icon'] }}"></i> {{ $action['label'] }}
                                        </button>
                                    </form>
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
            @empty
                <div class="wn-empty-state">
                    <i class="bi bi-send"></i>
                    <h3>Aún no te postulaste a ninguna oferta</h3>
                    <p>Explorá el mapa de ofertas y tocá <strong>Postularme</strong> para empezar tu historia de postulaciones.</p>
                    <a href="{{ route('map.index') }}" class="btn-wn-primary">
                        <i class="bi bi-map"></i> Ir al mapa de ofertas
                    </a>
                </div>
            @endforelse

            {{-- Mensaje cuando un filtro quedó sin resultados --}}
            <div class="wn-empty-state" id="applicationsEmptyFilter" hidden>
                <i class="bi bi-funnel"></i>
                <h3>No hay postulaciones con este estado</h3>
            </div>

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

        // Tabs funcionales: filtran las tarjetas por estado.
        document.querySelectorAll('.wn-page-applications .wn-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.wn-page-applications .wn-tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');

                const filter = tab.dataset.filter;
                let visibles = 0;
                document.querySelectorAll('.wn-page-applications .wn-application-card').forEach(card => {
                    const match = filter === 'all' || card.dataset.status === filter;
                    card.style.display = match ? '' : 'none';
                    if (match) visibles++;
                });

                const empty = document.getElementById('applicationsEmptyFilter');
                if (empty) empty.hidden = visibles !== 0;
            });
        });

        // Retirar postulación: confirmación + fade, luego envía el
        // form DELETE que cambia el estado a "Retirada" en la BD.
        document.querySelectorAll('.wn-page-applications .wn-app-withdraw-form button').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const form = btn.closest('form');
                if (!confirm('¿Querés retirar tu postulación a "' + (form.dataset.offerTitle || 'esta oferta') + '"?')) return;

                const card = form.closest('.wn-application-card');
                card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                card.style.opacity = '0';
                card.style.transform = 'scale(0.98)';
                setTimeout(() => form.submit(), 280);
            });
        });
    </script>
@endpush
