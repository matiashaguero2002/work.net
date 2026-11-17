@extends('layouts.app')

@section('title', 'Work.net - Mis entrevistas')

@section('content')
    <div class="wn-bg"><div class="wn-bg-grid"></div></div>

    @include('partials.wn-navbar')

    <main class="wn-main wn-page-interviews">

        <header class="wn-header">
            <div class="wn-header-left">
                <h1>Mis entrevistas</h1>
                <p>Tenés <strong>{{ $upcomingCount }} entrevistas próximas</strong> y {{ $pastCount }} ya realizadas.</p>
            </div>
        </header>

        {{-- Tabs --}}
        <div class="wn-tabs">
            <button class="wn-tab active" type="button" data-filter="scheduled">Próximas <span class="count">{{ $upcomingCount }}</span></button>
            <button class="wn-tab" type="button" data-filter="past">Realizadas <span class="count">{{ $pastCount }}</span></button>
            <button class="wn-tab" type="button" data-filter="cancelled">Canceladas <span class="count">{{ $cancelledCount }}</span></button>
        </div>

        {{-- Lista --}}
        <div class="wn-interviews"
             data-map-url="{{ route('map.index') }}"
             data-cancel-url="{{ route('interviews.cancel', [':idEntrevista']) }}"
             data-negotiate-url="{{ route('interviews.negotiate', [':idEntrevista']) }}">

            @forelse($interviews as $interview)
                <article class="wn-interview-card {{ $interview['status'] !== 'scheduled' ? $interview['status'] : '' }}{{ isset($interview['negotiating']) && $interview['negotiating'] ? ' negotiating' : '' }}"
                         data-status="{{ $interview['status'] }}">
                    <div class="wn-date-block {{ $interview['status'] !== 'scheduled' ? $interview['status'] : '' }}">
                        <span class="day">{{ $interview['day'] }}</span>
                        <span class="month">{{ $interview['month'] }}</span>
                        <span class="year">{{ $interview['year'] }}</span>
                    </div>

                    <div class="wn-interview-content">
                        <div class="wn-interview-header">
                            <h3 class="wn-interview-title">{{ $interview['title'] }}</h3>
                            <span class="wn-interview-status wn-status-{{ $interview['negotiating'] ?? false ? 'negotiating' : $interview['status'] }}">
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
                                <strong>Notas de la entrevista</strong>
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
                                <form method="POST" action="" class="wn-interview-cancel-form" data-id="{{ $interview['id'] }}" data-cancel="{{ route('interviews.cancel', $interview['id']) }}" data-company="{{ $interview['company'] }}">
                                    @csrf
                                    <button class="wn-action-btn wn-action-btn-danger" type="submit">
                                        <i class="bi bi-x-circle"></i> Cancelar
                                    </button>
                                </form>
                                <button class="wn-action-btn wn-action-btn-secondary" type="button"
                                        data-negotiate
                                        data-id="{{ $interview['id'] }}"
                                        data-date="{{ $interview['date'] }}"
                                        data-company="{{ $interview['company'] }}"
                                        data-position="{{ $interview['position'] }}"
                                        data-notes="{{ $interview['notes'] }}"
                                        data-estado="{{ ($interview['negotiating'] ?? false) ? 'negotiating' : 'pending' }}"
                                        data-responder-url="{{ route('interviews.responder', $interview['id']) }}"
                                        data-visto-url="{{ route('interviews.visto', $interview['id']) }}">
                                    <i class="bi bi-calendar-plus"></i> Negociar horario
                                </button>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="wn-empty-state">
                    <i class="bi bi-calendar-x"></i>
                    <h3>Todavía no tenés entrevistas</h3>
                    <p>Cuando una empresa te convoque, la entrevista aparecerá acá con su fecha, lugar y notas.</p>
                    <a href="{{ route('map.index') }}" class="btn-wn-primary">
                        <i class="bi bi-map"></i> Ir al mapa de ofertas
                    </a>
                </div>
            @endforelse

                {{-- Mensaje cuando un filtro quedó sin resultados --}}
                <div class="wn-empty-state" id="interviewsEmptyFilter" hidden>
                    <i class="bi bi-funnel"></i>
                    <h3>No hay entrevistas en esta categoría</h3>
                </div>

        </div>

    </main>

    {{-- MODAL REAGENDAR / NEGOCIAR HORARIO (RN-19): la propuesta se
         envía a la BD (estado "negociando" + notas + notificación). --}}
    <div class="wn-modal-backdrop wn-negotiate" id="negotiateModal" role="dialog" aria-modal="true" aria-labelledby="negotiateModalTitle">
        <div class="wn-modal">

            {{-- Header --}}
            <div class="wn-modal-header">
                <h2 class="wn-modal-title" id="negotiateModalTitle"><i class="bi bi-calendar-event"></i> Reagendar Entrevista</h2>
                <button class="wn-modal-close" type="button" data-negotiate-close aria-label="Cerrar">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            {{-- Body --}}
            <div class="wn-modal-body">

                {{-- Contexto actual --}}
                <div class="wn-alert-info">
                    <i class="bi bi-info-circle-fill"></i>
                    <div>
                        <p>Entrevista con: <strong id="negotiateCompany"></strong></p>
                        <p>Puesto: <strong id="negotiatePosition"></strong></p>
                    </div>
                </div>

                {{-- Formulario de propuesta --}}
                <div>
                    <h3 class="wn-modal-subtitle">Proponer nueva fecha y hora</h3>
                    <div class="wn-form-row">
                        <div>
                            <label class="wn-label" for="negotiateDate">Nueva fecha <span class="wn-required">*</span></label>
                            <input type="date" class="wn-input" id="negotiateDate" name="nueva_fecha">
                        </div>
                        <div>
                            <label class="wn-label" for="negotiateTime">Nueva hora <span class="wn-required">*</span></label>
                            <input type="time" class="wn-input" id="negotiateTime" name="nueva_hora">
                        </div>
                    </div>
                    <div class="wn-form-row mb-none">
                        <div>
                            <label class="wn-label" for="negotiateMessage">Mensaje (opcional)</label>
                            <textarea class="wn-textarea" id="negotiateMessage" name="mensaje" rows="2" placeholder="Ej: Me surgió un imprevisto, ¿podemos pasar la entrevista para el martes?"></textarea>
                        </div>
                    </div>
                </div>

                {{-- Historial: notas reales del empleador / propuesta anterior --}}
                <div class="wn-chat-history" id="negotiateChat" hidden>
                    <h3 class="wn-modal-subtitle">Historial de la entrevista</h3>
                    <div class="wn-chat-history-items" id="negotiateChatItems"></div>
                </div>

            </div>

            {{-- Footer --}}
            <div class="wn-modal-footer">
                <button class="btn-wn-secondary" type="button" data-negotiate-close>Cancelar</button>
                <button class="btn-wn-primary" type="button" id="negotiateSend">Enviar Propuesta</button>
            </div>

        </div>
    </div>

@endsection
