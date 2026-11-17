@extends('layouts.app')

@section('title', 'Work.net - Notificaciones')

@section('content')
    <div class="wn-bg"><div class="wn-bg-grid"></div></div>

    @include('partials.wn-navbar')

    <main class="wn-main wn-page-employer">
        <header class="wn-header">
            <div class="wn-header-left">
                <h1>Notificaciones</h1>
                <p>Tenés <strong id="notifUnreadCount">{{ $unreadCount }} notificaciones sin leer</strong> de un total de {{ $totalCount }}.</p>
            </div>
            <div class="wn-header-actions">
                <button class="btn-wn-secondary" id="markAllRead" type="button" data-url="{{ route('employer.notifications.markAllRead') }}">
                    <i class="bi bi-check2-all"></i> Marcar todas como leídas
                </button>
            </div>
        </header>

        <div class="wn-tabs" id="notifTabs">
            <button class="wn-tab active" type="button" data-filter="">Todas <span class="count">{{ $totalCount }}</span></button>
            <button class="wn-tab" type="button" data-filter="unread">Sin leer <span class="count" id="tabUnreadCount">{{ $unreadCount }}</span></button>
            <button class="wn-tab" type="button" data-filter="1"><i class="bi bi-inbox"></i> Postulaciones</button>
            <button class="wn-tab" type="button" data-filter="2"><i class="bi bi-calendar-event"></i> Entrevistas</button>
            <button class="wn-tab" type="button" data-filter="3"><i class="bi bi-star"></i> Ofertas</button>
        </div>

        <div class="wn-notifications" id="employerNotifications">
            @forelse($groups as $group)
                @if(collect($notifications)->where('group', $group['key'])->count())
                    <div class="wn-date-header">{{ $group['label'] }}</div>

                    @foreach(collect($notifications)->where('group', $group['key']) as $notification)
                        <a href="{{ $notification['link'] }}"
                            class="wn-notification{{ $notification['isUnread'] ? ' unread' : '' }}"
                            data-id="{{ $notification['id'] }}"
                            data-tipo="{{ $notification['tipo'] }}"
                            data-unread="{{ $notification['isUnread'] ? '1' : '0' }}"
                            data-unread-url="{{ route('employer.notifications.markRead', $notification['id']) }}"
                            data-delete-url="{{ route('employer.notifications.destroy', $notification['id']) }}">
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
                                    <button class="wn-notification-action mark-read" title="Marcar como leída" type="button" onclick="event.preventDefault();">
                                        <i class="bi bi-check2"></i>
                                    </button>
                                @endif
                                <button class="wn-notification-action delete" title="Eliminar" type="button" onclick="event.preventDefault();">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </a>
                    @endforeach
                @endif
            @empty
                <div class="wn-empty">
                    <div class="wn-empty-icon"><i class="bi bi-bell"></i></div>
                    <h3>No tenés notificaciones</h3>
                    <p>Acá vas a ver las postulaciones nuevas, los cambios de estado y las novedades de tus ofertas.</p>
                </div>
            @endforelse
        </div>
    </main>
@endsection

@push('scripts')
    <script>
        (function () {
            const list = document.getElementById('employerNotifications');
            if (!list) return;

            const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

            const recount = () => {
                const unread = list.querySelectorAll('.wn-notification.unread').length;
                const total = list.querySelectorAll('.wn-notification').length;
                const headerStrong = document.getElementById('notifUnreadCount');
                if (headerStrong) headerStrong.textContent = `${unread} notificaciones sin leer`;
                const tabCount = document.getElementById('tabUnreadCount');
                if (tabCount) tabCount.textContent = unread;
                // Los tabs muestran el total real (todas / sin leer).
                const allTab = document.querySelector('#notifTabs .wn-tab[data-filter=""] .count');
                if (allTab) allTab.textContent = total;
            };

            const ajax = (url, method) => fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            }).then((res) => res.json().then((body) => ({ ok: res.ok, body })));

            // Marcar como leída (AJAX).
            list.querySelectorAll('.wn-notification-action.mark-read').forEach((btn) => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    const item = btn.closest('.wn-notification');
                    if (!item) return;

                    ajax(item.dataset.unreadUrl, 'POST').then(({ ok }) => {
                        if (!ok) return;
                        item.classList.remove('unread');
                        item.dataset.unread = '0';
                        btn.remove();
                        recount();
                    });
                });
            });

            // Eliminar (AJAX).
            list.querySelectorAll('.wn-notification-action.delete').forEach((btn) => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    const item = btn.closest('.wn-notification');
                    if (!item) return;

                    ajax(item.dataset.deleteUrl, 'DELETE').then(({ ok, body }) => {
                        if (!ok) {
                            alert(body.message || 'No se pudo eliminar la notificación.');
                            return;
                        }
                        item.remove();
                        recount();
                    });
                });
            });

            // Marcar todas como leídas (AJAX).
            const markAll = document.getElementById('markAllRead');
            if (markAll) {
                markAll.addEventListener('click', () => {
                    const url = markAll.dataset.url;
                    if (!url) return;

                    ajax(url, 'POST').then(({ ok }) => {
                        if (!ok) return;
                        list.querySelectorAll('.wn-notification.unread').forEach((item) => {
                            item.classList.remove('unread');
                            item.dataset.unread = '0';
                            const btn = item.querySelector('.wn-notification-action.mark-read');
                            if (btn) btn.remove();
                        });
                        recount();
                    });
                });
            }

            // Tabs: filtra por tipo (o sin leer) sin recargar.
            const tabs = document.querySelectorAll('#notifTabs .wn-tab');
            let activeFilter = '';

            tabs.forEach((tab) => {
                tab.addEventListener('click', () => {
                    tabs.forEach((t) => t.classList.remove('active'));
                    tab.classList.add('active');
                    activeFilter = tab.dataset.filter;

                    list.querySelectorAll('.wn-notification').forEach((item) => {
                        let match = true;
                        if (activeFilter === 'unread') {
                            match = item.dataset.unread === '1';
                        } else if (activeFilter !== '') {
                            match = item.dataset.tipo === activeFilter;
                        }
                        item.style.display = match ? '' : 'none';
                    });

                    // Oculta los separadores de fecha que quedaron vacíos.
                    list.querySelectorAll('.wn-date-header').forEach((header) => {
                        let next = header.nextElementSibling;
                        let hasVisible = false;
                        while (next && !next.classList.contains('wn-date-header')) {
                            if (next.classList.contains('wn-notification') && next.style.display !== 'none') {
                                hasVisible = true;
                                break;
                            }
                            next = next.nextElementSibling;
                        }
                        header.style.display = hasVisible ? '' : 'none';
                    });
                });
            });
        })();
    </script>
@endpush
