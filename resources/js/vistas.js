/**
 * Lógica de UI para el layout principal
 */

import { toast, confirm } from './ux/messages';

// ============================================================================
// CARRITO - Actualización y eliminación con AJAX
// ============================================================================

/**
 * Inicializa controles del carrito (confirmación eliminar, validación actualizar, AJAX)
 */
export function initCartPage() {
  initCartUpdateForms();
  initCartDeleteForms();
}

/**
 * Inicializa formularios de actualización de cantidad
 */
function initCartUpdateForms() {
  const forms = document.querySelectorAll('form[action*="/carrito/update"]');
  forms.forEach(setupUpdateForm);
}

function setupUpdateForm(form) {
  const elements = extractFormElements(form);
  if (!elements) return;

  const { qtyInput, updateBtn, productId, originalQty } = elements;

  setButtonState(updateBtn, false);
  attachQuantityChangeListener(qtyInput, updateBtn, originalQty);
  attachUpdateSubmitHandler(form, qtyInput, updateBtn, productId, originalQty);
}

function extractFormElements(form) {
  const qtyInput = form.querySelector('input[name="qty"]');
  const updateBtn = form.querySelector('button.confirm');
  const productIdInput = form.querySelector('input[name="product_id"]');
  
  if (!qtyInput || !updateBtn || !productIdInput) return null;

  return {
    qtyInput,
    updateBtn,
    productId: productIdInput.value,
    originalQty: parseInt(qtyInput.value, 10)
  };
}

function attachQuantityChangeListener(input, button, originalQty) {
  input.addEventListener('input', () => {
    const newQty = parseInt(input.value, 10);
    const hasChanged = newQty !== originalQty && newQty >= 1;
    setButtonState(button, hasChanged);
  });
}

function attachUpdateSubmitHandler(form, input, button, productId, originalQty) {
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const newQty = parseInt(input.value, 10);

    const validation = validateQuantityUpdate(newQty, originalQty);
    if (!validation.valid) {
      toast({ type: validation.type, message: validation.message });
      return;
    }

    await updateCartQuantity(productId, newQty, button);
  });
}

function validateQuantityUpdate(newQty, originalQty) {
  if (newQty === originalQty) {
    return { valid: false, type: 'info', message: 'No has modificado la cantidad.' };
  }
  if (newQty < 1) {
    return { valid: false, type: 'warn', message: 'La cantidad debe ser al menos 1.' };
  }
  return { valid: true };
}

async function updateCartQuantity(productId, qty, updateBtn) {
  toast({ type: 'info', message: 'Actualizando carrito...' });

  try {
    const response = await fetchCartUpdate(productId, qty);

    if (response.ok) {
      updateCartBadge(response.cart.count);
      updateCartTotal(response.cart.total);
      toast({ type: 'success', message: 'Cantidad actualizada.' });
      setButtonState(updateBtn, false);
    } else {
      toast({ type: 'error', message: response.message || 'No se pudo actualizar.' });
    }
  } catch (e) {
    handleFetchError(e, 'actualizar carrito');
  }
}

async function fetchCartUpdate(productId, qty) {
  const res = await fetch('/carrito/update', {
    method: 'POST',
    headers: getAuthHeaders(),
    body: JSON.stringify({ product_id: productId, qty })
  });
  return await res.json();
}

// ============================================================================
// CARRITO - Eliminación
// ============================================================================

function initCartDeleteForms() {
  const forms = document.querySelectorAll('form');
  forms.forEach(form => {
    if (isCartDeleteForm(form)) {
      setupDeleteForm(form);
    }
  });
}

function isCartDeleteForm(form) {
  const hasDeleteMethod = form.querySelector('input[name="_method"][value="DELETE"]');
  const deleteBtn = form.querySelector('button.cancel');
  const actionUrl = form.getAttribute('action');

  return hasDeleteMethod && 
         deleteBtn && 
         actionUrl && 
         actionUrl.includes('/carrito/') && 
         !actionUrl.includes('/update');
}

function setupDeleteForm(form) {
  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const { productName, productId } = extractDeleteFormData(form);

    if (!productId) {
      toast({ type: 'error', message: 'Error: ID de producto no encontrado.' });
      return;
    }

    const confirmed = await confirmDeletion(productName);
    if (confirmed) {
      await deleteCartItem(productId, form);
    }
  });
}

