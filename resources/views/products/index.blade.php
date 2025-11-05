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

<!-- Modal simple de detalle -->
<div id="productModal" hidden style="position:fixed; inset:0; background:rgba(0,0,0,.5); align-items:center; justify-content:center; z-index:1200;">
    <div style="background:#fff; color:#0e1726; border-radius:12px; padding:1.25rem; max-width:700px; width:95%;">
        <button id="modalClose" style="float:right;background:none;border:0;font-weight:700;">×</button>
        <div style="display:flex; gap:1rem; flex-wrap:wrap;">
            <div style="flex:1; min-width:180px;"><img id="modalImage" src="" alt="" style="max-width:100%; border-radius:8px;"></div>
            <div style="flex:1.5;">
                <h3 id="modalName"></h3>
                <p id="modalCategory" style="color:#6b7280;"></p>
                <p id="modalPrice" style="font-weight:700;color:var(--navy-700);"></p>
                <p id="modalStock"></p>
                <p id="modalDescription"></p>
                <div style="margin-top:1rem;">
                    <button id="modalAddCart" style="padding:.6rem .9rem;border-radius:8px;border:0;background:var(--navy-700);color:#fff;cursor:pointer;">Agregar al carrito</button>
                </div>
            </div>
        </div>
    </div>
</div>
<div style="height:140px;"></div>
@endsection