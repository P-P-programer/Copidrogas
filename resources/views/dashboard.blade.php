@extends('layouts.app')

@section('title', 'Bienvenido')

@section('content')
@php
  $redirect = session('post_login_redirect');
  $role = auth()->user()->role ?? 'usuario';
@endphp

<div class="card">
  <h1 style="margin-top:0;">¡Te has logueado con éxito!</h1>
  @if($redirect)
    <p>Te estamos redirigiendo a: <strong>{{ $redirect }}</strong>. Por favor espera…</p>
    <div id="postLogin" data-redirect="{{ $redirect }}" style="display:none;"></div>
  @else
    <p>Selecciona una opción según tu perfil.</p>
  @endif
</div>

@if(!$redirect)
  <div class="info-section">
    @if($role === 'admin')
      <div class="info-card">
        <h2>Panel administrador</h2>
        <ul>
          <li><a href="{{ route('products.index') }}">Gestionar productos</a></li>
          <li><a href="{{ route('orders.index') }}">Pedidos</a></li>
          <li><a href="{{ route('profile.edit') }}">Perfil</a></li>
        </ul>
      </div>
    @elseif($role === 'proveedor')
      <div class="info-card">
        <h2>Panel proveedor</h2>
        <ul>
          <li><a href="{{ route('products.index') }}">Productos</a></li>
          <li><a href="{{ route('orders.index') }}">Órdenes</a></li>
        </ul>
      </div>
    @else
      <div class="info-card">
        <h2>Panel usuario</h2>
        <ul>
          <li><a href="{{ route('products.index') }}">Ver productos</a></li>
          <li><a href="{{ route('orders.index') }}">Mis pedidos</a></li>
          <li><a href="{{ route('profile.edit') }}">Mi perfil</a></li>
        </ul>
      </div>
    @endif
  </div>
@endif
@endsection
