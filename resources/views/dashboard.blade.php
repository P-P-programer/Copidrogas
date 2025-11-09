@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@include('layouts.breadcrumbs', ['breadcrumbs' => [
    ['label' => 'Inicio', 'url' => url('/')],
    ['label' => 'Dashboard'],
]])

<div class="dashboard-welcome">
    <div class="card welcome-card">
        <div class="welcome-header">
            <div class="avatar-large">
                {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="welcome-text">
                <h1>¡Bienvenido, {{ auth()->user()->name }}!</h1>
                <p class="welcome-subtitle">{{ auth()->user()->email }}</p>
                @if(auth()->user()->role)
                    <span class="role-badge {{ auth()->user()->role->badge_class }}" style="margin-top: 0.5rem; display: inline-block;">
                        {{ auth()->user()->role->display_name }}
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    {{-- Analítica: SOLO Super Admin --}}
    @can('view-analytics')
        <a href="{{ route('analytics.index') }}" class="dashboard-card">
            <div class="dashboard-card-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M4 11H2v3h2v-3zm5-4H7v7h2V7zm5-5v12h-2V2h2zm-2-1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1h-2zM6 7a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7zm-5 4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-3z"/>
                </svg>
            </div>
            <div class="dashboard-card-content">
                <h3>Analítica</h3>
                <p>Visualiza estadísticas y métricas del sistema</p>
            </div>
        </a>
    @endcan

    {{-- Gestionar Stock: Super Admin, Admin, Proveedor --}}
    @can('manage-products')
        <a href="{{ route('stock.index') }}" class="dashboard-card">
            <div class="dashboard-card-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M2.5 1A1.5 1.5 0 0 0 1 2.5v11A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-11A1.5 1.5 0 0 0 13.5 1h-11zM2 2.5a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 .5.5v11a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11z"/>
                    <path d="M10.854 6.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 8.793l2.646-2.647a.5.5 0 0 1 .708 0z"/>
                </svg>
            </div>
            <div class="dashboard-card-content">
                <h3>Gestionar Stock</h3>
                <p>Administra inventario y productos</p>
            </div>
        </a>
    @endcan

    {{-- Gestionar usuarios: Admin y Super Admin --}}
    @can('manage-users')
        <a href="{{ route('users.index') }}" class="dashboard-card">
            <div class="dashboard-card-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                    <path fill-rule="evenodd" d="M5.216 14A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216z"/>
                    <path d="M4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z"/>
                </svg>
            </div>
            <div class="dashboard-card-content">
                <h3>Gestionar Usuarios</h3>
                <p>Administra usuarios y permisos</p>
            </div>
        </a>
    @endcan

    {{-- Mis pedidos: Todos los usuarios autenticados --}}
    <a href="{{ route('orders.index') }}" class="dashboard-card">
        <div class="dashboard-card-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" viewBox="0 0 16 16">
                <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
            </svg>
        </div>
        <div class="dashboard-card-content">
            <h3>Mis Pedidos</h3>
            <p>Consulta el estado de tus órdenes</p>
        </div>
    </a>

    {{-- Ver productos: Todos --}}
    <a href="{{ route('products.index') }}" class="dashboard-card">
        <div class="dashboard-card-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" viewBox="0 0 16 16">
                <path d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1zm3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4h-3.5z"/>
            </svg>
        </div>
        <div class="dashboard-card-content">
            <h3>Productos</h3>
            <p>Explora nuestro catálogo</p>
        </div>
    </a>

    {{-- Mi perfil: Todos --}}
    <a href="{{ route('profile.edit') }}" class="dashboard-card">
        <div class="dashboard-card-icon" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" viewBox="0 0 16 16">
                <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
            </svg>
        </div>
        <div class="dashboard-card-content">
            <h3>Mi Perfil</h3>
            <p>Actualiza tu información personal</p>
        </div>
    </a>
</div>
@endsection
