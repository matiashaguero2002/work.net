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
