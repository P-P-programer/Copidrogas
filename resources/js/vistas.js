/**
 * Lógica de UI para el layout principal
 */

import { toast, confirm } from './ux/messages';

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
  document.querySelectorAll('form[action*="/carrito/update"]').forEach(form => {
    const qtyInput = form.querySelector('input[name="qty"]');
    const updateBtn = form.querySelector('button.confirm');
    const productId = form.querySelector('input[name="product_id"]').value;
    const originalQty = parseInt(qtyInput.value, 10);

    // Estado inicial del botón
    setButtonState(updateBtn, false);

    // Habilitar/deshabilitar botón según cambios
    qtyInput.addEventListener('input', () => {
      const newQty = parseInt(qtyInput.value, 10);
      const hasChanged = newQty !== originalQty && newQty >= 1;
      setButtonState(updateBtn, hasChanged);
    });

    // AJAX submit
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const newQty = parseInt(qtyInput.value, 10);
      
      if (newQty === originalQty) {
        toast({ type: 'info', message: 'No has modificado la cantidad.' });
        return;
      }
      
      if (newQty < 1) {
        toast({ type: 'warn', message: 'La cantidad debe ser al menos 1.' });
        return;
      }

      await updateCartQuantity(productId, newQty, updateBtn, qtyInput);
    });
  });
}

/**
 * Actualiza la cantidad de un producto en el carrito
 */
async function updateCartQuantity(productId, qty, updateBtn, qtyInput) {
  toast({ type: 'info', message: 'Actualizando carrito...' });
  
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  
  try {
    const res = await fetch('/carrito/update', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': token ?? ''
      },
      body: JSON.stringify({ product_id: productId, qty })
    });

    const json = await res.json();
    
    if (json.ok) {
      updateCartBadge(json.cart.count);
      updateCartTotal(json.cart.total);
      toast({ type: 'success', message: 'Cantidad actualizada.' });
      setButtonState(updateBtn, false);
    } else {
      toast({ type: 'error', message: json.message || 'No se pudo actualizar.' });
    }
  } catch (e) {
    console.error('Error al actualizar carrito:', e);
    toast({ type: 'error', message: 'Error de red al actualizar.' });
  }
}

/**
 * Inicializa formularios de eliminación de productos
 */
function initCartDeleteForms() {
  document.querySelectorAll('form').forEach(form => {
    const hasDeleteMethod = form.querySelector('input[name="_method"][value="DELETE"]');
    const deleteBtn = form.querySelector('button.cancel');
    const actionUrl = form.getAttribute('action');
    
    // Validar que sea un formulario de eliminación del carrito
    if (!hasDeleteMethod || !deleteBtn || !actionUrl) return;
    if (!actionUrl.includes('/carrito/') || actionUrl.includes('/update')) return;
    
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      
      const productName = form.closest('[data-product-name]')?.dataset.productName || 'este producto';
      const productId = extractProductId(form, actionUrl);
      
      if (!productId) {
        toast({ type: 'error', message: 'Error: ID de producto no encontrado.' });
        return;
      }
      
      const confirmed = await confirm({
        title: 'Eliminar del carrito',
        message: `¿Seguro deseas eliminar "${productName}"?`,
        confirmText: 'Sí, eliminar',
        cancelText: 'Cancelar'
      });
      
      if (confirmed) {
        await deleteCartItem(productId, form);
      }
    });
  });
}

/**
 * Extrae el ID del producto del formulario o de la URL
 */
function extractProductId(form, actionUrl) {
  const dataId = form.closest('[data-product-name]')?.dataset.productId;
  if (dataId) return dataId;
  
  const matches = actionUrl.match(/\/carrito\/(\d+)/);
  return matches ? matches[1] : null;
}

/**
 * Elimina un producto del carrito
 */
async function deleteCartItem(productId, form) {
  toast({ type: 'info', message: 'Eliminando producto...' });
  
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  
  try {
    const res = await fetch(`/carrito/${productId}`, {
      method: 'DELETE',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': token ?? ''
      }
    });

    if (!res.ok) {
      throw new Error(`HTTP ${res.status}`);
    }

    const json = await res.json();
    
    if (json.ok) {
      updateCartBadge(json.cart.count);
      removeCartItemFromDOM(form, json.cart.total);
      toast({ type: 'success', message: 'Producto eliminado del carrito.' });
    } else {
      toast({ type: 'error', message: json.message || 'No se pudo eliminar.' });
    }
  } catch (e) {
    console.error('Error al eliminar del carrito:', e);
    toast({ type: 'error', message: 'Error al eliminar del carrito.' });
  }
}

