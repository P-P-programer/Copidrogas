@extends('layouts.app')

@section('title', 'Carrito')

@section('content')
@include('layouts.breadcrumbs', ['breadcrumbs' => [
  ['label' => 'Inicio', 'url' => url('/')],
  ['label' => 'Carrito'],
]])

<div class="card cart-page">
  <h1 style="margin-top:0;">Tu carrito</h1>
  @if(empty($cart['items']))
    <p>No tienes productos en el carrito.</p>
  @else
    <div style="display:flex; flex-direction:column; gap:1rem;">
      @foreach($cart['items'] as $item)
        <div class="cart-item" data-product-name="{{ $item['name'] }}" data-product-id="{{ $item['id'] }}">
          <img src="{{ asset('img/' . ($item['image'] ?? 'default.png')) }}" alt="" class="cart-item-image">
          <div class="cart-item-info">
            <div class="cart-item-name">{{ $item['name'] }}</div>
            <div class="cart-item-price">${{ number_format($item['price'],0,',','.') }} COP</div>
          </div>
          <div class="cart-item-actions">
            <form method="POST" action="{{ route('cart.update') }}" style="display:flex; align-items:center; gap:.5rem;">
              @csrf
              <input type="hidden" name="product_id" value="{{ $item['id'] }}">
              <label class="sr-only" for="qty-{{ $item['id'] }}">Cantidad para {{ $item['name'] }}</label>
              <input id="qty-{{ $item['id'] }}" type="number" name="qty" min="1" step="1" value="{{ $item['qty'] }}" class="cart-qty-input" inputmode="numeric">
              <button class="confirm" style="padding:.4rem .6rem; border:0; border-radius:8px; background:#1a4175; color:#fff; cursor:pointer; white-space:nowrap;">Actualizar</button>
            </form>
            <form method="POST" action="{{ route('cart.remove', $item['id']) }}">
              @csrf
              @method('DELETE')
              <button type="submit" class="cancel" aria-label="Eliminar {{ $item['name'] }} del carrito" style="padding:.4rem .6rem; border:1px solid #e5e7eb; border-radius:8px; background:#fff; cursor:pointer; white-space:nowrap;">Eliminar</button>
            </form>
          </div>
        </div>
      @endforeach
    </div>
    <div class="cart-total-row">
      <div class="cart-total-label" aria-live="polite" aria-atomic="true">Total: $<span class="cart-total-value">{{ number_format($cart['total'],0,',','.') }}</span> COP</div>
      <button disabled title="Próximamente" class="confirm" style="opacity:.6; cursor:not-allowed; padding:.6rem .9rem; border:0; border-radius:8px; background:#1a4175; color:#fff;">Proceder al pago</button>
    </div>
  @endif
</div>
@endsection