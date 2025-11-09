@extends('layouts.app')

@section('title', 'Gestionar Usuarios')

@section('content')
@include('layouts.breadcrumbs', ['breadcrumbs' => [
    ['label' => 'Inicio', 'url' => url('/')],
    ['label' => 'Dashboard', 'url' => route('dashboard')],
    ['label' => 'Gestionar Usuarios'],
]])

<h1 style="margin-bottom:1rem;">Gestionar Usuarios</h1>

<div class="card" style="margin-bottom:1rem;">
  <h3>Crear usuario</h3>
  <form id="createUserForm">
    @csrf
    <div>
      <label for="newUserName" class="visually-hidden">Nombre</label>
      <input id="newUserName" type="text" name="name" placeholder="Nombre" required aria-required="true">
    </div>
    <div>
      <label for="newUserEmail" class="visually-hidden">Email</label>
      <input id="newUserEmail" type="email" name="email" placeholder="Email" required aria-required="true">
    </div>
    <div>
      <label for="newUserPassword" class="visually-hidden">Contraseña</label>
      <input id="newUserPassword" type="password" name="password" placeholder="Contraseña (min 8)" minlength="8" required aria-required="true">
    </div>
    <div>
      <label for="newUserRole" class="visually-hidden">Rol</label>
      <select id="newUserRole" name="role_id" required aria-required="true">
        @foreach($roles as $r)
          @if($r->name === 'super_admin' && auth()->user()->role_id !== \App\Models\Role::SUPER_ADMIN)
            {{-- Admin no puede crear Super Admin --}}
          @else
            <option value="{{ $r->id }}">{{ ucfirst(str_replace('_',' ', $r->name)) }}</option>
          @endif
        @endforeach
      </select>
    </div>
    <div>
      <label for="newUserStatus" class="visually-hidden">Estado</label>
      <select id="newUserStatus" name="status" required aria-required="true">
        <option value="1">Activo</option>
        <option value="0">Inactivo</option>
      </select>
    </div>
    <button type="submit" class="confirm">Crear</button>
  </form>
</div>

<div class="card">
  <h3>Usuarios</h3>
  <div id="usersTable" aria-live="polite">Cargando...</div>
</div>

{{-- Pasar roles dinámicamente al JS (sin Super Admin si no eres Super Admin) --}}
<script>
  window.APP_ROLES = @json(
    $roles
      ->filter(fn($r) => $r->name !== 'super_admin' || auth()->user()->role_id === \App\Models\Role::SUPER_ADMIN)
      ->mapWithKeys(fn($r) => [$r->id => $r->name])
  );
  window.IS_SUPER_ADMIN = @json(auth()->user()->role_id === \App\Models\Role::SUPER_ADMIN);
</script>

@vite('resources/js/users.js')
@endsection
