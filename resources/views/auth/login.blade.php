@extends('layouts.app')

@section('title', 'Iniciar sesión')

@section('content')
@include('layouts.breadcrumbs', ['breadcrumbs' => [
    ['label' => 'Inicio', 'url' => url('/')],
    ['label' => 'Iniciar sesión', 'url' => route('login')],
]])

    <section class="card" style="max-width:400px;margin:auto;">
        <h2 class="mb-4" style="margin-bottom:1rem;">Iniciar sesión</h2>
        <!-- Session Status -->
        @if (session('status'))
            <div class="mb-4 text-green-600">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email Address -->
            <div>
                <label for="email" class="block font-medium text-sm text-gray-700">Email</label>
                <input id="email" class="block mt-1 w-full rounded" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" />
                @error('email')
                    <div class="mt-2 text-red-600 text-sm">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password -->
            <div class="mt-4">
                <label for="password" class="block font-medium text-sm text-gray-700">Contraseña</label>
                <input id="password" class="block mt-1 w-full rounded"
                        type="password"
                        name="password"
                        required autocomplete="current-password" />
                @error('password')
                    <div class="mt-2 text-red-600 text-sm">{{ $message }}</div>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="block mt-4">
                <label for="remember_me" class="inline-flex items-center">
                    <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                    <span class="ms-2 text-sm text-gray-600">Recordarme</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                        ¿Olvidaste tu contraseña?
                    </a>
                @endif

                <button type="submit" class="ms-3 px-4 py-2 bg-blue-800 text-white rounded hover:bg-blue-900 transition">
                    Iniciar sesión
                </button>
            </div>
        </form>
    </section>
@endsection
