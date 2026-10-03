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
                <button class="btn-wn-secondary" id="markAllRead" type="button">
                    <i class="bi bi-check2-all"></i> Marcar todas como leídas
                </button>
            </div>
        </header>

        {{-- TABS --}}
        <div class="wn-tabs">
            <button class="wn-tab active" type="button">
                Todas <span class="count">{{ count($notifications) }}</span>
            </button>
            <button class="wn-tab" type="button">
                Sin leer <span class="count" id="tabUnreadCount">{{ $unreadCount }}</span>
            </button>
            <button class="wn-tab" type="button">
                <i class="bi bi-send"></i> Postulaciones
            </button>
            <button class="wn-tab" type="button">
                <i class="bi bi-calendar-event"></i> Entrevistas
            </button>
            <button class="wn-tab" type="button">
                <i class="bi bi-stars"></i> Ofertas
            </button>
        </div>

        {{-- NOTIFICACIONES --}}
        <div class="wn-notifications">
            @foreach($groups as $group)
                @if(collect($notifications)->where('group', $group['key'])->count())
                    <div class="wn-date-header">{{ $group['label'] }}</div>

                    @foreach(collect($notifications)->where('group', $group['key']) as $notification)
                        <a href="#" class="wn-notification{{ $notification['isUnread'] ? ' unread' : '' }}">
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
                                    <button class="wn-notification-action mark-read" title="Marcar como leída" type="button">
                                        <i class="bi bi-check2"></i>
                                    </button>
                                @endif
                                <button class="wn-notification-action delete" title="Eliminar" type="button">
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