/**
 * Remueve el elemento del DOM con animación
 */
function removeCartItemFromDOM(form, newTotal) {
  const itemRow = form.closest('[data-product-name]');
  if (!itemRow) return;
  
  itemRow.style.transition = 'opacity 0.3s';
  itemRow.style.opacity = '0';
  
  setTimeout(() => {
    itemRow.remove();
    const remaining = document.querySelectorAll('[data-product-name]').length;
    
    if (remaining === 0) {
      location.reload();
    } else {
      updateCartTotal(newTotal);
    }
  }, 300);
}

/**
 * Actualiza el badge del carrito en el header
 */
function updateCartBadge(count) {
  const badge = document.getElementById('cartCount');
  if (badge) badge.textContent = count;
}

/**
 * Actualiza el total del carrito en la página
 */
function updateCartTotal(total) {
  const totalEl = document.querySelector('.cart-total-value');
  if (totalEl) {
    totalEl.textContent = new Intl.NumberFormat('es-CO').format(total);
  }
}

/**
 * Establece el estado visual de un botón
 */
function setButtonState(button, enabled) {
  button.disabled = !enabled;
  button.style.opacity = enabled ? '1' : '0.5';
  button.style.cursor = enabled ? 'pointer' : 'not-allowed';
}

/**
 * Menú hamburguesa responsive
 */
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
      closeHamburgerMenu(btn, nav);
    }
  });

  // Cerrar al hacer clic en enlaces del menú (mobile)
  nav.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', () => {
      if (window.innerWidth < 768) {
        closeHamburgerMenu(btn, nav);
      }
    });
  });
}

/**
 * Cierra el menú hamburguesa
 */
function closeHamburgerMenu(btn, nav) {
  nav.classList.remove('open');
  btn.setAttribute('aria-expanded', 'false');
}

/**
 * Menú de usuario (guest/auth)
 */
export function initUserMenu() {
  const btn = document.getElementById('userMenuBtn');
  const menu = document.getElementById('userDropdown');
  if (!btn || !menu) return;

  const closeMenu = () => {
    menu.classList.remove('open');
    btn.setAttribute('aria-expanded', 'false');
  };

  btn.addEventListener('click', (e) => {
    e.stopPropagation();
    const isOpen = menu.classList.toggle('open');
    btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  });

  document.addEventListener('click', (e) => {
    if (menu.classList.contains('open') && !menu.contains(e.target)) {
      closeMenu();
    }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeMenu();
  });
}

/**
 * Botón flotante de acción
 */
export function initFloatingBtn(options = {}) {
  const btn = document.getElementById('verProductosBtn');
  if (!btn) return;
  
  document.body.classList.add('has-floating-btn');

  btn.addEventListener('click', () => {
    if (options.url) window.location.href = options.url;
  });
}

/**
 * Sistema AJAX para la página de productos
 */
