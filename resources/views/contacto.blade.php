@extends('layouts.app')

@section('title', 'Contacto')

@section('content')
@include('layouts.breadcrumbs', [
  'breadcrumbs' => [
    ['label' => 'Inicio', 'url' => url('/')],
    ['label' => 'Contacto'],
  ]
])

<section class="card" style="margin-bottom:1.5rem;">
  <h1 style="margin:0 0 1rem;">Contáctanos</h1>
  <p style="margin:0 0 1rem; color:#6b7280;">
    Estamos para ayudarte. Escríbenos o llámanos y te responderemos lo antes posible.
  </p>

  <div style="display:grid; gap:1rem; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">
    <div class="contact-block">
      <h2 style="margin:0 0 .5rem; font-size:1.1rem;">Información</h2>
      <ul style="list-style:none; padding:0; margin:0; color:#374151;">
        <li><strong>Dirección:</strong> Cra 7 # 123-45, Bogotá, Colombia</li>
        <li><strong>Teléfono:</strong> (+57) 1 234 5678</li>
        <li><strong>WhatsApp:</strong> (+57) 300 123 4567</li>
        <li><strong>Email:</strong> contacto@farmacia.com</li>
        <li><strong>Horario:</strong> L-V 8:00–18:00, Sáb 9:00–13:00</li>
      </ul>
    </div>

    <div class="contact-block">
      <h2 style="margin:0 0 .5rem; font-size:1.1rem;">Escríbenos</h2>
      <form onsubmit="return false;" novalidate style="display:grid; gap:.75rem;">
        <label for="c_name" style="font-weight:600;">Nombre</label>
        <input id="c_name" type="text" placeholder="Tu nombre" required>

        <label for="c_email" style="font-weight:600;">Correo electrónico</label>
        <input id="c_email" type="email" placeholder="tucorreo@ejemplo.com" required>

        <label for="c_msg" style="font-weight:600;">Mensaje</label>
        <textarea id="c_msg" rows="4" placeholder="Cuéntanos en qué podemos ayudarte" required></textarea>

        <div>
          <a href="mailto:contacto@farmacia.com" class="confirm" style="display:inline-block; padding:.6rem 1rem; border-radius:8px; background:var(--navy-700); color:#fff; text-decoration:none;">
            Enviar correo
          </a>
        </div>
      </form>
      <p style="margin:.5rem 0 0; font-size:.9rem; color:#6b7280;">
        También puedes escribirnos directamente a <a href="mailto:contacto@farmacia.com">contacto@farmacia.com</a>.
      </p>
    </div>

    <div class="contact-block">
      <h2 style="margin:0 0 .5rem; font-size:1.1rem;">Ubicación</h2>
      <a href="https://maps.google.com/?q=Cra+7+%23123-45+Bogota" target="_blank" rel="noopener" class="confirm" style="display:inline-block; padding:.6rem 1rem; border-radius:8px; background:var(--navy-700); color:#fff; text-decoration:none;">
        Ver en Google Maps
      </a>
    </div>
  </div>
</section>
@endsection