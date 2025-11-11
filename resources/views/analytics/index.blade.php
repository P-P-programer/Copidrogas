@extends('layouts.app')

@section('title', 'Analítica')

@section('content')
@include('layouts.breadcrumbs', ['breadcrumbs' => [
  ['label' => 'Inicio', 'url' => url('/')],
  ['label' => 'Dashboard', 'url' => route('dashboard')],
  ['label' => 'Analítica'],
]])

<section class="card analytics-section">
  <h2 style="margin:0;">Resumen de ventas y stock</h2>

  <div class="analytics-grid">
    <div class="card chart-card">
      <h3>Top 5 productos más vendidos</h3>
      <div class="chart-container">
        <canvas id="chartTopProducts" aria-label="Top 5 productos más vendidos" role="img"></canvas>
      </div>
    </div>
    <div class="card chart-card">
      <h3>Top 5 con menor stock</h3>
      <div class="chart-container">
        <canvas id="chartLowStock" aria-label="Top 5 productos con menor stock" role="img"></canvas>
      </div>
    </div>
  </div>

  {{-- Panel de actividad exclusivo para Super Admin --}}
  @if(auth()->user()->role_id === 4)
    <div class="activity-panel">
      <h2 style="margin:2rem 0 1rem;">Panel de actividad (Super Admin)</h2>
      
      {{-- Últimas órdenes --}}
      <div class="card">
        <h3>Últimos 10 pedidos realizados</h3>
        @if($activity['orders']->isEmpty())
          <p style="color:#6b7280;">Sin pedidos recientes.</p>
        @else
          <div class="activity-table-wrapper">
            <table class="activity-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Usuario</th>
                  <th>Email</th>
                  <th>Rol</th>
                  <th>Total</th>
                  <th>Estado</th>
                  <th>Fecha</th>
                </tr>
              </thead>
              <tbody>
                @foreach($activity['orders'] as $order)
                  <tr>
                    <td data-label="ID">#{{ $order->id }}</td>
                    <td data-label="Usuario">{{ $order->user->name ?? 'N/D' }}</td>
                    <td data-label="Email">{{ $order->user->email ?? 'N/D' }}</td>
                    <td data-label="Rol">
                      <span class="role-badge {{ $order->user->role->badge_class ?? 'role-default' }}">
                        {{ $order->user->role->display_name ?? 'N/D' }}
                      </span>
                    </td>
                    <td data-label="Total">{{ number_format($order->total,0,',','.') }} COP</td>
                    <td data-label="Estado"><span class="badge {{ $order->status_badge_class }}">{{ $order->status_label }}</span></td>
                    <td data-label="Fecha">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>

      {{-- Últimas actualizaciones de stock --}}
      <div class="card" style="margin-top:1.5rem;">
        <h3>Últimas 10 actualizaciones de stock</h3>
        @if($activity['stock_updates']->isEmpty())
          <p style="color:#6b7280;">Sin actualizaciones recientes.</p>
        @else
          <div class="activity-table-wrapper">
            <table class="activity-table">
              <thead>
                <tr>
                  <th>Producto</th>
                  <th>Stock actual</th>
                  <th>Última actualización</th>
                </tr>
              </thead>
              <tbody>
                @foreach($activity['stock_updates'] as $product)
                  <tr>
                    <td data-label="Producto">{{ $product->name }}</td>
                    <td data-label="Stock">
                      <span class="stock-indicator @if($product->stock < 10) low @elseif($product->stock < 50) medium @else high @endif">
                        {{ $product->stock }}
                      </span>
                    </td>
                    <td data-label="Actualización">{{ $product->updated_at?->diffForHumans() ?? 'Sin actualizar' }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>

      {{-- Ajustes de stock recientes --}}
      <div class="card" style="margin-top:1.5rem;">
        <h3>Ajustes de stock recientes</h3>
        @if(empty($activity['stock_logs']) || $activity['stock_logs']->isEmpty())
          <p style="color:#6b7280;">Sin registros recientes.</p>
        @else
          <div class="activity-table-wrapper">
            <table class="activity-table">
              <thead>
                <tr>
                  <th>Usuario</th>
                  <th>Producto</th>
                  <th>Antes</th>
                  <th>Después</th>
                  <th>Fecha</th>
                </tr>
              </thead>
              <tbody>
                @foreach($activity['stock_logs'] as $log)
                  <tr>
                    <td data-label="Usuario">{{ $log->user->name ?? 'N/D' }} ({{ $log->user->email ?? 'N/D' }})</td>
                    <td data-label="Producto">{{ $log->product->name ?? 'N/D' }}</td>
                    <td data-label="Antes">{{ $log->before_stock ?? 'N/D' }}</td>
                    <td data-label="Después">{{ $log->after_stock ?? 'N/D' }}</td>
                    <td data-label="Fecha">{{ $log->created_at?->format('d/m/Y H:i') ?? 'N/D' }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </div>
  @endif
</section>

@vite('resources/js/analytics.js')
@endsection