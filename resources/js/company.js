// ============================================
// Perfil de empresa (empleador): modales, logo y compartir
// ============================================

function initCompany() {
    const root = document.querySelector('.wn-page-company');
    if (!root) return;

    // ---------- Abrir / cerrar modales ----------
    root.querySelectorAll('[data-modal-open]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const modal = document.getElementById(btn.dataset.modalOpen);
            if (!modal) return;

            const form = modal.querySelector('form:not(.wn-confirm-form)');
            if (form) form.reset();

            modal.classList.add('show');
            modal.querySelectorAll('input:not([type=hidden]), select, textarea')[0]?.focus();
        });
    });

    root.querySelectorAll('[data-modal-close]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const modal = btn.closest('.wn-modal-backdrop');
            if (modal) modal.classList.remove('show');
        });
    });

    root.querySelectorAll('.wn-modal-backdrop').forEach((modal) => {
        modal.addEventListener('mousedown', (e) => {
            if (e.target === modal) modal.classList.remove('show');
        });
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            root.querySelectorAll('.wn-modal-backdrop.show').forEach((modal) => modal.classList.remove('show'));
        }
    });

    // ---------- Selector de ubicación de la sede ----------
    // Búsqueda de dirección (Nominatim, sin paquetes) + mapa con
    // marcador arrastrable, igual que en "Publicar oferta".
    let uMap = null;
    let uMarker = null;

    const numOr = (value, fallback) => {
        const n = parseFloat(value);
        return Number.isFinite(n) ? n : fallback;
    };

    const pintarCoords = (lat, lng) => {
        const latInput = document.getElementById('u-lat');
        const lngInput = document.getElementById('u-lng');
        if (latInput) latInput.value = lat.toFixed(6);
        if (lngInput) lngInput.value = lng.toFixed(6);
    };

    const reverseGeocode = (lat, lng) => {
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&accept-language=es`, {
            headers: { 'Accept': 'application/json' },
        })
            .then((res) => res.json())
            .then((data) => {
                const addr = data.address || {};
                const dir = [addr.road, addr.house_number].filter(Boolean).join(' ');
                const ciudad = addr.city || addr.town || addr.village || addr.municipality || '';
                const prov = addr.state || addr.department || '';
                if (dir) document.getElementById('u-dir').value = dir;
                if (ciudad) document.getElementById('u-ciudad').value = ciudad;
                if (prov) document.getElementById('u-prov').value = prov;
            })
            .catch(() => { /* Sin conexión: quedan las coordenadas manualmente. */ });
    };

    const ensureUbicacionMap = () => {
        if (uMap) {
            uMap.invalidateSize();
            return;
        }

        if (typeof L === 'undefined') return;

        const el = document.getElementById('u-map');
        if (!el) return;

        const latInput = document.getElementById('u-lat');
        const lngInput = document.getElementById('u-lng');
        const lat = numOr(latInput?.value, -27.3306);
        const lng = numOr(lngInput?.value, -55.8667);

        uMap = L.map(el, { zoomControl: true, attributionControl: false }).setView([lat, lng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { subdomains: ['a', 'b', 'c'], maxZoom: 19 }).addTo(uMap);
        uMarker = L.marker([lat, lng], { draggable: true }).addTo(uMap);

        const setPos = (pos) => {
            pintarCoords(pos.lat, pos.lng);
            reverseGeocode(pos.lat, pos.lng);
        };

        uMarker.on('dragend', (e) => setPos(e.target.getLatLng()));
        uMap.on('click', (e) => {
            uMarker.setLatLng(e.latlng);
            setPos(e.latlng);
        });
    };

    // Al abrir el modal de ubicación, inicializa el mapa.
    root.querySelectorAll('[data-modal-open]').forEach((btn) => {
        btn.addEventListener('click', () => {
            if (btn.dataset.modalOpen === 'modal-ubicacion') {
                setTimeout(ensureUbicacionMap, 80);
            }
        });
    });

    // Búsqueda de direcciones (Nominatim) con mini listado.
    const uSearch = document.getElementById('u-search');
    const uResults = document.getElementById('u-search-results');
    let searchTimer = null;

    const cerrarResultados = () => {
        if (uResults) uResults.hidden = true;
    };

    document.addEventListener('click', (e) => {
        if (uResults && !uResults.hidden && !e.target.closest('.wn-location-search')) cerrarResultados();
    });

    if (uSearch && uResults) {
        uSearch.addEventListener('input', () => {
            clearTimeout(searchTimer);
            const q = uSearch.value.trim();
            if (q.length < 3) {
                cerrarResultados();
                return;
            }

            searchTimer = setTimeout(() => {
                fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=5&accept-language=es&countrycodes=py&q=${encodeURIComponent(q)}`, {
                    headers: { 'Accept': 'application/json' },
                })
                    .then((res) => res.json())
                    .then((items) => {
                        uResults.innerHTML = '';
                        if (!Array.isArray(items) || items.length === 0) {
                            uResults.innerHTML = '<div class="wn-search-item muted">Sin resultados</div>';
                            uResults.hidden = false;
                            return;
                        }

                        items.forEach((item) => {
                            const option = document.createElement('button');
                            option.type = 'button';
                            option.className = 'wn-search-item';
                            option.textContent = item.display_name;
                            option.addEventListener('click', () => {
                                const lat = parseFloat(item.lat);
                                const lng = parseFloat(item.lon);

                                // Dirección / ciudad / provincia desde el resultado.
                                const addr = item.address || {};
                                const dir = [addr.road, addr.house_number].filter(Boolean).join(' ');
                                const ciudad = addr.city || addr.town || addr.village || addr.municipality || '';
                                const prov = addr.state || addr.department || '';
                                if (dir) document.getElementById('u-dir').value = dir;
                                if (ciudad) document.getElementById('u-ciudad').value = ciudad;
                                if (prov) document.getElementById('u-prov').value = prov;
                                if (!document.getElementById('u-dir').value) {
                                    document.getElementById('u-dir').value = (item.display_name || '').split(',')[0];
                                }

                                pintarCoords(lat, lng);
                                if (uMap && uMarker) {
                                    uMarker.setLatLng([lat, lng]);
                                    uMap.setView([lat, lng], 16);
                                }
                                cerrarResultados();
                            });
                            uResults.appendChild(option);
                        });

                        uResults.hidden = false;
                    })
                    .catch(() => cerrarResultados());
            }, 600);
        });
    }

    // ---------- Confirmaciones ----------
    root.querySelectorAll('.wn-confirm-form').forEach((form) => {
        form.addEventListener('submit', (e) => {
            if (!window.confirm('¿Seguro que querés quitar el logo actual?')) {
                e.preventDefault();
            }
        });
    });

    // ---------- Compartir (copia el enlace del perfil público) ----------
    root.querySelectorAll('[data-share-url]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const url = btn.dataset.shareUrl;
            try {
                await navigator.clipboard.writeText(url);
                const original = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-check-lg"></i> Copiado';
                btn.disabled = true;
                setTimeout(() => {
                    btn.innerHTML = original;
                    btn.disabled = false;
                }, 1600);
            } catch {
                window.prompt('Copiá el enlace de tu perfil público:', url);
            }
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCompany);
} else {
    initCompany();
}
