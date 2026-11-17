@extends('layouts.app')

@section('title', 'Work.net - Mi empresa')

@section('content')
    <div class="wn-bg"><div class="wn-bg-grid"></div></div>

    @include('partials.wn-navbar')

    <main class="wn-main wn-page-employer wn-page-company">
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

        <div class="wn-company-header">
            <div class="wn-company-cover"></div>

            <div class="wn-company-info">
                <div class="wn-company-logo-wrapper">
                    @if($logoUrl)
                        <img class="wn-company-logo-img" src="{{ $logoUrl }}" alt="Logo de {{ $company['name'] }}">
                    @else
                        <div class="wn-company-logo">{{ $company['initials'] }}</div>
                    @endif
                    <button class="wn-company-logo-edit" title="Cambiar logo" type="button" data-modal-open="modal-logo">
                        <i class="bi bi-camera-fill"></i>
                    </button>
                </div>

                <div class="wn-company-details">
                    <div class="wn-company-name-row">
                        <h1 class="wn-company-name">{{ $company['name'] }}</h1>
                    </div>

                    <div class="wn-company-meta">
                        <span><i class="bi bi-card-text"></i> RUC: {{ $company['ruc'] ?? '—' }}</span>
                        <span><i class="bi bi-geo-alt-fill"></i> {{ $sede?->ubicacion ? $sede->ubicacion->ciudad . ', ' . $sede->ubicacion->pais : 'Sin ubicación' }}</span>
                        <span><i class="bi bi-envelope-fill"></i> {{ $company['email'] }}</span>
                        <span><i class="bi bi-telephone-fill"></i> {{ $company['telefono'] ?? '—' }}</span>
                        <span><i class="bi bi-globe"></i> {{ $company['sitioWeb'] ?? '—' }}</span>
                    </div>

                    <div class="wn-company-actions">
                        <button class="btn-wn-primary" type="button" data-modal-open="modal-empresa">
                            <i class="bi bi-pencil"></i> Editar empresa
                        </button>
                        <a class="btn-wn-secondary" href="{{ route('candidate.company.show', Auth::user()->empleador->id_empleador) }}">
                            <i class="bi bi-eye"></i> Ver perfil público
                        </a>
                        <button class="btn-wn-secondary" type="button" data-share-url="{{ route('candidate.company.show', Auth::user()->empleador->id_empleador) }}">
                            <i class="bi bi-share"></i> Compartir
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <section class="wn-profile-progress-card">
            <div class="wn-profile-progress-header">
                <div class="wn-profile-progress-title">
                    <i class="bi bi-graph-up-arrow"></i>
                    <span>Completitud del perfil de empresa</span>
                </div>
                <div class="wn-profile-progress-value">{{ $progress['percentage'] }}%</div>
            </div>
            <div class="wn-progress-bar">
                <div class="wn-progress-fill" style="width: {{ $progress['percentage'] }}%;"></div>
            </div>
            <p class="wn-profile-progress-hint">
                <i class="bi bi-lightbulb-fill"></i>
                @if(empty($progress['missing']))
                    ¡Perfil de empresa completo! Se ve mejor para los candidatos.
                @elseif(count($progress['missing']) === 1)
                    Agregá tu <strong>{{ $progress['missing'][0] }}</strong> para completar tu perfil.
                @else
                    Agregá <strong>{{ implode('</strong>, <strong>', array_slice($progress['missing'], 0, -1)) }}</strong>
                    y <strong>{{ end($progress['missing']) }}</strong> para completar tu perfil.
                @endif
            </p>
        </section>

        <section class="wn-stats">
            @foreach($stats as $stat)
                <div class="wn-stat-card">
                    <div class="wn-stat-icon {{ $stat['modifier'] }}"><i class="bi bi-{{ $stat['icon'] }}"></i></div>
                    <div class="wn-stat-info">
                        <div class="wn-stat-value">{{ $stat['value'] }}</div>
                        <div class="wn-stat-label">{{ $stat['label'] }}</div>
                    </div>
                </div>
            @endforeach
        </section>

        <section class="wn-section">
            <div class="wn-section-header">
                <h2 class="wn-section-title">
                    <i class="bi bi-file-text"></i>
                    Descripción institucional
                </h2>
                <button class="wn-add-btn" type="button" data-modal-open="modal-descripcion">
                    <i class="bi bi-pencil"></i> Editar
                </button>
            </div>
            <div class="wn-company-description">
                {{ $company['descripcion'] ?? 'Todavía no cargaste la descripción de tu empresa.' }}
            </div>
        </section>

        <section class="wn-section">
            <div class="wn-section-header">
                <h2 class="wn-section-title">
                    <i class="bi bi-building"></i>
                    Datos institucionales
                </h2>
                <button class="wn-add-btn" type="button" data-modal-open="modal-empresa">
                    <i class="bi bi-pencil"></i> Editar
                </button>
            </div>
            <div class="wn-personal-grid">
                <div class="wn-personal-item">
                    <div class="wn-personal-label"><i class="bi bi-building"></i> Razón social</div>
                    <div class="wn-personal-value">{{ $company['name'] }}</div>
                </div>
                <div class="wn-personal-item">
                    <div class="wn-personal-label"><i class="bi bi-card-text"></i> RUC</div>
                    <div class="wn-personal-value">{{ $company['ruc'] ?? '—' }}</div>
                </div>
                <div class="wn-personal-item">
                    <div class="wn-personal-label"><i class="bi bi-globe"></i> Sitio web</div>
                    <div class="wn-personal-value">{{ $company['sitioWeb'] ?? '—' }}</div>
                </div>
                <div class="wn-personal-item">
                    <div class="wn-personal-label"><i class="bi bi-envelope"></i> Email de la cuenta</div>
                    <div class="wn-personal-value">{{ $company['email'] }}</div>
                </div>
                @if($company['registradoEn'])
                    <div class="wn-personal-item">
                        <div class="wn-personal-label"><i class="bi bi-calendar3"></i> En Work.net desde</div>
                        <div class="wn-personal-value">{{ $company['registradoEn'] }}</div>
                    </div>
                @endif
            </div>
        </section>

        <section class="wn-section">
            <div class="wn-section-header">
                <h2 class="wn-section-title">
                    <i class="bi bi-telephone"></i>
                    Contacto
                </h2>
                <button class="wn-add-btn" type="button" data-modal-open="modal-empresa">
                    <i class="bi bi-pencil"></i> Editar
                </button>
            </div>
            <div class="wn-personal-grid">
                <div class="wn-personal-item">
                    <div class="wn-personal-label"><i class="bi bi-envelope"></i> Email</div>
                    <div class="wn-personal-value">{{ $company['email'] }}</div>
                </div>
                <div class="wn-personal-item">
                    <div class="wn-personal-label"><i class="bi bi-telephone"></i> Teléfono</div>
                    <div class="wn-personal-value">{{ $company['telefono'] ?? '—' }}</div>
                </div>
            </div>
        </section>

        <section class="wn-section">
            <div class="wn-section-header">
                <h2 class="wn-section-title">
                    <i class="bi bi-geo-alt"></i>
                    Ubicación de la sede
                </h2>
                <button class="wn-add-btn" type="button" data-modal-open="modal-ubicacion">
                    <i class="bi bi-pencil"></i> {{ $sede ? 'Editar' : 'Agregar' }}
                </button>
            </div>
            @if($sede?->ubicacion)
                <div class="wn-location-card">
                    <div class="wn-location-address">
                        <i class="bi bi-pin-map-fill"></i>
                        {{ $sede->ubicacion->direccion }}, {{ $sede->ubicacion->ciudad }}, {{ $sede->ubicacion->pais }}
                    </div>
                    @if($sede->nombre || $sede->horario)
                        <div class="wn-location-address wn-location-extra">
                            @if($sede->nombre)<span><i class="bi bi-signpost-split-fill"></i> {{ $sede->nombre }}</span>@endif
                            @if($sede->horario)<span><i class="bi bi-clock-fill"></i> {{ $sede->horario }}</span>@endif
                        </div>
                    @endif
                    <div class="wn-company-map" id="companyMap"
                        data-lat="{{ $sede->ubicacion->latitud }}" data-lng="{{ $sede->ubicacion->longitud }}"></div>
                </div>
            @else
                <div class="wn-location-card wn-location-empty">
                    <div class="wn-location-address">
                        <i class="bi bi-geo-alt"></i>
                        Todavía no cargaste la ubicación de tu sede.
                    </div>
                    <button class="btn-wn-primary" type="button" data-modal-open="modal-ubicacion">
                        <i class="bi bi-plus-lg"></i> Cargar sede
                    </button>
                </div>
            @endif
        </section>

        {{-- ============================================ --}}
        {{-- Modales (mismo patrón que el perfil candidato) --}}
        {{-- ============================================ --}}

        {{-- Datos de la empresa + contacto --}}
        <div class="wn-modal-backdrop" id="modal-empresa">
            <div class="wn-modal" role="dialog" aria-modal="true">
                <div class="wn-modal-header">
                    <h3 class="wn-modal-title">Editar empresa</h3>
                    <button class="wn-modal-close" type="button" data-modal-close><i class="bi bi-x-lg"></i></button>
                </div>
                <form method="POST" action="{{ route('employer.company.update') }}">
                    @csrf
                    <div class="wn-modal-body">
                        <div class="wn-form-row">
                            <div>
                                <label class="wn-label" for="e-nombre">Razón social *</label>
                                <input class="wn-input" id="e-nombre" type="text" name="nombre_empresa" value="{{ old('nombre_empresa', $company['name']) }}" required maxlength="100">
                            </div>
                            <div>
                                <label class="wn-label" for="e-ruc">RUC</label>
                                <input class="wn-input" id="e-ruc" type="text" name="ruc" value="{{ old('ruc', $company['ruc']) }}" maxlength="20" placeholder="Ej: 80012345-6">
                            </div>
                        </div>
                        <div class="wn-form-row">
                            <div>
                                <label class="wn-label" for="e-email">Email de contacto *</label>
                                <input class="wn-input" id="e-email" type="email" name="email" value="{{ old('email', $company['email']) }}" required maxlength="100">
                                <p class="wn-field-hint">También es tu correo de acceso a Work.net.</p>
                            </div>
                            <div>
                                <label class="wn-label" for="e-telefono">Teléfono</label>
                                <input class="wn-input" id="e-telefono" type="text" name="telefono" value="{{ old('telefono', $company['telefono']) }}" maxlength="20" placeholder="Ej: +595 71 200 300">
                            </div>
                        </div>
                        <div>
                            <label class="wn-label" for="e-web">Sitio web</label>
                            <input class="wn-input" id="e-web" type="text" name="sitio_web" value="{{ old('sitio_web', $company['sitioWeb']) }}" maxlength="255" placeholder="Ej: www.miempresa.com.py">
                        </div>
                    </div>
                    <div class="wn-modal-footer">
                        <button class="btn-wn-secondary" type="button" data-modal-close>Cancelar</button>
                        <button class="btn-wn-primary" type="submit"><i class="bi bi-check-lg"></i> Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Descripción institucional --}}
        <div class="wn-modal-backdrop" id="modal-descripcion">
            <div class="wn-modal" role="dialog" aria-modal="true">
                <div class="wn-modal-header">
                    <h3 class="wn-modal-title">Descripción institucional</h3>
                    <button class="wn-modal-close" type="button" data-modal-close><i class="bi bi-x-lg"></i></button>
                </div>
                <form method="POST" action="{{ route('employer.company.descripcion') }}">
                    @csrf
                    <div class="wn-modal-body">
                        <label class="wn-label" for="d-descripcion">Contale a los candidatos quiénes son</label>
                        <textarea class="wn-textarea" id="d-descripcion" name="descripcion" maxlength="3000" rows="6" placeholder="A qué se dedica la empresa, su misión, el equipo, lo que ofrecen...">{{ old('descripcion', $company['descripcion']) }}</textarea>
                    </div>
                    <div class="wn-modal-footer">
                        <button class="btn-wn-secondary" type="button" data-modal-close>Cancelar</button>
                        <button class="btn-wn-primary" type="submit"><i class="bi bi-check-lg"></i> Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Logo corporativo --}}
        <div class="wn-modal-backdrop" id="modal-logo">
            <div class="wn-modal" role="dialog" aria-modal="true">
                <div class="wn-modal-header">
                    <h3 class="wn-modal-title">Logo corporativo</h3>
                    <button class="wn-modal-close" type="button" data-modal-close><i class="bi bi-x-lg"></i></button>
                </div>
                <form method="POST" action="{{ route('employer.company.logo') }}" enctype="multipart/form-data" class="wn-logo-form">
                    @csrf
                    <div class="wn-modal-body">
                        <p class="wn-modal-text">Elegí una imagen cuadrada (JPG, PNG o WebP, hasta 2 MB). Se muestra como logo de tu empresa en el perfil y en tus ofertas.</p>
                        <input class="wn-input wn-logo-input-file" type="file" name="logo_file" accept=".jpg,.jpeg,.png,.webp" required>
                    </div>
                    <div class="wn-modal-footer">
                        <button class="btn-wn-secondary" type="button" data-modal-close>Cancelar</button>
                        <button class="btn-wn-primary" type="submit"><i class="bi bi-upload"></i> Cargar logo</button>
                    </div>
                </form>
                @if($logoUrl)
                    <form method="POST" action="{{ route('employer.company.logo.destroy') }}" class="wn-confirm-form wn-logo-remove-form">
                        @csrf
                        <div class="wn-modal-footer wn-modal-footer-top">
                            <button class="btn-wn-secondary" type="submit"><i class="bi bi-trash"></i> Quitar logo actual</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>

        {{-- Ubicación de la sede --}}
        <div class="wn-modal-backdrop" id="modal-ubicacion">
            <div class="wn-modal" role="dialog" aria-modal="true">
                <div class="wn-modal-header">
                    <h3 class="wn-modal-title">Ubicación de la sede</h3>
                    <button class="wn-modal-close" type="button" data-modal-close><i class="bi bi-x-lg"></i></button>
                </div>
                <form method="POST" action="{{ route('employer.company.ubicacion') }}">
                    @csrf
                    <div class="wn-modal-body">
                        <div class="wn-form-row">
                            <div>
                                <label class="wn-label" for="u-sede">Nombre de la sede</label>
                                <input class="wn-input" id="u-sede" type="text" name="sede_nombre" value="{{ old('sede_nombre', $sede?->nombre) }}" maxlength="100" placeholder="Ej: Casa Central">
                            </div>
                            <div>
                                <label class="wn-label" for="u-tel">Teléfono de la sede</label>
                                <input class="wn-input" id="u-tel" type="text" name="sede_telefono" value="{{ old('sede_telefono', $sede?->telefono) }}" maxlength="20">
                            </div>
                        </div>

                        {{-- Selector de ubicación: búsqueda de dirección + mapa --}}
                        {{-- con marcador arrastrable (idéntico a "Publicar oferta") --}}
                        <div class="wn-location-search">
                            <i class="bi bi-search"></i>
                            <input class="wn-input" id="u-search" type="text" placeholder="Buscar dirección y elegir del listado..." autocomplete="off">
                            <div class="wn-search-results" id="u-search-results" hidden></div>
                        </div>

                        <div class="wn-location-map" id="u-map"></div>

                        <div class="wn-location-coords">
                            <div>
                                <label class="wn-label" for="u-lat">Latitud *</label>
                                <input class="wn-input" id="u-lat" type="number" step="any" name="latitud" value="{{ old('latitud', $sede?->ubicacion?->latitud) }}" required placeholder="Ej: -27.3306">
                            </div>
                            <div>
                                <label class="wn-label" for="u-lng">Longitud *</label>
                                <input class="wn-input" id="u-lng" type="number" step="any" name="longitud" value="{{ old('longitud', $sede?->ubicacion?->longitud) }}" required placeholder="Ej: -55.8667">
                            </div>
                        </div>

                        <div>
                            <label class="wn-label" for="u-dir">Dirección *</label>
                            <input class="wn-input" id="u-dir" type="text" name="direccion" value="{{ old('direccion', $sede?->ubicacion?->direccion) }}" required maxlength="200" placeholder="Ej: Av. Irrazábal 1234">
                        </div>
                        <div class="wn-form-row">
                            <div>
                                <label class="wn-label" for="u-ciudad">Ciudad *</label>
                                <input class="wn-input" id="u-ciudad" type="text" name="ciudad" value="{{ old('ciudad', $sede?->ubicacion?->ciudad) }}" required maxlength="100" placeholder="Ej: Encarnación">
                            </div>
                            <div>
                                <label class="wn-label" for="u-prov">Departamento / Provincia</label>
                                <input class="wn-input" id="u-prov" type="text" name="provincia" value="{{ old('provincia', $sede?->ubicacion?->provincia) }}" maxlength="100" placeholder="Ej: Itapúa">
                            </div>
                        </div>
                        <div class="wn-form-row">
                            <div>
                                <label class="wn-label" for="u-ref">Referencia</label>
                                <input class="wn-input" id="u-ref" type="text" name="referencia" value="{{ old('referencia', $sede?->ubicacion?->referencia) }}" maxlength="255" placeholder="Ej: A media cuadra de la Costanera">
                            </div>
                            <div>
                                <label class="wn-label" for="u-horario">Horario de atención</label>
                                <input class="wn-input" id="u-horario" type="text" name="sede_horario" value="{{ old('sede_horario', $sede?->horario) }}" maxlength="100" placeholder="Ej: Lun a Vie 08:00 - 17:00">
                            </div>
                        </div>
                        <p class="wn-field-hint">Buscá la dirección (o arrastrá el marcador en el mapa): los candidatos la ven en el mapa de tu perfil y de tus ofertas.</p>
                    </div>
                    <div class="wn-modal-footer">
                        <button class="btn-wn-secondary" type="button" data-modal-close>Cancelar</button>
                        <button class="btn-wn-primary" type="submit"><i class="bi bi-check-lg"></i> Guardar ubicación</button>
                    </div>
                </form>
            </div>
        </div>

    </main>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @if($sede?->ubicacion)
        <script>
            (function () {
                const el = document.getElementById('companyMap');
                if (!el) return;
                const lat = parseFloat(el.dataset.lat), lng = parseFloat(el.dataset.lng);
                const companyMap = L.map(el, { zoomControl: true, attributionControl: false }).setView([lat, lng], 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { subdomains: ['a', 'b', 'c'], maxZoom: 19 }).addTo(companyMap);
                L.marker([lat, lng]).addTo(companyMap);
            })();
        </script>
    @endif
@endpush
