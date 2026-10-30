// Work.net — entrevistas del candidato (datos reales + acciones en BD).
// Se ejecuta solo si existe .wn-interviews. El modal de negociar
// envía la propuesta por AJAX (estado "negociando" + notas).
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

    // CSRF nativo de Laravel para las peticiones AJAX.
    function csrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    // ============================================
    // TABS FUNCIONALES (Próximas / Realizadas / Canceladas)
    // ============================================
    const tabs = document.querySelectorAll('.wn-page-interviews .wn-tab');
    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            tabs.forEach((t) => t.classList.remove('active'));
            tab.classList.add('active');

            const filter = tab.dataset.filter;
            let visibles = 0;
            document.querySelectorAll('.wn-page-interviews .wn-interview-card').forEach((card) => {
                const match = filter === 'all' || card.dataset.status === filter;
                card.style.display = match ? '' : 'none';
                if (match) visibles++;
            });

            const empty = document.getElementById('interviewsEmptyFilter');
            if (empty) empty.hidden = visibles !== 0;
        });
    });

    // ============================================
    // CANCELAR ENTREVISTA (AJAX → estado "cancelada" en la BD)
    // ============================================
    const cancelUrlTemplate = root.dataset.cancelUrl || '';

    document.querySelectorAll('.wn-page-interviews .wn-interview-cancel-form').forEach((form) => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();

            const id = form.dataset.id;
            const url = cancelUrlTemplate ? cancelUrlTemplate.replace(':idEntrevista', id) : null;
            if (!url) return;

            if (!confirm('¿Querés cancelar esta entrevista? El empleador recibirá una notificación.')) return;

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            })
                .then((res) => res.json().then((body) => ({ ok: res.ok, body })))
                .then(({ ok, body }) => {
                    if (!ok) {
                        alert(body.message || 'No se pudo cancelar la entrevista.');
                        return;
                    }
                    // Recarga para reflejar el estado real desde la BD.
                    window.location.reload();
                })
                .catch(() => alert('No se pudo cancelar la entrevista. Verificá tu conexión.'));
        });
    });

    // ============================================
    // MODAL REAGENDAR / NEGOCIAR HORARIO (RN-19)
    // Propuesta → POST /candidato/entrevistas/{id}/negociar.
    // ============================================
    const negotiateModal = document.getElementById('negotiateModal');

    if (negotiateModal) {
        const negotiateCompany = document.getElementById('negotiateCompany');
        const negotiatePosition = document.getElementById('negotiatePosition');
        const negotiateSend = document.getElementById('negotiateSend');
        const negotiateDateInput = document.getElementById('negotiateDate');
        const negotiateTimeInput = document.getElementById('negotiateTime');
        const negotiateMessageInput = document.getElementById('negotiateMessage');
        const negotiateChat = document.getElementById('negotiateChat');
        const negotiateChatItems = document.getElementById('negotiateChatItems');
        const negotiateUrlTemplate = root.dataset.negotiateUrl || '';

        let currentInterviewId = null;

        const openNegotiate = (btn) => {
            currentInterviewId = btn.dataset.id || null;
            negotiateCompany.textContent = btn.dataset.company || '';
            negotiatePosition.textContent = btn.dataset.position || '';

            // Los campos empiezan vacíos: es una propuesta nueva.
            negotiateDateInput.value = '';
            negotiateTimeInput.value = '';
            negotiateMessageInput.value = '';

            // Historial real: notas de la entrevista (texto simple,
            // líneas que empiezan con [Propuesta del candidato] se ven como "Vos").
            negotiateChatItems.innerHTML = '';
            const notes = btn.dataset.notes || '';
            if (notes.trim() !== '') {
                negotiateChat.hidden = false;
                notes.trim().split('\n').forEach((line, i, arr) => {
                    const esPropuesta = line.startsWith('[Propuesta del candidato]');
                    const bubble = document.createElement('div');
                    bubble.className = 'wn-chat-bubble ' + (esPropuesta ? 'sent' : 'received');

                    const meta = document.createElement('div');
                    meta.className = 'wn-chat-meta';
                    const author = document.createElement('strong');
                    author.textContent = esPropuesta ? 'Vos (Candidato)' : (btn.dataset.company || 'Empleador');
                    meta.appendChild(author);

                    const text = document.createElement('p');
                    text.style.margin = '0';
                    text.textContent = esPropuesta ? line.replace('[Propuesta del candidato] ', '') : line;
                    bubble.appendChild(meta);
                    bubble.appendChild(text);
                    negotiateChatItems.appendChild(bubble);
                });
            } else {
                negotiateChat.hidden = true;
            }

            negotiateModal.classList.add('show');
        };

        const closeNegotiate = () => negotiateModal.classList.remove('show');

        document.querySelectorAll('[data-negotiate]').forEach((btn) => {
            btn.addEventListener('click', () => openNegotiate(btn));
        });

        negotiateModal.querySelectorAll('[data-negotiate-close]').forEach((el) => {
            el.addEventListener('click', closeNegotiate);
        });

        negotiateModal.addEventListener('click', (e) => {
            if (e.target === negotiateModal) closeNegotiate();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && negotiateModal.classList.contains('show')) closeNegotiate();
        });

        // "Enviar Propuesta" (AJAX a la BD).
        if (negotiateSend) {
            negotiateSend.addEventListener('click', () => {
                if (!currentInterviewId) return;

                const fecha = negotiateDateInput.value;
                const hora = negotiateTimeInput.value;

                if (!fecha || !hora) {
                    alert('Elegí la nueva fecha y hora de la entrevista.');
                    return;
                }

                const url = negotiateUrlTemplate.replace(':idEntrevista', currentInterviewId);
                if (!url) return;

                const payload = new URLSearchParams({
                    fecha: fecha,
                    hora: hora,
                    mensaje: negotiateMessageInput.value.trim(),
                });

                negotiateSend.disabled = true;

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'Accept': 'application/json',
                    },
                    body: payload.toString(),
                    credentials: 'same-origin',
                })
                    .then((res) => res.json().then((body) => ({ ok: res.ok, body })))
                    .then(({ ok, body }) => {
                        negotiateSend.disabled = false;
                        if (!ok) {
                            alert((body.message) || 'No se pudo enviar la propuesta.');
                            return;
                        }
                        // Recarga para mostrar el estado "Reagendando" real.
                        window.location.reload();
                    })
                    .catch(() => {
                        negotiateSend.disabled = false;
                        alert('No se pudo enviar la propuesta. Verificá tu conexión.');
                    });
            });
        }
    }

    // ============================================
    // MINI MAPAS (Leaflet no interactivo).
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

        // Click en el mini mapa → mapa principal centrado en la entrevista.
        mapEl.addEventListener('click', () => {
            const base = root.dataset.mapUrl || '/mapa';
            const offerId = mapEl.dataset.offerId || '';
            let url = `${base}?lat=${lat}&lng=${lng}&zoom=17`;
            if (offerId) url += `&offer=${encodeURIComponent(offerId)}`;
            window.location.href = url;
        });
    });
})();
