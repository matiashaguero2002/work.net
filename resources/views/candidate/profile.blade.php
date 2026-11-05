@extends('layouts.app')

@section('title', 'Work.net - Mi perfil')

@section('content')
    <div class="wn-bg"><div class="wn-bg-grid"></div></div>

    @include('partials.wn-navbar')

    <main class="wn-main wn-page-profile">

        @if (session('success'))
            <div class="wn-flash-success">
                <i class="bi bi-check-circle-fill"></i>
                {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="wn-flash-error">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div>
                    @foreach ($errors->all() as $error)
                        <span>{{ $error }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- HEADER DEL PERFIL --}}
        <header class="wn-profile-header">
            <div class="wn-profile-photo-wrapper">
                <div class="wn-profile-photo">
                    {{ $profile['initials'] }}
                </div>
            </div>

            <div class="wn-profile-header-info">
                <h1 class="wn-profile-name">{{ $profile['name'] }}</h1>
                <p class="wn-profile-headline">{{ $profile['headline'] }}</p>

                <div class="wn-profile-meta">
                    @if ($profile['location'])
                        <div class="wn-profile-meta-item">
                            <i class="bi bi-geo-alt-fill"></i>
                            <span>{{ $profile['location'] }}</span>
                        </div>
                    @endif
                    <div class="wn-profile-meta-item">
                        <i class="bi bi-envelope-fill"></i>
                        <span>{{ $profile['email'] }}</span>
                    </div>
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
                <div class="wn-profile-progress-value">{{ $progress['percentage'] }}%</div>
            </div>
            <div class="wn-progress-bar">
                <div class="wn-progress-fill" style="width: {{ $progress['percentage'] }}%;"></div>
            </div>
            <p class="wn-profile-progress-hint">
                @if (empty($progress['missing']))
                    <i class="bi bi-emoji-smile-fill"></i>
                    ¡Tu perfil está completo! Los reclutadores van a encontrar toda tu información.
                @else
                    <i class="bi bi-lightbulb-fill"></i>
                    Para completar tu perfil: {!! '<strong>' . implode('</strong>, <strong>', $progress['missing']) . '</strong>' !!}.
                @endif
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
                    <button class="wn-add-btn" type="button" data-modal-open="modal-personal">
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
                    <div class="wn-personal-label"><i class="bi bi-envelope"></i> Email</div>
                    <div class="wn-personal-value">{{ $profile['email'] }}</div>
                </div>
                <div class="wn-personal-item">
                    <div class="wn-personal-label"><i class="bi bi-calendar3"></i> Fecha de nacimiento</div>
                    <div class="wn-personal-value">{{ $profile['birthdate'] ?: '—' }}</div>
                </div>
                <div class="wn-personal-item">
                    <div class="wn-personal-label"><i class="bi bi-gender-ambiguous"></i> Género</div>
                    <div class="wn-personal-value">{{ $profile['genero'] ?: '—' }}</div>
                </div>
                <div class="wn-personal-item">
                    <div class="wn-personal-label"><i class="bi bi-geo-alt"></i> Nacionalidad</div>
                    <div class="wn-personal-value">{{ $profile['location'] ?: '—' }}</div>
                </div>
                <div class="wn-personal-item">
                    <div class="wn-personal-label"><i class="bi bi-chat-square-text"></i> Sobre mí</div>
                    <div class="wn-personal-value">{{ $profile['about'] ?: '—' }}</div>
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
                    <button class="wn-add-btn" type="button"
                        data-modal-open="modal-experiencia"
                        data-store-url="{{ route('candidate.profile.experiencia.store') }}">
                        <i class="bi bi-plus-lg"></i> Agregar
                    </button>
                </div>
            </div>

            <div class="wn-info-grid">
                @forelse($experiences as $exp)
                    <article class="wn-info-card">
                        <div class="wn-info-card-header">
                            <div>
                                <h3 class="wn-info-card-title">{{ $exp['position'] }}</h3>
                                <p class="wn-info-card-subtitle">{{ $exp['company'] }}@if($exp['ubicacion']) · {{ $exp['ubicacion'] }}@endif</p>
                            </div>
                            <div class="wn-info-card-actions">
                                <button class="wn-icon-btn" title="Editar" type="button"
                                    data-modal-open="modal-experiencia"
                                    data-store-url="{{ route('candidate.profile.experiencia.store') }}"
                                    data-action-url="{{ route('candidate.profile.experiencia.update', $exp['id']) }}"
                                    data-payload="{{ json_encode([
                                        '_title' => $exp['position'],
                                        'puesto' => $exp['position'],
                                        'empresa' => $exp['company'],
                                        'fecha_inicio' => substr((string) $exp['rawInicio'], 0, 10),
                                        'fecha_fin' => substr((string) ($exp['rawFin'] ?? ''), 0, 10),
                                        'actual' => (bool) $exp['rawActual'],
                                        'tareas' => $exp['rawTareas'],
                                        'ubicacion' => $exp['rawUbicacion'],
                                    ]) }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="{{ route('candidate.profile.experiencia.destroy', $exp['id']) }}" class="wn-confirm-form">
                                    @csrf
                                    @method('DELETE')
                                    <button class="wn-icon-btn danger" title="Eliminar" type="submit"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </div>
                        <div class="wn-info-card-date">
                            <i class="bi bi-calendar-range"></i> {{ $exp['period'] }}
                        </div>
                        @if($exp['description'])
                            <p class="wn-info-card-description">{{ $exp['description'] }}</p>
                        @endif
                    </article>
                @empty
                    <div class="wn-empty">
                        <i class="bi bi-briefcase"></i>
                        <p>Todavía no agregaste experiencia laboral. Usá el botón "Agregar" para contar dónde trabajaste.</p>
                    </div>
                @endforelse
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
                    <button class="wn-add-btn" type="button"
                        data-modal-open="modal-titulo"
                        data-store-url="{{ route('candidate.profile.titulo.store') }}">
                        <i class="bi bi-plus-lg"></i> Agregar
                    </button>
                </div>
            </div>

            <div class="wn-info-grid">
                @forelse($educations as $edu)
                    <article class="wn-info-card">
                        <div class="wn-info-card-header">
                            <div>
                                <h3 class="wn-info-card-title">{{ $edu['degree'] }}@if($edu['description']) <span class="wn-tag-type">{{ $edu['description'] }}</span>@endif</h3>
                                <p class="wn-info-card-subtitle">{{ $edu['institution'] }}</p>
                            </div>
                            <div class="wn-info-card-actions">
                                <button class="wn-icon-btn" title="Editar" type="button"
                                    data-modal-open="modal-titulo"
                                    data-store-url="{{ route('candidate.profile.titulo.store') }}"
                                    data-action-url="{{ route('candidate.profile.titulo.update', $edu['id']) }}"
                                    data-payload="{{ json_encode([
                                        '_title' => $edu['degree'],
                                        'nombre' => $edu['degree'],
                                        'institucion' => $edu['institution'],
                                        'tipo' => $edu['rawTipo'],
                                        'fecha_inicio' => substr((string) $edu['rawInicio'], 0, 10),
                                        'fecha_fin' => substr((string) ($edu['rawFin'] ?? ''), 0, 10),
                                        'certificado' => (bool) $edu['rawCertificado'],
                                    ]) }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="{{ route('candidate.profile.titulo.destroy', $edu['id']) }}" class="wn-confirm-form">
                                    @csrf
                                    @method('DELETE')
                                    <button class="wn-icon-btn danger" title="Eliminar" type="submit"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </div>
                        <div class="wn-info-card-date">
                            <i class="bi bi-calendar-range"></i> {{ $edu['period'] }}
                            @if ($edu['rawCertificado'])
                                <i class="bi bi-patch-check-fill wn-certified" title="Con certificado"></i>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="wn-empty">
                        <i class="bi bi-mortarboard"></i>
                        <p>Todavía no agregaste formación académica. Usá "Agregar" para cargar títulos, cursos o capacitaciones.</p>
                    </div>
                @endforelse
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
                    <button class="wn-add-btn" type="button" data-modal-open="modal-habilidad">
                        <i class="bi bi-plus-lg"></i> Agregar
                    </button>
                </div>
            </div>

            @forelse($skills as $skill)
                <div class="wn-chips">
                    <span class="wn-chip">
                        {{ $skill['name'] }}
                        <span class="wn-chip-level">{{ $skill['level'] }}</span>
                        <form method="POST" action="{{ route('candidate.profile.habilidad.destroy', $skill['id']) }}" class="wn-confirm-form wn-chip-form">
                            @csrf
                            @method('DELETE')
                            <button class="wn-chip-remove" title="Eliminar" type="submit"><i class="bi bi-x"></i></button>
                        </form>
                    </span>
                </div>
            @empty
                <div class="wn-empty">
                    <i class="bi bi-lightning-charge"></i>
                    <p>Todavía no cargaste habilidades. Agregá las tuyas para que las empresas puedan encontrarte.</p>
                </div>
            @endforelse
        </section>

        {{-- IDIOMAS --}}
        <section class="wn-section">
            <div class="wn-section-header">
                <h2 class="wn-section-title">
                    <i class="bi bi-translate"></i>
                    Idiomas
                </h2>
                <div class="wn-section-actions">
                    <button class="wn-add-btn" type="button" data-modal-open="modal-idioma">
                        <i class="bi bi-plus-lg"></i> Agregar
                    </button>
                </div>
            </div>

            @forelse($languages as $lang)
                <div class="wn-chips">
                    <span class="wn-chip">
                        {{ $lang['name'] }}
                        <span class="wn-chip-level">{{ $lang['level'] }}</span>
                        <form method="POST" action="{{ route('candidate.profile.idioma.destroy', $lang['id']) }}" class="wn-confirm-form wn-chip-form">
                            @csrf
                            @method('DELETE')
                            <button class="wn-chip-remove" title="Eliminar" type="submit"><i class="bi bi-x"></i></button>
                        </form>
                    </span>
                </div>
            @empty
                <div class="wn-empty">
                    <i class="bi bi-translate"></i>
                    <p>Todavía no cargaste idiomas. Agregá cuáles hablás y tu nivel.</p>
                </div>
            @endforelse
        </section>

        {{-- CV --}}
        <section class="wn-section" id="perfil-cv">
            <div class="wn-section-header">
                <h2 class="wn-section-title">
                    <i class="bi bi-file-earmark-pdf"></i>
                    Currículum Vitae
                </h2>
            </div>

            @if ($cv)
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
                            @if ($cv['uploaded_at']) Última actualización: {{ $cv['uploaded_at'] }} · @endif{{ $cv['size'] }}
                        </p>
                    </div>
                    <div class="wn-cv-actions">
                        <a class="btn-wn-secondary" href="{{ route('candidate.profile.cv.download') }}">
                            <i class="bi bi-eye"></i> Ver
                        </a>
                        <form method="POST" action="{{ route('candidate.profile.cv.store') }}" enctype="multipart/form-data" class="wn-cv-upload-form">
                            @csrf
                            <input type="file" name="cv_file" accept=".pdf,.doc,.docx" class="wn-cv-input-file">
                            <button class="btn-wn-secondary" type="button" data-cv-replace>
                                <i class="bi bi-arrow-repeat"></i> Reemplazar
                            </button>
                        </form>
                        <form method="POST" action="{{ route('candidate.profile.cv.destroy') }}" class="wn-confirm-form">
                            @csrf
                            @method('DELETE')
                            <button class="btn-wn-danger" type="submit">
                                <i class="bi bi-trash"></i> Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <form method="POST" action="{{ route('candidate.profile.cv.store') }}" enctype="multipart/form-data" class="wn-cv-upload-form wn-cv-upload-empty">
                    @csrf
                    <div class="wn-empty wn-empty-upload">
                        <i class="bi bi-cloud-arrow-up"></i>
                        <p>Todavía no cargaste tu CV. Podés subirlo en PDF, DOC o DOCX (máximo 4 MB).</p>
                        <span class="btn-wn-primary wn-cv-pick">
                            <i class="bi bi-upload"></i> Cargar CV
                        </span>
                        <input type="file" name="cv_file" accept=".pdf,.doc,.docx" class="wn-cv-input-file">
                    </div>
                </form>
            @endif
        </section>

        {{-- PREFERENCIAS / SITUACIÓN LABORAL --}}
        <section class="wn-section">
            <div class="wn-section-header">
                <h2 class="wn-section-title">
                    <i class="bi bi-sliders"></i>
                    Situación laboral
                </h2>
                <div class="wn-section-actions">
                    <button class="wn-add-btn" type="button" data-modal-open="modal-situacion">
                        <i class="bi bi-pencil"></i> Editar
                    </button>
                </div>
            </div>

            <div class="wn-preferences">
                <div class="wn-preference-card">
                    <div class="wn-preference-icon"><i class="bi bi-briefcase-fill"></i></div>
                    <div class="wn-preference-info">
                        <div class="wn-preference-label">Estado laboral</div>
                        <div class="wn-preference-value">{{ $profile['estadoLaboral'] ?: '—' }}</div>
                    </div>
                </div>
                <div class="wn-preference-card">
                    <div class="wn-preference-icon"><i class="bi bi-mortarboard-fill"></i></div>
                    <div class="wn-preference-info">
                        <div class="wn-preference-label">Estudiando actualmente</div>
                        <div class="wn-preference-value">{{ $profile['esEstudiante'] ? 'Sí' : 'No' }}</div>
                    </div>
                </div>
                <div class="wn-preference-card">
                    <div class="wn-preference-icon"><i class="bi bi-bank"></i></div>
                    <div class="wn-preference-info">
                        <div class="wn-preference-label">Universidad</div>
                        <div class="wn-preference-value">{{ $profile['universidad'] ?: '—' }}</div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ============================================ --}}
        {{-- MODALES --}}
        {{-- ============================================ --}}

        {{-- Datos personales --}}
        <div class="wn-modal-backdrop" id="modal-personal">
            <div class="wn-modal" role="dialog" aria-modal="true">
                <div class="wn-modal-header">
                    <h3 class="wn-modal-title">Editar perfil</h3>
                    <button class="wn-modal-close" type="button" data-modal-close><i class="bi bi-x-lg"></i></button>
                </div>
                <form method="POST" action="{{ route('candidate.profile.update') }}">
                    @csrf
                    <div class="wn-modal-body">
                        <div class="wn-form-row">
                            <div>
                                <label class="wn-label" for="p-nombre">Nombre completo *</label>
                                <input class="wn-input" id="p-nombre" type="text" name="nombre" value="{{ old('nombre', $profile['name']) }}" required maxlength="100">
                            </div>
                            <div>
                                <label class="wn-label" for="p-email">Email *</label>
                                <input class="wn-input" id="p-email" type="email" name="email" value="{{ old('email', $profile['email']) }}" required maxlength="100">
                            </div>
                        </div>
                        <div class="wn-form-row">
                            <div>
                                <label class="wn-label" for="p-fnac">Fecha de nacimiento</label>
                                <input class="wn-input" id="p-fnac" type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $profile['rawFnac']) }}">
                            </div>
                            <div>
                                <label class="wn-label" for="p-genero">Género</label>
                                <select class="wn-select" id="p-genero" name="genero">
                                    <option value="">Preferir no decir</option>
                                    @foreach ($generos as $genero)
                                        <option value="{{ $genero }}" @selected(old('genero', $profile['genero']) === $genero)>{{ $genero }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="wn-form-row">
                            <div>
                                <label class="wn-label" for="p-nac">Nacionalidad</label>
                                <input class="wn-input" id="p-nac" type="text" name="nacionalidad" value="{{ old('nacionalidad', $profile['location']) }}" maxlength="80" placeholder="Ej: Paraguaya">
                            </div>
                            <div>
                                <label class="wn-label" for="p-estado">Estado laboral</label>
                                <select class="wn-select" id="p-estado" name="estado_laboral">
                                    <option value="">—</option>
                                    @foreach ($estadosLaborales as $estado)
                                        <option value="{{ $estado }}" @selected(old('estado_laboral', $profile['estadoLaboral']) === $estado)>{{ $estado }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="wn-form-row">
                            <div>
                                <label class="wn-label" for="p-estudia">Estudiando actualmente</label>
                                <label class="wn-check" for="p-estudia">
                                    <input type="checkbox" id="p-estudia" name="es_estudiante" value="1" @checked((bool) old('es_estudiante', $profile['esEstudiante']))>
                                    <span>Estoy estudiando en este momento</span>
                                </label>
                            </div>
                            <div>
                                <label class="wn-label" for="p-uni">Universidad</label>
                                <input class="wn-input" id="p-uni" type="text" name="universidad" value="{{ old('universidad', $profile['universidad']) }}" maxlength="120" placeholder="Ej: UNaM">
                            </div>
                        </div>
                        <div>
                            <label class="wn-label" for="p-perfil">Sobre mí</label>
                            <textarea class="wn-textarea" id="p-perfil" name="perfil" maxlength="2000" placeholder="Una breve descripción de vos, tu experiencia y lo que buscás.">{{ old('perfil', $profile['about']) }}</textarea>
                        </div>
                    </div>
                    <div class="wn-modal-footer">
                        <button class="btn-wn-secondary" type="button" data-modal-close>Cancelar</button>
                        <button class="btn-wn-primary" type="submit"><i class="bi bi-check-lg"></i> Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Experiencia laboral --}}
        <div class="wn-modal-backdrop" id="modal-experiencia">
            <div class="wn-modal" role="dialog" aria-modal="true">
                <div class="wn-modal-header">
                    <h3 class="wn-modal-title" data-title-default="Agregar experiencia">Agregar experiencia</h3>
                    <button class="wn-modal-close" type="button" data-modal-close><i class="bi bi-x-lg"></i></button>
                </div>
                <form method="POST" action="{{ route('candidate.profile.experiencia.store') }}">
                    @csrf
                    <div class="wn-modal-body">
                        <div class="wn-form-row">
                            <div>
                                <label class="wn-label" for="e-puesto">Puesto *</label>
                                <input class="wn-input" id="e-puesto" type="text" name="puesto" required maxlength="100" placeholder="Ej: Back-office">
                            </div>
                            <div>
                                <label class="wn-label" for="e-empresa">Empresa *</label>
                                <input class="wn-input" id="e-empresa" type="text" name="empresa" required maxlength="100" placeholder="Ej: Stone SAS">
                            </div>
                        </div>
                        <div class="wn-form-row">
                            <div>
                                <label class="wn-label" for="e-inicio">Desde *</label>
                                <input class="wn-input" id="e-inicio" type="date" name="fecha_inicio" required>
                            </div>
                            <div>
                                <label class="wn-label" for="e-fin">Hasta</label>
                                <input class="wn-input" id="e-fin" type="date" name="fecha_fin">
                            </div>
                        </div>
                        <label class="wn-check">
                            <input type="checkbox" name="actual" id="e-actual" value="1" data-toggle-disable="e-fin">
                            <span>Trabajo aquí actualmente</span>
                        </label>
                        <div>
                            <label class="wn-label" for="e-ubicacion">Ubicación</label>
                            <input class="wn-input" id="e-ubicacion" type="text" name="ubicacion" maxlength="120" placeholder="Ej: Posadas, Misiones">
                        </div>
                        <div>
                            <label class="wn-label" for="e-tareas">Tareas principales</label>
                            <textarea class="wn-textarea" id="e-tareas" name="tareas" maxlength="2000" placeholder="¿Qué hacías en este puesto?"></textarea>
                        </div>
                    </div>
                    <div class="wn-modal-footer">
                        <button class="btn-wn-secondary" type="button" data-modal-close>Cancelar</button>
                        <button class="btn-wn-primary" type="submit"><i class="bi bi-check-lg"></i> Guardar experiencia</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Formación académica --}}
        <div class="wn-modal-backdrop" id="modal-titulo">
            <div class="wn-modal" role="dialog" aria-modal="true">
                <div class="wn-modal-header">
                    <h3 class="wn-modal-title" data-title-default="Agregar formación académica">Agregar formación académica</h3>
                    <button class="wn-modal-close" type="button" data-modal-close><i class="bi bi-x-lg"></i></button>
                </div>
                <form method="POST" action="{{ route('candidate.profile.titulo.store') }}">
                    @csrf
                    <div class="wn-modal-body">
                        <div class="wn-form-row">
                            <div>
                                <label class="wn-label" for="t-nombre">Título / curso *</label>
                                <input class="wn-input" id="t-nombre" type="text" name="nombre" required maxlength="120" placeholder="Ej: Analista de Sistemas">
                            </div>
                            <div>
                                <label class="wn-label" for="t-tipo">Tipo</label>
                                <select class="wn-select" id="t-tipo" name="tipo">
                                    <option value="">—</option>
                                    <option value="Secundario">Secundario</option>
                                    <option value="Terciario">Terciario</option>
                                    <option value="Universitario">Universitario</option>
                                    <option value="Posgrado">Posgrado</option>
                                    <option value="Curso">Curso</option>
                                    <option value="Certificación">Certificación</option>
                                </select>
                            </div>
                        </div>
                        <div class="wn-form-row">
                            <div>
                                <label class="wn-label" for="t-inst">Institución *</label>
                                <input class="wn-input" id="t-inst" type="text" name="institucion" required maxlength="120" placeholder="Ej: UNaM">
                            </div>
                            <div>
                                <label class="wn-label" for="t-inicio">Desde *</label>
                                <input class="wn-input" id="t-inicio" type="date" name="fecha_inicio" required>
                            </div>
                        </div>
                        <div class="wn-form-row">
                            <div>
                                <label class="wn-label" for="t-fin">Hasta</label>
                                <input class="wn-input" id="t-fin" type="date" name="fecha_fin">
                            </div>
                            <div>
                                <label class="wn-label" for="t-cert">Certificado</label>
                                <label class="wn-check" for="t-cert">
                                    <input type="checkbox" id="t-cert" name="certificado" value="1">
                                    <span>Tengo certificado</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="wn-modal-footer">
                        <button class="btn-wn-secondary" type="button" data-modal-close>Cancelar</button>
                        <button class="btn-wn-primary" type="submit"><i class="bi bi-check-lg"></i> Guardar formación</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Habilidad --}}
        <div class="wn-modal-backdrop" id="modal-habilidad">
            <div class="wn-modal" role="dialog" aria-modal="true">
                <div class="wn-modal-header">
                    <h3 class="wn-modal-title">Agregar habilidad</h3>
                    <button class="wn-modal-close" type="button" data-modal-close><i class="bi bi-x-lg"></i></button>
                </div>
                <form method="POST" action="{{ route('candidate.profile.habilidad.store') }}">
                    @csrf
                    <div class="wn-modal-body">
                        <div>
                            <label class="wn-label" for="h-nombre">Habilidad *</label>
                            <input class="wn-input" id="h-nombre" type="text" name="habilidad" required maxlength="60" placeholder="Ej: Excel avanzado">
                        </div>
                        <div>
                            <label class="wn-label" for="h-nivel">Nivel</label>
                            <select class="wn-select" id="h-nivel" name="nivel">
                                @foreach ($nivelesAptitud as $nivel)
                                    <option value="{{ $nivel }}" @selected($nivel === 'Intermedio')>{{ $nivel }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="wn-modal-footer">
                        <button class="btn-wn-secondary" type="button" data-modal-close>Cancelar</button>
                        <button class="btn-wn-primary" type="submit"><i class="bi bi-check-lg"></i> Agregar habilidad</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Idioma --}}
        <div class="wn-modal-backdrop" id="modal-idioma">
            <div class="wn-modal" role="dialog" aria-modal="true">
                <div class="wn-modal-header">
                    <h3 class="wn-modal-title">Agregar idioma</h3>
                    <button class="wn-modal-close" type="button" data-modal-close><i class="bi bi-x-lg"></i></button>
                </div>
                <form method="POST" action="{{ route('candidate.profile.idioma.store') }}">
                    @csrf
                    <div class="wn-modal-body">
                        <div>
                            <label class="wn-label" for="i-nombre">Idioma *</label>
                            <input class="wn-input" id="i-nombre" type="text" name="idioma" required maxlength="60" list="idiomas-lista" placeholder="Ej: Inglés">
                            <datalist id="idiomas-lista">
                                <option value="Español"></option>
                                <option value="Inglés"></option>
                                <option value="Portugués"></option>
                                <option value="Francés"></option>
                                <option value="Alemán"></option>
                                <option value="Italiano"></option>
                                <option value="Guaraní"></option>
                            </datalist>
                        </div>
                        <div>
                            <label class="wn-label" for="i-nivel">Nivel *</label>
                            <select class="wn-select" id="i-nivel" name="id_nivel" required>
                                @forelse ($nivelesIdioma as $nivel)
                                    <option value="{{ $nivel->id_nivel }}">{{ $nivel->nombre }}</option>
                                @empty
                                    <option value="">Sin niveles configurados</option>
                                @endforelse
                            </select>
                        </div>
                    </div>
                    <div class="wn-modal-footer">
                        <button class="btn-wn-secondary" type="button" data-modal-close>Cancelar</button>
                        <button class="btn-wn-primary" type="submit"><i class="bi bi-check-lg"></i> Agregar idioma</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Situación laboral --}}
        <div class="wn-modal-backdrop" id="modal-situacion">
            <div class="wn-modal" role="dialog" aria-modal="true">
                <div class="wn-modal-header">
                    <h3 class="wn-modal-title">Editar situación laboral</h3>
                    <button class="wn-modal-close" type="button" data-modal-close><i class="bi bi-x-lg"></i></button>
                </div>
                <form method="POST" action="{{ route('candidate.profile.situacion.update') }}">
                    @csrf
                    <div class="wn-modal-body">
                        <div>
                            <label class="wn-label" for="s-estado">Estado laboral</label>
                            <select class="wn-select" id="s-estado" name="estado_laboral">
                                <option value="">—</option>
                                @foreach ($estadosLaborales as $estado)
                                    <option value="{{ $estado }}" @selected(old('estado_laboral', $profile['estadoLaboral']) === $estado)>{{ $estado }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="wn-label" for="s-estudia">Estudiando actualmente</label>
                            <label class="wn-check" for="s-estudia">
                                <input type="checkbox" id="s-estudia" name="es_estudiante" value="1" @checked((bool) old('es_estudiante', $profile['esEstudiante']))>
                                <span>Estoy estudiando en este momento</span>
                            </label>
                        </div>
                        <div>
                            <label class="wn-label" for="s-uni">Universidad</label>
                            <input class="wn-input" id="s-uni" type="text" name="universidad" value="{{ old('universidad', $profile['universidad']) }}" maxlength="120" placeholder="Ej: UNaM">
                        </div>
                    </div>
                    <div class="wn-modal-footer">
                        <button class="btn-wn-secondary" type="button" data-modal-close>Cancelar</button>
                        <button class="btn-wn-primary" type="submit"><i class="bi bi-check-lg"></i> Guardar</button>
                    </div>
                </form>
            </div>
        </div>

    </main>
@endsection