function extractDeleteFormData(form) {
  const productName = form.closest('[data-product-name]')?.dataset.productName || 'este producto';
  const actionUrl = form.getAttribute('action');
  const productId = extractProductId(form, actionUrl);

  return { productName, productId };
}

function extractProductId(form, actionUrl) {
  const dataId = form.closest('[data-product-name]')?.dataset.productId;
  if (dataId) return dataId;

  const matches = actionUrl.match(/\/carrito\/(\d+)/);
  return matches ? matches[1] : null;
}

async function confirmDeletion(productName) {
  return await confirm({
    title: 'Eliminar del carrito',
    message: `¿Seguro deseas eliminar "${productName}"?`,
    confirmText: 'Sí, eliminar',
    cancelText: 'Cancelar'
  });
}

async function deleteCartItem(productId, form) {
  toast({ type: 'info', message: 'Eliminando producto...' });

  try {
    const response = await fetchCartDelete(productId);

    if (response.ok) {
      updateCartBadge(response.cart.count);
      removeCartItemFromDOM(form, response.cart.total);
      toast({ type: 'success', message: 'Producto eliminado del carrito.' });
    } else {
      toast({ type: 'error', message: response.message || 'No se pudo eliminar.' });
    }
  } catch (e) {
    handleFetchError(e, 'eliminar del carrito');
  }
}

async function fetchCartDelete(productId) {
  const res = await fetch(`/carrito/${productId}`, {
    method: 'DELETE',
    headers: getAuthHeaders()
  });

  if (!res.ok) throw new Error(`HTTP ${res.status}`);
  return await res.json();
}

function removeCartItemFromDOM(form, newTotal) {
  const itemRow = form.closest('[data-product-name]');
  if (!itemRow) return;

  animateRemoval(itemRow, () => {
    itemRow.remove();
    handleCartEmptyState(newTotal);
  });
}

function animateRemoval(element, callback) {
  element.style.transition = 'opacity 0.3s';
  element.style.opacity = '0';
  setTimeout(callback, 300);
}

function handleCartEmptyState(newTotal) {
  const remaining = document.querySelectorAll('[data-product-name]').length;
  remaining === 0 ? location.reload() : updateCartTotal(newTotal);
}

// ============================================================================
// UTILIDADES DEL CARRITO
// ============================================================================

function updateCartBadge(count) {
  const badge = document.getElementById('cartCount');
  if (badge) badge.textContent = count;
}

function updateCartTotal(total) {
  const totalEl = document.querySelector('.cart-total-value');
  if (totalEl) {
    totalEl.textContent = new Intl.NumberFormat('es-CO').format(total);
  }
}

function setButtonState(button, enabled) {
  button.disabled = !enabled;
  button.style.opacity = enabled ? '1' : '0.5';
  button.style.cursor = enabled ? 'pointer' : 'not-allowed';
}

// ============================================================================
// MENÚS DE NAVEGACIÓN
// ============================================================================

export function initHamburgerMenu() {
  const elements = {
    btn: document.getElementById('hamburgerBtn'),
    nav: document.getElementById('primaryNav')
  };

  if (!elements.btn || !elements.nav) return;

  attachHamburgerToggle(elements);
  attachHamburgerOutsideClick(elements);
  attachHamburgerLinkClick(elements);
}

function attachHamburgerToggle({ btn, nav }) {
  btn.addEventListener('click', () => {
    const isOpen = nav.classList.toggle('open');
    btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  });
}

function attachHamburgerOutsideClick({ btn, nav }) {
  document.addEventListener('click', (e) => {
    if (window.innerWidth >= 768) return;
    if (!btn.contains(e.target) && !nav.contains(e.target)) {
      closeHamburgerMenu(btn, nav);
    }
  });
}

function attachHamburgerLinkClick({ btn, nav }) {
  nav.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', () => {
      if (window.innerWidth < 768) {
        closeHamburgerMenu(btn, nav);
      }
    });
  });
}

function closeHamburgerMenu(btn, nav) {
  nav.classList.remove('open');
  btn.setAttribute('aria-expanded', 'false');
}

export function initUserMenu() {
  const elements = {
    btn: document.getElementById('userMenuBtn'),
    menu: document.getElementById('userDropdown')
  };

  if (!elements.btn || !elements.menu) return;

  const closeMenu = createMenuCloser(elements);

  attachUserMenuToggle(elements, closeMenu);
  attachUserMenuOutsideClick(elements.menu, closeMenu);
  attachUserMenuEscapeKey(closeMenu);
}

