import { makeSortable } from './components/sortable-list.js';

const tbody = document.getElementById('videos-table-body');
const searchInput = document.getElementById('dashboard-search');
let debounceTimer;

const PROVIDER_LABELS = { youtube: 'YouTube', vimeo: 'Vimeo', other: 'Otro' };
const MODE_LABELS = { lightbox: 'Lightbox', external: 'Enlace externo' };

async function loadVideos(query = '') {
  tbody.closest('table').setAttribute('aria-busy', 'true');
  try {
    const res = await fetch(`/api/videos.php?q=${encodeURIComponent(query)}`);
    const videos = await res.json();
    renderRows(videos, query);
  } catch {
    tbody.innerHTML = `<tr><td colspan="8">No se pudo cargar el listado. <button id="retry-load">Reintentar</button></td></tr>`;
    document.getElementById('retry-load')?.addEventListener('click', () => loadVideos(query));
  } finally {
    tbody.closest('table').setAttribute('aria-busy', 'false');
  }
}

function renderRows(videos, query) {
  if (!videos.length) {
    tbody.innerHTML = `<tr><td colspan="8">Sin resultados.</td></tr>`;
    return;
  }
  const canReorder = !query;
  tbody.innerHTML = videos.map((v) => `
    <tr data-id="${v.id}">
      <td>${canReorder ? `<span class="drag-handle" draggable="true" aria-label="Arrastrar para reordenar">⠿</span>` : ''}</td>
      <td><img src="/uploads/${v.thumbnail}" alt="" width="48" height="48" style="object-fit:cover;border-radius:2px"></td>
      <td>${escapeHtml(v.title_es)}</td>
      <td>${PROVIDER_LABELS[v.video_provider] || v.video_provider}</td>
      <td>${MODE_LABELS[v.display_mode] || v.display_mode}</td>
      <td>${v.status === 'published' ? 'Publicado' : 'Borrador'}</td>
      <td>${Number(v.featured) ? 'Sí' : '—'}</td>
      <td><a href="video-form.php?id=${v.id}">Editar</a></td>
    </tr>
  `).join('');

  if (canReorder) makeSortable(tbody, { type: 'videos' });
}

function escapeHtml(str) {
  const div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}

loadVideos();
searchInput?.addEventListener('input', () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => loadVideos(searchInput.value), 300);
});

// --- Ajustes de la página /videos (todo opcional) ---
const VIDEOS_SETTINGS_KEYS = [
  'videos_show_in_header', 'videos_show_in_footer',
  'videos_slug_es', 'videos_slug_en',
  'videos_h1_es', 'videos_h1_en',
  'videos_description_es', 'videos_description_en',
  'videos_meta_description_es', 'videos_meta_description_en',
];

async function loadVideoPageSettings() {
  const res = await fetch('/api/settings.php');
  const settings = await res.json();

  document.getElementById('videos-show-header').checked = settings.videos_show_in_header === '1';
  document.getElementById('videos-show-footer').checked = settings.videos_show_in_footer === '1';
  document.getElementById('videos-slug-es').value = settings.videos_slug_es || '';
  document.getElementById('videos-slug-en').value = settings.videos_slug_en || '';
  document.getElementById('videos-h1-es').value = settings.videos_h1_es || '';
  document.getElementById('videos-h1-en').value = settings.videos_h1_en || '';
  document.getElementById('videos-desc-es').value = settings.videos_description_es || '';
  document.getElementById('videos-desc-en').value = settings.videos_description_en || '';
  document.getElementById('videos-meta-title-es').value = settings.videos_meta_title_es || '';
  document.getElementById('videos-meta-title-en').value = settings.videos_meta_title_en || '';
  document.getElementById('videos-meta-es').value = settings.videos_meta_description_es || '';
  document.getElementById('videos-meta-en').value = settings.videos_meta_description_en || '';
  updateSlugPreviews();
}

function updateSlugPreviews() {
  document.getElementById('videos-slug-es-preview').textContent = document.getElementById('videos-slug-es').value || 'videos';
  document.getElementById('videos-slug-en-preview').textContent = document.getElementById('videos-slug-en').value || 'videos';
}
document.getElementById('videos-slug-es').addEventListener('input', updateSlugPreviews);
document.getElementById('videos-slug-en').addEventListener('input', updateSlugPreviews);

document.getElementById('save-videos-settings-btn').addEventListener('click', async () => {
  const statusEl = document.getElementById('videos-settings-status');
  statusEl.textContent = 'Guardando...';
  statusEl.dataset.state = 'saving';

  const payload = {
    videos_show_in_header: document.getElementById('videos-show-header').checked ? '1' : '0',
    videos_show_in_footer: document.getElementById('videos-show-footer').checked ? '1' : '0',
    videos_slug_es: document.getElementById('videos-slug-es').value.trim(),
    videos_slug_en: document.getElementById('videos-slug-en').value.trim(),
    videos_h1_es: document.getElementById('videos-h1-es').value,
    videos_h1_en: document.getElementById('videos-h1-en').value,
    videos_description_es: document.getElementById('videos-desc-es').value,
    videos_description_en: document.getElementById('videos-desc-en').value,
    videos_meta_title_es: document.getElementById('videos-meta-title-es').value,
    videos_meta_title_en: document.getElementById('videos-meta-title-en').value,
    videos_meta_description_es: document.getElementById('videos-meta-es').value,
    videos_meta_description_en: document.getElementById('videos-meta-en').value,
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

loadVideoPageSettings();
