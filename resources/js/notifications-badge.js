/**
 * Badge de notificaciones sin leer (punto 3: live update).
 * Consulta el contador cada 30s y actualiza el badge de la
 * barra lateral sin recargar la página.
 */
const pollLink = document.querySelector('[data-unread-poll]');

if (pollLink) {
    const badge = () => pollLink.querySelector('[data-unread-badge]');

    const refrescar = async () => {
        // No consultar si la pestaña está en segundo plano (ahorra requests).
        if (document.hidden) return;
        try {
            const res = await fetch(pollLink.dataset.unreadPoll, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                credentials: 'same-origin',
            });
            if (!res.ok) return;
            const body = await res.json();
            const el = badge();
            if (!el) return;
            const n = Number(body.unread) || 0;
            el.textContent = n;
            el.style.display = n > 0 ? '' : 'none';
        } catch (e) {
            /* silencioso: el badge queda con el último valor */
        }
    };

    setInterval(refrescar, 30000);
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) refrescar();
    });
}
