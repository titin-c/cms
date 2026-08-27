import './components/save-status.js';
import { parseApiJson, saveErrorMessage } from './components/session-check.js';
import { startSessionKeepalive } from './components/session-keepalive.js';

startSessionKeepalive();

const form = document.getElementById('page-form');
const main = document.querySelector('.admin-form');
let pageId = main.dataset.pageId || null;
const statusEl = document.getElementById('save-status');

const quillEs = new Quill('#page-content-es-editor', { theme: 'bubble', placeholder: 'Escribe aquí...' });
const quillEn = new Quill('#page-content-en-editor', { theme: 'bubble', placeholder: 'Write here...' });

function updateSlugPreviews() {
  document.getElementById('slug-es-preview').textContent = form.slug.value || '...';
  document.getElementById('slug-en-preview').textContent = form.slug_en.value || '...';
}
form.slug.addEventListener('input', updateSlugPreviews);
form.slug_en.addEventListener('input', updateSlugPreviews);

// Autogenera el slug ES a partir del título si el campo está vacío (solo al crear)
form.title_es.addEventListener('blur', () => {
  if (!pageId && !form.slug.value.trim()) {
    form.slug.value = slugify(form.title_es.value);
    updateSlugPreviews();
  }
});
function slugify(text) {
  return text.toLowerCase()
    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '');
}

async function savePage() {
  window.setSaveStatus(statusEl, 'sending');

  document.getElementById('page-content-es').value = quillEs.root.innerHTML;
  document.getElementById('page-content-en').value = quillEn.getText().trim() ? quillEn.root.innerHTML : '';

  const data = Object.fromEntries(new FormData(form).entries());
  data.id = pageId;
  data.show_in_header = document.getElementById('page-show-header').checked ? 1 : 0;
  data.show_in_footer = document.getElementById('page-show-footer').checked ? 1 : 0;
  data.noindex = document.getElementById('page-noindex').checked ? 1 : 0;

  try {
    const res = await fetch('/api/content-pages.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data),
    });
    const result = await parseApiJson(res);
    if (!res.ok) {
      window.setSaveStatus(statusEl, 'error', result.message || 'No se pudo guardar.');
      return;
    }
    pageId = result.id;
    main.dataset.pageId = pageId;
    window.setSaveStatus(statusEl, 'sent');
    // fix: ya no redirige sola tras guardar — antes te dejaba a mitad de edición
  } catch (err) {
    window.setSaveStatus(statusEl, 'error', saveErrorMessage(err));
  }
}

form.addEventListener('submit', (e) => {
  e.preventDefault();
  savePage();
});

document.getElementById('delete-page-btn')?.addEventListener('click', async () => {
  if (!pageId) return;
  if (!confirm('¿Eliminar esta página? Esta acción no se puede deshacer.')) return;
  const res = await fetch(`/api/content-pages.php?id=${pageId}`, { method: 'DELETE' });
  if (res.ok) {
    window.location.href = 'paginas.php';
  } else {
    alert('No se pudo eliminar la página.');
  }
});
