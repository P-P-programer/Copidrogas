@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
@include('layouts.breadcrumbs', ['breadcrumbs' => [
    ['label' => 'Inicio', 'url' => url('/')],
]])

<div class="hero-bg">
    <div class="hero-content">
        <h1>Bienvenido a Farmacia</h1>
        <p>
            Tu salud y bienestar, nuestra prioridad.<br>
            Descubre nuestros productos y servicios.
        </p>
    </div>
</div>

<section class="container productos-destacados">
    <h2>Productos destacados</h2>
    <div class="productos-grid">
        @php
            $featured = \App\Models\Product::take(6)->get();
        @endphp
        @foreach($featured as $i => $product)
            <div class="card producto-card" style="animation-delay: {{ $i * 0.12 }}s;">
                <img src="{{ asset('img/' . ($product->image ?? 'default.png')) }}" alt="{{ $product->name }}">
                <h3>{{ $product->name }}</h3>
                <h3 class="precio">${{ number_format($product->price, 0, ',', '.') }} COP</h3>
            </div>
        @endforeach
    </div>
</section>

<section class="container info-section">
    <div class="info-card">
        <div class="icon">
            <!-- Icono de farmacia -->
            <svg width="32" height="32" fill="none" viewBox="0 0 24 24"><path fill="#1a4175" d="M12 2a7 7 0 0 1 7 7v2h1a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1v-9a1 1 0 0 1 1-1h1V9a7 7 0 0 1 7-7Zm5 9V9a5 5 0 0 0-10 0v2h10Zm2 2H5v7h14v-7Z"/></svg>
        </div>
        <h2>¿Quiénes somos?</h2>
        <p>
            Somos una farmacia comprometida con tu salud y la de tu familia. Ofrecemos productos de calidad, atención personalizada y asesoría profesional para tu bienestar.
        </p>
    </div>
    <div class="info-card">
        <div class="icon">
            <!-- Icono de recomendaciones/salud -->
            <svg width="32" height="32" fill="none" viewBox="0 0 24 24"><path fill="#1a4175" d="M12 2a10 10 0 1 1 0 20 10 10 0 0 1 0-20Zm1 5h-2v4H7v2h4v4h2v-4h4v-2h-4V7Z"/></svg>
        </div>
        <h2>Recomendaciones</h2>
        <ul>
            <li>Consulta siempre a tu médico antes de automedicarte.</li>
            <li>Mantén los medicamentos fuera del alcance de los niños.</li>
            <li>Revisa la fecha de vencimiento antes de consumir cualquier producto.</li>
        </ul>
    </div>
</section>

@if(auth()->check())
    <button id="verProductosBtn" class="floating-btn">
        Ver productos
    </button>
@else
    <button id="verProductosBtn" class="floating-btn" onclick="window.location.href='{{ route('login') }}'">
        Ver productos
    </button>
@endif

<div style="height: 120px;"></div> <!-- Espacio extra para el botón flotante -->

@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/plantilla.css') }}">
@endpush

@push('scripts')
<script type="module">
import { initFloatingBtn } from '/js/vistas.js';
initFloatingBtn({ 
    url: '{{ auth()->check() ? route('products.index') : route('login') }}'
});
</script>
@endpush