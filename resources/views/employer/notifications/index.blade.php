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
                <button class="btn-wn-secondary" id="markAllRead" type="button">
                    <i class="bi bi-check2-all"></i> Marcar todas como leídas
                </button>
            </div>
        </header>

        <div class="wn-tabs">
            <button class="wn-tab active" type="button">Todas <span class="count">{{ $totalCount }}</span></button>
            <button class="wn-tab" type="button">Sin leer <span class="count" id="tabUnreadCount">{{ $unreadCount }}</span></button>
            <button class="wn-tab" type="button"><i class="bi bi-inbox"></i> Postulaciones</button>
            <button class="wn-tab" type="button"><i class="bi bi-calendar-event"></i> Entrevistas</button>
            <button class="wn-tab" type="button"><i class="bi bi-star"></i> Ofertas</button>
        </div>

        <div class="wn-notifications" id="employerNotifications">
            @foreach($groups as $group)
                @if(collect($notifications)->where('group', $group['key'])->count())
                    <div class="wn-date-header">{{ $group['label'] }}</div>

                    @foreach(collect($notifications)->where('group', $group['key']) as $notification)
                        <a href="{{ $notification['link'] }}" class="wn-notification{{ $notification['isUnread'] ? ' unread' : '' }}">
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
            @endforeach
        </div>
    </main>
@endsection

@push('scripts')
    <script>
        // Tabs (demo visual, igual que el prototipo)
        document.querySelectorAll('.wn-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.wn-tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
            });
        });

        // Notificaciones: marcar leída, eliminar y marcar todas (demo)
        (function () {
            const list = document.getElementById('employerNotifications');
            if (!list) return;

            const recount = () => {
                const unread = list.querySelectorAll('.wn-notification.unread').length;
                const headerStrong = document.getElementById('notifUnreadCount');
                if (headerStrong) headerStrong.textContent = `${unread} notificaciones sin leer`;
                const tabCount = document.getElementById('tabUnreadCount');
                if (tabCount) tabCount.textContent = unread;
            };

            list.querySelectorAll('.wn-notification-action.mark-read').forEach((btn) => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    const item = btn.closest('.wn-notification');
                    if (!item) return;
                    item.classList.remove('unread');
                    btn.remove();
                    recount();
                });
            });

            list.querySelectorAll('.wn-notification-action.delete').forEach((btn) => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    const item = btn.closest('.wn-notification');
                    if (item) item.remove();
                    recount();
                });
            });

            const markAll = document.getElementById('markAllRead');
            if (markAll) {
                markAll.addEventListener('click', () => {
                    list.querySelectorAll('.wn-notification.unread').forEach((item) => {
                        item.classList.remove('unread');
                        const btn = item.querySelector('.wn-notification-action.mark-read');
                        if (btn) btn.remove();
                    });
                    recount();
                });
            }
        })();
    </script>
@endpush
