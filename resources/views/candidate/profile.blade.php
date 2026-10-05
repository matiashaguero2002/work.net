@extends('layouts.app')

@section('title', 'Work.net - Mi perfil')

@section('content')
    <div class="wn-bg"><div class="wn-bg-grid"></div></div>

    @include('partials.wn-navbar')

    <main class="wn-main">

        {{-- HEADER DEL PERFIL --}}
        <header class="wn-profile-header">
            <div class="wn-profile-photo-wrapper">
                <div class="wn-profile-photo">
                    {{ $profile['initials'] }}
                </div>
                <button class="wn-profile-photo-edit" title="Cambiar foto" type="button">
                    <i class="bi bi-camera-fill"></i>
                </button>
            </div>

            <div class="wn-profile-header-info">
                <h1 class="wn-profile-name">{{ $profile['name'] }}</h1>
                <p class="wn-profile-headline">{{ $profile['headline'] }}</p>

                <div class="wn-profile-meta">
                    <div class="wn-profile-meta-item">
                        <i class="bi bi-geo-alt-fill"></i>
                        <span>{{ $profile['location'] }}</span>
                    </div>
                    <div class="wn-profile-meta-item">
                        <i class="bi bi-envelope-fill"></i>
                        <span>{{ $profile['email'] }}</span>
                    </div>
                    <div class="wn-profile-meta-item">
                        <i class="bi bi-telephone-fill"></i>
                        <span>{{ $profile['phone'] }}</span>
                    </div>
                </div>

                <div class="wn-profile-actions">
                    <button class="btn-wn-primary" type="button">
                        <i class="bi bi-pencil"></i> Editar perfil
                    </button>
                    <button class="btn-wn-secondary" type="button">
                        <i class="bi bi-download"></i> Descargar CV
                    </button>
                    <button class="btn-wn-secondary" type="button">
                        <i class="bi bi-share"></i> Compartir
                    </button>
                </div>
            </div>
        </header>

        {{-- BARRA DE PROGRESO --}}
        <section class="wn-profile-progress-card">
            <div class="wn-profile-progress-header">
                <div class="wn-profile-progress-title">
                    <i class="bi bi-graph-up-arrow"></i>
                    <span>Completitud del perfil</span>
                </div>
                <div class="wn-profile-progress-value">{{ $profile['completion'] }}%</div>
            </div>
            <div class="wn-progress-bar">
                <div class="wn-progress-fill" style="width: {{ $profile['completion'] }}%;"></div>
            </div>
            <p class="wn-profile-progress-hint">
                <i class="bi bi-lightbulb-fill"></i>
                Te falta agregar <strong>1 experiencia laboral</strong> y <strong>cargar tu CV en PDF</strong> para completar tu perfil.
            </p>
        </section>

        {{-- DATOS PERSONALES --}}
        <section class="wn-section">
            <div class="wn-section-header">
                <h2 class="wn-section-title">
                    <i class="bi bi-person-vcard"></i>
                    Datos personales
                </h2>
                <div class="wn-section-actions">
                    <button class="wn-add-btn" type="button">
                        <i class="bi bi-pencil"></i> Editar
                    </button>
                </div>
            </div>

            <div class="wn-personal-grid">
                <div class="wn-personal-item">
                    <div class="wn-personal-label"><i class="bi bi-person"></i> Nombre completo</div>
                    <div class="wn-personal-value">{{ $profile['name'] }}</div>
                </div>
                <div class="wn-personal-item">
                    <div class="wn-personal-label"><i class="bi bi-card-text"></i> Documento</div>
                    <div class="wn-personal-value">{{ $profile['document'] }}</div>
                </div>
                <div class="wn-personal-item">
                    <div class="wn-personal-label"><i class="bi bi-calendar3"></i> Fecha de nacimiento</div>
                    <div class="wn-personal-value">{{ $profile['birthdate'] }}</div>
                </div>
                <div class="wn-personal-item">
                    <div class="wn-personal-label"><i class="bi bi-envelope"></i> Email</div>
                    <div class="wn-personal-value">{{ $profile['email'] }}</div>
                </div>
                <div class="wn-personal-item">
                    <div class="wn-personal-label"><i class="bi bi-telephone"></i> Teléfono</div>
                    <div class="wn-personal-value">{{ $profile['phone'] }}</div>
                </div>
                <div class="wn-personal-item">
                    <div class="wn-personal-label"><i class="bi bi-geo-alt"></i> Ubicación</div>
                    <div class="wn-personal-value">{{ $profile['location'] }}</div>
                </div>
            </div>
        </section>

        {{-- EXPERIENCIA LABORAL --}}
        <section class="wn-section">
            <div class="wn-section-header">
                <h2 class="wn-section-title">
                    <i class="bi bi-briefcase"></i>
                    Experiencia laboral
                </h2>
                <div class="wn-section-actions">
                    <button class="wn-add-btn" type="button">
                        <i class="bi bi-plus-lg"></i> Agregar
                    </button>
                </div>
            </div>

            <div class="wn-info-grid">
                @foreach($experiences as $exp)
                    <article class="wn-info-card">
                        <div class="wn-info-card-header">
                            <div>
                                <h3 class="wn-info-card-title">{{ $exp['position'] }}</h3>
                                <p class="wn-info-card-subtitle">{{ $exp['company'] }}</p>
                            </div>
                            <div class="wn-info-card-actions">
                                <button class="wn-icon-btn" title="Editar" type="button"><i class="bi bi-pencil"></i></button>
                                <button class="wn-icon-btn danger" title="Eliminar" type="button"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                        <div class="wn-info-card-date">
                            <i class="bi bi-calendar-range"></i> {{ $exp['period'] }}
                        </div>
                        @if($exp['description'])
                            <p class="wn-info-card-description">{{ $exp['description'] }}</p>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>

        {{-- FORMACIÓN ACADÉMICA --}}
        <section class="wn-section">
            <div class="wn-section-header">
                <h2 class="wn-section-title">
                    <i class="bi bi-mortarboard"></i>
                    Formación académica
                </h2>
                <div class="wn-section-actions">
                    <button class="wn-add-btn" type="button">
                        <i class="bi bi-plus-lg"></i> Agregar
                    </button>
                </div>
            </div>

            <div class="wn-info-grid">
                @foreach($educations as $edu)
                    <article class="wn-info-card">
                        <div class="wn-info-card-header">
                            <div>
                                <h3 class="wn-info-card-title">{{ $edu['degree'] }}</h3>
                                <p class="wn-info-card-subtitle">{{ $edu['institution'] }}</p>
                            </div>
                            <div class="wn-info-card-actions">
                                <button class="wn-icon-btn" title="Editar" type="button"><i class="bi bi-pencil"></i></button>
                                <button class="wn-icon-btn danger" title="Eliminar" type="button"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                        <div class="wn-info-card-date">
                            <i class="bi bi-calendar-range"></i> {{ $edu['period'] }}
                        </div>
                        @if($edu['description'])
                            <p class="wn-info-card-description">{{ $edu['description'] }}</p>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>

        {{-- HABILIDADES --}}
        <section class="wn-section">
            <div class="wn-section-header">
                <h2 class="wn-section-title">
                    <i class="bi bi-lightning-charge"></i>
                    Habilidades
                </h2>
                <div class="wn-section-actions">
                    <button class="wn-add-btn" type="button">
                        <i class="bi bi-plus-lg"></i> Agregar
                    </button>
                </div>
            </div>

            <div class="wn-chips">
                @foreach($skills as $skill)
                    <span class="wn-chip">
                        {{ $skill }}
                        <button class="wn-chip-remove" title="Eliminar" type="button"><i class="bi bi-x"></i></button>
                    </span>
                @endforeach
            </div>
        </section>

        {{-- IDIOMAS --}}
        <section class="wn-section">
            <div class="wn-section-header">
                <h2 class="wn-section-title">
                    <i class="bi bi-translate"></i>
                    Idiomas
                </h2>
                <div class="wn-section-actions">
                    <button class="wn-add-btn" type="button">
                        <i class="bi bi-plus-lg"></i> Agregar
                    </button>
                </div>
            </div>

            <div class="wn-chips">
                @foreach($languages as $lang)
                    <span class="wn-chip">
                        {{ $lang['name'] }} ({{ $lang['level'] }})
                        <button class="wn-chip-remove" title="Eliminar" type="button"><i class="bi bi-x"></i></button>
                    </span>
                @endforeach
            </div>
        </section>

        {{-- CV --}}
        <section class="wn-section">
            <div class="wn-section-header">
                <h2 class="wn-section-title">
                    <i class="bi bi-file-earmark-pdf"></i>
                    Currículum Vitae
                </h2>
            </div>

            <div class="wn-cv-card">
                <div class="wn-cv-icon">
                    <i class="bi bi-file-earmark-pdf-fill"></i>
                </div>
                <div class="wn-cv-info">
                    <h3 class="wn-cv-title">
                        {{ $cv['filename'] }}
                        <span class="wn-cv-badge">Cargado</span>
                    </h3>
                    <p class="wn-cv-meta">
                        Última actualización: {{ $cv['uploaded_at'] }} · {{ $cv['size'] }}
                    </p>
                </div>
                <div class="wn-cv-actions">
                    <button class="btn-wn-secondary" type="button">
                        <i class="bi bi-eye"></i> Ver
                    </button>
                    <button class="btn-wn-secondary" type="button">
                        <i class="bi bi-arrow-repeat"></i> Reemplazar
                    </button>
                    <button class="btn-wn-danger" type="button">
                        <i class="bi bi-trash"></i> Eliminar
                    </button>
                </div>
            </div>
        </section>

        {{-- PREFERENCIAS LABORALES --}}
        <section class="wn-section">
            <div class="wn-section-header">
                <h2 class="wn-section-title">
                    <i class="bi bi-sliders"></i>
                    Preferencias laborales
                </h2>
                <div class="wn-section-actions">
                    <button class="wn-add-btn" type="button">
                        <i class="bi bi-pencil"></i> Editar
                    </button>
                </div>
            </div>

            <div class="wn-preferences">
                @foreach($preferences as $pref)
                    <div class="wn-preference-card">
                        <div class="wn-preference-icon"><i class="bi {{ $pref['icon'] }}"></i></div>
                        <div class="wn-preference-info">
                            <div class="wn-preference-label">{{ $pref['label'] }}</div>
                            <div class="wn-preference-value">{{ $pref['value'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

    </main>
@endsection
