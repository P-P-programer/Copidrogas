@extends('layouts.app')

@section('title', 'Confirmar contraseña')

@section('content')
@include('layouts.breadcrumbs', ['breadcrumbs' => [
    ['label' => 'Inicio', 'url' => url('/')],
    ['label' => 'Confirmar contraseña', 'url' => route('password.confirm')],
]])

    <section class="card" style="max-width:400px;margin:auto;">
        <div class="mb-4 text-sm text-gray-600">
            {{ __('Esta es un área segura de la aplicación. Por favor confirma tu contraseña antes de continuar.') }}
        </div>

        <form method="POST" action="{{ route('password.confirm') }}">
            @csrf

            <!-- Password -->
            <div>
                <label for="password" class="block font-medium text-sm text-gray-700">Contraseña</label>
                <input id="password" class="block mt-1 w-full rounded"
                        type="password"
                        name="password"
                        required autocomplete="current-password" />
                @error('password')
                    <div class="mt-2 text-red-600 text-sm">{{ $message }}</div>
                @enderror
            </div>

            <div class="flex items-center justify-end mt-4">
                <button type="submit" class="px-4 py-2 bg-blue-800 text-white rounded hover:bg-blue-900 transition">
                    {{ __('Confirmar') }}
                </button>
            </div>
        </form>
    </section>
@endsection

