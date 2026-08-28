/**
 * fix (ux-agent): autoguardado como borrador — Andrea puede trabajar en
 * sesiones interrumpidas sin perder progreso.
 * fix (usability-agent 🟠): SaveStatusIndicator visible y persistente.
 */
import './components/image-cropper.js';
import './components/text-excerpt-picker.js';
import './components/save-status.js';
import './components/gallery-uploader.js';
import { parseApiJson, saveErrorMessage } from './components/session-check.js';
import { startSessionKeepalive } from './components/session-keepalive.js';

startSessionKeepalive();

const form = document.getElementById('project-form');
const main = document.querySelector('.admin-form');
let projectId = main.dataset.projectId || null;
const statusEl = document.getElementById('save-status');

window.renderGalleryUploader(projectId); // fix: inicializa (o muestra el aviso de "guarda primero") al cargar la pantalla

// Quill — tema Bubble, tooltip flotante al seleccionar texto (decisión técnica, paso 5)
const quillEs = new Quill('#editor_es', { theme: 'bubble', placeholder: 'Cuenta la historia de este proyecto...' });
const quillEn = new Quill('#editor_en', { theme: 'bubble', placeholder: 'Tell the story of this project...' });

function serializeForm(status) {
  const data = Object.fromEntries(new FormData(form).entries());
  data.id = projectId;
  data.content_es = quillEs.root.innerHTML;
  data.content_en = quillEn.getText().trim() ? quillEn.root.innerHTML : null;
  data.featured = form.featured.checked;
  data.status = status;
  // fix (Andrea): Object.fromEntries solo se queda con el último valor de
  // "extra_categories[]" — se recogen todos los checkboxes marcados aparte
  data.extra_categories = Array.from(form.querySelectorAll('input[name="extra_categories[]"]:checked'))
    .map((el) => el.value);
  return data;
}

// fix (Andrea): mapea el nombre de campo que devuelve la API al elemento
// visible en el formulario, para poder hacer scroll + resaltar el bloque exacto
const FIELD_TO_ELEMENT_ID = {
  main_image: 'image-cropper',
  title_es: 'title_es',
  content_es: 'editor_es',
};

function highlightMissingField(fieldName) {
  const targetId = FIELD_TO_ELEMENT_ID[fieldName];
  const el = targetId && document.getElementById(targetId);
  if (!el) return;
  const block = el.closest('.admin-form__block') || el;
  block.scrollIntoView({ behavior: 'smooth', block: 'center' });
  block.classList.add('admin-form__block--error');
  setTimeout(() => block.classList.remove('admin-form__block--error'), 2400);
}

async function saveProject(status) {
  window.setSaveStatus(statusEl, status === 'draft' ? 'saving' : 'sending');
  try {
    const res = await fetch('/api/projects.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(serializeForm(status)),
    });
    const data = await parseApiJson(res);
    if (!res.ok) {
      window.setSaveStatus(statusEl, 'error', data.message || 'No se pudo guardar.');
      if (data.field) highlightMissingField(data.field); // fix: scroll + resaltado del campo exacto que falta
      return;
    }
    projectId = data.id; // fix: evita duplicados de slug en autoguardados posteriores (siguiente guardado ya hace UPDATE)
    window.renderGalleryUploader(projectId); // fix: habilita la subida de galería en cuanto el proyecto ya existe
    window.setSaveStatus(statusEl, status === 'draft' ? 'saved' : 'sent');
    // fix (Andrea): si el servidor ha tenido que acortar algún campo por
    // superar el máximo permitido, se avisa aparte — el guardado ha ido bien
    if (data.warning) window.showWarningToast(data.warning);
    // fix: ya no redirige sola tras publicar — antes te dejaba a mitad de edición
  } catch (err) {
    window.setSaveStatus(statusEl, 'error', saveErrorMessage(err));
  }
}

// Autoguardado silencioso cada 20s si hay cambios, y al perder el foco de un campo
const draftToggle = document.getElementById('draft-toggle');
let dirty = false;
form.addEventListener('input', () => { dirty = true; });
setInterval(() => {
  // fix: el autoguardado respeta el interruptor — antes siempre forzaba
  // "borrador" aunque el proyecto ya estuviera publicado
  if (dirty && projectId) { saveProject(draftToggle.checked ? 'draft' : 'published'); dirty = false; }
}, 20000);

form.addEventListener('submit', (e) => {
  e.preventDefault();
  saveProject(draftToggle.checked ? 'draft' : 'published');
});
