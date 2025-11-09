import './bootstrap';
import Alpine from 'alpinejs';

import { mountUX, toast } from './ux/messages';
import { 
  initHamburgerMenu, 
  initUserMenu, 
  initFloatingBtn, 
  initProductsPage, 
  initCartPage, 
  initIdleLogout, 
  initCheckoutAjax 
} from './vistas.js';

import '../css/messages.css';
import 'bootstrap-icons/font/bootstrap-icons.css';
import '../css/analytics.css';
import '../css/dashboard.css';

window.Alpine = Alpine;
Alpine.start();

/**
 * Punto de arranque global de la aplicación.
 * Orquesta inicialización de:
 * - Sistema de mensajes/toasts
 * - Componentes globales de navegación y UI
 * - Funcionalidades específicas por página
 * - Sistema de cierre por inactividad
 * - Flash de pedidos (feedback post-checkout)
 */
function bootstrap() {
  mountUX();
  initGlobals();
  initPage();
  initIdle();
  handleOrderFlash();
}

/**
 * Inicializa elementos presentes en casi todas las vistas:
 * - Menú hamburguesa (móvil)
 * - Menú de usuario (dropdown)
 * - Botón flotante (scroll / acceso rápido)
 */
function initGlobals() {
  initHamburgerMenu();
  initUserMenu();
  initFloatingBtn({ url: '/productos' });
}

/**
 * Detecta qué bloques específicos existen en el DOM
 * y dispara su inicialización:
 * - Grid de productos (carga dinámica / modal)
 * - Página del carrito (eliminar ítems)
 * - Checkout AJAX (envío sin recarga)
 */
function initPage() {
  if (document.querySelector('#productosGrid')) {
    initProductsPage({ dataUrl: '/productos/data', productUrl: '/productos' });
  }
  if (document.querySelector('.cart-page')) {
    initCartPage();
  }
  if (document.querySelector('#checkoutForm')) {
    initCheckoutAjax();
  }
  if (document.querySelector('#stockGrid')) {
    import('./stock.js');
  }
  // Gestión de usuarios (carga diferida)
  if (document.querySelector('#usersTable')) {
    import('./users.js');
  }
}

/**
 * Activa el sistema de cierre de sesión por inactividad
 * solo si el usuario está autenticado.
 * - timeoutMs: tiempo total de inactividad permitido
 * - warnMs: aviso antes del cierre
 */
function initIdle() {
  if (document.body.dataset.auth === '1') {
    initIdleLogout({ timeoutMs: 10 * 60 * 1000, warnMs: 60 * 1000 });
  }
}

/**
 * Muestra toast posterior a creación de pedido
 * usando el flash de sesión inyectado en la vista.
 */
function handleOrderFlash() {
  const el = document.getElementById('orderFlash');
  if (!el) return;
  const msg = el.dataset.status?.trim();
  if (msg) toast({ type: 'success', message: msg });
}

// Ejecutar bootstrap según estado del documento
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', bootstrap);
} else {
  bootstrap();
}
