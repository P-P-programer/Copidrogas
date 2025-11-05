let toastContainer;

/** Monta contenedores necesarios (idempotente) */
export function mountUX() {
  if (!toastContainer) {
    toastContainer = document.createElement('div');
    toastContainer.className = 'ux-toast-container';
    document.body.appendChild(toastContainer);
  }
}

/** Muestra un toast. type: 'success' | 'error' | 'info' | 'warn' */
export function toast({ message, type = 'info', timeout = 3000, icon } = {}) {
  if (!toastContainer) mountUX();
  const el = document.createElement('div');
  el.className = `ux-toast ux-toast--${type}`;
  el.setAttribute('role', 'status');
  el.setAttribute('aria-live', 'polite');

  const icons = {
    success: '✔️', error: '❌', info: 'ℹ️', warn: '⚠️'
  };
  const i = icon ?? icons[type] ?? 'ℹ️';

  el.innerHTML = `
    <span class="ux-toast-icon" aria-hidden="true">${i}</span>
    <div class="ux-toast-text">${message}</div>
    <button class="ux-toast-close" aria-label="Cerrar">✕</button>
  `;

  el.querySelector('.ux-toast-close').onclick = () => el.remove();
  toastContainer.appendChild(el);

  if (timeout > 0) setTimeout(() => el.remove(), timeout);
  return el;
}

/** Confirmación accesible. Retorna Promise<boolean> */
export function confirm({
  title = 'Confirmar acción',
  message = '¿Deseas continuar?',
  confirmText = 'Confirmar',
  cancelText = 'Cancelar'
} = {}) {
  return new Promise((resolve) => {
    const overlay = document.createElement('div');
    overlay.className = 'ux-overlay';
    overlay.innerHTML = `
      <div class="ux-dialog" role="alertdialog" aria-modal="true" aria-labelledby="uxdlg-title" aria-describedby="uxdlg-msg">
        <h3 id="uxdlg-title">${title}</h3>
        <p id="uxdlg-msg">${message}</p>
        <div class="ux-dialog-actions">
          <button class="ux-btn" data-act="cancel">${cancelText}</button>
          <button class="ux-btn ux-btn--primary" data-act="ok">${confirmText}</button>
        </div>
      </div>
    `;
    document.body.appendChild(overlay);

    const okBtn = overlay.querySelector('[data-act="ok"]');
    const cancelBtn = overlay.querySelector('[data-act="cancel"]');
    const close = (val) => { overlay.remove(); resolve(val); };

    okBtn.focus();
    okBtn.onclick = () => close(true);
    cancelBtn.onclick = () => close(false);
    overlay.addEventListener('click', (e) => { if (e.target === overlay) close(false); });
    document.addEventListener('keydown', function onKey(e) {
      if (!document.body.contains(overlay)) return document.removeEventListener('keydown', onKey);
      if (e.key === 'Escape') close(false);
      if (e.key === 'Enter') close(true);
    });
  });
}