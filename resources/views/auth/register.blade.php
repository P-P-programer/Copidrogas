@extends('layouts.app')

@section('title', 'Registrarse')

@section('content')
@include('layouts.breadcrumbs', ['breadcrumbs' => [
    ['label' => 'Inicio', 'url' => url('/')],
    ['label' => 'Registrarse', 'url' => route('register')],
]])
    <section class="card" style="max-width:400px;margin:auto;">
        <h2 style="margin-bottom:1rem;">Registrarse</h2>
        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- Name -->
            <div>
                <label for="name" class="block font-medium text-sm text-gray-700">Nombre</label>
                <input id="name" class="block mt-1 w-full rounded" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" />
                @error('name')
                    <div class="mt-2 text-red-600 text-sm">{{ $message }}</div>
                @enderror
            </div>

            <!-- Email Address -->
            <div class="mt-4">
                <label for="email" class="block font-medium text-sm text-gray-700">Email</label>
                <input id="email" class="block mt-1 w-full rounded" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" />
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
                        required autocomplete="new-password" />
                @error('password')
                    <div class="mt-2 text-red-600 text-sm">{{ $message }}</div>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div class="mt-4">
                <label for="password_confirmation" class="block font-medium text-sm text-gray-700">Confirmar contraseña</label>
                <input id="password_confirmation" class="block mt-1 w-full rounded"
                        type="password"
                        name="password_confirmation" required autocomplete="new-password" />
                @error('password_confirmation')
                    <div class="mt-2 text-red-600 text-sm">{{ $message }}</div>
                @enderror
            </div>

            <div class="flex items-center justify-end mt-4">
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                    ¿Ya tienes cuenta?
                </a>

                <button type="submit" class="ms-4 px-4 py-2 bg-blue-800 text-white rounded hover:bg-blue-900 transition">
                    Registrarse
                </button>
            </div>
        </form>
    </section>
@endsection
