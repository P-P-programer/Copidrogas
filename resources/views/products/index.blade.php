@extends('layouts.app')

@section('title', 'Productos')

@section('content')
@include('layouts.breadcrumbs', ['breadcrumbs' => [
    ['label' => 'Inicio', 'url' => url('/')],
    ['label' => 'Productos'],
]])
<div class="catalogo-flex" style="padding-top:1rem; padding-bottom:1rem; display:flex; gap:1.5rem; align-items:flex-start; flex-wrap:wrap;">
    <aside style="min-width:240px; max-width:300px;">
        <label for="searchInput" style="display:block; margin-bottom:.5rem; font-weight:600;">Buscar productos</label>
        <input id="searchInput" type="search" placeholder="Buscar..." style="margin-bottom:1rem; width:100%;">
        <div class="card">
            <h2 style="margin-top:0;">Filtros</h2>
            <br>
            <ul id="categoriesList" style="padding-left:0; list-style:none; margin:0;">
                <li><button data-id="" class="category-btn active">Todas</button></li>
                @foreach($categories as $cat)
                    <li>
                        <button data-id="{{ $cat->id }}" class="category-btn">{{ $cat->name }}</button>
                    </li>
                @endforeach
            </ul>
        </div>
    </aside>
    <main style="flex:1; min-width:300px;">
        <h1 style="margin-bottom:1rem;">Catálogo</h1>
        <div id="productosGrid" class="productos-grid"></div>
        <div id="noResults" style="display:none; text-align:center; color:#888; margin-top:2rem;">
            No se encontraron productos.
        </div>
        <div id="loader" style="text-align:center; padding:1rem; display:none;">Cargando...</div>
    </main>
</div>

<!-- Modal de detalle -->
<div id="productModal" class="modal-overlay" hidden role="dialog" aria-modal="true" aria-labelledby="modalName">
  <div class="modal-dialog">
    <button id="modalClose" class="modal-close" aria-label="Cerrar">×</button>
    <div class="modal-body">
      <div class="modal-media">
        <img id="modalImage" src="" alt="" class="modal-image">
      </div>
      <div class="modal-info">
        <h3 id="modalName"></h3>
        <p id="modalCategory" class="muted"></p>
        <p id="modalPrice" class="price"></p>
        <p id="modalStock"></p>
        <p id="modalDescription"></p>

        <div class="modal-actions">
          <button id="modalAddCart" class="confirm">Agregar al carrito</button>
        </div>

        <!-- Contenedor para controles de cantidad (se llena por JS) -->
        <div id="qtyBox"></div>
      </div>
    </div>
  </div>
</div>
<div style="height:140px;"></div>
@endsection