function createMenuCloser({ btn, menu }) {
  return () => {
    menu.classList.remove('open');
    btn.setAttribute('aria-expanded', 'false');
  };
}

function attachUserMenuToggle({ btn, menu }, closeMenu) {
  btn.addEventListener('click', (e) => {
    e.stopPropagation();
    const isOpen = menu.classList.toggle('open');
    btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  });
}

function attachUserMenuOutsideClick(menu, closeMenu) {
  document.addEventListener('click', (e) => {
    if (menu.classList.contains('open') && !menu.contains(e.target)) {
      closeMenu();
    }
  });
}

function attachUserMenuEscapeKey(closeMenu) {
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeMenu();
  });
}

// ============================================================================
// BOTÓN FLOTANTE
// ============================================================================

export function initFloatingBtn(options = {}) {
  const btn = document.getElementById('verProductosBtn');
  if (!btn) return;

  document.body.classList.add('has-floating-btn');

  btn.addEventListener('click', () => {
    if (options.url) window.location.href = options.url;
  });
}

// ============================================================================
// PÁGINA DE PRODUCTOS - AJAX
// ============================================================================

export function initProductsPage(options = {}) {
  const elements = {
    grid: document.getElementById('productosGrid'),
    loader: document.getElementById('loader'),
    search: document.getElementById('searchInput'),
    categoryBtns: document.querySelectorAll('.category-btn'),
    noResults: document.getElementById('noResults')
  };

  const state = createProductsState();
  const api = createProductsAPI(options, elements, state);

  initProductSearch(elements.search, api, state);
  initCategoryFilters(elements.categoryBtns, api, state);
  initInfiniteScroll(api, state);
  initProductModal();

  api.fetchProducts({ append: false });
}

function createProductsState() {
  return {
    currentCategory: '',
    currentQ: '',
    currentPage: 1,
    lastPage: 1,
    isLoading: false
  };
}

