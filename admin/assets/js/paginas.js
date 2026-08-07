import { makeSortable } from './components/sortable-list.js';

const tbody = document.getElementById('pages-table-body');

async function loadPages() {
  const res = await fetch('/api/content-pages.php');
  const pages = await res.json();
  renderRows(pages);
  makeSortable(tbody, { type: 'content_pages' });
}

function renderRows(pages) {
  if (!pages.length) {
    tbody.innerHTML = `<tr><td colspan="6">Sin páginas todavía.</td></tr>`;
    return;
  }
  tbody.innerHTML = pages.map((p) => `
    <tr data-id="${p.id}">
      <td><span class="drag-handle" draggable="true" aria-label="Arrastrar para reordenar">⠿</span></td>
      <td>${escapeHtml(p.title_es)}</td>
      <td><a href="/${encodeURIComponent(p.slug)}" target="_blank" rel="noopener">/${escapeHtml(p.slug)} ↗</a></td>
      <td>${Number(p.show_in_header) ? '✅' : '—'}</td>
      <td>${Number(p.show_in_footer) ? '✅' : '—'}</td>
      <td><a href="pagina-form.php?id=${p.id}" class="admin-btn admin-btn--link">Editar</a></td>
    </tr>
  `).join('');
}

function escapeHtml(str) {
  const div = document.createElement('div');
  div.textContent = str ?? '';
  return div.innerHTML;
}

loadPages();
