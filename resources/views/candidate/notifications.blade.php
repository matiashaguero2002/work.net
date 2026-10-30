@extends('layouts.app')

@section('title', 'Work.net - Notificaciones')

@section('content')
    <div class="wn-bg"><div class="wn-bg-grid"></div></div>

    @include('partials.wn-navbar')

    <main class="wn-main wn-page-notifications">

        <header class="wn-header">
            <div class="wn-header-left">
                <h1>Notificaciones</h1>
                <p>Tenés <strong id="notifUnreadCount">{{ $unreadCount }} notificaciones sin leer</strong> de un total de {{ count($notifications) }}.</p>
            </div>
            <div class="wn-header-actions">
                <button class="btn-wn-secondary" id="markAllRead" type="button" data-url="{{ route('notifications.markAllRead') }}">
                    <i class="bi bi-check2-all"></i> Marcar todas como leídas
                </button>
            </div>
        </header>

        {{-- TABS (filtran por estado / tipo real de la BD) --}}
        <div class="wn-tabs">
            <button class="wn-tab active" type="button" data-filter="all">
                Todas <span class="count">{{ count($notifications) }}</span>
            </button>
            <button class="wn-tab" type="button" data-filter="unread">
                Sin leer <span class="count" id="tabUnreadCount">{{ $unreadCount }}</span>
            </button>
            <button class="wn-tab" type="button" data-filter="tipo-1">
                <i class="bi bi-send"></i> Postulaciones
            </button>
            <button class="wn-tab" type="button" data-filter="tipo-2">
                <i class="bi bi-calendar-event"></i> Entrevistas
            </button>
            <button class="wn-tab" type="button" data-filter="tipo-3">
                <i class="bi bi-stars"></i> Ofertas
            </button>
        </div>

        {{-- NOTIFICACIONES --}}
        <div class="wn-notifications">
            @forelse($groups as $group)
                @php
                    $delGrupo = collect($notifications)->where('group', $group['key']);
                @endphp
                @if($delGrupo->count())
                    <div class="wn-date-header" data-hide-on-filter>{{ $group['label'] }}</div>

                    @foreach($delGrupo as $notification)
                        <a href="{{ $notification['href'] }}" class="wn-notification{{ $notification['isUnread'] ? ' unread' : '' }}"
                           data-id="{{ $notification['id'] }}"
                           data-tipo="{{ $notification['tipo'] }}"
                           data-unread="{{ $notification['isUnread'] ? '1' : '0' }}">
                            <div class="wn-notification-icon{{ $notification['iconClass'] ? ' '.$notification['iconClass'] : '' }}">
                                <i class="bi bi-{{ $notification['icon'] }}"></i>
                            </div>
                            <div class="wn-notification-info">
                                <p class="wn-notification-title">
                                    {!! $notification['titleHtml'] !!}
                                </p>
                                <p class="wn-notification-text">
                                    {!! $notification['textHtml'] !!}
                                </p>
                                <span class="wn-notification-time">
                                    <i class="bi bi-clock"></i> {{ $notification['time'] }}
                                </span>
                            </div>
                            <div class="wn-notification-actions">
                                @if($notification['isUnread'])
                                    <button class="wn-notification-action mark-read" title="Marcar como leída" type="button"
                                            data-url="{{ route('notifications.markRead', $notification['id']) }}">
                                        <i class="bi bi-check2"></i>
                                    </button>
                                @endif
                                <button class="wn-notification-action delete" title="Eliminar" type="button"
                                        data-url="{{ route('notifications.destroy', $notification['id']) }}">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </a>
                    @endforeach
                @endif
            @empty
                <div class="wn-empty-state">
                    <i class="bi bi-bell"></i>
                    <h3>No tenés notificaciones</h3>
                    <p>Cuando avance la revisión de tus postulaciones, las novedades van a aparecer acá.</p>
                </div>
            @endforelse

            {{-- Mensaje cuando un filtro quedó sin resultados --}}
            <div class="wn-empty-state" id="notificationsEmptyFilter" hidden>
                <i class="bi bi-funnel"></i>
                <h3>No hay notificaciones en esta categoría</h3>
            </div>

        </div>

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

        (function () {
            // CSRF nativo de Laravel para las peticiones AJAX.
            const csrfToken = () => {
                const meta = document.querySelector('meta[name="csrf-token"]');
                return meta ? meta.getAttribute('content') : '';
            };

            const unreadLabel = document.getElementById('notifUnreadCount');
            const tabUnread = document.getElementById('tabUnreadCount');
            const notificaciones = Array.from(document.querySelectorAll('.wn-page-notifications .wn-notification'));

            const updateUnread = (count) => {
                if (unreadLabel) unreadLabel.textContent = count + (count === 1 ? ' notificación sin leer' : ' notificaciones sin leer');
                if (tabUnread) tabUnread.textContent = String(count);
            };

            // Marcar como leída: POST AJAX + actualización visual inmediata.
            document.querySelectorAll('.wn-page-notifications .wn-notification-action.mark-read').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation(); // no seguir el link de la notificación
                    const url = btn.dataset.url;
                    if (!url) return;

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken(),
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    })
                        .then((res) => res.json().then((body) => ({ ok: res.ok, body })))
                        .then(({ ok, body }) => {
                            if (!ok) return;
                            const card = btn.closest('.wn-notification');
                            card.classList.remove('unread');
                            card.dataset.unread = '0';
                            btn.remove();
                            updateUnread(body.unreadCount);
                        });
                });
            });

            // Eliminar notificación: DELETE AJAX real (borra la fila de la BD).
            document.querySelectorAll('.wn-page-notifications .wn-notification-action.delete').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    const url = btn.dataset.url;
                    if (!url) return;

                    const card = btn.closest('.wn-notification');
                    fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken(),
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    })
                        .then((res) => res.json().then((body) => ({ ok: res.ok, body })))
                        .then(({ ok, body }) => {
                            if (!ok) return;
                            card.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
                            card.style.opacity = '0';
                            card.style.transform = 'scale(0.97)';
                            setTimeout(() => card.remove(), 250);
                            updateUnread(body.unreadCount);
                            recomputeVisibility();
                        });
                });
            });

            // Marcar todas como leídas (POST real).
            const markAll = document.getElementById('markAllRead');
            if (markAll) {
                markAll.addEventListener('click', () => {
                    const url = markAll.dataset.url;
                    if (!url) return;

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken(),
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    })
                        .then((res) => res.json().then((body) => ({ ok: res.ok, body })))
                        .then(({ ok, body }) => {
                            if (!ok) return;
                            notificaciones.forEach((card) => {
                                card.classList.remove('unread');
                                card.dataset.unread = '0';
                                const markBtn = card.querySelector('.wn-notification-action.mark-read');
                                if (markBtn) markBtn.remove();
                            });
                            updateUnread(body.unreadCount);
                        });
                });
            }

            // Tabs funcionales: todas / sin leer / por tipo (BD).
            const empty = document.getElementById('notificationsEmptyFilter');
            document.querySelectorAll('.wn-page-notifications .wn-tab').forEach(tab => {
                tab.addEventListener('click', () => {
                    document.querySelectorAll('.wn-page-notifications .wn-tab').forEach(t => t.classList.remove('active'));
                    tab.classList.add('active');
                    applyFilter(tab.dataset.filter);
                });
            });

            function recomputeVisibility() {
                const active = document.querySelector('.wn-page-notifications .wn-tab.active');
                if (active) applyFilter(active.dataset.filter);
            }

            function applyFilter(filter) {
                if (filter === 'all') {
                    notificaciones.forEach((card) => { card.hidden = false; });
                    document.querySelectorAll('.wn-page-notifications .wn-date-header').forEach((h) => { h.hidden = false; });
                    empty.hidden = notificaciones.length !== 0;
                    return;
                }

                const ver = (card) => {
                    if (filter === 'unread') return card.classList.contains('unread');
                    return card.dataset.tipo === filter.replace('tipo-', '');
                };

                // Ocultar los headers de fecha que quedaron vacíos.
                let visibles = 0;
                notificaciones.forEach((card) => {
                    card.hidden = !ver(card);
                    if (ver(card)) visibles++;
                });

                document.querySelectorAll('.wn-page-notifications .wn-date-header').forEach((header) => {
                    let next = header.nextElementSibling;
                    let hayAlguna = false;
                    while (next && !next.classList.contains('wn-date-header')) {
                        if (next.classList.contains('wn-notification') && !next.hidden) {
                            hayAlguna = true;
                            break;
                        }
                        next = next.nextElementSibling;
                    }
                    header.hidden = !hayAlguna;
                });

                empty.hidden = visibles !== 0;
            }
        })();
    </script>
@endpush
