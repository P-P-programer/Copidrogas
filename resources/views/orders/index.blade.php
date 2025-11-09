@extends('layouts.app')

@section('title', 'Mis pedidos')

@section('content')
@include('layouts.breadcrumbs', ['breadcrumbs' => [
    ['label' => 'Inicio', 'url' => url('/')],
    ['label' => 'Dashboard', 'url' => route('dashboard')],
    ['label' => 'Mis pedidos'],
]])

<section class="card">
  <h1 style="margin:0 0 1rem;">Mis pedidos</h1>

  @if($orders->isEmpty())
    <p style="text-align:center; color:#6b7280; padding:2rem 0;">
      Aún no tienes pedidos. <a href="{{ route('products.index') }}" style="color:var(--navy-700); text-decoration:underline;">Explora nuestros productos</a>
    </p>
  @else
    <div class="orders-list">
      @foreach($orders as $order)
        <article class="order-card">
          <div class="order-header">
            <div>
              <h2 class="order-id">Pedido #{{ $order->id }}</h2>
              <p class="order-date">{{ $order->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <span class="badge {{ $order->status_badge_class }}">{{ $order->status_label }}</span>
          </div>
          
          <div class="order-summary">
            <div class="order-detail">
              <span class="label">Productos:</span>
              <span class="value">{{ $order->items->count() }}</span>
            </div>
            <div class="order-detail">
              <span class="label">Total:</span>
              <span class="value">{{ number_format($order->total, 0, ',', '.') }} COP</span>
            </div>
            <div class="order-detail">
              <span class="label">Destino:</span>
              <span class="value">{{ $order->ship_city }}</span>
            </div>
            {{-- Estado de receta --}}
            <div class="order-detail">
              <span class="label">Receta:</span>
              @if($order->recetas->count())
                <span class="value">
                  <a href="{{ asset('storage/' . $order->recetas->last()->file_path) }}" target="_blank">
                    PDF subido
                  </a>
                  <span style="color:#15803d; font-weight:bold; background:#dcfce7; padding:2px 8px; border-radius:6px;">
                    En revisión
                  </span>
                </span>
              @else
                <span class="value" style="color:#b91c1c; font-weight:bold; background:#fff1f2; padding:2px 8px; border-radius:6px;">
                  Pendiente de subir
                </span>
              @endif
            </div>
          </div>

          {{-- Formulario para subir receta si está pendiente --}}
          @if($order->status === 'placed' && $order->recetas->count() === 0)
            <form method="POST" action="{{ route('orders.uploadReceta', $order) }}" enctype="multipart/form-data" style="margin-top:1rem;">
              @csrf
              <label for="receta_pdf_{{ $order->id }}" style="font-weight:600; margin-bottom:0.5rem; display:block;">
                Subir receta médica (PDF)
              </label>
              <input id="receta_pdf_{{ $order->id }}" type="file" name="receta_pdf" accept="application/pdf" required>
              <button type="submit" class="confirm" style="margin-top:.5rem;">Subir receta</button>
              @error('receta_pdf')
                <div class="alert alert-danger">{{ $message }}</div>
              @enderror
            </form>
          @endif

          <div class="order-actions">
            <a href="{{ route('orders.show', $order) }}" class="btn-view">Ver detalles</a>
          </div>
        </article>
      @endforeach
    </div>

    <div class="pagination-wrapper" style="margin-top:1.5rem;">
      {{ $orders->links() }}
    </div>
  @endif
</section>
@endsection