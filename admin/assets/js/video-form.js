import './components/image-cropper.js';
import './components/save-status.js';
import { parseApiJson, saveErrorMessage } from './components/session-check.js';

const form = document.getElementById('video-form');
const main = document.querySelector('.admin-form');
let videoId = main.dataset.videoId || null;
const statusEl = document.getElementById('save-status');

// fix: aviso si el proveedor es "Otro" (no se puede garantizar el embebido)
const providerSelect = document.getElementById('video_provider');
const displayModeSelect = document.getElementById('display_mode');
const otherHint = document.getElementById('provider-other-hint');
function updateProviderHint() {
  otherHint.hidden = providerSelect.value !== 'other';
}
providerSelect.addEventListener('change', updateProviderHint);
updateProviderHint();

function serializeForm(status) {
  const data = Object.fromEntries(new FormData(form).entries());
  data.id = videoId;
  data.featured = form.featured.checked;
  data.status = status;
  return data;
}

const FIELD_TO_ELEMENT_ID = { thumbnail: 'image-cropper', title_es: 'title_es', video_url: 'video_url' };
function highlightMissingField(fieldName) {
  const targetId = FIELD_TO_ELEMENT_ID[fieldName];
  const el = targetId && document.getElementById(targetId);
  if (!el) return;
  const block = el.closest('.admin-form__block') || el;
  block.scrollIntoView({ behavior: 'smooth', block: 'center' });
  block.classList.add('admin-form__block--error');
  setTimeout(() => block.classList.remove('admin-form__block--error'), 2400);
}

async function saveVideo(status) {
  window.setSaveStatus(statusEl, status === 'draft' ? 'saving' : 'sending');
  try {
    const res = await fetch('/api/videos.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(serializeForm(status)),
    });
    const data = await parseApiJson(res);
    if (!res.ok) {
      window.setSaveStatus(statusEl, 'error', data.message || 'No se pudo guardar.');
      if (data.field) highlightMissingField(data.field);
      return;
    }
    videoId = data.id;
    window.setSaveStatus(statusEl, status === 'draft' ? 'saved' : 'sent');
    // fix: ya no redirige sola tras publicar — antes te dejaba a mitad de edición
  } catch (err) {
    window.setSaveStatus(statusEl, 'error', saveErrorMessage(err));
  }
}

let dirty = false;
form.addEventListener('input', () => { dirty = true; });
setInterval(() => { if (dirty && videoId) { saveVideo('draft'); dirty = false; } }, 20000);

document.getElementById('save-draft-btn').addEventListener('click', () => saveVideo('draft'));
form.addEventListener('submit', (e) => {
  e.preventDefault();
  saveVideo('published');
});
