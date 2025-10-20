@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
@include('layouts.breadcrumbs', ['breadcrumbs' => [
    ['label' => 'Inicio', 'url' => url('/')],
]])

    <section class="card">
        
        <h1 style="margin:0 0 .5rem 0;">Bienvenido a Farmacia</h1>
        <p style="margin:0;">
            
            Empecemos a construir las vistas con esta plantilla base.
        </p>
    </section>
    
@endsection