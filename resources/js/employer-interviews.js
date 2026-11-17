// Work.net — entrevistas del EMPLEADOR (datos reales + acciones en BD).
// Filtros, orden, modal de cancelación (AJAX) y modal de reprogramar
// (chat RN-19: la propuesta se guarda en entrevista.notas y el
// candidato la ve desde sus entrevistas).

document.addEventListener('DOMContentLoaded', () => {
    const interviewsList = document.getElementById('interviewsList');
    if (!interviewsList) return;

    const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

    // ============================================
    // FILTROS Y ORDEN (client-side sobre las tarjetas reales)
    // ============================================
    const filterSearch = document.getElementById('filterSearch');
    const filterStatus = document.getElementById('filterStatus');
    const filterModality = document.getElementById('filterModality');
    const filterDate = document.getElementById('filterDate');
    const filterClear = document.getElementById('filterClear');
    const sortBy = document.getElementById('sortBy');
    const resultsCount = document.getElementById('resultsCount');
    const emptyState = document.getElementById('emptyState');

    const allCards = Array.from(interviewsList.querySelectorAll('.wn-interview-card'));

    const cardMatches = (card) => {
        const q = (filterSearch?.value || '').toLowerCase().trim();
        if (q && !(card.dataset.name.includes(q) || card.dataset.position.includes(q))) return false;

        const status = filterStatus?.value || '';
        if (status && card.dataset.status !== status) return false;

        const modality = filterModality?.value || '';
        if (modality && card.dataset.modality !== modality) return false;

        const dateFilter = filterDate?.value || '';
        if (dateFilter && card.dataset.date) {
            const today = new Date();
            const todayStr = today.toISOString().slice(0, 10);
            const cardDate = new Date(card.dataset.date + 'T12:00:00');

            if (dateFilter === 'today' && card.dataset.date !== todayStr) return false;
            if (dateFilter === 'week') {
                const weekEnd = new Date(today);
                weekEnd.setDate(weekEnd.getDate() + 7);
                if (cardDate < new Date(todayStr) || cardDate > weekEnd) return false;
            }
            if (dateFilter === 'month') {
                if (cardDate.getMonth() !== today.getMonth() || cardDate.getFullYear() !== today.getFullYear()) return false;
            }
        }

        return true;
    };

    const applyFilters = () => {
        let visible = 0;
        allCards.forEach((card) => {
            const match = cardMatches(card);
            card.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        if (resultsCount) resultsCount.textContent = String(visible);
        if (emptyState) emptyState.style.display = visible === 0 ? '' : 'none';
    };

    [filterSearch, filterStatus, filterModality, filterDate].forEach((el) => {
        if (!el) return;
        el.addEventListener(el.tagName === 'INPUT' ? 'input' : 'change', applyFilters);
    });

    if (filterClear) {
        filterClear.addEventListener('click', () => {
            if (filterSearch) filterSearch.value = '';
            if (filterStatus) filterStatus.value = '';
            if (filterModality) filterModality.value = '';
            if (filterDate) filterDate.value = '';
            applyFilters();
        });
    }

    if (sortBy) {
        sortBy.addEventListener('change', () => {
            const mode = sortBy.value;
            const sorted = allCards.slice().sort((a, b) => {
                if (mode === 'date-asc') return new Date(a.dataset.date) - new Date(b.dataset.date);
                if (mode === 'date-desc') return new Date(b.dataset.date) - new Date(a.dataset.date);
                if (mode === 'candidate') return a.dataset.name.localeCompare(b.dataset.name);
                return 0;
            });
            sorted.forEach((card) => interviewsList.appendChild(card));
        });
    }

    // ============================================
    // MODAL CANCELAR (AJAX a la BD)
    // ============================================
    const cancelModal = document.getElementById('cancelModal');
    const cancelModalYes = document.getElementById('cancelModalYes');
    const cancelModalNo = document.getElementById('cancelModalNo');
    const cancelModalText = document.getElementById('cancelModalText');
    let cancelUrl = null;

    const closeCancel = () => cancelModal?.classList.remove('show');

    document.querySelectorAll('[data-cancel]').forEach((btn) => {
        btn.addEventListener('click', () => {
            cancelUrl = btn.dataset.url;
            if (cancelModalText) {
                cancelModalText.textContent = 'Se notificará a ' + (btn.dataset.candidate || 'el candidato')
                    + ' y la entrevista quedará marcada como cancelada. Esta acción no se puede deshacer.';
            }
            cancelModal?.classList.add('show');
        });
    });

    cancelModal?.addEventListener('click', (e) => { if (e.target === cancelModal) closeCancel(); });
    cancelModalNo?.addEventListener('click', closeCancel);

    cancelModalYes?.addEventListener('click', () => {
        if (!cancelUrl) return;
        cancelModalYes.disabled = true;

        fetch(cancelUrl, {
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
                    cancelModalYes.disabled = false;
                    closeCancel();
                    alert(body.message || 'No se pudo cancelar la entrevista.');
                    return;
                }
                window.location.reload();
            })
            .catch(() => {
                cancelModalYes.disabled = false;
                closeCancel();
                alert('No se pudo cancelar la entrevista. Verificá tu conexión.');
            });
    });

    // ============================================
    // MODAL REPROGRAMAR (chat espejo del candidato)
    // ============================================
    const negotiateModal = document.getElementById('negotiateModal');
    if (!negotiateModal) return;

    const negotiateCompany = document.getElementById('negotiateCompany');
    const negotiatePosition = document.getElementById('negotiatePosition');
    const negotiateDateInput = document.getElementById('negotiateDate');
    const negotiateTimeInput = document.getElementById('negotiateTime');
    const negotiateMessageInput = document.getElementById('negotiateMessage');
    const negotiateChat = document.getElementById('negotiateChat');
    const negotiateChatItems = document.getElementById('negotiateChatItems');
    const negotiateSend = document.getElementById('negotiateSend');

    let currentInterviewId = null;
    let negotiateUrl = null;

    const openNegotiate = (btn) => {
        currentInterviewId = btn.dataset.id || null;
        negotiateUrl = btn.dataset.url || null;
        negotiateCompany.textContent = btn.dataset.candidate || '';
        negotiatePosition.textContent = btn.dataset.position || '';

        negotiateDateInput.value = '';
        negotiateTimeInput.value = '';
        negotiateMessageInput.value = '';

        // Historial real: notas de la entrevista. Las líneas que
        // empiezan con [Propuesta del empleador] son enviadas por
        // vos; el resto son respuestas del candidato.
        negotiateChatItems.innerHTML = '';
        const notes = btn.dataset.notes || '';
        let ultimaPropuestaAjena = false;

        if (notes.trim() !== '') {
            negotiateChat.hidden = false;
            let ultimaBurbuja = null;

            notes.trim().split('\n').forEach((line) => {
                const limpia = line.trim();
                if (limpia === '') return;

                const esPropia = limpia.startsWith('[Propuesta del empleador]');
                const esVisto = limpia.startsWith('[Visto por ');
                const esAceptada = limpia.startsWith('[Aceptada por ');
                const esRechazada = limpia.startsWith('[Rechazada por ');

                // Recibo de lectura / estado: chip sobre la última burbuja.
                if (esVisto || esAceptada || esRechazada) {
                    if (ultimaBurbuja) {
                        const chip = document.createElement('span');
                        chip.className = 'wn-chat-receipt';
                        const cuando = limpia.match(/\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}/);
                        const hora = cuando ? ' · ' + cuando[0] : '';
                        chip.textContent = esVisto
                            ? 'Visto ✓' + hora
                            : (esAceptada ? 'Aceptada por el empleador' + hora : 'Rechazada por el empleador' + hora);
                        ultimaBurbuja.appendChild(chip);
                    }
                    return;
                }

                if (!limpia.startsWith('[Propuesta del ')) return;

                // Cuerpo: sin el prefijo ni el timestamp de envío.
                const enviadoMatch = limpia.match(/\(enviado (\d{2}\/\d{2}\/\d{4} \d{2}:\d{2})\)/);
                const cuerpo = limpia
                    .replace(/^\[Propuesta del (candidato|empleador)\]:\s*/, '')
                    .replace(/\s*\(enviado \d{2}\/\d{2}\/\d{4} \d{2}:\d{2}\)$/, '');

                const bubble = document.createElement('div');
                bubble.className = 'wn-chat-bubble ' + (esPropia ? 'sent' : 'received');

                const meta = document.createElement('div');
                meta.className = 'wn-chat-meta';
                const author = document.createElement('strong');
                author.textContent = esPropia ? 'Vos (Empleador)' : (btn.dataset.candidate || 'Candidato');
                meta.appendChild(author);
                // Fecha PROPUESTA (a la derecha).
                const fechaMatch = cuerpo.match(/\d{2}\/\d{2}\/\d{4} a las \d{2}:\d{2}/);
                if (fechaMatch) {
                    const cuando = document.createElement('span');
                    cuando.textContent = fechaMatch[0];
                    meta.appendChild(cuando);
                }

                const text = document.createElement('p');
                text.style.margin = '0';
                text.textContent = cuerpo;

                bubble.appendChild(meta);
                bubble.appendChild(text);

                // Timestamp real de envío (punto 4).
                if (enviadoMatch) {
                    const pie = document.createElement('span');
                    pie.className = 'wn-chat-time';
                    pie.textContent = 'Enviado el ' + enviadoMatch[1];
                    bubble.appendChild(pie);
                }

                negotiateChatItems.appendChild(bubble);
                ultimaBurbuja = bubble;
                ultimaPropuestaAjena = !esPropia;
            });
        } else {
            negotiateChat.hidden = true;
            ultimaPropuestaAjena = false;
        }

        // Acciones: si el último mensaje es una propuesta del CANDIDATO
        // y la entrevista sigue "negociando", puedo aceptarla o rechazarla.
        const responderUrl = (btn.dataset.responderUrl || '').replace(':idEntrevista', btn.dataset.id || '');
        const hayAcciones = btn.dataset.estado === 'negotiating' && ultimaPropuestaAjena && responderUrl;
        renderAcciones(hayAcciones ? responderUrl : null);

        negotiateModal.classList.add('show');

        // Recibo de lectura: aviso al servidor que vi la propuesta (punto 4).
        const vistoUrl = (btn.dataset.vistoUrl || '').replace(':idEntrevista', btn.dataset.id || '');
        if (vistoUrl && ultimaPropuestaAjena) {
            fetch(vistoUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken(), 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                credentials: 'same-origin',
            }).catch(() => {});
        }
    };

    // Barra Aceptar / Rechazar propuesta (RN-19).
    const renderAcciones = (url) => {
        document.getElementById('negotiateActions')?.remove();
        if (!url) return;

        const bar = document.createElement('div');
        bar.className = 'wn-chat-actions';
        bar.id = 'negotiateActions';

        const aceptar = document.createElement('button');
        aceptar.className = 'btn-wn-primary';
        aceptar.type = 'button';
        aceptar.innerHTML = '<i class="bi bi-check-lg"></i> Aceptar propuesta';
        aceptar.addEventListener('click', () => responder(url, 'aceptar'));

        const rechazar = document.createElement('button');
        rechazar.className = 'btn-wn-secondary';
        rechazar.type = 'button';
        rechazar.textContent = 'Rechazar';
        rechazar.addEventListener('click', () => {
            if (window.confirm('¿Rechazás la propuesta? La entrevista mantiene su fecha original.')) {
                responder(url, 'rechazar');
            }
        });

        bar.appendChild(rechazar);
        bar.appendChild(aceptar);
        negotiateChatItems.after(bar);
    };

    const responder = (url, accion) => {
        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json',
            },
            body: new URLSearchParams({ accion: accion }).toString(),
            credentials: 'same-origin',
        })
            .then((res) => res.json().then((body) => ({ ok: res.ok, body })))
            .then(({ ok, body }) => {
                if (!ok) {
                    alert(body.message || 'No se pudo responder la propuesta.');
                    return;
                }
                window.location.reload();
            })
            .catch(() => alert('No se pudo responder la propuesta. Verificá tu conexión.'));
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
        if (e.key === 'Escape') {
            if (negotiateModal.classList.contains('show')) closeNegotiate();
            if (cancelModal?.classList.contains('show')) closeCancel();
        }
    });

    // "Enviar Propuesta" (AJAX a la BD: notas + estado "negociando").
    if (negotiateSend) {
        negotiateSend.addEventListener('click', () => {
            if (!currentInterviewId) return;

            const fecha = negotiateDateInput.value;
            const hora = negotiateTimeInput.value;

            if (!fecha || !hora) {
                alert('Elegí la nueva fecha y hora de la entrevista.');
                return;
            }

            const payload = new URLSearchParams({
                fecha: fecha,
                hora: hora,
                mensaje: negotiateMessageInput.value.trim(),
            });

            negotiateSend.disabled = true;

            fetch(negotiateUrl, {
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
});
