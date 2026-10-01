// Work.net — scripts de autenticación (solo frontend, sin backend todavía).
//
// Se mantiene `showView()` por compatibilidad con el prototipo
// `specs/login.html`, aunque la navegación real ahora se hace
// con rutas Laravel (enlaces y formularios -> route('auth.*')).

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
