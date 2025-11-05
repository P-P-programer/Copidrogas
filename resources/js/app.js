import './bootstrap';
import Alpine from 'alpinejs';
import { initHamburgerMenu, initUserMenu, initFloatingBtn, initProductsPage, initCartPage } from './vistas.js';
import { mountUX } from './ux/messages';
import '../css/messages.css';

window.Alpine = Alpine;
Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
  mountUX();
  initHamburgerMenu();
  initUserMenu();
  initFloatingBtn({ url: '/productos' });

  // Catálogo AJAX
  if (document.getElementById('productosGrid')) {
    initProductsPage({
      dataUrl: '/productos/data',
      productUrl: '/productos'
    });
  }

  // Carrito
  if (document.querySelector('.cart-page')) {
    initCartPage();
  }
});
