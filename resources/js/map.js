// Work.net — mapa de ofertas (extraído de resources/views/prototypes/mapa.html, adaptado).
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
// OFERTAS GUARDADAS (RF-08)
// ============================================
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
// MODAL DE REPORTAR OFERTA
// ============================================

// Abre el modal de reporte para una oferta específica.
function openReportModal(offerId, title, company) {
    currentModalOfferId = offerId;

    // Llenar el título y empresa en el modal
    const reportTitleEl = document.getElementById('reportModalTitle');
    const reportCompanyEl = document.getElementById('reportCompany');
    if (reportTitleEl) reportTitleEl.textContent = title;
    if (reportCompanyEl) reportCompanyEl.textContent = company;

    // Cargar tipos de reporte (se definen abajo)
    loadReportTypes();

    document.getElementById('reportModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

// Carga los tipos de reporte y llena el selector.
function loadReportTypes() {
    const reportTypeSelect = document.getElementById('reportType');

    const reportTypes = [
        { value: 'fraud', label: 'Estafa o fraude' },
        { value: 'inappropriate', label: 'Contenido inapropiado' },
        { value: 'misleading', label: 'Información engañosa' },
        { value: 'spam', label: 'Spam o duplicado' },
        { value: 'other', label: 'Otro' }
    ];

    reportTypeSelect.innerHTML = reportTypes.map(type =>
        `<option value="${type.value}">${type.label}</option>`
    ).join('');

    // Si ya hay un motivo guardado (por ejemplo, al reabrir), lo seleccionamos
    const savedReason = document.getElementById('reportReasonInput').value;
    if (savedReason && reportTypeSelect.querySelector(`option[value="${savedReason}"]`)) {
        reportTypeSelect.value = savedReason;
    }
}

// Cierra el modal de reporte.
function closeReportModal(event) {
    if (event) event.stopPropagation();
    document.getElementById('reportModal').classList.remove('active');
    document.body.style.overflow = '';
}

// Envía el reporte al servidor.
function submitReport(event) {
    if (event) event.stopPropagation();

    const offerId = String(currentModalOfferId);
    const tipo = document.getElementById('reportType') ? document.getElementById('reportType').value : '';
    const motivo = document.getElementById('reportMotivo') ? document.getElementById('reportMotivo').value : '';

    if (!tipo || !motivo) {
        showGeoAlert('Por favor completá todos los campos del reporte.');
        return;
    }

    const reportUrl = mapEl.dataset.reportUrl || '';
    const userId = mapEl.dataset.userId || '';

    fetch(reportUrl, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        },
        credentials: 'same-origin',
        body: JSON.stringify({
            oferta_id: offerId,
            tipo: tipo,
            motivo: motivo,
            id_usuario: userId
        })
    })
        .then((res) => res.json().then((body) => ({ ok: res.ok, body })))
        .then(({ ok, body }) => {
            if (ok) {
                showGeoAlert('Gracias. Tu reporte fue enviado correctamente.');
                closeReportModal();
            } else {
                showGeoAlert((body && body.message) || 'No se pudo enviar el reporte. Probá de nuevo.');
            }
        })
        .catch(() => {
            showGeoAlert('No se pudo enviar el reporte. Verificá tu conexión e intentá de nuevo.');
        });
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
        geoDeniedReported = false;
        // fallback sin geolocalización
        hideGeoAlert();
    };

    const geoOptions = {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0
    };

    navigator.geolocation.getCurrentPosition(onGeoSuccess, onGeoFinalError, geoOptions);
} else {
    // El navegador no soporta geolocalización
    hideGeoAlert();
}

// CSRF nativo de Laravel para las peticiones AJAX.
function csrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

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