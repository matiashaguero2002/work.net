// Work.net — dashboard del candidato + notificaciones
// (extraído de specs/dashboard.html y specs/notificaciones.html, adaptado).
// Solo se ejecuta si existe .wn-page-dashboard o .wn-page-notifications.
// NOTA: el toggle del navbar vive aquí porque map.js solo lo registra
// cuando existe #map (página del mapa). Estas páginas no tienen #map.

(function () {
    const dashMain = document.querySelector('.wn-page-dashboard');
    const notifMain = document.querySelector('.wn-page-notifications');
    if (!dashMain && !notifMain) return;

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
