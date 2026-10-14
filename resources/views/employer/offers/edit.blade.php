@extends('layouts.app')

@section('title', 'Work.net - Editar oferta')

@section('content')
    <div class="wn-bg"><div class="wn-bg-grid"></div></div>

    @include('partials.wn-navbar')

    <main class="wn-main wn-page-employer">
        <header class="wn-header">
            <div class="wn-header-left">
                <h1>Editar oferta</h1>
                <p>Modificá la información del puesto según sea necesario.</p>
            </div>
            <div class="wn-header-actions">
                <a href="{{ route('employer.offers.index') }}" class="btn-wn-secondary">
                    <i class="bi bi-arrow-left"></i> Cancelar
                </a>
            </div>
        </header>

        @include('employer.offers._form', ['offer' => $offer])
    </main>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Mapa de ubicación precargado con las coordenadas de la oferta
        const map = L.map('locationMap').setView([-27.3306, -55.8667], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { subdomains: ['a', 'b', 'c'], maxZoom: 19 }).addTo(map);
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
