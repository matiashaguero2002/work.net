
// ==== resources/js/auth.js ==== 
// Work.net — módulo auth (navegación entre vistas del prototipo specs/login.html).
//
// `showView()` se expone globalmente por compatibilidad con el prototipo.
// En Laravel cada vista es una ruta propia; los listeners solo se activan
// si hay marcado de auth en el DOM (guarda #view-role / .wn-view).

// ===== Navegación entre vistas (modo prototipo de una sola página) =====
function showView(viewId) {
    document.querySelectorAll('.wn-view').forEach((v) => v.classList.remove('active'));
    const target = document.getElementById(viewId);
    if (target) {
        target.classList.add('active');
        // Reset scroll de la card al cambiar de vista
        const card = target.querySelector('.wn-card');
        if (card) card.scrollTop = 0;
    }
}

window.showView = showView;

// Solo ejecutar listeners si existe marcado de auth en el DOM.
if (document.getElementById('view-role') || document.querySelector('.wn-view')) {
    // ===== ESC para volver al inicio =====
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            // En Laravel cada vista es una ruta propia: volver a selección de rol.
            if (window.WN_ROUTES && window.WN_ROUTES.role) {
                window.location.href = window.WN_ROUTES.role;
            } else {
                showView('view-role');
            }
        }
    });

    // ===== Prevenir doble-tap zoom en iOS =====
    let lastTouchEnd = 0;
    document.addEventListener(
        'touchend',
        (e) => {
            const now = Date.now();
            if (now - lastTouchEnd <= 300) {
                e.preventDefault();
            }
            lastTouchEnd = now;
        },
        { passive: false },
    );
}

// ==== resources/js/map.js ==== 
// Work.net — mapa de ofertas (extraído de specs/mapa.html, adaptado).
// Las ofertas llegan desde Blade vía data-offers del #map (@json($offers)),
// con fallback a window.__OFFERS__ por compatibilidad.
// Solo se ejecuta si existe el elemento #map (no rompe otras vistas).

const mapEl = document.getElementById('map');

