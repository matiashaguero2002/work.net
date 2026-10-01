// Work.net — mapa de ofertas (extraído de specs/mapa.html, adaptado).
// Las ofertas llegan desde Blade vía window.__OFFERS__ (@json($offers)).
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
        // ============================================
        const offers = window.__OFFERS__ || [];

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

            if (offer.isPrimary) {
                setTimeout(() => {
                    marker.openPopup();
                }, 800);
            }
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
        // FILTROS
        // ============================================
        function toggleFilters() {
            const panel = document.getElementById('filtersPanel');
            panel.classList.toggle('collapsed');
        }
        window.toggleFilters = toggleFilters;

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
