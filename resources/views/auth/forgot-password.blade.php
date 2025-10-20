@extends('layouts.app')

@section('title', 'Recuperar contraseña')

@section('content')
@include('layouts.breadcrumbs', ['breadcrumbs' => [
    ['label' => 'Inicio', 'url' => url('/')],
    ['label' => 'Recuperar contraseña', 'url' => route('password.request')],
]])

    <section class="card" style="max-width:400px;margin:auto;">
        <div class="mb-4 text-sm text-gray-600">
            {{ __('¿Olvidaste tu contraseña? No hay problema. Ingresa tu correo y te enviaremos un enlace para restablecerla.') }}
        </div>

        @if (session('status'))
            <div class="mb-4 text-green-600">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <!-- Email Address -->
            <div>
                <label for="email" class="block font-medium text-sm text-gray-700">Email</label>
                <input id="email" class="block mt-1 w-full rounded" type="email" name="email" value="{{ old('email') }}" required autofocus />
                @error('email')
                    <div class="mt-2 text-red-600 text-sm">{{ $message }}</div>
                @enderror
            </div>

            <div class="flex items-center justify-end mt-4">
                <button type="submit" class="px-4 py-2 bg-blue-800 text-white rounded hover:bg-blue-900 transition">
                    {{ __('Enviar enlace de recuperación') }}
                </button>
            </div>
        </form>
    </section>
@endsection

