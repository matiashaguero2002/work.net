@extends('layouts.app')

@section('title', 'Work.net - Detalle del postulante')

@section('content')
    <div class="wn-bg"><div class="wn-bg-grid"></div></div>

    @include('partials.wn-navbar')

    <main class="wn-main wn-page-employer">
        @if (session('success'))
            <div class="wn-flash-success"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="wn-flash-error">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div>@foreach ($errors->all() as $error)<span>{{ $error }}</span>@endforeach</div>
            </div>
        @endif

        <header class="wn-header">
            <div class="wn-header-left">
                <div class="wn-header-avatar">{{ $applicant['initials'] }}</div>
                <div class="wn-header-info">
                    <h1>{{ $applicant['name'] }}</h1>
                    <p class="wn-header-position">Postuló a: {{ $applicant['position'] }}</p>
                    <div class="wn-header-meta">
                        <span><i class="bi bi-geo-alt-fill"></i> {{ $applicant['location'] }}</span>
                        <span><i class="bi bi-envelope-fill"></i> {{ $applicant['email'] }}</span>
                        <span><i class="bi bi-clock-fill"></i> {{ strtolower($applicant['appliedAt']) }}</span>
                    </div>
                </div>
            </div>
            <div class="wn-header-actions">
                <a href="{{ route('employer.applicants.index') }}" class="btn-wn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
                <form method="POST" action="{{ route('employer.applicants.reject', $applicant['id']) }}" data-confirm="¿Rechazar la postulación de {{ $applicant['name'] }}? El candidato será notificado.">
                    @csrf
                    <button class="btn-wn-danger" type="submit">
                        <i class="bi bi-x-circle"></i> Rechazar
                    </button>
                </form>
                <a href="{{ route('employer.interviews.create', $applicant['id']) }}" class="btn-wn-primary">
                    <i class="bi bi-calendar-plus"></i> Convocar a entrevista
                </a>
            </div>
        </header>

        <div class="wn-detail-grid">

            <div class="wn-detail-main">

                @if(!empty($applicant['perfil']))
                    <div class="wn-card">
                        <h2 class="wn-card-title"><i class="bi bi-person-lines-fill"></i> Sobre mí</h2>
                        <p class="wn-card-text">{{ $applicant['perfil'] }}</p>
                    </div>
                @endif

                @if(!empty($applicant['experiencias']))
                    <div class="wn-card">
                        <h2 class="wn-card-title"><i class="bi bi-briefcase"></i> Experiencia laboral</h2>
                        <div class="wn-info-grid">
                            @foreach($applicant['experiencias'] as $exp)
                                <article class="wn-info-card">
                                    <h3 class="wn-info-card-title">{{ $exp['puesto'] }}</h3>
                                    <p class="wn-info-card-subtitle">{{ $exp['empresa'] }}</p>
                                    <div class="wn-info-card-date"><i class="bi bi-calendar-range"></i> {{ $exp['periodo'] }}</div>
                                    @if(!empty($exp['tareas']))
                                        <p class="wn-info-card-description">{{ $exp['tareas'] }}</p>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(!empty($applicant['titulos']))
                    <div class="wn-card">
                        <h2 class="wn-card-title"><i class="bi bi-mortarboard"></i> Formación académica</h2>
                        <div class="wn-info-grid">
                            @foreach($applicant['titulos'] as $titulo)
                                <article class="wn-info-card">
                                    <h3 class="wn-info-card-title">{{ $titulo['nombre'] }}</h3>
                                    <p class="wn-info-card-subtitle">{{ $titulo['institucion'] }}</p>
                                    <div class="wn-info-card-date"><i class="bi bi-calendar-range"></i> {{ $titulo['periodo'] }}</div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(!empty($applicant['skills']))
                    <div class="wn-card">
                        <h2 class="wn-card-title"><i class="bi bi-lightning-charge"></i> Habilidades</h2>
                        <div class="wn-chips">
                            @foreach($applicant['skills'] as $skill)
                                <span class="wn-chip">{{ $skill }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(!empty($applicant['languages']))
                    <div class="wn-card">
                        <h2 class="wn-card-title"><i class="bi bi-translate"></i> Idiomas</h2>
                        <div class="wn-chips">
                            @foreach($applicant['languages'] as $lang)
                                <span class="wn-chip">{{ $lang['name'] }} ({{ $lang['level'] }})</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="wn-card">
                    <h2 class="wn-card-title"><i class="bi bi-file-earmark-pdf"></i> Currículum Vitae</h2>
                    @if(!empty($applicant['cv']))
                        <div class="wn-cv-card">
                            <div class="wn-cv-icon"><i class="bi bi-file-earmark-pdf-fill"></i></div>
                            <div class="wn-cv-info">
                                <h3 class="wn-cv-title">{{ $applicant['cv']['filename'] }}</h3>
                                <p class="wn-cv-meta">{{ $applicant['cv']['size'] }}</p>
                            </div>
                            <a class="btn-wn-secondary" href="{{ $applicant['cv']['url'] }}">
                                <i class="bi bi-download"></i> Descargar
                            </a>
                        </div>
                    @else
                        <p class="wn-card-text" style="color: var(--wn-text-muted);">El candidato todavía no cargó un CV en su perfil.</p>
                    @endif
                </div>

            </div>

            <aside class="wn-detail-sidebar">

                <div class="wn-sidebar-card">
                    <h3 class="wn-sidebar-card-title">Match con la oferta</h3>
                    <div class="wn-match-display">
                        <div class="wn-match-value">{{ $applicant['match'] }}%</div>
                        <div class="wn-match-label">Compatibilidad</div>
                    </div>
                </div>

                <div class="wn-sidebar-card">
                    <h3 class="wn-sidebar-card-title">Estado de postulación</h3>
                    <div class="wn-status-selector">
                        @foreach($statusOptions as $option)
                            <div class="wn-status-option{{ $option['id'] === (int) $postulacion->id_estado_post ? ' selected' : '' }}"
                                data-estado="{{ $option['id'] }}" style="cursor: pointer;">
                                <div class="wn-status-dot {{ $option['key'] }}"></div>
                                <span>{{ $option['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                    <p style="font-size: 0.72rem; color: var(--wn-text-muted); margin: 8px 0 0 0; text-align: center;">
                        Seleccioná un estado para actualizar
                    </p>
                </div>

            </aside>

        </div>

    </main>
@endsection

@push('scripts')
    <script>
        (function () {
            // Confirmación del botón Rechazar.
            document.querySelectorAll('form[data-confirm]').forEach((form) => {
                form.addEventListener('submit', (e) => {
                    if (!window.confirm(form.dataset.confirm)) e.preventDefault();
                });
            });

            // Selector de estado: POST real con el nuevo estado.
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('employer.applicants.estado', $applicant['id']) }}';
            form.appendChild(document.createElement('input')).setAttribute('type', 'hidden');
            form.firstElementChild.name = '_token';
            form.firstElementChild.value = '{{ csrf_token() }}';
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'estado';
            form.appendChild(input);

            document.querySelectorAll('.wn-status-option').forEach(opt => {
                opt.addEventListener('click', () => {
                    input.value = opt.dataset.estado;
                    form.submit();
                });
            });
        })();
    </script>
@endpush
