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
            <form method="POST" action="{{ route('cart.update', $item['id']) }}" style="display:flex; align-items:center; gap:.5rem;">
              @csrf
              @method('PATCH')
              <input type="hidden" name="product_id" value="{{ $item['id'] }}">
              <label class="sr-only" for="qty-{{ $item['id'] }}">Cantidad para {{ $item['name'] }}</label>
              <input id="qty-{{ $item['id'] }}" type="number" name="qty" min="1" step="1" value="{{ $item['qty'] }}" class="cart-qty-input" inputmode="numeric">
              <button class="confirm" style="padding:.4rem .6rem; border:0; border-radius:8px; background:#1a4175; color:#fff; cursor:pointer; white-space:nowrap;">Actualizar</button>
            </form>
            <form method="POST" action="{{ route('cart.destroy', $item['id']) }}">
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

@if(!empty($cart['items']))
  <section class="card" style="margin-top:1.5rem; max-width:600px;">
    <h3 style="margin-top:0;">Datos de envío (simulado)</h3>
    <div aria-live="polite" aria-atomic="true">
      @if ($errors->has('cart'))
        <p class="form-error">{{ $errors->first('cart') }}</p>
      @endif
    </div>
    @error('cart') <p class="form-error">{{ $message }}</p> @enderror
    <form id="checkoutForm" data-ajax="1" method="POST" action="{{ route('cart.checkout') }}" style="display:grid; gap:.75rem;">
      @csrf
      <div>
        <label for="ship_name">Nombre completo</label>
        <input id="ship_name" name="ship_name" required value="{{ old('ship_name', auth()->user()->name) }}">
        @error('ship_name') <p class="form-error">{{ $message }}</p> @enderror
      </div>
      <div>
        <label for="ship_phone">Teléfono</label>
        <input id="ship_phone" name="ship_phone" value="{{ old('ship_phone') }}">
        @error('ship_phone') <p class="form-error">{{ $message }}</p> @enderror
      </div>
      <div>
        <label for="ship_address">Dirección</label>
        <input id="ship_address" name="ship_address" required value="{{ old('ship_address') }}">
        @error('ship_address') <p class="form-error">{{ $message }}</p> @enderror
      </div>
      <div>
        <label for="ship_city">Ciudad</label>
        <input id="ship_city" name="ship_city" required value="{{ old('ship_city') }}">
        @error('ship_city') <p class="form-error">{{ $message }}</p> @enderror
      </div>
      <div>
        <label for="ship_notes">Notas (opcional)</label>
        <textarea id="ship_notes" name="ship_notes" rows="3">{{ old('ship_notes') }}</textarea>
        @error('ship_notes') <p class="form-error">{{ $message }}</p> @enderror
      </div>
      <button class="confirm" style="justify-self:start;">Confirmar pedido</button>
    </form>
  </section>
@endif

@endsection