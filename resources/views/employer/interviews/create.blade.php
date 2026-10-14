@extends('layouts.app')

@section('title', 'Work.net - Convocar a entrevista')

@section('content')
    <div class="wn-bg"><div class="wn-bg-grid"></div></div>

    @include('partials.wn-navbar')

    <main class="wn-main wn-page-employer">
        <header class="wn-header">
            <div class="wn-header-left">
                <h1>Convocar a entrevista</h1>
                <p>Programá una entrevista con el candidato. Se le notificará automáticamente.</p>
            </div>
            <div class="wn-header-actions">
                <a href="{{ route('employer.applicants.show', $applicant['id']) }}" class="btn-wn-secondary">
                    <i class="bi bi-arrow-left"></i> Cancelar
                </a>
            </div>
        </header>

        <div class="wn-candidate-card">
            <div class="wn-candidate-avatar">{{ $applicant['initials'] }}</div>
            <div class="wn-candidate-info">
                <h2 class="wn-candidate-name">{{ $applicant['name'] }}</h2>
                <p class="wn-candidate-position">{{ $applicant['position'] }}</p>
                <div class="wn-candidate-meta">
                    <span><i class="bi bi-envelope-fill"></i> {{ $applicant['email'] }}</span>
                    <span><i class="bi bi-telephone-fill"></i> {{ $applicant['phone'] }}</span>
                    <span><i class="bi bi-star-fill"></i> {{ $applicant['match'] }}% match</span>
                </div>
            </div>
        </div>

        <form class="wn-form" method="POST" action="#">
            @csrf

            <section class="wn-section">
                <div class="wn-section-header">
                    <div class="wn-section-icon"><i class="bi bi-calendar-event"></i></div>
                    <div>
                        <h2 class="wn-section-title">Fecha y hora</h2>
                        <p class="wn-section-subtitle">Definí cuándo se realizará la entrevista.</p>
                    </div>
                </div>

                <div class="wn-form-row">
                    <div class="wn-form-group">
                        <label class="wn-label">Fecha <span class="required">*</span></label>
                        <input type="date" name="date" class="wn-input" required>
                    </div>
                    <div class="wn-form-group">
                        <label class="wn-label">Hora de inicio <span class="required">*</span></label>
                        <input type="time" name="time" class="wn-input" required>
                    </div>
                </div>

                <div class="wn-form-row" style="margin-top: 16px;">
                    <div class="wn-form-group">
                        <label class="wn-label">Duración estimada <span class="required">*</span></label>
                        <select name="duration" class="wn-select" required>
                            <option value="">Seleccionar duración</option>
                            <option value="30">30 minutos</option>
                            <option value="45">45 minutos</option>
                            <option value="60" selected>1 hora</option>
                            <option value="90">1 hora 30 minutos</option>
                            <option value="120">2 horas</option>
                        </select>
                    </div>
                </div>
            </section>

            <section class="wn-section">
                <div class="wn-section-header">
                    <div class="wn-section-icon"><i class="bi bi-laptop"></i></div>
                    <div>
                        <h2 class="wn-section-title">Modalidad de la entrevista</h2>
                        <p class="wn-section-subtitle">Elegí cómo se realizará la entrevista.</p>
                    </div>
                </div>

                <div class="wn-radio-cards">
                    <div class="wn-radio-card">
                        <input type="radio" name="modality" id="mod-presencial" value="presencial" checked>
                        <label for="mod-presencial">
                            <i class="bi bi-building"></i>
                            <span>Presencial</span>
                        </label>
                    </div>
                    <div class="wn-radio-card">
                        <input type="radio" name="modality" id="mod-video" value="videollamada">
                        <label for="mod-video">
                            <i class="bi bi-camera-video"></i>
                            <span>Videollamada</span>
                        </label>
                    </div>
                </div>

                <div id="locationSection" style="margin-top: 20px;">
                    <div class="wn-form-group" style="margin-bottom: 16px;">
                        <label class="wn-label">Dirección de la entrevista <span class="required">*</span></label>
                        <input type="text" name="address" class="wn-input" placeholder="Ej: Av. Irrazábal 1234, Encarnación">
                        <span class="wn-hint">Ajustá el marcador en el mapa si es necesario.</span>
                    </div>
                    <div class="wn-location-map" id="locationMap"></div>
                    <div class="wn-location-coords">
                        <div class="wn-form-group">
                            <label class="wn-label">Latitud</label>
                            <input type="text" class="wn-input" id="latInput" value="-27.3306" readonly>
                        </div>
                        <div class="wn-form-group">
                            <label class="wn-label">Longitud</label>
                            <input type="text" class="wn-input" id="lngInput" value="-55.8667" readonly>
                        </div>
                    </div>
                </div>

                <div id="videoSection" style="margin-top: 20px; display: none;">
                    <div class="wn-form-group">
                        <label class="wn-label">Enlace de la videollamada <span class="required">*</span></label>
                        <input type="url" name="video_url" class="wn-input" placeholder="https://meet.google.com/abc-defg-hij">
                        <span class="wn-hint">Se enviará al candidato junto con la convocatoria.</span>
                    </div>
                </div>
            </section>

            <section class="wn-section">
                <div class="wn-section-header">
                    <div class="wn-section-icon"><i class="bi bi-chat-left-text"></i></div>
                    <div>
                        <h2 class="wn-section-title">Notas adicionales</h2>
                        <p class="wn-section-subtitle">Información adicional para el candidato (opcional).</p>
                    </div>
                </div>

                <div class="wn-form-group">
                    <label class="wn-label">Mensaje para el candidato <span class="optional">(opcional)</span></label>
                    <textarea name="notes" class="wn-textarea" rows="4" placeholder="Ej: Traer CV impreso. La entrevista será con el Ing. Roberto Giménez."></textarea>
                    <span class="wn-hint">Este mensaje se incluirá en la notificación al candidato.</span>
                </div>

                <div class="wn-info-box">
                    <i class="bi bi-info-circle-fill"></i>
                    <div class="wn-info-box-text">
                        <strong>Notificación automática:</strong> El candidato recibirá una notificación con la fecha, hora, modalidad, ubicación (o enlace) y las notas que agregues aquí.
                    </div>
                </div>
            </section>

            <div class="wn-form-actions">
                <a href="{{ route('employer.applicants.show', $applicant['id']) }}" class="btn-wn-secondary">
                    <i class="bi bi-x-lg"></i> Cancelar
                </a>
                <div class="wn-form-actions-right">
                    <button type="button" class="btn-wn-secondary">
                        <i class="bi bi-file-earmark"></i> Guardar borrador
                    </button>
                    <button type="submit" class="btn-wn-primary">
                        <i class="bi bi-send"></i> Enviar convocatoria
                    </button>
                </div>
            </div>

        </form>

    </main>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Toggle de modalidad (idéntico al prototipo)
        const modPresencial = document.getElementById('mod-presencial');
        const modVideo = document.getElementById('mod-video');
        const locationSection = document.getElementById('locationSection');
        const videoSection = document.getElementById('videoSection');

        function updateModalitySections() {
            if (modPresencial.checked) {
                locationSection.style.display = 'block';
                videoSection.style.display = 'none';
            } else {
                locationSection.style.display = 'none';
                videoSection.style.display = 'block';
            }
        }

        modPresencial.addEventListener('change', updateModalitySections);
        modVideo.addEventListener('change', updateModalitySections);

        // Mapa
        const map = L.map('locationMap').setView([-27.3306, -55.8667], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            subdomains: ['a', 'b', 'c'],
            attribution: '© OpenStreetMap',
            maxZoom: 19
        }).addTo(map);

        const marker = L.marker([-27.3306, -55.8667], { draggable: true }).addTo(map);

        marker.on('dragend', (e) => {
            const pos = e.target.getLatLng();
            document.getElementById('latInput').value = pos.lat.toFixed(6);
            document.getElementById('lngInput').value = pos.lng.toFixed(6);
        });

        map.on('click', (e) => {
            marker.setLatLng(e.latlng);
            document.getElementById('latInput').value = e.latlng.lat.toFixed(6);
            document.getElementById('lngInput').value = e.latlng.lng.toFixed(6);
        });
    </script>
@endpush
