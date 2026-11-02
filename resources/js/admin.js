/**
 * WORK.NET — Módulo Admin
 * Navbar, filtros, tabs rápidos, modales de acción y filtros de listados.
 * Solo frontend: los datos son mock y nada se envía al backend.
 */

document.addEventListener('DOMContentLoaded', () => {
    const navbar = document.getElementById('navbar');
    const navbarToggle = document.getElementById('navbarToggle');
    const navbarBrandTrigger = document.getElementById('navbarBrandTrigger');

    /* ===== NAVBAR ===== */
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
    // Nota: el dropdown de usuario (userInfoTrigger / userDropdown) lo
    // maneja dashboard.js, que se carga globalmente vía app.js.

    /* ===== FILTROS + TABS (genérico, según página) ===== */
    const list = document.getElementById('reportsList')
        || document.getElementById('offersGrid')
        || document.getElementById('usersTbody');
    const searchInput = document.getElementById('filterSearch');
    const clearBtn = document.getElementById('filterClear');
    const resultsCount = document.getElementById('resultsCount');
    const emptyState = document.getElementById('emptyState');
    const tabs = document.querySelectorAll('.wn-tab');

    const selects = {
        reports: {
            severity: document.getElementById('filterSeverity'),
            status: document.getElementById('filterStatus'),
            reason: document.getElementById('filterReason'),
        },
        offers: {
            status: document.getElementById('filterStatus'),
            category: document.getElementById('filterCategory'),
            date: document.getElementById('filterDate'),
        },
        users: {
            role: document.getElementById('filterRole'),
            status: document.getElementById('filterStatus'),
            date: document.getElementById('filterDate'),
        },
    };

    let activeTab = '';

    if (list && searchInput) {
        const items = Array.from(list.querySelectorAll('tr, .wn-report-card, .wn-offer-card')).filter((el) => el.tagName === 'TR' || el.classList.contains('wn-report-card') || el.classList.contains('wn-offer-card'));
        const selectList = [...Object.values(selects.reports), ...Object.values(selects.offers), ...Object.values(selects.users)].filter(Boolean);

        function applyFilters() {
            const q = (searchInput.value || '').toLowerCase().trim();
            let visible = 0;

            items.forEach((item) => {
                const d = item.dataset;
                let match = true;

                if (item.tagName === 'TR') {
                    // Tabla de usuarios
                    const name = (d.name || '').toLowerCase();
                    const email = (d.email || '').toLowerCase();
                    if (q && !name.includes(q) && !email.includes(q)) match = false;
                    if (selects.users.role && selects.users.role.value && d.role !== selects.users.role.value) match = false;
                    if (selects.users.status && selects.users.status.value && d.status !== selects.users.status.value) match = false;
                    if ((activeTab || (selects.users.status && selects.users.status.value)) && d.status !== (activeTab || (selects.users.status && selects.users.status.value))) match = false;
                    if (selects.users.date && selects.users.date.value) {
                        if (!matchesDate(d.date, selects.users.date.value)) match = false;
                    }
                } else if (item.classList.contains('wn-offer-card')) {
                    // Grid de ofertas
                    const title = (d.title || '').toLowerCase();
                    const company = (d.company || '').toLowerCase();
                    if (q && !title.includes(q) && !company.includes(q)) match = false;
                    if (selects.offers.category && selects.offers.category.value && d.category !== selects.offers.category.value) match = false;
                    const status = activeTab || (selects.offers.status && selects.offers.status.value) || '';
                    if (status && d.status !== status) match = false;
                    if (selects.offers.date && selects.offers.date.value) {
                        if (!matchesDate(d.date, selects.offers.date.value)) match = false;
                    }
                } else {
                    // Reportes
                    const name = (d.name || '').toLowerCase();
                    const offer = (d.offer || '').toLowerCase();
                    if (q && !name.includes(q) && !offer.includes(q)) match = false;
                    if (selects.reports.severity && selects.reports.severity.value && d.severity !== selects.reports.severity.value) match = false;
                    if (selects.reports.reason && selects.reports.reason.value && d.reason !== selects.reports.reason.value) match = false;
                    const status = activeTab || (selects.reports.status && selects.reports.status.value) || '';
                    if (status && d.status !== status) match = false;
                }

                item.style.display = match ? '' : 'none';
                if (match) visible++;
            });

            if (resultsCount) resultsCount.textContent = visible;
            if (emptyState) emptyState.style.display = visible === 0 ? 'flex' : 'none';
        }

        function matchesDate(value, range) {
            if (!value) return !range;
            const d = new Date(value);
            const now = new Date();
            const diffDays = Math.floor((now - d) / 86400000);
            if (range === 'today') return diffDays < 1;
            if (range === 'week') return diffDays <= 7;
            if (range === 'month') return diffDays <= 31;
            return true;
        }

        selectList.forEach((el) => {
            el.addEventListener('input', applyFilters);
            el.addEventListener('change', applyFilters);
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                searchInput.value = '';
                selectList.forEach((el) => { el.value = ''; });
                activeTab = '';
                tabs.forEach((t) => t.classList.remove('active'));
                if (tabs[0]) tabs[0].classList.add('active');
                applyFilters();
            });
        }

        tabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                activeTab = tab.dataset.tab;
                tabs.forEach((t) => t.classList.remove('active'));
                tab.classList.add('active');
                // Si la página tiene select de estado, se anula para que mande el tab
                if (selects.users.status) selects.users.status.value = '';
                if (selects.offers.status) selects.offers.status.value = '';
                if (selects.reports.status) selects.reports.status.value = '';
                applyFilters();
            });
        });

        applyFilters();
    }

    /* ===== MODAL DE ACCIÓN ===== */
    const actionModal = document.getElementById('actionModal');
    if (actionModal) {
        const actionModalTitle = document.getElementById('actionModalTitle');
        const actionModalText = document.getElementById('actionModalText');
        const actionModalIcon = document.getElementById('actionModalIcon');
        const actionModalNo = document.getElementById('actionModalNo');
        const actionModalYes = document.getElementById('actionModalYes');
        let pendingItem = null;
        let pendingAction = '';

        const actionConfig = {
            // Usuarios
            suspend: { title: '¿Suspender usuario?', text: 'El usuario no podrá iniciar sesión ni postularse a ofertas mientras esté suspendido.', icon: 'bi-slash-circle-fill', color: 'danger' },
            approve: { title: '¿Aprobar usuario?', text: 'El usuario podrá usar todas las funciones de la plataforma.', icon: 'bi-check-circle-fill', color: 'success' },
            reject: { title: '¿Rechazar usuario?', text: 'El usuario no podrá acceder a la plataforma. Se le notificará por email.', icon: 'bi-x-circle-fill', color: 'danger' },
            reactivate: { title: '¿Reactivar usuario?', text: 'El usuario volverá a tener acceso completo a la plataforma.', icon: 'bi-check-circle-fill', color: 'success' },
            delete: { title: '¿Eliminar permanentemente?', text: 'Esta acción no se puede deshacer. Los datos del elemento seleccionado se perderán.', icon: 'bi-trash-fill', color: 'danger' },
            // Ofertas
            pause: { title: '¿Pausar oferta?', text: 'La oferta quedará oculta para los postulantes hasta que el empleador la reactive.', icon: 'bi-pause-circle-fill', color: 'danger' },
            // Reportes
            review: { title: '¿Marcar en revisión?', text: 'El reporte quedará marcado como "en revisión" para su posterior análisis.', icon: 'bi-search', color: 'info' },
            resolve: { title: '¿Resolver reporte?', text: 'El reporte quedará marcado como resuelto. Se notificará al usuario que lo envió.', icon: 'bi-check-circle-fill', color: 'success' },
            dismiss: { title: '¿Desestimar reporte?', text: 'El reporte será archivado sin acción adicional. Se notificará al usuario que lo envió.', icon: 'bi-x-circle-fill', color: 'danger' },
        };

        function openActionModal(item, action) {
            pendingItem = item;
            pendingAction = action;
            const cfg = actionConfig[action];
            if (!cfg) return;
            actionModalTitle.textContent = cfg.title;
            actionModalText.textContent = cfg.text;
            actionModalIcon.innerHTML = `<i class="bi ${cfg.icon}"></i>`;
            if (cfg.color === 'info') {
                actionModalIcon.style.backgroundColor = 'rgba(31, 111, 235, 0.15)';
                actionModalIcon.style.color = 'var(--wn-accent)';
            } else if (cfg.color === 'success') {
                actionModalIcon.style.backgroundColor = 'rgba(35, 134, 54, 0.15)';
                actionModalIcon.style.color = 'var(--wn-success)';
            } else {
                actionModalIcon.style.backgroundColor = 'rgba(218, 54, 51, 0.15)';
                actionModalIcon.style.color = 'var(--wn-danger)';
            }
            actionModal.classList.add('show');
        }

        function closeActionModal() {
            actionModal.classList.remove('show');
            pendingItem = null;
            pendingAction = '';
        }

        // Botones con data-action (fuera del modal)
        document.querySelectorAll('[data-action]').forEach((btn) => {
            if (actionModal.contains(btn)) return;
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                openActionModal(btn, btn.dataset.action);
            });
        });

        actionModalNo.addEventListener('click', closeActionModal);
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeActionModal();
        });
        actionModal.addEventListener('click', (e) => { if (e.target === actionModal) closeActionModal(); });

        actionModalYes.addEventListener('click', () => {
            if (pendingItem && pendingAction) {
                // Reportes: actualizar badge sin recargar
                const reportCard = pendingItem.closest('.wn-report-card');
                if (reportCard) {
                    const badges = reportCard.querySelector('.wn-report-badges');
                    const statusBadge = badges ? Array.from(badges.children).find((b) => b.textContent.includes('Pendiente') || b.textContent.includes('En revisión')) : null;
                    if (pendingAction === 'review') {
                        reportCard.dataset.status = 'review';
                        if (statusBadge) {
                            statusBadge.className = 'wn-report-badge review';
                            statusBadge.innerHTML = '<i class="bi bi-search"></i> En revisión';
                        }
                    } else if (pendingAction === 'resolve') {
                        reportCard.dataset.status = 'resolved';
                        reportCard.classList.add('resolved');
                        if (statusBadge) {
                            statusBadge.className = 'wn-report-badge resolved';
                            statusBadge.innerHTML = '<i class="bi bi-check-circle-fill"></i> Resuelto';
                        }
                    } else if (pendingAction === 'dismiss') {
                        reportCard.dataset.status = 'resolved';
                        reportCard.classList.add('resolved');
                        if (statusBadge) {
                            statusBadge.className = 'wn-report-badge';
                            statusBadge.style.backgroundColor = 'rgba(139, 148, 158, 0.15)';
                            statusBadge.style.color = 'var(--wn-text-muted)';
                            statusBadge.innerHTML = '<i class="bi bi-archive-fill"></i> Archivado';
                        }
                    }
                }

                // Usuarios (tabla): actualizar badge de estado
                const row = pendingItem.closest('tr');
                if (row) {
                    const badge = row.querySelector('.wn-status-badge');
                    const statusMap = {
                        approve: ['activo', 'bi-check-circle-fill', 'Activo'],
                        reactivate: ['activo', 'bi-check-circle-fill', 'Activo'],
                        suspend: ['suspendido', 'bi-x-circle-fill', 'Suspendido'],
                        reject: ['suspendido', 'bi-x-circle-fill', 'Rechazado'],
                    };
                    if (statusMap[pendingAction]) {
                        const [cls, icon, label] = statusMap[pendingAction];
                        row.dataset.status = cls;
                        if (badge) {
                            badge.className = `wn-status-badge ${cls}`;
                            badge.innerHTML = `<i class="bi ${icon}"></i> ${label}`;
                        }
                    } else if (pendingAction === 'delete') {
                        row.remove();
                    }
                }

                // Ofertas (grid): actualizar estado de la card
                const offerCard = pendingItem.closest('.wn-offer-card');
                if (offerCard) {
                    const statusMap = {
                        pause: ['paused', 'Pausada'],
                        reactivate: ['active', 'Activa'],
                    };
                    if (statusMap[pendingAction]) {
                        const [cls, label] = statusMap[pendingAction];
                        offerCard.dataset.status = cls;
                        offerCard.classList.remove('status-active', 'status-paused', 'status-closed', 'status-reported');
                        offerCard.classList.add(`status-${cls}`);
                        const badge = offerCard.querySelector('.wn-offer-status');
                        if (badge) {
                            badge.className = `wn-offer-status ${cls}`;
                            badge.textContent = label;
                        }
                    } else if (pendingAction === 'delete') {
                        offerCard.remove();
                    }
                }
            }
            closeActionModal();
            if (searchInput && list) {
                // Recontar tras los cambios
                searchInput.dispatchEvent(new Event('input'));
            }
        });
    }
});
