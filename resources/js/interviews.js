// Work.net — entrevistas del candidato (extraído de specs/entrevistas.html, adaptado).
// Solo se ejecuta si existe .wn-interviews (no rompe otras vistas).
// NOTA: el toggle del navbar vive aquí porque map.js solo lo registra
// cuando existe #map (página del mapa). En esta página no hay #map.

(function () {
    const root = document.querySelector('.wn-interviews');
    if (!root) return;

    // Clase de respaldo para el CSS de la página (por si :has() no aplica).
    document.body.classList.add('wn-page-interviews');
    document.documentElement.classList.add('wn-page-interviews');

    // ============================================
    // TOGGLE DEL NAVBAR
    // Flecha: colapsa cuando está abierto.
    // Logo iluminado: expande cuando está colapsado.
    // ============================================
    const navbar = document.getElementById('navbar');
    const navbarToggle = document.getElementById('navbarToggle');
    const navbarBrandTrigger = document.getElementById('navbarBrandTrigger');

    if (navbar && navbarToggle) {
        navbarToggle.addEventListener('click', () => {
            navbar.classList.add('collapsed');
            document.body.classList.add('navbar-collapsed');
        });
    }

    if (navbar && navbarBrandTrigger) {
        navbarBrandTrigger.addEventListener('click', () => {
            if (navbar.classList.contains('collapsed')) {
                navbar.classList.remove('collapsed');
                document.body.classList.remove('navbar-collapsed');
            }
        });
    }

    // ============================================
    // TABS (demo visual, igual que el prototipo:
    // solo alternan la clase activa)
    // ============================================
    const tabs = document.querySelectorAll('.wn-tab');
    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            tabs.forEach((t) => t.classList.remove('active'));
            tab.classList.add('active');
        });
    });

    // ============================================
    // MINI MAPAS (Leaflet no interactivo).
    // Coordenadas vía data-lat / data-lng; la URL del
    // mapa principal vía data-map-url del contenedor.
    // ============================================
    if (typeof L === 'undefined') return;

    document.querySelectorAll('.wn-mini-map').forEach((mapEl) => {
        const lat = parseFloat(mapEl.dataset.lat);
        const lng = parseFloat(mapEl.dataset.lng);
        if (Number.isNaN(lat) || Number.isNaN(lng)) return;

        const map = L.map(mapEl, {
            zoomControl: false,
            attributionControl: true,
            dragging: false,
            scrollWheelZoom: false,
            doubleClickZoom: false,
            touchZoom: false,
            boxZoom: false,
            keyboard: false,
        }).setView([lat, lng], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            subdomains: ['a', 'b', 'c'],
            attribution: '© OSM',
            maxZoom: 19,
        }).addTo(map);

        L.marker([lat, lng], {
            icon: L.divIcon({
                html: `
                    <div style="position: relative;">
                        <div style="
                            width: 32px; height: 32px;
                            background: #1F6FEB;
                            border-radius: 50% 50% 50% 0;
                            transform: rotate(-45deg);
                            border: 3px solid white;
                            box-shadow: 0 4px 12px rgba(31, 111, 235, 0.5);
                            display: flex; align-items: center; justify-content: center;
                        ">
                            <i class="bi bi-geo-alt-fill" style="
                                transform: rotate(45deg);
                                color: white; font-size: 14px;
                            "></i>
                        </div>
                    </div>
                `,
                className: '',
                iconSize: [32, 32],
                iconAnchor: [16, 32],
            }),
        }).addTo(map);

        // Click en el mini mapa → mapa principal centrado en la
        // entrevista y con la tarjeta de la oferta abierta (si existe).
        mapEl.addEventListener('click', () => {
            const base = root.dataset.mapUrl || '/mapa';
            const offerId = mapEl.dataset.offerId || '';
            let url = `${base}?lat=${lat}&lng=${lng}&zoom=17`;
            if (offerId) url += `&offer=${encodeURIComponent(offerId)}`;
            window.location.href = url;
        });
    });
})();
