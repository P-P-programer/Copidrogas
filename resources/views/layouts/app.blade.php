<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'Laravel'))</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Vite: CSS y JS -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="site-body" data-auth="{{ auth()->check() ? '1' : '0' }}">
        <header class="site-header">
            <div class="container header-inner">
                <span class="brand">{{ config('app.name', 'Farmacia') }}</span> <!-- Solo logo, sin enlace -->

                <div class="header-right">
                    <button class="hamburger" id="hamburgerBtn" aria-label="Abrir menú" aria-expanded="false" aria-controls="primaryNav">
                        <span></span><span></span><span></span>
                    </button>

                    <nav id="primaryNav" class="nav">
                        <a href="{{ url('/') }}">Inicio</a>
                        <a href="{{ url('/productos') }}">Productos</a>
                        <a href="{{ url('/contacto') }}">Contacto</a>
                    </nav>

                    <a href="{{ route('cart.index') }}" class="cart-btn" id="cartBtn" aria-label="Carrito">
                        <i class="bi bi-cart-fill cart-icon" aria-hidden="true"></i>
                        @auth
                            @php $cartCount = auth()->user()->cartItems()->sum('qty'); @endphp
                            <span id="cartCount" class="cart-badge">{{ $cartCount }}</span>
                        @else
                            <span id="cartCount" class="cart-badge">0</span>
                        @endauth
                    </a>

                    <div class="user-menu">
                        <button class="avatar-btn" id="userMenuBtn" aria-haspopup="menu" aria-expanded="false">
                            <span class="avatar">
                                @auth
                                    {{ mb_strtoupper(mb_substr(auth()->user()->name,0,1)) }}
                                @else
                                    U
                                @endauth
                            </span>
                        </button>
                        <div class="dropdown" id="userDropdown">
                            @guest
                                @if (Route::has('login'))
                                    <a href="{{ route('login') }}">Iniciar sesión</a>
                                @else
                                    <a href="{{ url('/login') }}">Iniciar sesión</a>
                                @endif

                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}">Registrarse</a>
                                @endif
                            @endguest

                            @auth
                                <div class="user-info">
                                    <div class="name">{{ auth()->user()->name }}</div>
                                    <div class="email">{{ auth()->user()->email }}</div>
                                </div>
                                @if (Route::has('profile.edit'))
                                    <a href="{{ route('profile.edit') }}">Mi perfil</a>
                                @endif
                                @if (Route::has('orders.index'))
                                    <a href="{{ route('orders.index') }}">Mis pedidos</a>
                                @endif
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="logout">Cerrar sesión</button>
                                </form>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="site-main">
            <div class="container">
                @hasSection('content')
                    @yield('content')
                @else
                    {{ $slot ?? '' }}
                @endif
            </div>
        </main>

        <footer class="site-footer">
            <div class="container footer-inner">
                <p>&copy; {{ date('Y') }} {{ config('app.name', 'Farmacia') }}. Todos los derechos reservados.</p>
            </div>
        </footer>
    </body>
</html>
