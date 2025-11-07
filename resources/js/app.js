import './bootstrap';
import Alpine from 'alpinejs';
import { mountUX, toast } from './ux/messages';
import { initHamburgerMenu, initUserMenu, initFloatingBtn, initProductsPage, initCartPage, initIdleLogout } from './vistas.js';
import '../css/messages.css';
import 'bootstrap-icons/font/bootstrap-icons.css';

window.Alpine = Alpine;
Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
  mountUX();
  
  // Inicializar componentes globales
  initGlobalComponents();
  
  // Manejar eventos de autenticación
  handleAuthEvents();
  
  // Inicializar páginas específicas
  initPageSpecificFeatures();
  
  // Sistema de inactividad para usuarios autenticados
  initIdleLogoutIfAuthenticated();
});

/**
 * Inicializa componentes globales del layout
 */
function initGlobalComponents() {
  initHamburgerMenu();
  initUserMenu();
  initFloatingBtn({ url: '/productos' });
}

/**
 * Maneja eventos relacionados con autenticación
 */
function handleAuthEvents() {
  const handlers = [
    handleLogoutToast,
    handleAuthFlashMessages,
    handlePostLoginRedirect
  ];
  
  handlers.forEach(handler => handler());
}

/**
 * Muestra toast cuando el usuario cierra sesión
 */
function handleLogoutToast() {
  const params = new URLSearchParams(window.location.search);
  if (params.get('logged_out') === '1') {
    toast({ type: 'info', message: 'Has cerrado sesión.' });
  }
}

/**
 * Muestra toasts de errores o mensajes de sesión
 */
function handleAuthFlashMessages() {
  const flash = document.getElementById('authFlash');
  if (!flash) return;
  
  const error = flash.dataset.error?.trim();
  const status = flash.dataset.status?.trim();
  
  if (error) toast({ type: 'error', message: error, timeout: 5000 });
  if (status) toast({ type: 'info', message: status });
}

/**
 * Redirige al usuario después del login si viene de una ruta protegida
 */
function handlePostLoginRedirect() {
  const postLogin = document.getElementById('postLogin');
  if (!postLogin?.dataset.redirect) return;
  
  const targetUrl = postLogin.dataset.redirect;
  
  toast({ type: 'success', message: 'Inicio de sesión correcto.' });
  toast({ type: 'info', message: 'Redirigiendo…' });
  
  setTimeout(() => {
    window.location.href = targetUrl;
  }, 1400);
}

/**
 * Inicializa funcionalidades específicas según la página
 */
function initPageSpecificFeatures() {
  const pageInitializers = [
    { 
      selector: '#productosGrid', 
      init: () => initProductsPage({ dataUrl: '/productos/data', productUrl: '/productos' })
    },
    { 
      selector: '.cart-page', 
      init: initCartPage 
    }
  ];
  
  pageInitializers.forEach(({ selector, init }) => {
    if (document.querySelector(selector)) {
      init();
    }
  });
}

/**
 * Activa el sistema de cierre por inactividad si el usuario está autenticado
 */
function initIdleLogoutIfAuthenticated() {
  if (document.body.dataset.auth === '1') {
    initIdleLogout({ 
      timeoutMs: 10 * 60 * 1000,  // 10 minutos
      warnMs: 60 * 1000            // Aviso 60s antes
    });
  }
}