if (mapEl && typeof L !== 'undefined') {

// ============================================
        // INICIALIZACIÓN DEL MAPA
        // ============================================
        const map = L.map('map', {
            zoomControl: false,
            attributionControl: true
        }).setView([-27.3306, -55.8667], 14);

        L.control.zoom({ position: 'topright' }).addTo(map);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            subdomains: ['a', 'b', 'c'],
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 19
        }).addTo(map);

        // ============================================
        // MARCADOR CON MINIATURA
        // ============================================
        function createMarker(offer) {
            const pulseHTML = offer.isPrimary
                ? '<div class="wn-marker-pulse"></div>'
                : '';

            const thumbContent = offer.logoUrl
                ? `<img src="${offer.logoUrl}" alt="${offer.company}" onerror="this.parentElement.innerHTML='<span class=wn-marker-thumb-text>${offer.companyInitials}</span>'">`
                : `<span class="wn-marker-thumb-text">${offer.companyInitials}</span>`;

            return L.divIcon({
                html: `
                    <div class="wn-marker-wrapper">
                        ${pulseHTML}
                        <div class="wn-marker-pin">
                            <div class="wn-marker-thumb">
                                ${thumbContent}
                            </div>
                        </div>
                    </div>
                `,
                className: '',
                iconSize: [48, 58],
                iconAnchor: [24, 58],
                popupAnchor: [0, -58]
            });
        }

        // ============================================
        // HTML DEL POPUP MINI
        // ============================================
        function createPopupHTML(offer) {
            const logoContent = offer.logoUrl
                ? `<img src="${offer.logoUrl}" alt="${offer.company}" onerror="this.parentElement.textContent='${offer.companyInitials}'">`
                : offer.companyInitials;

            return `
                <div class="wn-popup-mini">
                    <img src="${offer.image}" alt="${offer.company}" class="wn-popup-image" 
                         onerror="this.style.display='none'">
                    
                    <div class="wn-popup-body">
                        <div class="wn-popup-header">
                            <div class="wn-popup-logo">${logoContent}</div>
                            <div>
                                <h3 class="wn-popup-title">${offer.title}</h3>
                                <p class="wn-popup-company">${offer.company}</p>
                            </div>
                        </div>

                        <div class="wn-popup-badges">
                            <span class="wn-popup-badge">
                                <i class="bi bi-geo-alt-fill"></i> ${offer.location}
                            </span>
                            <span class="wn-popup-badge">
                                <i class="bi bi-laptop"></i> ${offer.modality}
                            </span>
                            <span class="wn-popup-badge">
                                <i class="bi bi-clock"></i> ${offer.contractType}
                            </span>
                        </div>

                        <p class="wn-popup-description">${offer.shortDescription}</p>

                        <div class="wn-popup-actions">
                            <button class="wn-popup-btn wn-popup-btn-secondary" 
                                    onclick="openModal(${offer.id})">
                                <i class="bi bi-arrows-angle-expand"></i> Ver más
                            </button>
                            <a href="#" class="wn-popup-btn wn-popup-btn-primary">
                                <i class="bi bi-send"></i> Postularme
                            </a>
                        </div>
                    </div>
                </div>
            `;
        }

        // ============================================
        // DATOS DE OFERTAS
        // Vía atributo data-offers del #map (inyectado por Blade);
        // fallback a window.__OFFERS__ por compatibilidad.
        // ============================================
        let offers = [];
        try {
            offers = mapEl.dataset.offers ? JSON.parse(mapEl.dataset.offers) : (window.__OFFERS__ || []);
        } catch (e) {
            offers = window.__OFFERS__ || [];
        }

        // ============================================
        // AGREGAR MARCADORES
        // ============================================
        const markers = [];

        offers.forEach(offer => {
            const marker = L.marker(offer.coords, {
                icon: createMarker(offer)
            }).addTo(map);

            marker.bindPopup(createPopupHTML(offer), {
                maxWidth: 320,
                minWidth: 280,
                closeButton: true,
                autoPan: true,
                autoPanPadding: [80, 80]
            });

            marker.offerId = offer.id;
            markers.push(marker);
        });

        // ============================================
        // FOCUS POR DEEP-LINK (desde entrevistas:
        // ?lat=&lng=&zoom=&offer=)
        // Si la oferta existe, se centra en su marcador
        // y se abre su tarjeta; si no, solo se centra
        // en las coordenadas recibidas.
        // ============================================
        (function applyMapFocus() {
            let focus = null;
            try {
                focus = mapEl.dataset.focus ? JSON.parse(mapEl.dataset.focus) : null;
            } catch (e) {
                focus = null;
            }
            if (!focus) return;

            let target = null;
            if (focus.offerId !== null && focus.offerId !== undefined) {
                const found = markers.find((m) => String(m.offerId) === String(focus.offerId));
                if (found) target = found;
            }

            const zoom = Number.isInteger(focus.zoom) ? focus.zoom : null;

            if (target) {
                const ll = target.getLatLng();
                map.setView(ll, zoom || 17);
                setTimeout(() => target.openPopup(), 350);
            } else if (Number.isFinite(focus.lat) && Number.isFinite(focus.lng)) {
                map.setView([focus.lat, focus.lng], zoom || 15);
            }
        })();

        // ============================================
        // MODAL
        // ============================================
        function openModal(offerId) {
            const offer = offers.find(o => o.id === offerId);
            if (!offer) return;

            document.getElementById('modalImage').src = offer.image;
            document.getElementById('modalImage').alt = offer.company;

            const logoEl = document.getElementById('modalLogo');
            if (offer.logoUrl) {
                logoEl.innerHTML = `<img src="${offer.logoUrl}" alt="${offer.company}" onerror="this.parentElement.textContent='${offer.companyInitials}'">`;
            } else {
                logoEl.textContent = offer.companyInitials;
            }

            document.getElementById('modalTitle').textContent = offer.title;
            document.getElementById('modalCompany').querySelector('span').textContent = offer.company;

            const badgesEl = document.getElementById('modalBadges');
            badgesEl.innerHTML = `
                <span class="wn-modal-badge"><i class="bi bi-geo-alt-fill"></i> ${offer.location}</span>
                <span class="wn-modal-badge"><i class="bi bi-laptop"></i> ${offer.modality}</span>
                <span class="wn-modal-badge"><i class="bi bi-clock"></i> ${offer.contractType}</span>
            `;

            document.getElementById('modalDescription').textContent = offer.fullDescription;

            const reqEl = document.getElementById('modalRequirements');
            reqEl.innerHTML = offer.requirements.map(r => 
                `<span class="wn-modal-tag"><i class="bi bi-check-circle-fill"></i> ${r}</span>`
            ).join('');

            document.getElementById('modalSalary').textContent = offer.salary;
            document.getElementById('modalPublished').textContent = offer.publishedAt;
            document.getElementById('modalModality').textContent = offer.modality;
            document.getElementById('modalContract').textContent = offer.contractType;

            document.getElementById('modalBenefits').textContent = offer.benefits;
            document.getElementById('modalContact').textContent = offer.contact;

            document.getElementById('offerModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(event) {
            if (event) event.stopPropagation();
            document.getElementById('offerModal').classList.remove('active');
            document.body.style.overflow = '';
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeModal();
            }
        });

        window.openModal = openModal;
        window.closeModal = closeModal;

        // ============================================
        // TOGGLE DEL PANEL DE FILTROS
        // (por defecto cerrado)
        // ============================================
        const filtersPanel = document.getElementById('filtersPanel');
        const filtersHeader = document.getElementById('filtersHeader');

        function toggleFilters() {
            if (!filtersPanel) return;
            filtersPanel.classList.toggle('open');
            document.body.classList.toggle('filters-open');
            setTimeout(updateResultsBadgePosition, 350);
        }
        window.toggleFilters = toggleFilters;

        if (filtersHeader && filtersPanel) {
            filtersHeader.addEventListener('click', toggleFilters);
        }

        const distanceRange = document.getElementById('distanceRange');
        const distanceValue = document.getElementById('distanceValue');
        if (distanceRange && distanceValue) {
            distanceRange.addEventListener('input', (e) => {
                distanceValue.textContent = e.target.value + ' km';
            });
        }

        document.querySelectorAll('.wn-filter-chip').forEach(chip => {
            chip.addEventListener('click', () => {
                const input = chip.querySelector('input');
                if (input) {
                    input.checked = !input.checked;
                    chip.classList.toggle('active', input.checked);
                }
            });

            const input = chip.querySelector('input');
            if (input && input.checked) {
                chip.classList.add('active');
            }
        });

        // ============================================
        // RESPONSIVE
        // ============================================
        function adjustMapView() {
            if (window.innerWidth <= 768) {
                map.setView([-27.3306, -55.8667], 13);
            }
        }

        window.addEventListener('resize', adjustMapView);
        adjustMapView();

        // ============================================
        // TOGGLE DEL NAVBAR
        // Flecha: colapsa cuando está abierto
        // Logo iluminado: expande cuando está colapsado
        // ============================================
        const navbar = document.getElementById('navbar');
        const navbarToggle = document.getElementById('navbarToggle');
        const navbarBrandTrigger = document.getElementById('navbarBrandTrigger');

        // Colapsar desde la flecha
        if (navbarToggle) {
            navbarToggle.addEventListener('click', () => {
                navbar.classList.add('collapsed');
                document.body.classList.add('navbar-collapsed');
                setTimeout(updateResultsBadgePosition, 350);
            });
        }

        // Expandir desde el logo (solo cuando está colapsado)
        if (navbarBrandTrigger) {
            navbarBrandTrigger.addEventListener('click', () => {
                if (navbar.classList.contains('collapsed')) {
                    navbar.classList.remove('collapsed');
                    document.body.classList.remove('navbar-collapsed');
                    setTimeout(updateResultsBadgePosition, 350);
                }
            });
        }

        // ============================================
        // CONTADOR DE RESULTADOS CON DETECCIÓN DE COLISIÓN
        // Solo se mueve si colisiona con el panel de filtros abierto.
        // ============================================
        const resultsBadge = document.getElementById('resultsBadge');

        function updateResultsBadgePosition() {
            if (!resultsBadge) return;

            // Solo actuar en pantallas grandes
            if (window.innerWidth <= 992) {
                resultsBadge.style.left = '';
                return;
            }

            const filtersOpen = filtersPanel && filtersPanel.classList.contains('open');
            const navbarCollapsed = navbar && navbar.classList.contains('collapsed');

            const navbarWidth = navbarCollapsed ? 72 : 240;
            const filtersLeft = navbarWidth + 32;
            const filtersRight = filtersOpen ? (filtersLeft + 320) : filtersLeft;

            const badgeWidth = resultsBadge.offsetWidth || 200;
            const defaultLeft = filtersRight + 16;

            const viewportWidth = window.innerWidth;
            const availableSpace = viewportWidth - filtersRight;

            // Si el badge no cabe entre el panel y el borde derecho, moverlo
            if (badgeWidth + 32 > availableSpace) {
                resultsBadge.style.left = (filtersLeft + 16) + 'px';
            } else {
                resultsBadge.style.left = defaultLeft + 'px';
            }
        }

        window.addEventListener('resize', updateResultsBadgePosition);
        setTimeout(updateResultsBadgePosition, 100);

        // ============================================
        // PREVENIR DOBLE-TAP ZOOM EN iOS
        // ============================================
        let lastTouchEnd = 0;
        document.addEventListener('touchend', (e) => {
            const now = Date.now();
            if (now - lastTouchEnd <= 300) {
                e.preventDefault();
            }
            lastTouchEnd = now;
        }, { passive: false });

} // fin guard #map

