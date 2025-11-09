import { toast } from './ux/messages';

document.addEventListener('DOMContentLoaded', () => {
  const grid = document.getElementById('stockGrid');
  const loader = document.getElementById('loader');
  const noResults = document.getElementById('noResults');
  const searchInput = document.getElementById('searchInput');
  const categoriesList = document.getElementById('categoriesList');
  const modal = document.getElementById('stockModal');
  const modalClose = document.getElementById('modalClose');
  const stockForm = document.getElementById('stockForm');

  let products = [];
  let currentProduct = null;

  // Cargar productos
  async function loadProducts(categoryId = '', search = '') {
    loader.style.display = 'block';
    grid.innerHTML = '';
    noResults.style.display = 'none';

    try {
      const params = new URLSearchParams();
      if (categoryId) params.append('category_id', categoryId);
      if (search) params.append('search', search);

      const res = await fetch(`/stock/data?${params}`);
      products = await res.json();

      if (products.length === 0) {
        noResults.style.display = 'block';
      } else {
        renderProducts(products);
      }
    } catch (err) {
      console.error(err);
      toast({ type: 'error', message: 'Error al cargar productos.' });
    } finally {
      loader.style.display = 'none';
    }
  }

  // Renderizar grid
  function renderProducts(items) {
    grid.innerHTML = items.map(p => {
      const hId = `prod-name-${p.id}`;
      return `
      <article class="producto-card" data-id="${p.id}" aria-labelledby="${hId}">
        <img src="/img/${p.image ?? 'default.png'}" alt="" loading="lazy" decoding="async">
        <h3 id="${hId}">${p.name}</h3>
        <p class="muted">${p.category?.name || 'Sin categoría'}</p>
        <p class="price">${formatPrice(p.price)} COP</p>
        <p class="stock-info ${p.stock < 10 ? 'low' : p.stock < 50 ? 'medium' : 'high'}">
          Stock: ${p.stock}
        </p>
        <button class="btn-edit-stock" data-id="${p.id}">Editar Stock</button>
      </article>`;
    }).join('');

    // Event listeners para abrir modal
    document.querySelectorAll('.btn-edit-stock').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = parseInt(btn.dataset.id);
        openModal(id);
      });
    });
  }

  // Abrir modal
  function openModal(productId) {
    currentProduct = products.find(p => p.id === productId);
    if (!currentProduct) return;
    const img = document.getElementById('modalImage');
    img.src = `/img/${currentProduct.image ?? 'default.png'}`;
    img.alt = ""; // redundante con el título

    document.getElementById('modalName').textContent = currentProduct.name;
    document.getElementById('modalCategory').textContent = currentProduct.category?.name || 'Sin categoría';
    document.getElementById('modalPrice').textContent = `${formatPrice(currentProduct.price)} COP`;
    document.getElementById('modalStock').textContent = `Stock actual: ${currentProduct.stock}`;
    document.getElementById('newStock').value = currentProduct.stock;

    modal.hidden = false;
    document.getElementById('newStock').focus();
  }

  // Cerrar modal
  function closeModal() {
    modal.hidden = true;
    currentProduct = null;
    stockForm.reset();
  }

  modalClose.addEventListener('click', closeModal);
  modal.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
  });

  // Enviar actualización
  stockForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!currentProduct) return;

    const newStock = parseInt(document.getElementById('newStock').value);
    const btn = document.getElementById('btnUpdateStock');
    const originalText = btn.textContent;

    btn.disabled = true;
    btn.textContent = 'Actualizando...';

    try {
      const res = await fetch(`/stock/${currentProduct.id}`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({ stock: newStock })
      });

      const data = await res.json();

      if (data.ok) {
        toast({ type: 'success', message: data.message });
        currentProduct.stock = data.product.stock;
        closeModal();
        loadProducts(getActiveCategory(), searchInput.value.trim());
      } else {
        toast({ type: 'error', message: data.message || 'Error al actualizar.' });
      }
    } catch (err) {
      console.error(err);
      toast({ type: 'error', message: 'Error de red.' });
    } finally {
      btn.disabled = false;
      btn.textContent = originalText;
    }
  });

  // Búsqueda
  searchInput.addEventListener('input', () => {
    loadProducts(getActiveCategory(), searchInput.value.trim());
  });

  // Filtros de categoría
  categoriesList.addEventListener('click', (e) => {
    if (e.target.classList.contains('category-btn')) {
      document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
      e.target.classList.add('active');
      loadProducts(e.target.dataset.id, searchInput.value.trim());
    }
  });

  function getActiveCategory() {
    return document.querySelector('.category-btn.active')?.dataset.id || '';
  }

  function formatPrice(price) {
    return new Intl.NumberFormat('es-CO').format(price);
  }

  // Cargar inicial
  loadProducts();
});