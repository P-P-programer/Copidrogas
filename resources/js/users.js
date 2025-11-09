import { toast } from './ux/messages';

document.addEventListener('DOMContentLoaded', () => {
  const tableWrap = document.getElementById('usersTable');
  const createForm = document.getElementById('createUserForm');
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const rolesMap = window.APP_ROLES || {};
  const isSuperAdmin = window.IS_SUPER_ADMIN || false;

  async function fetchUsers() {
    tableWrap.textContent = 'Cargando...';
    try {
      const res = await fetch('/users/data');
      const users = await res.json();
      renderTable(users);
    } catch {
      tableWrap.textContent = 'Error cargando usuarios.';
      toast({ type: 'error', message: 'Error cargando usuarios.' });
    }
  }

  function renderTable(users) {
    tableWrap.innerHTML = `
      <table class="activity-table">
        <thead>
          <tr>
            <th>ID</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Estado</th><th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          ${users.map(u => rowHTML(u)).join('')}
        </tbody>
      </table>
    `;

    tableWrap.querySelectorAll('.btn-save-user').forEach(btn => {
      btn.addEventListener('click', () => handleSave(btn));
    });

    tableWrap.querySelectorAll('.select-status').forEach(sel => {
      sel.addEventListener('change', (e) => {
        const badge = sel.closest('tr').querySelector('.status-badge');
        badge.className = 'status-badge ' + (sel.value === '1' ? 'status-active' : 'status-inactive');
        badge.textContent = sel.value === '1' ? 'ACTIVO' : 'INACTIVO';
      });
    });
  }

  function rowHTML(u) {
    const disabled = u.editable ? '' : 'disabled';
    const roleSelect = roleSelectHTML(u.role_id, u.editable, u.id);
    const isActive = u.status === 1 || u.status === '1';
    return `
      <tr data-id="${u.id}">
        <td data-label="ID">${u.id}</td>
        <td data-label="Nombre">
          <label for="userName_${u.id}" class="visually-hidden">Nombre de usuario ${u.id}</label>
          <input id="userName_${u.id}" data-k="name" value="${escapeHTML(u.name)}" ${disabled} aria-label="Nombre de ${escapeHTML(u.name)}">
        </td>
        <td data-label="Email">
          <label for="userEmail_${u.id}" class="visually-hidden">Email de usuario ${u.id}</label>
          <input id="userEmail_${u.id}" data-k="email" value="${escapeHTML(u.email)}" ${disabled} aria-label="Email de ${escapeHTML(u.name)}">
        </td>
        <td data-label="Rol">${roleSelect}</td>
        <td data-label="Estado">
          <span class="status-badge ${isActive ? 'status-active':'status-inactive'}">
            ${isActive ? 'ACTIVO':'INACTIVO'}
          </span>
          <label for="userStatus_${u.id}" class="visually-hidden">Estado de usuario ${u.id}</label>
          <select id="userStatus_${u.id}" class="select-status" data-k="status" ${disabled} aria-label="Estado de ${escapeHTML(u.name)}">
            <option value="1" ${isActive?'selected':''}>Activo</option>
            <option value="0" ${!isActive?'selected':''}>Inactivo</option>
          </select>
        </td>
        <td data-label="Acciones">
          <button class="confirm btn-save-user" ${disabled} aria-label="Guardar cambios de ${escapeHTML(u.name)}">Guardar</button>
        </td>
      </tr>
    `;
  }

  function roleSelectHTML(current, editable, userId) {
    // Mostrar todos los roles, pero deshabilitar opciones según permisos
    const allRoles = {
      1: 'admin',
      2: 'usuario', 
      3: 'proveedor',
      4: 'super_admin',
      ...rolesMap
    };
    
    const options = Object.entries(allRoles)
      .map(([id, name]) => {
        const numId = parseInt(id);
        const sel = numId === current ? 'selected' : '';
        // Deshabilitar Super Admin si no eres Super Admin (pero mostrarlo)
        const disableOption = (!isSuperAdmin && name === 'super_admin') ? 'disabled' : '';
        return `<option value="${id}" ${sel} ${disableOption}>${formatRole(name)}</option>`;
      }).join('');
    return `
      <label for="userRole_${userId}" class="visually-hidden">Rol de usuario ${userId}</label>
      <select id="userRole_${userId}" data-k="role_id" ${editable ? '' : 'disabled'} aria-label="Rol de usuario ${userId}">${options}</select>
    `;
  }

  function formatRole(r) {
    return r === 'super_admin' ? 'Super Admin' :
           r === 'admin' ? 'Administrador' :
           r === 'proveedor' ? 'Proveedor' :
           r === 'usuario' ? 'Usuario' : 
           r.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
  }

  async function handleSave(btn) {
    const tr = btn.closest('tr');
    const id = tr.dataset.id;
    const payload = {};
    tr.querySelectorAll('input[data-k], select[data-k]').forEach(el => {
      if (el.dataset.k === 'status') {
        payload[el.dataset.k] = parseInt(el.value);
      } else if (el.dataset.k === 'role_id') {
        payload[el.dataset.k] = parseInt(el.value);
      } else {
        payload[el.dataset.k] = el.value;
      }
    });

    btn.disabled = true;
    const original = btn.textContent;
    btn.textContent = 'Guardando...';
    
    try {
      const res = await fetch(`/users/${id}`, {
        method: 'PATCH',
        headers: {
          'Content-Type':'application/json',
          'X-CSRF-TOKEN': csrf
        },
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      
      if (!res.ok || !data.ok) {
        throw new Error(data.message || 'Error al actualizar usuario.');
      }
      
      toast({ type: 'success', message: data.message });
      await new Promise(resolve => setTimeout(resolve, 150));
      fetchUsers();
      
    } catch (e) {
      toast({ type: 'error', message: e.message });
    } finally {
      btn.disabled = false;
      btn.textContent = original;
    }
  }

  createForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(createForm);
    const payload = Object.fromEntries(fd.entries());
    payload.status = parseInt(payload.status);
    payload.role_id = parseInt(payload.role_id);
    
    const btn = createForm.querySelector('button[type="submit"]');
    const original = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Creando...';
    
    try {
      const res = await fetch('/users', {
        method:'POST',
        headers:{
          'Content-Type':'application/json',
          'X-CSRF-TOKEN': csrf
        },
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      
      if (!res.ok || !data.ok) {
        throw new Error(data.message || 'Error al crear usuario.');
      }
      
      toast({ type: 'success', message: data.message });
      createForm.reset();
      fetchUsers();
      
    } catch (e) {
      toast({ type: 'error', message: e.message });
    } finally {
      btn.disabled = false;
      btn.textContent = original;
    }
  });

  function escapeHTML(str='') {
    return str.replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[c]));
  }

  fetchUsers();
});