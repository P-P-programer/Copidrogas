@extends('layouts.app')

@section('title', 'Restablecer contraseña')

@section('content')
@include('layouts.breadcrumbs', ['breadcrumbs' => [
    ['label' => 'Inicio', 'url' => url('/')],
    ['label' => 'Restablecer contraseña', 'url' => route('password.reset', ['token' => $request->route('token')])],
]])
    <section class="card" style="max-width:400px;margin:auto;">
        <h2 style="margin-bottom:1rem;">Restablecer contraseña</h2>
        <form method="POST" action="{{ route('password.store') }}">
            @csrf

            <!-- Password Reset Token -->
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <!-- Email Address -->
            <div>
                <label for="email" class="block font-medium text-sm text-gray-700">Email</label>
                <input id="email" class="block mt-1 w-full rounded" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" />
                @error('email')
                    <div class="mt-2 text-red-600 text-sm">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password -->
            <div class="mt-4">
                <label for="password" class="block font-medium text-sm text-gray-700">Contraseña nueva</label>
                <input id="password" class="block mt-1 w-full rounded" type="password" name="password" required autocomplete="new-password" />
                @error('password')
                    <div class="mt-2 text-red-600 text-sm">{{ $message }}</div>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div class="mt-4">
                <label for="password_confirmation" class="block font-medium text-sm text-gray-700">Confirmar contraseña</label>
                <input id="password_confirmation" class="block mt-1 w-full rounded" type="password" name="password_confirmation" required autocomplete="new-password" />
                @error('password_confirmation')
                    <div class="mt-2 text-red-600 text-sm">{{ $message }}</div>
                @enderror
            </div>

            <div class="flex items-center justify-end mt-4">
                <button type="submit" class="px-4 py-2 bg-blue-800 text-white rounded hover:bg-blue-900 transition">
                    Restablecer contraseña
                </button>
            </div>
        </form>
    </section>
@endsection
