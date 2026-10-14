@extends('layouts.app')

@section('title', 'Work.net - Detalle del postulante')

@section('content')
    <div class="wn-bg"><div class="wn-bg-grid"></div></div>

    @include('partials.wn-navbar')

    <main class="wn-main wn-page-employer">
        <header class="wn-header">
            <div class="wn-header-left">
                <div class="wn-header-avatar">{{ $applicant['initials'] }}</div>
                <div class="wn-header-info">
                    <h1>{{ $applicant['name'] }}</h1>
                    <p class="wn-header-position">Postuló a: {{ $applicant['position'] }}</p>
                    <div class="wn-header-meta">
                        <span><i class="bi bi-geo-alt-fill"></i> {{ $applicant['location'] }}</span>
                        <span><i class="bi bi-envelope-fill"></i> {{ $applicant['email'] }}</span>
                        <span><i class="bi bi-telephone-fill"></i> {{ $applicant['phone'] }}</span>
                        <span><i class="bi bi-clock-fill"></i> Postuló {{ strtolower($applicant['appliedAt']) }}</span>
                    </div>
                </div>
            </div>
            <div class="wn-header-actions">
                <a href="{{ route('employer.applicants.index') }}" class="btn-wn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
                <a href="{{ route('employer.interviews.create', $applicant['id']) }}" class="btn-wn-primary">
                    <i class="bi bi-calendar-plus"></i> Convocar a entrevista
                </a>
            </div>
        </header>

        <div class="wn-detail-grid">

            <div class="wn-detail-main">

                <div class="wn-card">
                    <h2 class="wn-card-title"><i class="bi bi-briefcase"></i> Experiencia laboral</h2>
                    <div class="wn-info-grid">
                        <article class="wn-info-card">
                            <h3 class="wn-info-card-title">Desarrollador Full Stack</h3>
                            <p class="wn-info-card-subtitle">Tech Solutions S.A.</p>
                            <div class="wn-info-card-date"><i class="bi bi-calendar-range"></i> Marzo 2023 - Presente</div>
                            <p class="wn-info-card-description">Desarrollo de aplicaciones web con Laravel y Vue.js. Mantenimiento de APIs REST y optimización de base de datos.</p>
                        </article>

                        <article class="wn-info-card">
                            <h3 class="wn-info-card-title">Desarrollador Backend Junior</h3>
                            <p class="wn-info-card-subtitle">DataCorp S.A.</p>
                            <div class="wn-info-card-date"><i class="bi bi-calendar-range"></i> Enero 2021 - Febrero 2023</div>
                            <p class="wn-info-card-description">Desarrollo de APIs con Laravel, integración con servicios externos y testing con PHPUnit.</p>
                        </article>
                    </div>
                </div>

                <div class="wn-card">
                    <h2 class="wn-card-title"><i class="bi bi-mortarboard"></i> Formación académica</h2>
                    <div class="wn-info-grid">
                        <article class="wn-info-card">
                            <h3 class="wn-info-card-title">Licenciatura en Análisis de Sistemas</h3>
                            <p class="wn-info-card-subtitle">Universidad Autónoma de Encarnación</p>
                            <div class="wn-info-card-date"><i class="bi bi-calendar-range"></i> 2018 - 2024</div>
                        </article>
                    </div>
                </div>

                <div class="wn-card">
                    <h2 class="wn-card-title"><i class="bi bi-lightning-charge"></i> Habilidades</h2>
                    <div class="wn-chips">
                        @foreach($applicant['skills'] as $skill)
                            <span class="wn-chip">{{ $skill }}</span>
                        @endforeach
                    </div>
                </div>

                <div class="wn-card">
                    <h2 class="wn-card-title"><i class="bi bi-translate"></i> Idiomas</h2>
                    <div class="wn-chips">
                        @foreach($applicant['languages'] as $lang)
                            <span class="wn-chip">{{ $lang['name'] }} ({{ $lang['level'] }})</span>
                        @endforeach
                    </div>
                </div>

                <div class="wn-card">
                    <h2 class="wn-card-title"><i class="bi bi-file-earmark-pdf"></i> Currículum Vitae</h2>
                    <div class="wn-cv-card">
                        <div class="wn-cv-icon"><i class="bi bi-file-earmark-pdf-fill"></i></div>
                        <div class="wn-cv-info">
                            <h3 class="wn-cv-title">{{ $applicant['cv']['filename'] }}</h3>
                            <p class="wn-cv-meta">{{ $applicant['cv']['size'] }} · Subido el 15 de septiembre de 2026</p>
                        </div>
                        <button class="btn-wn-secondary" type="button">
                            <i class="bi bi-download"></i> Descargar
                        </button>
                    </div>
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
                            <div class="wn-status-option{{ $option['key'] === $applicant['status'] ? ' selected' : '' }}">
                                <div class="wn-status-dot {{ $option['key'] }}"></div>
                                <span>{{ $option['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                    <p style="font-size: 0.72rem; color: var(--wn-text-muted); margin: 8px 0 0 0; text-align: center;">
                        Seleccioná un estado para actualizar
                    </p>
                </div>

                <div class="wn-sidebar-card">
                    <h3 class="wn-sidebar-card-title">Acciones</h3>
                    <div class="wn-sidebar-actions">
                        <a href="{{ route('employer.interviews.create', $applicant['id']) }}" class="btn-wn-primary">
                            <i class="bi bi-calendar-plus"></i> Convocar a entrevista
                        </a>
                        <button class="btn-wn-success" type="button">
                            <i class="bi bi-check-circle"></i> Aceptar candidato
                        </button>
                        <button class="btn-wn-danger" type="button">
                            <i class="bi bi-x-circle"></i> Rechazar candidato
                        </button>
                        <button class="btn-wn-secondary" type="button">
                            <i class="bi bi-envelope"></i> Enviar mensaje
                        </button>
                    </div>
                </div>

            </aside>

        </div>

    </main>
@endsection

@push('scripts')
    <script>
        // Estado selector
        document.querySelectorAll('.wn-status-option').forEach(opt => {
            opt.addEventListener('click', () => {
                document.querySelectorAll('.wn-status-option').forEach(o => o.classList.remove('selected'));
                opt.classList.add('selected');
            });
        });
    </script>
@endpush
