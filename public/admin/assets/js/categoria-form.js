import './components/save-status.js';
import { linkToggleVisibility } from './components/toggle-visibility.js';
import { parseApiJson, saveErrorMessage } from './components/session-check.js';
import { startSessionKeepalive } from './components/session-keepalive.js';

startSessionKeepalive();

const form = document.getElementById('category-form');
const main = document.querySelector('.admin-form');
let categoryId = main.dataset.categoryId || null;
const statusEl = document.getElementById('save-status');

// fix: los dos toggles "fila en la home" (arriba y junto a sus campos) se
// mantienen sincronizados entre sí — cambiar uno actualiza el otro
const showHomeA = document.getElementById('cat-show-home');
const showHomeB = document.getElementById('cat-show-home-2');
showHomeA.addEventListener('change', () => { showHomeB.checked = showHomeA.checked; showHomeB.dispatchEvent(new Event('change')); });
showHomeB.addEventListener('change', () => { showHomeA.checked = showHomeB.checked; });

// fix: los campos de "Fila de la home" solo se muestran si esa fila está activada
linkToggleVisibility('#cat-show-home-2', ['#cat-home-row-fields']);

const RICHTEXT_FIELDS = [
  { editorId: 'cat-desc-es-editor', hiddenId: 'cat-desc-es', field: 'description_es' },
  { editorId: 'cat-desc-en-editor', hiddenId: 'cat-desc-en', field: 'description_en' },
  { editorId: 'cat-home-desc-es-editor', hiddenId: 'cat-home-desc-es', field: 'home_description_es' },
  { editorId: 'cat-home-desc-en-editor', hiddenId: 'cat-home-desc-en', field: 'home_description_en' },
];
const editors = RICHTEXT_FIELDS.map(({ editorId }) => new Quill(`#${editorId}`, { theme: 'bubble', placeholder: 'Escribe aquí...' }));

async function saveCategory(status) {
  window.setSaveStatus(statusEl, status === 'draft' ? 'saving' : 'sending');

  RICHTEXT_FIELDS.forEach(({ hiddenId, field }, i) => {
    const hiddenInput = document.getElementById(hiddenId);
    hiddenInput.name = field;
    hiddenInput.value = editors[i].getText().trim() ? editors[i].root.innerHTML : '';
  });

  const data = Object.fromEntries(new FormData(form).entries());
  data.id = categoryId;
  data.status = status;
  data.show_title = document.getElementById('cat-show-title').checked ? 1 : 0;
  data.header_placement = document.getElementById('cat-header-placement').value;
  data.show_in_footer = document.getElementById('cat-show-footer').checked ? 1 : 0;
  data.show_in_home = document.getElementById('cat-show-home').checked ? 1 : 0;

  try {
    const res = await fetch('/api/categories.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data),
    });
    const result = await parseApiJson(res);
    if (!res.ok) {
      window.setSaveStatus(statusEl, 'error', result.message || 'No se pudo guardar.');
      return;
    }
    categoryId = result.id;
    main.dataset.categoryId = categoryId;
    window.setSaveStatus(statusEl, status === 'draft' ? 'saved' : 'sent');
    // fix (Andrea): si el servidor ha tenido que acortar algún campo por
    // superar el máximo permitido, se avisa aparte — el guardado ha ido bien
    if (result.warning) window.showWarningToast(result.warning);
    // fix: ya no redirige sola tras guardar — antes te dejaba a mitad de edición
  } catch (err) {
    window.setSaveStatus(statusEl, 'error', saveErrorMessage(err));
  }
}

let dirty = false;
form.addEventListener('input', () => { dirty = true; });
const draftToggle = document.getElementById('cat-draft');
setInterval(() => {
  if (dirty && categoryId) { saveCategory(draftToggle.checked ? 'draft' : 'published'); dirty = false; }
}, 20000);

form.addEventListener('submit', (e) => {
  e.preventDefault();
  saveCategory(draftToggle.checked ? 'draft' : 'published');
});

document.getElementById('delete-category-btn')?.addEventListener('click', async () => {
  if (!categoryId) return;
  if (!confirm('¿Eliminar esta categoría? Los proyectos que la usen pasarán a "Sin categorizar".')) return;
  const res = await fetch(`/api/categories.php?id=${categoryId}`, { method: 'DELETE' });
  if (res.ok) {
    window.location.href = 'categorias.php';
  } else {
    alert('No se pudo eliminar la categoría.');
  }
});