function createProductsAPI(options, elements, state) {
  const showLoader = (show = true) => {
    if (elements.loader) elements.loader.style.display = show ? 'block' : 'none';
  };

  const fetchProducts = async ({ append = false } = {}) => {
    if (state.isLoading) return;

    state.isLoading = true;
    showLoader(true);

    const params = buildProductParams(state);

    try {
      const res = await fetch(`${options.dataUrl}?${params}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      const json = await res.json();
      state.lastPage = json.meta.last_page;
      renderProducts(elements.grid, elements.noResults, json.data, append);
    } catch (e) {
      handleFetchError(e, 'cargar productos');
    } finally {
      showLoader(false);
      state.isLoading = false;
    }
  };

  return { fetchProducts };
}

function buildProductParams(state) {
  const params = new URLSearchParams();
  if (state.currentCategory) params.set('category', state.currentCategory);
  if (state.currentQ) params.set('q', state.currentQ);
  params.set('page', state.currentPage);
  return params;
}

function renderProducts(grid, noResults, items, append = false) {
  if (!grid) return;
  if (!append) grid.innerHTML = '';

  items.forEach(item => {
    const card = createProductCard(item);
    grid.appendChild(card);
  });

  if (noResults) {
    noResults.style.display = grid.children.length === 0 ? 'block' : 'none';
  }

  attachCardEvents();
}

function createProductCard(item) {
  const div = document.createElement('div');
  div.className = 'card producto-card';
  div.dataset.id = item.id;
  div.innerHTML = `
    <img src="/img/${item.image ?? 'default.png'}" alt="">
    <h2>${item.name}</h2>
    <div class="precio">${new Intl.NumberFormat('es-CO').format(item.price)} COP</div>
    <button class="ver-detalle" style="margin-top:.5rem;padding:.5rem .75rem;border-radius:8px;border:0;background:var(--navy-700);color:#fff;cursor:pointer;">Ver</button>
  `;
  return div;
}

function attachCardEvents() {
  document.querySelectorAll('.producto-card .ver-detalle').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      const id = e.target.closest('.producto-card').dataset.id;
      await openProductModal(id);
    });
  });
}

function initProductSearch(searchInput, api, state) {
  if (!searchInput) return;

  let debounce;
  searchInput.addEventListener('input', (e) => {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
      state.currentQ = e.target.value.trim();
      state.currentPage = 1;
      api.fetchProducts({ append: false });
    }, 300);
  });
}

function initCategoryFilters(categoryBtns, api, state) {
  categoryBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      categoryBtns.forEach(b => b.classList.remove('active'));
      e.target.classList.add('active');
      state.currentCategory = e.target.dataset.id || '';
      state.currentPage = 1;
      api.fetchProducts({ append: false });
    });
  });
}

function initInfiniteScroll(api, state) {
  window.addEventListener('scroll', () => {
    if (state.isLoading || state.currentPage >= state.lastPage) return;

    const nearBottom = (window.innerHeight + window.scrollY) >= (document.body.offsetHeight - 300);
    if (nearBottom) {
      state.currentPage++;
      api.fetchProducts({ append: true });
    }
  });
}

// ============================================================================
// MODAL DE PRODUCTO
// ============================================================================

function initProductModal() {
  const closeModal = () => {
    const modal = document.getElementById('productModal');
    modal?.setAttribute('hidden', '');
    if (modal) modal.style.display = 'none';
    document.body.style.overflow = ''; // ← habilita scroll del body
  };

  document.getElementById('modalClose')?.addEventListener('click', closeModal);
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeModal();
  });
  document.getElementById('productModal')?.addEventListener('click', (e) => {
    if (e.target.id === 'productModal') closeModal();
  });
}

async function openProductModal(id) {
  try {
    const product = await fetchProduct(id);
    populateModalWithProduct(product);
    showModal();
  } catch (e) {
    handleFetchError(e, 'cargar el producto');
  }
}

async function fetchProduct(id) {
  const res = await fetch(`/productos/${id}/json`, {
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  });
  return await res.json();
}

function populateModalWithProduct(product) {
  const fields = {
    modalImage: { attr: 'src', value: `/img/${product.image ?? 'default.png'}` },
    modalName: { text: product.name },
    modalPrice: { text: `${new Intl.NumberFormat('es-CO').format(product.price)} COP` },
    modalCategory: { text: product.category ? `Categoría: ${product.category.name ?? product.category}` : '' },
    modalStock: { text: product.stock ? `En stock: ${product.stock}` : 'Sin stock' },
    modalDescription: { text: product.description ?? '' }
  };

  Object.entries(fields).forEach(([id, config]) => {
    const el = document.getElementById(id);
    if (!el) return;
    if (config.attr) el.setAttribute(config.attr, config.value);
    if (config.text !== undefined) el.textContent = config.text;
  });

  const addBtn = document.getElementById('modalAddCart');
  if (addBtn) {
    addBtn.dataset.productId = product.id;
    addBtn.dataset.price = product.price;
    addBtn.onclick = () => showQtyControls(product);
  }
}

function showModal() {
  const modal = document.getElementById('productModal');
  if (!modal) return;
  modal.removeAttribute('hidden');
  modal.style.display = 'flex';
  document.body.style.overflow = 'hidden';
  trapFocus(modal); // activar trampa de foco
}

function trapFocus(container){
  const selectors = ['a[href]','button','input','select','textarea','[tabindex]:not([tabindex="-1"])'];
  const nodes = Array.from(container.querySelectorAll(selectors.join(','))).filter(el => !el.hasAttribute('disabled'));
  if (!nodes.length) return;
  nodes[0].focus();
  const onKey = (e) => {
    if (e.key !== 'Tab') return;
    const first = nodes[0], last = nodes[nodes.length - 1];
    if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
    else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
  };
  container.addEventListener('keydown', onKey);
}

function showQtyControls(product) {
  const box = getOrCreateQtyBox();
  renderQtyBox(box, product);
  attachQtyBoxEvents(box, product);
}

function getOrCreateQtyBox() {
  let box = document.getElementById('qtyBox');
  if (!box) {
    box = document.createElement('div');
    box.id = 'qtyBox';
    document.getElementById('modalAddCart')?.parentElement.appendChild(box);
  }
  return box;
}

function renderQtyBox(box, product) {
  box.innerHTML = `
    <div class="qty-controls">
      <button type="button" id="qtyMinus" aria-label="Menos">-</button>
      <input type="number" id="qtyInput" min="1" value="1" />
      <button type="button" id="qtyPlus" aria-label="Más">+</button>
    </div>
    <div class="qty-total">Total aprox: <span id="qtyTotal">${formatCOP(product.price)}</span></div>
    <div class="qty-actions">
      <button type="button" class="confirm" id="qtyConfirm">Confirmar</button>
      <button type="button" class="cancel" id="qtyCancel">Cancelar</button>
    </div>
  `;
}

function attachQtyBoxEvents(box, product) {
  const qtyInput = box.querySelector('#qtyInput');
  const totalEl = box.querySelector('#qtyTotal');

  const recalc = () => {
    const qty = Math.max(1, parseInt(qtyInput.value || '1', 10));
    totalEl.textContent = formatCOP(product.price * qty);
  };

  box.querySelector('#qtyMinus').onclick = () => {
    qtyInput.value = Math.max(1, parseInt(qtyInput.value || '1', 10) - 1);
    recalc();
  };

  box.querySelector('#qtyPlus').onclick = () => {
    qtyInput.value = Math.max(1, parseInt(qtyInput.value || '1', 10) + 1);
    recalc();
  };

  qtyInput.oninput = recalc;

  box.querySelector('#qtyCancel').onclick = () => box.remove();

  box.querySelector('#qtyConfirm').onclick = () => {
    const qty = Math.max(1, parseInt(qtyInput.value || '1', 10));
    confirmAddToCart(product.id, qty, product.price);
  };
}

async function confirmAddToCart(productId, qty, price) {
  const totalText = formatCOP(price * qty);
  const confirmed = await confirm({
    title: 'Agregar al carrito',
    message: `¿Agregar ${qty} unidad(es) por un total aprox de ${totalText}?`,
    confirmText: 'Agregar',
    cancelText: 'Cancelar'
  });

  if (!confirmed) return;

  await addToCart(productId, qty);
}

async function addToCart(productId, qty) {
  try {
    const res = await fetch('/carrito/add', {
      method: 'POST',
      headers: getAuthHeaders(),
      body: JSON.stringify({ product_id: productId, qty })
    });

    if (res.status === 401) {
      handleUnauthorized();
      return;
    }

    const json = await res.json();

    if (json.ok) {
      updateCartBadge(json.cart.count);
      document.getElementById('qtyBox')?.remove();
      toast({ type: 'success', message: 'Producto agregado al carrito.' });
    } else {
      toast({ type: 'error', message: 'No se pudo agregar al carrito.' });
    }
  } catch (e) {
    handleFetchError(e, 'agregar al carrito');
  }
}

function handleUnauthorized() {
  toast({ type: 'info', message: 'Inicia sesión para usar el carrito.' });
  setTimeout(() => window.location.href = '/login', 1500);
}

// ============================================================================
// IDLE LOGOUT - Cierre por inactividad
// ============================================================================

export function initIdleLogout({ timeoutMs = 10 * 60 * 1000, warnMs = 60 * 1000 } = {}) {
  const timers = { warn: null, logout: null };
  let warningShown = false;

  const resetTimers = () => {
    warningShown = false;
    clearTimeout(timers.warn);
    clearTimeout(timers.logout);

    timers.warn = setTimeout(showWarning, Math.max(0, timeoutMs - warnMs));
    timers.logout = setTimeout(performLogout, timeoutMs);
  };

  const showWarning = async () => {
    if (warningShown) return;
    warningShown = true;

    const keepAlive = await confirm({
      title: 'Sesión a punto de expirar',
      message: 'Has estado inactivo. ¿Deseas mantener tu sesión iniciada?',
      confirmText: 'Seguir conectado',
      cancelText: 'Cerrar sesión'
    });

    keepAlive ? await extendSession(resetTimers) : await performLogout();
  };

  const performLogout = async () => {
    try {
      await fetch('/logout', {
        method: 'POST',
        headers: getAuthHeaders()
      });
    } finally {
      window.location.href = '/?logged_out=1';
    }
  };

  ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'].forEach(evt =>
    window.addEventListener(evt, resetTimers, { passive: true })
  );

  resetTimers();
}

async function extendSession(callback) {
  try {
    await fetch('/session/ping', {
      method: 'POST',
      headers: getAuthHeaders()
    });
    toast({ type: 'success', message: 'Sesión extendida.' });
    callback();
  } catch {
    window.location.href = '/?logged_out=1';
  }
}

// ============================================================================
// UTILIDADES GLOBALES
// ============================================================================

function getAuthHeaders() {
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  return {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN': token ?? ''
  };
}

function formatCOP(n) {
  return `${new Intl.NumberFormat('es-CO', { maximumFractionDigits: 0 }).format(n)} COP`;
}

function handleFetchError(error, context) {
  console.error(`Error al ${context}:`, error);
  toast({ type: 'error', message: `Error al ${context}.` });
}