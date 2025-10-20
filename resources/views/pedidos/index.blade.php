@extends('layouts.app')

@section('title', 'Mis pedidos')

@section('content')

@include('layouts.breadcrumbs', ['breadcrumbs' => [
    ['label' => 'Inicio', 'url' => url('/')],
    ['label' => 'Pedidos', 'url' => route('orders.index')]],
])
<div class="card">
  <h2 style="margin:0 0 .5rem 0;">Mis pedidos</h2>
  <p>Aquí verás tus pedidos cuando empecemos el módulo.</p>
</div>
@endsection

