/**
 * Lógica de UI para el layout principal
 */

// Menú hamburguesa responsive
export function initHamburgerMenu() {
  const btn = document.getElementById('hamburgerBtn');
  const nav = document.getElementById('primaryNav');
  if (!btn || !nav) return;

  btn.addEventListener('click', () => {
    const isOpen = nav.classList.toggle('open');
    btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  });

  // Cerrar al hacer clic fuera (mobile)
  document.addEventListener('click', (e) => {
    if (window.innerWidth >= 768) return;
    if (!btn.contains(e.target) && !nav.contains(e.target)) {
      nav.classList.remove('open');
      btn.setAttribute('aria-expanded', 'false');
    }
  });

  // Cerrar al hacer clic en cualquier opción del menú (solo mobile)
  nav.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', () => {
      if (window.innerWidth < 768) {
        nav.classList.remove('open');
        btn.setAttribute('aria-expanded', 'false');
      }
    });
  });
}

// Menú de usuario (guest/auth)
export function initUserMenu() {
  const btn = document.getElementById('userMenuBtn');
  const menu = document.getElementById('userDropdown');
  if (!btn || !menu) return;

  const close = () => {
    menu.classList.remove('open');
    btn.setAttribute('aria-expanded', 'false');
  };

  btn.addEventListener('click', (e) => {
    e.stopPropagation();
    const open = menu.classList.toggle('open');
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
  });

  document.addEventListener('click', (e) => {
    if (menu.classList.contains('open') && !menu.contains(e.target)) close();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') close();
  });
}

export function initFloatingBtn(options = {}) {
    const btn = document.getElementById('verProductosBtn');
    if (!btn) return;
    btn.addEventListener('click', () => {
        if (options.url) {
            window.location.href = options.url;
        }
    });
}