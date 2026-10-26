/**
 * WORK.NET — Entrevistas del empleador (listado)
 * Filtros mock, orden de tarjetas y modal de cancelación.
 * Solo frontend: nada se envía al backend.
 */

document.addEventListener('DOMContentLoaded', () => {
    const interviewsList = document.getElementById('interviewsList');
    if (!interviewsList) return;

    const filterSearch = document.getElementById('filterSearch');
    const filterStatus = document.getElementById('filterStatus');
    const filterModality = document.getElementById('filterModality');
    const filterDate = document.getElementById('filterDate');
    const filterClear = document.getElementById('filterClear');
    const sortBy = document.getElementById('sortBy');
    const resultsCount = document.getElementById('resultsCount');
    const emptyState = document.getElementById('emptyState');

    const allCards = Array.from(interviewsList.querySelectorAll('.wn-interview-card'));
    // Fecha de referencia del mock (hoy en el prototipo)
    const today = new Date('2026-10-15');
    const weekEnd = new Date('2026-10-21');

    function applyFilters() {
        const q = (filterSearch.value || '').toLowerCase().trim();
        const status = filterStatus.value;
        const modality = filterModality.value;
        const dateFilter = filterDate.value;

        let visible = 0;
        allCards.forEach((card) => {
            const name = (card.dataset.name || '').toLowerCase();
            const cardStatus = card.dataset.status;
            const cardModality = card.dataset.modality;
            const cardDate = new Date(card.dataset.date);

            let match = true;
            if (q && !name.includes(q)) match = false;
            if (status && cardStatus !== status) match = false;
            if (modality && cardModality !== modality) match = false;
            if (dateFilter === 'today') {
                if (cardDate.toDateString() !== today.toDateString()) match = false;
            } else if (dateFilter === 'week') {
                if (cardDate < today || cardDate > weekEnd) match = false;
            } else if (dateFilter === 'month') {
                if (cardDate.getMonth() !== today.getMonth() ||
                    cardDate.getFullYear() !== today.getFullYear()) match = false;
            }

            card.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        resultsCount.textContent = visible;
        emptyState.style.display = visible === 0 ? 'flex' : 'none';
        interviewsList.style.display = visible === 0 ? 'none' : '';
    }

    [filterSearch, filterStatus, filterModality, filterDate].forEach((el) => {
        el.addEventListener('input', applyFilters);
        el.addEventListener('change', applyFilters);
    });

    filterClear.addEventListener('click', () => {
        filterSearch.value = '';
        filterStatus.value = '';
        filterModality.value = '';
        filterDate.value = '';
        applyFilters();
    });

    /* Ordenamiento (mock simple) */
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

    /* ===== MODAL CANCELAR ===== */
    const cancelModal = document.getElementById('cancelModal');
    const cancelModalText = document.getElementById('cancelModalText');
    const cancelModalNo = document.getElementById('cancelModalNo');
    const cancelModalYes = document.getElementById('cancelModalYes');
    let pendingCancelCard = null;

    function openCancelModal(card) {
        pendingCancelCard = card;
        const name = card.dataset.name || 'el candidato';
        cancelModalText.textContent = `Se notificará a ${name} y la entrevista quedará marcada como cancelada. Esta acción no se puede deshacer.`;
        cancelModal.classList.add('show');
    }
    function closeCancelModal() {
        cancelModal.classList.remove('show');
        pendingCancelCard = null;
    }

    document.querySelectorAll('[data-cancel]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const card = btn.closest('.wn-interview-card');
            if (card) openCancelModal(card);
        });
    });

    cancelModalNo.addEventListener('click', closeCancelModal);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeCancelModal();
    });
    cancelModal.addEventListener('click', (e) => { if (e.target === cancelModal) closeCancelModal(); });
    cancelModalYes.addEventListener('click', () => {
        if (pendingCancelCard) {
            pendingCancelCard.classList.remove('status-confirmed', 'status-pending');
            pendingCancelCard.classList.add('status-cancelled');
            pendingCancelCard.dataset.status = 'cancelled';
            const statusEl = pendingCancelCard.querySelector('.wn-interview-status');
            if (statusEl) {
                statusEl.className = 'wn-interview-status wn-status-cancelled';
                statusEl.innerHTML = '<i class="bi bi-x-circle-fill"></i> Cancelada';
            }
            const cancelBtn = pendingCancelCard.querySelector('[data-cancel]');
            if (cancelBtn) cancelBtn.remove();
        }
        closeCancelModal();
    });
});
