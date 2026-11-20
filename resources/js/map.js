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

            // Si la empresa publicó más de una solicitud activa, el marcador
            // muestra cuántas (en la "flechita" del pin).
            const countBadge = (offer.companyOfferCount > 1)
                ? `<div class="wn-marker-count">${offer.companyOfferCount}</div>`
                : '';

            return L.divIcon({
                html: `
                    <div class="wn-marker-wrapper">
                        ${pulseHTML}
                        <div class="wn-marker-pin">
                            <div class="wn-marker-thumb">
                                ${thumbContent}
                            </div>
                        </div>
                        ${countBadge}
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
                                <button class="wn-popup-btn wn-popup-btn-danger wn-popup-btn-small" 
                                        onclick="openReportModal(${offer.id}, '${offer.title}', '${offer.company}')" 
                                        title="Reportar oferta">
                                    <i class="bi bi-exclamation-circle-fill"></i> !
                                </button>
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
                            <button class="wn-popup-btn wn-popup-btn-primary${postulatedOffers.has(String(offer.id)) ? ' applied' : ''}" 
                                    data-apply-offer="${offer.id}"
                                    onclick="window.toggleApplyOffer(${offer.id}, event)">
                                ${postulatedOffers.has(String(offer.id))
                                    ? '<i class="bi bi-box-arrow-right"></i> Retirar'
                                    : '<i class="bi bi-send"></i> Postularme'}
                            </button>
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
        // POSTULARSE / RETIRAR (estado real desde la BD)
        // Debe declararse ANTES de crear los marcadores:
        // el popup renderiza el botón según el estado.
        // ============================================
        let postulatedOffers = new Set();
        try {
            const postIds = mapEl.dataset.postulated ? JSON.parse(mapEl.dataset.postulated) : [];
            postulatedOffers = new Set(postIds.map(String));
        } catch (e) {
            postulatedOffers = new Set();
        }
        const postUrlTemplate = mapEl.dataset.postUrl || '';
        const withdrawUrlTemplate = mapEl.dataset.withdrawUrl || '';

        // Sincroniza todos los botones de postular de una oferta
        // (popup abierto + botón del modal).
        function refreshApplyButtons(offerId) {
            const isPost = postulatedOffers.has(String(offerId));
            document.querySelectorAll('[data-apply-offer="' + offerId + '"]').forEach((btn) => {
                btn.innerHTML = isPost
                    ? '<i class="bi bi-box-arrow-right"></i> Retirar'
                    : '<i class="bi bi-send"></i> Postularme';
                btn.classList.toggle('applied', isPost);
            });
        }

        function renderModalApplyState() {
            const btn = document.getElementById('modalApply');
            if (!btn || currentModalOfferId === null) return;
            btn.dataset.applyOffer = String(currentModalOfferId);
            refreshApplyButtons(String(currentModalOfferId));
        }

        // Postularse o retirar (optimista, sincroniza con la BD).
        function toggleApplyOffer(offerId, event) {
            if (event) event.stopPropagation();

            const id = String(offerId);
            const wasPost = postulatedOffers.has(id);

            const postUrl = postUrlTemplate ? postUrlTemplate.replace(':idOferta', id) : null;
            const withdrawUrl = withdrawUrlTemplate ? withdrawUrlTemplate.replace(':idOferta', id) : null;
            const url = wasPost ? withdrawUrl : postUrl;
            if (!url) {
                showGeoAlert('Necesitás una cuenta de candidato para postularte.');
                return;
            }

            if (wasPost) postulatedOffers.delete(id); else postulatedOffers.add(id);
            refreshApplyButtons(id);

            fetch(url, {
                method: wasPost ? 'DELETE' : 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            })
                .then((res) => res.json().then((body) => ({ ok: res.ok, body })))
                .then(({ ok, body }) => {
                    if (ok) return; // el estado optimista es el correcto

                    // Revertir el cambio optimista
                    if (wasPost) postulatedOffers.add(id); else postulatedOffers.delete(id);
                    refreshApplyButtons(id);

                    showGeoAlert((body && body.message) ||
                        'No se pudo enviar la postulación. Probá de nuevo.');
                })
                .catch(() => {
                    if (wasPost) postulatedOffers.add(id); else postulatedOffers.delete(id);
                    refreshApplyButtons(id);
                    showGeoAlert('No se pudo enviar la postulación. Verificá tu conexión e intentá de nuevo.');
                });
        }
        window.toggleApplyOffer = toggleApplyOffer;

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
        // Estado "guardado" real (RF-08): se inicializa con
        // las ofertas guardadas en la base de datos
        // (data-saved del #map) y se sincroniza vía AJAX
        // al presionar Guardar — persiste entre recargas.
        let savedOffers = new Set();
        try {
            const savedIds = mapEl.dataset.saved ? JSON.parse(mapEl.dataset.saved) : [];
            savedOffers = new Set(savedIds.map(String));
        } catch (e) {
            savedOffers = new Set();
        }
        const saveUrlTemplate = mapEl.dataset.saveUrl || '';
        let currentModalOfferId = null;
const openedForViews = new Set(); // ofertas ya contadas en esta carga (RF-05)

        // CSRF nativo de Laravel para las peticiones AJAX.
        function csrfToken() {
            const meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.getAttribute('content') : '';
        }

        function renderModalSaveState() {
            const btn = document.getElementById('modalSave');
            if (!btn || currentModalOfferId === null) return;
            const saved = savedOffers.has(String(currentModalOfferId));
            btn.classList.toggle('saved', saved);
            btn.querySelector('i').className = saved ? 'bi bi-bookmark-fill' : 'bi bi-bookmark';
            btn.querySelector('span').textContent = saved ? 'Guardada' : 'Guardar';
        }

        function openModal(offerId) {
            const offer = offers.find(o => o.id === offerId);
            if (!offer) return;

            currentModalOfferId = offerId;
            renderModalSaveState();
            renderModalApplyState();

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

            // Las flechas del banner solo aparecen si la empresa publicó
            // más de una oferta activa.
            const showNav = (offer.companyOfferCount || 0) > 1;
            document.getElementById('modalNav').style.display = showNav ? 'flex' : 'none';

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

            // RF-05: contador de visualizaciones. Si hay URL de registro
            // (candidato autenticado), aviso al servidor una vez por carga.
            const viewUrl = (mapEl.dataset.viewUrl || '').replace(':idOferta', String(offerId));
            if (viewUrl && !openedForViews.has(offerId)) {
                openedForViews.add(offerId);
                fetch(viewUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '', 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    credentials: 'same-origin',
                }).catch(() => {});
            }
        }

        function closeModal(event) {
            if (event) event.stopPropagation();
            document.getElementById('offerModal').classList.remove('active');
            document.body.style.overflow = '';
        }

        // Navegar SOLO entre las ofertas de la misma empresa (flechas
        // del banner del modal). Válido para cualquier empresa, incluidas
        // las ofertas que se creen en el futuro.
        function navigateOffer(direction) {
            if (!currentModalOfferId || offers.length === 0) return;

            const current = offers.find(o => o.id === currentModalOfferId);
            if (!current) return;

            const sameCompany = offers.filter(o => o.employerId === current.employerId);
            if (sameCompany.length <= 1) return;

            const idx = sameCompany.findIndex(o => o.id === currentModalOfferId);
            if (idx === -1) return;

            const nextIdx = (idx + direction + sameCompany.length) % sameCompany.length;
            const nextOffer = sameCompany[nextIdx];

            // Cierra y vuelve a abrir con la nueva oferta para animar el cambio.
            document.getElementById('offerModal').classList.remove('active');
            setTimeout(() => {
                openModal(nextOffer.id);
            }, 150);
        }

        window.navigateOffer = navigateOffer;

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeModal();
            }
        });

        window.openModal = openModal;
        window.closeModal = closeModal;

        // ============================================
        // MODAL DE REPORTAR OFERTA
        // ============================================
        function openReportModal(offerId, title, company) {
            const reportTitleEl = document.getElementById('reportModalTitle');
            const reportCompanyEl = document.getElementById('reportCompany');
            if (reportTitleEl) reportTitleEl.textContent = title || 'Reportar oferta';
            if (reportCompanyEl) reportCompanyEl.textContent = company || 'Empresa';
            currentModalOfferId = offerId;

            const reportTypeSelect = document.getElementById('reportType');
            if (reportTypeSelect && reportTypeSelect.options.length <= 1) {
                const reportTypes = [
                    { value: 'fraud', label: 'Estafa o fraude' },
                    { value: 'inappropriate', label: 'Contenido inapropiado' },
                    { value: 'misleading', label: 'Información engañosa' },
                    { value: 'spam', label: 'Spam o duplicado' },
                    { value: 'other', label: 'Otro' }
                ];
                reportTypeSelect.innerHTML = '<option value="">Seleccioná un tipo</option>' +
                    reportTypes.map(t => `<option value="${t.value}">${t.label}</option>`).join('');
            }

            const reportModal = document.getElementById('reportModal');
            if (reportModal) reportModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeReportModal(event) {
            if (event) event.stopPropagation();
            const reportModal = document.getElementById('reportModal');
            if (reportModal) reportModal.classList.remove('active');
            document.body.style.overflow = '';
        }

        function submitReport(event) {
            if (event) event.preventDefault();
            const offerId = String(currentModalOfferId || '');
            const tipo = document.getElementById('reportType') ? document.getElementById('reportType').value : '';
            const motivo = document.getElementById('reportMotivo') ? document.getElementById('reportMotivo').value.trim() : '';

            if (!tipo || !motivo) {
                showGeoAlert('Por favor completá todos los campos del reporte.');
                return;
            }

            const reportUrl = mapEl.dataset.reportUrl || '';
            if (!reportUrl) {
                showGeoAlert('No se pudo enviar el reporte. Intentá más tarde.');
                return;
            }

            fetch(reportUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ oferta_id: offerId, tipo: tipo, motivo: motivo })
            })
            .then(res => res.json().then(body => ({ ok: res.ok, body })))
            .then(({ ok, body }) => {
                if (ok && body && body.ok) {
                    showGeoAlert('Gracias. Tu reporte fue enviado correctamente.');
                    const motivoEl = document.getElementById('reportMotivo');
                    if (motivoEl) motivoEl.value = '';
                    closeReportModal();
                } else {
                    showGeoAlert((body && body.message) || 'No se pudo enviar el reporte. Probá de nuevo.');
                }
            })
            .catch(() => showGeoAlert('No se pudo enviar el reporte. Verificá tu conexión e intentá de nuevo.'));
        }

        window.openReportModal = openReportModal;
        window.closeReportModal = closeReportModal;
        window.submitReport = submitReport;

        const reportFormEl = document.getElementById('reportForm');
        if (reportFormEl) reportFormEl.addEventListener('submit', submitReport);

        // Botón "Postularme a esta oferta" del modal (mismo flujo que el popup).
        const modalApplyBtn = document.getElementById('modalApply');
        if (modalApplyBtn) {
            modalApplyBtn.addEventListener('click', (e) => {
                if (currentModalOfferId !== null) toggleApplyOffer(currentModalOfferId, e);
            });
        }

        // Botón "Guardar" del modal de oferta (RF-08):
        // POST AJAX al servidor; la BD decide el estado real.
        function toggleSaveOffer(event) {
            if (event) event.stopPropagation();
            if (currentModalOfferId === null) return;

            const offerId = String(currentModalOfferId);
            const url = saveUrlTemplate ? saveUrlTemplate.replace(':idOferta', offerId) : null;
            if (!url) return;

            // Optimista: se refleja al instante; si falla se revierte.
            const wasSaved = savedOffers.has(offerId);
            if (wasSaved) savedOffers.delete(offerId); else savedOffers.add(offerId);
            currentModalOfferId = Number(offerId);
            renderModalSaveState();

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            })
                .then((res) => res.json().then((body) => ({ ok: res.ok, status: res.status, body })))
                .then(({ ok, status, body }) => {
                    if (ok) return; // el estado optimista es el correcto

                    // Revertir el cambio optimista
                    if (wasSaved) savedOffers.add(offerId); else savedOffers.delete(offerId);
                    renderModalSaveState();

                    const msg = (body && body.message) ||
                        'No se pudo guardar la oferta. Probá de nuevo.';
                    showGeoAlert(msg);
                })
                .catch(() => {
                    // Revertir el cambio optimista
                    if (wasSaved) savedOffers.add(offerId); else savedOffers.delete(offerId);
                    renderModalSaveState();
                    showGeoAlert('No se pudo guardar la oferta. Verificá tu conexión e intentá de nuevo.');
                });
        }
        window.toggleSaveOffer = toggleSaveOffer;

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
                // RF-07: el círculo de radio se actualiza en tiempo real
                // mientras el usuario mueve el slider.
                userState.radiusKm = parseFloat(e.target.value);
                if (userState.circle) {
                    userState.circle.setRadius(userState.radiusKm * 1000);
                }
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
        // GEOLOCALIZACIÓN DEL USUARIO (RF-07 / RNF-04)
        // API nativa navigator.geolocation. Sin plugins.
        // - watchPosition con alta precisión (se actualiza
        //   si el usuario se mueve).
        // - Marcador distintivo (círculo azul + persona).
        // - L.circle con el radio elegido en el slider.
        // - Si se deniega el permiso, aviso y el mapa
        //   sigue funcionando con normalidad.
        // ============================================
        const initialKm = distanceRange ? parseFloat(distanceRange.value) : 10;

        const userState = {
            lat: null,
            lng: null,
            radiusKm: initialKm,
            marker: null,
            circle: null,
        };

        // Ícono distintivo: círculo azul con ícono de persona.
        const userIcon = L.divIcon({
            html: '<div class="wn-user-marker">' +
                      '<div class="wn-user-marker-pulse"></div>' +
                      '<div class="wn-user-marker-dot"><i class="bi bi-person-fill"></i></div>' +
                  '</div>',
            className: '',
            iconSize: [36, 36],
            iconAnchor: [18, 18],
        });

        function updateUserLocation(lat, lng) {
            const firstFix = userState.lat === null;
            userState.lat = lat;
            userState.lng = lng;

            // Con la primera ubicación concreta, el aviso de
            // "Obteniendo tu ubicación…" deja de ser necesario.
            hideGeoAlert();

            if (!userState.marker) {
                userState.marker = L.marker([lat, lng], {
                    icon: userIcon,
                    zIndexOffset: 1000, // siempre por encima de las ofertas
                    keyboard: false,
                    interactive: false,
                    title: 'Tu ubicación actual',
                }).addTo(map);

                userState.circle = L.circle([lat, lng], {
                    radius: userState.radiusKm * 1000,
                    className: 'wn-user-radius',
                    color: 'rgba(13, 110, 253, 0.9)',
                    weight: 2,
                    dashArray: '6 6',
                    fillColor: 'rgba(13, 110, 253, 1)',
                    fillOpacity: 0.08,
                    interactive: false,
                }).addTo(map);

                // Solo se centra en el primer fixed para no
                // "pelear" con el usuario si se mueve.
                if (!mapEl.dataset.focus) {
                    map.setView([lat, lng], Math.max(map.getZoom(), 14));
                }
            } else {
                userState.marker.setLatLng([lat, lng]);
                userState.circle.setLatLng([lat, lng]);
            }

            // Recalcular todos los filtros con la posición nueva
            // (por eso watchPosition).
            applyAllFilters();
        }

        function getOffersWithinRadius() {
            if (userState.lat === null) return null; // sin ubicación: sin filtro
            const center = L.latLng(userState.lat, userState.lng);
            const radiusMeters = userState.radiusKm * 1000;

            const within = new Set();
            markers.forEach((marker) => {
                // Método nativo de Leaflet: distancia en metros.
                const distance = center.distanceTo(marker.getLatLng());
                if (distance <= radiusMeters) within.add(String(marker.offerId));
            });
            return within;
        }

        function updateResultsCount(count) {
            const el = document.getElementById('resultsCount');
            if (el) el.textContent = String(count);
        }

        // ============================================
        // FILTROS COMPLETOS (100% funcionales)
        // Búsqueda de texto, área laboral, modalidad,
        // tipo de contrato, remuneración mínima y
        // distancia máxima. Se combinan (AND) en cada
        // "Aplicar".
        // ============================================
        const filterSearch = document.getElementById('filterSearch');
        const filterArea = document.getElementById('filterArea');
        const filterContract = document.getElementById('filterContract');
        const filterSalary = document.getElementById('filterSalary');
        const modalityInputs = Array.from(document.querySelectorAll('input[name="modality"]'));

        // Normaliza texto: minúsculas + sin tildes, para que
        // "diseño" y "diseno" sonen igual al filtrar.
        function normalizeText(value) {
            return String(value || '')
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '');
        }

        // Valores del <select> de contrato → texto de la oferta.
        const CONTRACT_LABELS = {
            'tiempo-completo': 'tiempo completo',
            'medio-tiempo': 'medio tiempo',
            'pasantia': 'pasantia',
            'contrato': 'por contrato',
            'freelance': 'freelance',
        };

        // "Gs. 7.000.000 - 9.000.000" → 7000000.
        // Texto sin números ("A convenir") → null.
        function parseSalary(value) {
            const digits = String(value || '').replace(/[^\d]/g, '');
            return digits ? parseInt(digits, 10) : null;
        }

        function getOfferByMarker(marker) {
            return offers.find((o) => String(o.id) === String(marker.offerId));
        }

        // Todas las condiciones del panel sobre una oferta.
        // userWithin: true | false | 'unknown' (sin ubicación).
        function offerMatchesFilters(offer, userWithin) {
            // 1) Búsqueda por texto (cargo, empresa, descripción, lugar)
            const query = normalizeText(filterSearch ? filterSearch.value : '').trim();
            if (query) {
                const haystack = normalizeText([
                    offer.title,
                    offer.company,
                    offer.shortDescription,
                    offer.location,
                ].join(' '));
                if (!haystack.includes(query)) return false;
            }

            // 2) Área laboral (campo area de la oferta)
            if (filterArea && filterArea.value && offer.area !== filterArea.value) {
                return false;
            }

            // 3) Modalidad (chips presencial / remoto / híbrido)
            const selectedModalities = modalityInputs
                .filter((input) => input.checked)
                .map((input) => input.value);
            if (selectedModalities.length > 0) {
                if (!selectedModalities.includes(normalizeText(offer.modality))) {
                    return false;
                }
            }

            // 4) Tipo de contrato
            if (filterContract && filterContract.value) {
                const expected = CONTRACT_LABELS[filterContract.value];
                if (normalizeText(offer.contractType) !== expected) return false;
            }

            // 5) Remuneración mínima (Gs.). Las ofertas con
            //    "A convenir" (salaryMin null) se ocultan cuando
            //    el usuario fijó un mínimo, porque no lo cumplen.
            if (filterSalary) {
                const min = parseSalary(filterSalary.value);
                if (min !== null &&
                    (offer.salaryMin === null || offer.salaryMin === undefined ||
                     offer.salaryMin < min)) {
                    return false;
                }
            }

            // 6) Distancia máxima respecto a la ubicación del
            //    usuario (solo se filtra si ya la tenemos).
            if (userWithin === false) return false;

            return true;
        }

        function applyAllFilters() {
            const withoutLocation = userState.lat === null;
            // Sin ubicación: el filtro de distancia queda deshabilitado
            // ('unknown' = no restrinige); con ubicación: set de IDs
            // dentro del radio elegido.
            const withinIds = withoutLocation ? null : getOffersWithinRadius();

            let visible = 0;
            markers.forEach((marker) => {
                const offer = getOfferByMarker(marker);
                const userWithin = withoutLocation
                    ? 'unknown'
                    : withinIds.has(String(marker.offerId));

                const shouldShow = offer && offerMatchesFilters(offer, userWithin);
                if (shouldShow) {
                    if (!map.hasLayer(marker)) marker.addTo(map);
                    visible++;
                } else if (map.hasLayer(marker)) {
                    marker.closePopup();
                    map.removeLayer(marker);
                }
            });

            updateResultsCount(visible);
        }

        // Botón "Aplicar": ejecuta los filtros del panel.
        const filtersApply = document.getElementById('filtersApply');
        if (filtersApply) {
            filtersApply.addEventListener('click', () => {
                if (userState.lat === null) {
                    showGeoAlert(GEO_ERROR_MSG);
                }
                applyAllFilters();
            });
        }

        // Buscar al presionar Enter (atajo conocido).
        if (filterSearch) {
            filterSearch.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    if (filtersApply) filtersApply.click();
                }
            });
        }

        // Botón "Limpiar": resetea el panel completo y vuelve a
        // mostrar todas las ofertas.
        const filtersClear = document.getElementById('filtersClear');
        if (filtersClear) {
            filtersClear.addEventListener('click', () => {
                if (filterSearch) filterSearch.value = '';
                if (filterArea) filterArea.value = '';
                if (filterContract) filterContract.value = '';
                if (filterSalary) filterSalary.value = '';
                modalityInputs.forEach((input) => { input.checked = false; });
                document.querySelectorAll('.wn-filter-chip').forEach((chip) => {
                    chip.classList.remove('active');
                });

                if (distanceRange && distanceValue) {
                    distanceRange.value = '10';
                    distanceValue.textContent = '10 km';
                }
                userState.radiusKm = 10;
                if (userState.circle) userState.circle.setRadius(10000);

                applyAllFilters();
            });
        }

        // Aviso del estado de la geolocalización (persistente,
        // con botón de cierre; reemplaza a alert que bloquea el mapa).
        let geoAlertTimeout = null;

        function showGeoAlert(message, options = {}) {
            const {
                persistent = true,
                dismissible = true,
                icon = 'bi-geo-alt',
                kind = 'warning', // 'warning' | 'info'
            } = options;

            let alertEl = document.querySelector('.wn-geo-alert');
            if (!alertEl) {
                alertEl = document.createElement('div');
                alertEl.className = 'wn-geo-alert';
                alertEl.innerHTML =
                    '<i class="bi bi-geo-alt"></i>' +
                    '<span></span>' +
                    '<button class="wn-geo-alert-close" type="button" aria-label="Cerrar aviso">' +
                    '<i class="bi bi-x-lg"></i></button>';
                document.body.appendChild(alertEl);
                alertEl.querySelector('.wn-geo-alert-close')
                    .addEventListener('click', hideGeoAlert);
            }

            alertEl.classList.toggle('info', kind === 'info');
            alertEl.querySelector('i').className = 'bi ' + icon;
            alertEl.querySelector('span').textContent = message;
            alertEl.classList.add('shown');

            clearTimeout(geoAlertTimeout);
            if (!persistent) {
                geoAlertTimeout = setTimeout(hideGeoAlert, 6000);
            }
        }

        function hideGeoAlert() {
            const alertEl = document.querySelector('.wn-geo-alert');
            if (alertEl) alertEl.classList.remove('shown');
            clearTimeout(geoAlertTimeout);
        }

        const GEO_ERROR_MSG =
            'No se pudo obtener tu ubicación. El filtro de distancia está deshabilitado.';
        const GEO_DENIED_MSG =
            'Ubicación bloqueada: en la barra de direcciones (ícono 📍) elegí "Permitir" y recargá la página. El filtro de distancia está deshabilitado.';
        let geoDeniedReported = false;

        // Permisos / seguimiento de la posición real.
        if ('geolocation' in navigator) {
            showGeoAlert('Obteniendo tu ubicación…', {
                persistent: true,
                dismissible: false,
                icon: 'bi-geo-alt-fill',
                kind: 'info',
            });

            // Detección inmediata si el permiso ya está denegado
            // (evita esperar el timeout de watchPosition).
            if (navigator.permissions && navigator.permissions.query) {
                navigator.permissions.query({ name: 'geolocation' }).then((status) => {
                    if (status.state === 'denied' && userState.lat === null) {
                        geoDeniedReported = true;
                        showGeoAlert(GEO_DENIED_MSG);
                    }
                    status.addEventListener('change', () => {
                        if (status.state === 'denied' && userState.lat === null) {
                            geoDeniedReported = true;
                            showGeoAlert(GEO_DENIED_MSG);
                        }
                    });
                }).catch(() => {});
            }

            const onGeoSuccess = (position) => {
                geoDeniedReported = false;
                updateUserLocation(position.coords.latitude, position.coords.longitude);
            };

            const onGeoFinalError = (err) => {
                // No repetir el aviso en cada reintento de watchPosition.
                if (geoDeniedReported || userState.lat !== null) return;
                geoDeniedReported = true;
                console.warn('[Work.net] Geolocalización:', err.code, err.message);
                showGeoAlert(err.code === err.PERMISSION_DENIED ? GEO_DENIED_MSG : GEO_ERROR_MSG);
            };

            // Estrategia en 2 intentos:
            // 1º) alta precisión (GPS/mini-BSSID), timeout corto — el caso ideal;
            // 2º) si expira o no hay proveedores de posición (típico en PC de
            //     escritorio de red cableada), reintento por red con tolerancia
            //     de caché: más lento pero mucho más probable que resuelva.
            let lowAccuracyRetry = false;
            let watchId = navigator.geolocation.watchPosition(
                onGeoSuccess,
                (err) => {
                    if (userState.lat !== null) return;
                    console.warn('[Work.net] Geolocalización (1er intento):', err.code, err.message);

                    if (!lowAccuracyRetry &&
                        (err.code === err.TIMEOUT || err.code === err.POSITION_UNAVAILABLE)) {
                        lowAccuracyRetry = true;
                        navigator.geolocation.clearWatch(watchId);
                        showGeoAlert('Se está tardando en obtener tu ubicación… Reintentando por red.', {
                            persistent: true,
                            dismissible: false,
                            icon: 'bi-geo-alt-fill',
                            kind: 'info',
                        });
                        watchId = navigator.geolocation.watchPosition(
                            onGeoSuccess,
                            onGeoFinalError,
                            { enableHighAccuracy: false, timeout: 25000, maximumAge: 60000 }
                        );
                        return;
                    }

                    onGeoFinalError(err);
                },
                { enableHighAccuracy: true, maximumAge: 0, timeout: 10000 }
            );
        } else {
            showGeoAlert('Tu navegador no soporta geolocalización. El filtro de distancia está deshabilitado.');
        }

        // Gancho de prueba: permite simular la ubicación del
        // usuario sin permisos del navegador (solo pruebas).
        window.setUserLocation = (lat, lng) => updateUserLocation(lat, lng);

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
