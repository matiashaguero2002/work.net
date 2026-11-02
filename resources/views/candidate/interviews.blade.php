@extends('layouts.app')

@section('title', 'Work.net - Mis entrevistas')

@section('content')
    <div class="wn-bg"><div class="wn-bg-grid"></div></div>

    @include('partials.wn-navbar')

    <main class="wn-main">

        <header class="wn-header">
            <div class="wn-header-left">
                <h1>Mis entrevistas</h1>
                <p>Tenés <strong>{{ $upcomingCount }} entrevistas próximas</strong> y {{ $pastCount }} ya realizadas.</p>
            </div>
        </header>

        {{-- Tabs --}}
        <div class="wn-tabs">
            <button class="wn-tab active" type="button">Próximas <span class="count">{{ $upcomingCount }}</span></button>
            <button class="wn-tab" type="button">Realizadas <span class="count">{{ $pastCount }}</span></button>
            <button class="wn-tab" type="button">Canceladas <span class="count">{{ $cancelledCount }}</span></button>
        </div>

        {{-- Lista --}}
        <div class="wn-interviews" data-map-url="{{ route('map.index') }}">

            @foreach($interviews as $interview)
                <article class="wn-interview-card {{ $interview['status'] !== 'scheduled' ? $interview['status'] : '' }}" data-status="{{ $interview['status'] }}">
                    <div class="wn-date-block {{ $interview['status'] !== 'scheduled' ? $interview['status'] : '' }}">
                        <span class="day">{{ $interview['day'] }}</span>
                        <span class="month">{{ $interview['month'] }}</span>
                        <span class="year">{{ $interview['year'] }}</span>
                    </div>

                    <div class="wn-interview-content">
                        <div class="wn-interview-header">
                            <h3 class="wn-interview-title">{{ $interview['title'] }}</h3>
                            <span class="wn-interview-status wn-status-{{ $interview['status'] }}">
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
                                <strong>Notas del empleador</strong>
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
                                <button class="wn-action-btn wn-action-btn-danger" type="button">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </button>
                                <button class="wn-action-btn wn-action-btn-secondary" type="button"
                                        data-negotiate
                                        data-date="{{ $interview['day'] }} de {{ $interview['monthName'] }}, {{ $interview['timeStart'] }}"
                                        data-company="{{ $interview['company'] }}"
                                        data-position="{{ $interview['position'] }}">
                                    <i class="bi bi-calendar-plus"></i> Negociar horario
                                </button>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach

        </div>

    </main>

    {{-- MODAL REAGENDAR / NEGOCIAR HORARIO
         (specs/modal - Reagendar.html) --}}
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
                        <p>Entrevista actual: <strong id="negotiateCurrentDate"></strong></p>
                        <p>Con: <strong id="negotiateCompany"></strong> (<span id="negotiatePosition"></span>)</p>
                    </div>
                </div>

                {{-- Formulario de propuesta --}}
                <div>
                    <h3 style="font-size: 0.95rem; font-weight: 600; margin-bottom: 12px;">Proponer nueva fecha y hora</h3>
                    <div class="wn-form-row">
                        <div>
                            <label class="wn-label" for="negotiateDate">Nueva fecha <span style="color: var(--wn-danger);">*</span></label>
                            <input type="date" class="wn-input" id="negotiateDate" name="nueva_fecha" value="2026-10-20">
                        </div>
                        <div>
                            <label class="wn-label" for="negotiateTime">Nueva hora <span style="color: var(--wn-danger);">*</span></label>
                            <input type="time" class="wn-input" id="negotiateTime" name="nueva_hora" value="15:30">
                        </div>
                    </div>
                    <div style="margin-top: 16px;">
                        <label class="wn-label" for="negotiateMessage">Mensaje (Opcional)</label>
                        <textarea class="wn-textarea" id="negotiateMessage" name="mensaje" rows="2" placeholder="Ej: Me surgió un imprevisto, ¿podemos pasar la entrevista para el martes?"></textarea>
                    </div>
                </div>

                {{-- Historial de conversación (RN-19) --}}
                <div class="wn-chat-history">
                    <h3 style="font-size: 0.95rem; font-weight: 600; margin-bottom: 4px;">Historial de la entrevista</h3>

                    {{-- Mensaje del empleador --}}
                    <div class="wn-chat-bubble received">
                        <div class="wn-chat-meta">
                            <strong>Tech Solutions</strong>
                            <span>Hace 2 días</span>
                        </div>
                        <p style="margin: 0;">Hola Juan, te confirmo la entrevista para el 15 de octubre a las 10:00 hs en nuestra oficina.</p>
                    </div>

                    {{-- Propuesta del candidato --}}
                    <div class="wn-chat-bubble sent">
                        <div class="wn-chat-meta">
                            <strong>Vos (Candidato)</strong>
                            <span>Hace 1 hora</span>
                        </div>
                        <p style="margin: 0;">Buen día, lamentablemente tengo un examen en la facultad a esa hora. ¿Podríamos pasar la entrevista al jueves 17 a las 15:00 hs?</p>

                        {{-- Botones de acción para el empleador (si el que mira es el empleador) --}}
                        <div class="wn-chat-status">
                            <button class="wn-chat-btn" type="button" style="background-color: var(--wn-success); color: white;"><i class="bi bi-check-lg"></i> Aceptar</button>
                            <button class="wn-chat-btn" type="button" style="background-color: var(--wn-bg-dark); color: var(--wn-text-muted); border: 1px solid var(--wn-border);"><i class="bi bi-arrow-left-right"></i> Contra-proponer</button>
                            <button class="wn-chat-btn" type="button" style="background-color: rgba(218, 54, 51, 0.15); color: var(--wn-danger);"><i class="bi bi-x-lg"></i> Rechazar</button>
                        </div>
                    </div>
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
