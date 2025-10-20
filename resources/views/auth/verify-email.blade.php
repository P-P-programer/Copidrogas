@extends('layouts.app')

@section('title', 'Verifica tu correo')

@section('content')
@include('layouts.breadcrumbs', ['breadcrumbs' => [
    ['label' => 'Inicio', 'url' => url('/')],
    ['label' => 'Verifica tu correo', 'url' => route('verification.notice')],
]])

    <section class="card" style="max-width:400px;margin:auto;">
        <div class="mb-4 text-sm text-gray-600">
            {{ __('¡Gracias por registrarte! Antes de continuar, por favor verifica tu dirección de correo haciendo clic en el enlace que te enviamos. Si no recibiste el correo, te podemos enviar otro.') }}
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ __('Un nuevo enlace de verificación ha sido enviado al correo que proporcionaste.') }}
            </div>
        @endif

        <div class="mt-4 flex items-center justify-between">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="px-4 py-2 bg-blue-800 text-white rounded hover:bg-blue-900 transition">
                    {{ __('Reenviar correo de verificación') }}
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    {{ __('Cerrar sesión') }}
                </button>
            </form>
        </div>
    </section>
@endsection

