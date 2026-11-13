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
