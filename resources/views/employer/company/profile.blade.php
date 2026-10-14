@extends('layouts.app')

@section('title', 'Work.net - Mi empresa')

@section('content')
    <div class="wn-bg"><div class="wn-bg-grid"></div></div>

    @include('partials.wn-navbar')

    <main class="wn-main wn-page-employer">
        <div class="wn-company-header">
            <div class="wn-company-cover">
                <button class="wn-company-cover-edit" type="button">
                    <i class="bi bi-camera-fill"></i> Cambiar portada
                </button>
            </div>

            <div class="wn-company-info">
                <div class="wn-company-logo-wrapper">
                    <div class="wn-company-logo">{{ $company['initials'] }}</div>
                    <button class="wn-company-logo-edit" title="Cambiar logo" type="button">
                        <i class="bi bi-camera-fill"></i>
                    </button>
                </div>

                <div class="wn-company-details">
                    <div class="wn-company-name-row">
                        <h1 class="wn-company-name">{{ $company['name'] }}</h1>
                        @if($company['verified'])
                            <span class="wn-company-verified">
                                <i class="bi bi-patch-check-fill"></i> Verificada
                            </span>
                        @endif
                    </div>

                    <div class="wn-company-meta">
                        <span><i class="bi bi-card-text"></i> RUC: {{ $company['ruc'] }}</span>
                        <span><i class="bi bi-geo-alt-fill"></i> Encarnación, Paraguay</span>
                        <span><i class="bi bi-envelope-fill"></i> {{ $company['email'] }}</span>
                        <span><i class="bi bi-telephone-fill"></i> {{ $company['phone'] }}</span>
                        <span><i class="bi bi-globe"></i> {{ $company['website'] }}</span>
                    </div>

                    <div class="wn-company-actions">
                        <button class="btn-wn-primary" type="button">
                            <i class="bi bi-pencil"></i> Editar empresa
                        </button>
                        <button class="btn-wn-secondary" type="button">
                            <i class="bi bi-eye"></i> Ver perfil público
                        </button>
                        <button class="btn-wn-secondary" type="button">
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
                <div class="wn-profile-progress-value">{{ $company['completion'] }}%</div>
            </div>
            <div class="wn-progress-bar">
                <div class="wn-progress-fill" style="width: {{ $company['completion'] }}%;"></div>
            </div>
            <p class="wn-profile-progress-hint">
                <i class="bi bi-lightbulb-fill"></i>
                Agregá una <strong>foto de portada</strong> y una <strong>descripción institucional</strong> para completar tu perfil.
            </p>
        </section>

        <section class="wn-stats">
            <div class="wn-stat-card">
                <div class="wn-stat-icon success"><i class="bi bi-briefcase-fill"></i></div>
                <div class="wn-stat-info">
                    <div class="wn-stat-value">{{ $stats['active_offers'] }}</div>
                    <div class="wn-stat-label">Ofertas activas</div>
                </div>
            </div>
            <div class="wn-stat-card">
                <div class="wn-stat-icon"><i class="bi bi-inbox-fill"></i></div>
                <div class="wn-stat-info">
                    <div class="wn-stat-value">{{ $stats['applications'] }}</div>
                    <div class="wn-stat-label">Postulaciones</div>
                </div>
            </div>
            <div class="wn-stat-card">
                <div class="wn-stat-icon purple"><i class="bi bi-people-fill"></i></div>
                <div class="wn-stat-info">
                    <div class="wn-stat-value">{{ $stats['followers'] }}</div>
                    <div class="wn-stat-label">Seguidores</div>
                </div>
            </div>
            <div class="wn-stat-card">
                <div class="wn-stat-icon warning"><i class="bi bi-star-fill"></i></div>
                <div class="wn-stat-info">
                    <div class="wn-stat-value">{{ $stats['rating'] }}</div>
                    <div class="wn-stat-label">Valoración</div>
                </div>
            </div>
        </section>

        <section class="wn-section">
            <div class="wn-section-header">
                <h2 class="wn-section-title">
                    <i class="bi bi-file-text"></i>
                    Descripción institucional
                </h2>
                <button class="wn-add-btn" type="button">
                    <i class="bi bi-pencil"></i> Editar
                </button>
            </div>
            <div class="wn-company-description">
                {{ $company['description'] }}
            </div>
        </section>

        <section class="wn-section">
            <div class="wn-section-header">
                <h2 class="wn-section-title">
                    <i class="bi bi-building"></i>
                    Datos institucionales
                </h2>
                <button class="wn-add-btn" type="button">
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
                    <div class="wn-personal-value">{{ $company['ruc'] }}</div>
                </div>
                <div class="wn-personal-item">
                    <div class="wn-personal-label"><i class="bi bi-briefcase"></i> Rubro</div>
                    <div class="wn-personal-value">{{ $company['industry'] }}</div>
                </div>
                <div class="wn-personal-item">
                    <div class="wn-personal-label"><i class="bi bi-people"></i> Tamaño</div>
                    <div class="wn-personal-value">{{ $company['size'] }}</div>
                </div>
                <div class="wn-personal-item">
                    <div class="wn-personal-label"><i class="bi bi-calendar3"></i> Fundación</div>
                    <div class="wn-personal-value">{{ $company['founded'] }}</div>
                </div>
                <div class="wn-personal-item">
                    <div class="wn-personal-label"><i class="bi bi-globe"></i> Sitio web</div>
                    <div class="wn-personal-value">{{ $company['website'] }}</div>
                </div>
            </div>
        </section>

        <section class="wn-section">
            <div class="wn-section-header">
                <h2 class="wn-section-title">
                    <i class="bi bi-telephone"></i>
                    Contacto
                </h2>
                <button class="wn-add-btn" type="button">
                    <i class="bi bi-pencil"></i> Editar
                </button>
            </div>
            <div class="wn-personal-grid">
                <div class="wn-personal-item">
                    <div class="wn-personal-label"><i class="bi bi-envelope"></i> Email corporativo</div>
                    <div class="wn-personal-value">{{ $company['email'] }}</div>
                </div>
                <div class="wn-personal-item">
                    <div class="wn-personal-label"><i class="bi bi-telephone"></i> Teléfono</div>
                    <div class="wn-personal-value">{{ $company['phone'] }}</div>
                </div>
                <div class="wn-personal-item">
                    <div class="wn-personal-label"><i class="bi bi-whatsapp"></i> WhatsApp</div>
                    <div class="wn-personal-value">{{ $company['whatsapp'] }}</div>
                </div>
            </div>
        </section>

        <section class="wn-section">
            <div class="wn-section-header">
                <h2 class="wn-section-title">
                    <i class="bi bi-geo-alt"></i>
                    Ubicación
                </h2>
                <button class="wn-add-btn" type="button">
                    <i class="bi bi-pencil"></i> Editar
                </button>
            </div>
            <div class="wn-location-card">
                <div class="wn-location-address">
                    <i class="bi bi-pin-map-fill"></i>
                    {{ $company['address'] }}
                </div>
                <div class="wn-company-map" id="companyMap"></div>
            </div>
        </section>

        <section class="wn-section">
            <div class="wn-section-header">
                <h2 class="wn-section-title">
                    <i class="bi bi-share"></i>
                    Redes sociales
                </h2>
                <button class="wn-add-btn" type="button">
                    <i class="bi bi-plus-lg"></i> Agregar
                </button>
            </div>
            <div class="wn-social-list">
                @foreach($socials as $social)
                    <a href="#" class="wn-social-item">
                        <i class="bi {{ $social['icon'] }}"></i> {{ $social['name'] }}
                    </a>
                @endforeach
            </div>
        </section>

    </main>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const companyMap = L.map('companyMap', { zoomControl: true, attributionControl: false }).setView([-27.3306, -55.8667], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { subdomains: ['a', 'b', 'c'], maxZoom: 19 }).addTo(companyMap);
        L.marker([-27.3306, -55.8667]).addTo(companyMap);
    </script>
@endpush
