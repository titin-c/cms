import { makeSortable } from './components/sortable-list.js';

const tbody = document.getElementById('categories-table-body');

async function loadCategories() {
  const res = await fetch('/api/categories.php');
  const categories = await res.json();
  renderRows(categories);
  makeSortable(tbody, { type: 'categories' });
}

function renderRows(categories) {
  if (!categories.length) {
    tbody.innerHTML = `<tr><td colspan="6">Sin categorías todavía.</td></tr>`;
    return;
  }
  tbody.innerHTML = categories.map((c) => `
    <tr data-id="${c.id}">
      <td><span class="drag-handle" draggable="true" aria-label="Arrastrar para reordenar">⠿</span></td>
      <td>${escapeHtml(c.title_es)}</td>
      <td>${escapeHtml(c.home_title_es || c.title_es)}</td>
      <td>${escapeHtml(c.title_en || '—')}</td>
      <td>${c.status === 'draft' ? '📝 Borrador' : '✅ Publicada'}</td>
      <td>
        <a href="categoria-form.php?id=${c.id}" class="admin-btn admin-btn--link">Editar</a>
        <a href="/categoria/${encodeURIComponent(c.slug)}" target="_blank" rel="noopener" class="admin-btn admin-btn--link">Ver ↗</a>
      </td>
    </tr>
  `).join('');
}

function escapeHtml(str) {
  const div = document.createElement('div');
  div.textContent = str ?? '';
  return div.innerHTML;
}

loadCategories();

// --- Ajustes de la página agregada /categorias (todo opcional) ---
async function loadCategoriesPageSettings() {
  const res = await fetch('/api/settings.php');
  const settings = await res.json();

  document.getElementById('categories-show-header').checked = settings.categories_show_in_header === '1';
  document.getElementById('categories-show-footer').checked = settings.categories_show_in_footer === '1';
  document.getElementById('categories-slug-es').value = settings.categories_slug_es || '';
  document.getElementById('categories-slug-en').value = settings.categories_slug_en || '';
  document.getElementById('categories-h1-es').value = settings.categories_h1_es || '';
  document.getElementById('categories-h1-en').value = settings.categories_h1_en || '';
  document.getElementById('categories-desc-es').value = settings.categories_description_es || '';
  document.getElementById('categories-desc-en').value = settings.categories_description_en || '';
  document.getElementById('categories-meta-title-es').value = settings.categories_meta_title_es || '';
  document.getElementById('categories-meta-title-en').value = settings.categories_meta_title_en || '';
  document.getElementById('categories-meta-es').value = settings.categories_meta_description_es || '';
  document.getElementById('categories-meta-en').value = settings.categories_meta_description_en || '';
  updateCategoriesSlugPreviews();
}

function updateCategoriesSlugPreviews() {
  document.getElementById('categories-slug-es-preview').textContent = document.getElementById('categories-slug-es').value || 'categorias';
  document.getElementById('categories-slug-en-preview').textContent = document.getElementById('categories-slug-en').value || 'categories';
}
document.getElementById('categories-slug-es').addEventListener('input', updateCategoriesSlugPreviews);
document.getElementById('categories-slug-en').addEventListener('input', updateCategoriesSlugPreviews);

document.getElementById('save-categories-settings-btn').addEventListener('click', async () => {
  const statusEl = document.getElementById('categories-settings-status');
  statusEl.textContent = 'Guardando...';
  statusEl.dataset.state = 'saving';

  const payload = {
    categories_show_in_header: document.getElementById('categories-show-header').checked ? '1' : '0',
    categories_show_in_footer: document.getElementById('categories-show-footer').checked ? '1' : '0',
    categories_slug_es: document.getElementById('categories-slug-es').value.trim(),
    categories_slug_en: document.getElementById('categories-slug-en').value.trim(),
    categories_h1_es: document.getElementById('categories-h1-es').value,
    categories_h1_en: document.getElementById('categories-h1-en').value,
    categories_description_es: document.getElementById('categories-desc-es').value,
    categories_description_en: document.getElementById('categories-desc-en').value,
    categories_meta_title_es: document.getElementById('categories-meta-title-es').value,
    categories_meta_title_en: document.getElementById('categories-meta-title-en').value,
    categories_meta_description_es: document.getElementById('categories-meta-es').value,
    categories_meta_description_en: document.getElementById('categories-meta-en').value,
  };

  try {
    const res = await fetch('/api/settings.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });
    if (!res.ok) throw new Error('save_failed');
    statusEl.textContent = 'Guardado';
    statusEl.dataset.state = 'success';
  } catch {
    statusEl.textContent = 'No se pudo guardar.';
    statusEl.dataset.state = 'error';
  }
});

loadCategoriesPageSettings();
