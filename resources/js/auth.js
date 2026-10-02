// Work.net — módulo auth (navegación entre vistas del prototipo specs/login.html).
//
// `showView()` se expone globalmente por compatibilidad con el prototipo.
// En Laravel cada vista es una ruta propia; los listeners solo se activan
// si hay marcado de auth en el DOM (guarda #view-role / .wn-view).

// ===== Navegación entre vistas (modo prototipo de una sola página) =====
function showView(viewId) {
    document.querySelectorAll('.wn-view').forEach((v) => v.classList.remove('active'));
    const target = document.getElementById(viewId);
    if (target) {
        target.classList.add('active');
        // Reset scroll de la card al cambiar de vista
        const card = target.querySelector('.wn-card');
        if (card) card.scrollTop = 0;
    }
}

window.showView = showView;

// Solo ejecutar listeners si existe marcado de auth en el DOM.
if (document.getElementById('view-role') || document.querySelector('.wn-view')) {
    // ===== ESC para volver al inicio =====
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            // En Laravel cada vista es una ruta propia: volver a selección de rol.
            if (window.WN_ROUTES && window.WN_ROUTES.role) {
                window.location.href = window.WN_ROUTES.role;
            } else {
                showView('view-role');
            }
        }
    });

    // ===== Prevenir doble-tap zoom en iOS =====
    let lastTouchEnd = 0;
    document.addEventListener(
        'touchend',
        (e) => {
            const now = Date.now();
            if (now - lastTouchEnd <= 300) {
                e.preventDefault();
            }
            lastTouchEnd = now;
        },
        { passive: false },
    );
}
