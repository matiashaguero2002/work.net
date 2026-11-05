// ============================================
// Perfil del candidato: modales, edición y CV
// ============================================

function initProfile() {
    const root = document.querySelector('.wn-page-profile');
    if (!root) return;

    // ---------- Abrir / cerrar modales ----------
    root.querySelectorAll('[data-modal-open]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const modal = document.getElementById(btn.dataset.modalOpen);
            if (!modal) return;

            const form = modal.querySelector('form');
            const title = modal.querySelector('.wn-modal-title');

            // Editar: rellenar con datos de la tarjeta y apuntar al update.
            if (btn.dataset.payload && form) {
                const payload = JSON.parse(btn.dataset.payload);
                form.reset();

                Object.entries(payload).forEach(([key, value]) => {
                    const field = form.querySelector(`[name="${key}"]`);
                    if (!field) return;

                    if (field.type === 'checkbox') {
                        field.checked = !!value;
                    } else {
                        field.value = value ?? '';
                    }
                });

                // fecha_fin deshabilitada si es trabajo actual
                syncActualCheckbox(form);

                if (btn.dataset.actionUrl) {
                    form.setAttribute('action', btn.dataset.actionUrl);
                }
                if (title && title.dataset.titleDefault) {
                    title.textContent = 'Editar: ' + (payload._title || title.dataset.titleDefault);
                }
            } else if (form) {
                // Alta: reset y POST al store (recuperado de data-store-url del botón o action original)
                form.reset();
                syncActualCheckbox(form);
                if (btn.dataset.storeUrl) {
                    form.setAttribute('action', btn.dataset.storeUrl);
                }
                if (title && title.dataset.titleDefault) {
                    title.textContent = title.dataset.titleDefault;
                }
            }

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
            if (!window.confirm('¿Seguro que querés eliminar esto? Esta acción no se puede deshacer.')) {
                e.preventDefault();
            }
        });
    });

    // ---------- Checkbox "actual": deshabilita fecha fin ----------
    root.querySelectorAll('[data-toggle-disable]').forEach((checkbox) => {
        checkbox.addEventListener('change', () => syncActualCheckbox(checkbox.closest('form') || root));
    });

    function syncActualCheckbox(scope) {
        scope.querySelectorAll('[data-toggle-disable]').forEach((checkbox) => {
            const target = document.getElementById(checkbox.dataset.toggleDisable) || (scope.querySelector(`#${checkbox.dataset.toggleDisable}`));
            if (target) {
                target.disabled = checkbox.checked;
                if (checkbox.checked) target.value = '';
            }
        });
    }

    // ---------- CV ----------
    const replaceForm = root.querySelector('.wn-cv-card .wn-cv-upload-form');
    const replaceFile = replaceForm?.querySelector('.wn-cv-input-file');
    const replaceBtn = root.querySelector('[data-cv-replace]');

    replaceBtn?.addEventListener('click', () => replaceFile?.click());
    replaceFile?.addEventListener('change', () => {
        if (replaceFile.files.length > 0) {
            if (!window.confirm('¿Reemplazás tu CV actual con el archivo elegido?')) {
                replaceFile.value = '';
                return;
            }
            replaceForm.submit();
        }
    });

    const emptyForm = root.querySelector('.wn-cv-upload-empty');
    const emptyFile = emptyForm?.querySelector('.wn-cv-input-file');
    const emptyPick = emptyForm?.querySelector('.wn-cv-pick');

    emptyPick?.addEventListener('click', (e) => {
        e.preventDefault();
        emptyFile?.click();
    });
    emptyFile?.addEventListener('change', () => {
        if (emptyFile.files.length > 0) emptyForm.submit();
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initProfile);
} else {
    initProfile();
}