// ==== resources/js/interviews.js ==== 
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

// ==== resources/js/dashboard.js ==== 
// Work.net — dashboard del candidato + notificaciones
// (extraído de specs/dashboard.html y specs/notificaciones.html, adaptado).
// Solo se ejecuta si existe .wn-page-dashboard o .wn-page-notifications.
// NOTA: el toggle del navbar vive aquí porque map.js solo lo registra
// cuando existe #map (página del mapa). Estas páginas no tienen #map.

(function () {
    const dashMain = document.querySelector('.wn-page-dashboard');
    const notifMain = document.querySelector('.wn-page-notifications');
    // La página de perfil no usa .wn-page-* pero necesita el toggle
    // del navbar (no tiene #map, así que map.js no lo registra ahí).
    const profileMain = document.querySelector('.wn-profile-header');
    if (!dashMain && !notifMain && !profileMain) return;

    // Clases de respaldo para el CSS de la página (por si :has() no aplica).
    if (dashMain) {
        document.body.classList.add('wn-page-dashboard');
        document.documentElement.classList.add('wn-page-dashboard');
    }
    if (notifMain) {
        document.body.classList.add('wn-page-notifications');
        document.documentElement.classList.add('wn-page-notifications');
    }

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
    // TABS (demo visual, igual que los prototipos:
    // solo alternan la clase activa)
    // ============================================
    document.querySelectorAll('.wn-tabs').forEach((tabs) => {
        const buttons = tabs.querySelectorAll('.wn-tab');
        buttons.forEach((tab) => {
            tab.addEventListener('click', () => {
                buttons.forEach((t) => t.classList.remove('active'));
                tab.classList.add('active');
            });
        });
    });

    // ============================================
    // DASHBOARD: quitar ofertas guardadas (demo)
    // ============================================
    if (dashMain) {
        dashMain.querySelectorAll('.wn-saved-remove').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const item = btn.closest('.wn-saved-item');
                if (item) item.remove();
            });
        });
    }

    // ============================================
    // NOTIFICACIONES (demo):
    // marcar leída, eliminar y marcar todas.
    // ============================================
    if (notifMain) {
        const recount = () => {
            const unread = notifMain.querySelectorAll('.wn-notification.unread').length;
            const headerStrong = document.getElementById('notifUnreadCount');
            if (headerStrong) headerStrong.textContent = `${unread} notificaciones sin leer`;
            const tabCount = document.getElementById('tabUnreadCount');
            if (tabCount) tabCount.textContent = unread;
        };

        notifMain.querySelectorAll('.wn-notification-action.mark-read').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const item = btn.closest('.wn-notification');
                if (!item) return;
                item.classList.remove('unread');
                btn.remove();
                recount();
            });
        });

        notifMain.querySelectorAll('.wn-notification-action.delete').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const item = btn.closest('.wn-notification');
                if (item) item.remove();
                recount();
            });
        });

        const markAll = document.getElementById('markAllRead');
        if (markAll) {
            markAll.addEventListener('click', () => {
                notifMain.querySelectorAll('.wn-notification.unread').forEach((item) => {
                    item.classList.remove('unread');
                    const btn = item.querySelector('.wn-notification-action.mark-read');
                    if (btn) btn.remove();
                });
                recount();
            });
        }
    }
})();

// ============================================
// DROPDOWN DE USUARIO EN EL NAVBAR
// (extraído de specs/perfil.html; global: funciona en
// todas las vistas porque dashboard.js se carga vía app.js)
// ============================================
document.addEventListener('DOMContentLoaded', () => {
    const userInfoTrigger = document.getElementById('userInfoTrigger');
    const userDropdown = document.getElementById('userDropdown');

    if (!userInfoTrigger || !userDropdown) return;

    userInfoTrigger.addEventListener('click', (e) => {
        e.stopPropagation();
        userInfoTrigger.classList.toggle('open');
        userDropdown.classList.toggle('show');
    });

    document.addEventListener('click', (e) => {
        if (!userInfoTrigger.contains(e.target) && !userDropdown.contains(e.target)) {
            userInfoTrigger.classList.remove('open');
            userDropdown.classList.remove('show');
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            userInfoTrigger.classList.remove('open');
            userDropdown.classList.remove('show');
        }
    });
});
