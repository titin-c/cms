import { makeSortable } from './components/sortable-list.js';

const tbody = document.getElementById('dashboard-table-body');
const searchInput = document.getElementById('dashboard-search');
let debounceTimer;

async function loadProjects(query = '') {
  tbody.closest('table').setAttribute('aria-busy', 'true');
  try {
    const res = await fetch(`/api/projects.php?q=${encodeURIComponent(query)}`);
    const projects = await res.json();
    renderRows(projects, query);
  } catch {
    tbody.innerHTML = `<tr><td colspan="7">No se pudo cargar el listado. <button id="retry-load">Reintentar</button></td></tr>`;
    document.getElementById('retry-load')?.addEventListener('click', () => loadProjects(query));
  } finally {
    tbody.closest('table').setAttribute('aria-busy', 'false');
  }
}

function renderRows(projects, query) {
  if (!projects.length) {
    tbody.innerHTML = `<tr><td colspan="7">Sin resultados.</td></tr>`;
    return;
  }
  // fix: arrastrar solo tiene sentido con la lista completa sin filtrar —
  // con una búsqueda activa, el orden visible no coincide con el real
  const canReorder = !query;
  tbody.innerHTML = projects.map((p) => `
    <tr data-id="${p.id}">
      <td>${canReorder ? `<span class="drag-handle" draggable="true" aria-label="Arrastrar para reordenar">⠿</span>` : ''}</td>
      <td><img src="/uploads/${p.main_image}" alt="" width="48" height="48" style="object-fit:cover;border-radius:2px"></td>
      <td>${escapeHtml(p.title_es)}</td>
      <td>${escapeHtml(p.category_title)}</td>
      <td>${p.status === 'published' ? 'Publicado' : 'Borrador'}</td>
      <td>${p.featured ? 'Sí' : '—'}</td>
      <td>
        <a href="proyecto-form.php?id=${p.id}">Editar</a>
        ${p.status === 'published' ? ` · <a href="/proyecto/${encodeURIComponent(p.slug)}" target="_blank" rel="noopener">Ver ↗</a>` : ''}
      </td>
    </tr>
  `).join('');

  if (canReorder) makeSortable(tbody, { type: 'projects' });
}

function escapeHtml(str) {
  const div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}

if (tbody) {
  loadProjects();
  searchInput?.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => loadProjects(searchInput.value), 300);
  });
}
