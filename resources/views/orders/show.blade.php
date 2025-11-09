@extends('layouts.app')

@section('title', 'Pedido #'.$order->id)

@section('content')
@include('layouts.breadcrumbs', ['breadcrumbs' => [
  ['label' => 'Mis pedidos', 'url' => route('orders.index')],
  ['label' => 'Pedido #'.$order->id],
]])

@if(session('status'))
  <div id="orderFlash" data-status="{{ session('status') }}"></div>
@endif

@if($order->recetas->count())
    <section class="card" style="margin-bottom:2rem;">
        <h3>Recetas asociadas</h3>
        <ul>
            @foreach($order->recetas as $receta)
                <li>
                    <a href="{{ asset('storage/' . $receta->file_path) }}" target="_blank">
                        {{ $receta->original_name ?? 'Receta PDF' }}
                    </a>
                    <span style="color:#888;">subida el {{ $receta->created_at->format('d/m/Y H:i') }}</span>
                </li>
            @endforeach
        </ul>
    </section>
@endif

<section class="card" style="display:grid; gap:1rem;">
  <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:.5rem;">
    <h2 style="margin:0;">Pedido #{{ $order->id }}</h2>
    <span class="badge {{ $order->status_badge_class }}">{{ $order->status_label }}</span>
  </div>
  
  <div><strong>Fecha:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</div>
  <div><strong>Total:</strong> {{ number_format($order->total,0,',','.') }} COP</div>

  <h3 style="margin:.75rem 0 0;">Datos de envío</h3>
  <ul style="margin:0; padding-left:1.1rem;">
    <li><strong>Nombre:</strong> {{ $order->ship_name }}</li>
    <li><strong>Teléfono:</strong> {{ $order->ship_phone ?: 'N/D' }}</li>
    <li><strong>Dirección:</strong> {{ $order->ship_address }}</li>
    <li><strong>Ciudad:</strong> {{ $order->ship_city }}</li>
    @if($order->ship_notes)
      <li><strong>Notas:</strong> {{ $order->ship_notes }}</li>
    @endif
  </ul>

  <h3 style="margin:.75rem 0 0;">Productos</h3>
  <div class="table-responsive">
    <table class="order-items-table">
      <thead>
        <tr>
          <th>Producto</th>
          <th>Cantidad</th>
          <th>Precio unit.</th>
          <th>Subtotal</th>
        </tr>
      </thead>
      <tbody>
        @foreach($order->items as $item)
          <tr>
            <td>{{ $item->product->name ?? 'Producto eliminado' }}</td>
            <td>{{ $item->qty }}</td>
            <td>{{ number_format($item->unit_price,0,',','.') }} COP</td>
            <td>{{ number_format($item->total,0,',','.') }} COP</td>
          </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <td colspan="3" style="text-align:right;"><strong>Total:</strong></td>
          <td><strong>{{ number_format($order->total,0,',','.') }} COP</strong></td>
        </tr>
      </tfoot>
    </table>
  </div>

  <div style="margin-top:1rem;">
    <a href="{{ route('orders.index') }}" class="btn-secondary">← Volver a mis pedidos</a>
  </div>
</section>
@endsection