export function initProductsPage(options = {}) {
  const grid = document.getElementById('productosGrid');
  const loader = document.getElementById('loader');
  const search = document.getElementById('searchInput');
  const categoryBtns = document.querySelectorAll('.category-btn');
  const noResults = document.getElementById('noResults');

  const state = {
    currentCategory: '',
    currentQ: '',
    currentPage: 1,
    lastPage: 1,
    isLoading: false
  };

  const showLoader = (show = true) => {
    if (loader) loader.style.display = show ? 'block' : 'none';
  };

  const fetchProducts = async ({ append = false } = {}) => {
    if (state.isLoading) return;
    
    state.isLoading = true;
    showLoader(true);
    
    const params = new URLSearchParams();
    if (state.currentCategory) params.set('category', state.currentCategory);
    if (state.currentQ) params.set('q', state.currentQ);
    params.set('page', state.currentPage);
    
    try {
      const res = await fetch(`${options.dataUrl}?${params}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      const json = await res.json();
      state.lastPage = json.meta.last_page;
      renderProducts(json.data, append);
    } catch (e) {
      console.error('Error al cargar productos:', e);
      toast({ type: 'error', message: 'Error al cargar productos.' });
    } finally {
      showLoader(false);
      state.isLoading = false;
    }
  };

  const renderProducts = (items, append = false) => {
    if (!grid) return;
    if (!append) grid.innerHTML = '';
    
    items.forEach(item => {
      const div = document.createElement('div');
      div.className = 'card producto-card';
      div.dataset.id = item.id;
      div.innerHTML = `
        <img src="/img/${item.image ?? 'default.png'}" alt="">
        <h2>${item.name}</h2>
        <div class="precio">${new Intl.NumberFormat('es-CO').format(item.price)} COP</div>
        <button class="ver-detalle" style="margin-top:.5rem;padding:.5rem .75rem;border-radius:8px;border:0;background:var(--navy-700);color:#fff;cursor:pointer;">Ver</button>
      `;
      grid.appendChild(div);
    });

    if (noResults) {
      noResults.style.display = grid.children.length === 0 ? 'block' : 'none';
    }
    
    attachCardEvents();
  };

  const attachCardEvents = () => {
    document.querySelectorAll('.producto-card .ver-detalle').forEach(btn => {
      btn.addEventListener('click', async (e) => {
        const id = e.target.closest('.producto-card').dataset.id;
        await openProductModal(id);
      });
    });
  };

  const openProductModal = async (id) => {
    try {
      const res = await fetch(`/productos/${id}/json`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      const product = await res.json();
      
      document.getElementById('modalImage').src = `/img/${product.image ?? 'default.png'}`;
      document.getElementById('modalName').textContent = product.name;
      document.getElementById('modalPrice').textContent = `${new Intl.NumberFormat('es-CO').format(product.price)} COP`;
      document.getElementById('modalCategory').textContent = product.category ? `Categoría: ${product.category.name ?? product.category}` : '';
      document.getElementById('modalStock').textContent = product.stock ? `En stock: ${product.stock}` : 'Sin stock';
      document.getElementById('modalDescription').textContent = product.description ?? '';
      
      const addBtn = document.getElementById('modalAddCart');
      addBtn.dataset.productId = product.id;
      addBtn.dataset.price = product.price;
      addBtn.onclick = () => showQtyControls(product);
      
      const modal = document.getElementById('productModal');
      modal.removeAttribute('hidden');
      modal.style.display = 'flex';
    } catch (e) {
      console.error('Error al cargar producto:', e);
      toast({ type: 'error', message: 'Error al cargar el producto.' });
    }
  };

  const showQtyControls = (product) => {
    let box = document.getElementById('qtyBox');
    
    if (!box) {
      box = document.createElement('div');
      box.id = 'qtyBox';
      document.getElementById('modalAddCart').parentElement.appendChild(box);
    }
    
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
  };

  const formatCOP = (n) => {
    return `${new Intl.NumberFormat('es-CO', { maximumFractionDigits: 0 }).format(n)} COP`;
  };

  const confirmAddToCart = async (productId, qty, price) => {
    const totalText = formatCOP(price * qty);
    const confirmed = await confirm({
      title: 'Agregar al carrito',
      message: `¿Agregar ${qty} unidad(es) por un total aprox de ${totalText}?`,
      confirmText: 'Agregar',
      cancelText: 'Cancelar'
    });
    
    if (!confirmed) return;
    
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    try {
      const res = await fetch('/carrito/add', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': token ?? ''
        },
        body: JSON.stringify({ product_id: productId, qty })
      });
      
      if (res.status === 401) {
        toast({ type: 'info', message: 'Inicia sesión para usar el carrito.' });
        setTimeout(() => window.location.href = '/login', 1500);
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
      console.error('Error al agregar al carrito:', e);
      toast({ type: 'error', message: 'Error de red al agregar al carrito.' });
    }
  };

  // Eventos del modal
  const closeModal = () => {
    const modal = document.getElementById('productModal');
    modal.setAttribute('hidden', '');
    modal.style.display = 'none';
  };

  document.getElementById('modalClose')?.addEventListener('click', closeModal);
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeModal();
  });
  document.getElementById('productModal')?.addEventListener('click', (e) => {
    if (e.target.id === 'productModal') closeModal();
  });

  // Buscador con debounce
  if (search) {
    let debounce;
    search.addEventListener('input', (e) => {
      clearTimeout(debounce);
      debounce = setTimeout(() => {
        state.currentQ = e.target.value.trim();
        state.currentPage = 1;
        fetchProducts({ append: false });
      }, 300);
    });
  }

  // Filtros de categoría
  categoryBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      categoryBtns.forEach(b => b.classList.remove('active'));
      e.target.classList.add('active');
      state.currentCategory = e.target.dataset.id || '';
      state.currentPage = 1;
      fetchProducts({ append: false });
    });
  });

  // Scroll infinito
  window.addEventListener('scroll', () => {
    if (state.isLoading || state.currentPage >= state.lastPage) return;
    
    const nearBottom = (window.innerHeight + window.scrollY) >= (document.body.offsetHeight - 300);
    if (nearBottom) {
      state.currentPage++;
      fetchProducts({ append: true });
    }
  });

  // Carga inicial
  fetchProducts({ append: false });
}