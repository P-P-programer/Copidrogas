import './bootstrap';

import Alpine from 'alpinejs';
import { initHamburgerMenu, initUserMenu } from './vistas.js';

window.Alpine = Alpine;

Alpine.start();

// Inicializar componentes de UI cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
  initHamburgerMenu();
  initUserMenu();
});
