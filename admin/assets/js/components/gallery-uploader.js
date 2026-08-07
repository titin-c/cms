/**
 * GalleryUploader — subida múltiple de imágenes secundarias del proyecto,
 * con campo de alt text (ES/EN) por imagen y opción de eliminar.
 *
 * fix (ux-agent): requiere que el proyecto ya exista (tenga id) — si es un
 * proyecto nuevo sin guardar, se muestra un aviso pidiendo guardar primero
 * como borrador. Se re-renderiza automáticamente en cuanto proyecto-form.js
 * obtiene un id tras el primer guardado (ver window.renderGalleryUploader).
 */

function renderGalleryUploader(projectId) {
  const container = document.querySelector('[data-gallery-uploader]');
  if (!container) return;

  if (!projectId) {
    container.innerHTML = '<p class="admin-form__hint">Guarda el proyecto como borrador primero para poder subir el resto de imágenes.</p>';
    container.dataset.initialized = '';
    return;
  }

  if (container.dataset.initialized === String(projectId)) return; // ya inicializado para este proyecto
  container.dataset.initialized = String(projectId);

  container.innerHTML = `
    <input type="file" accept="image/jpeg,image/png,image/webp" multiple class="gallery-uploader__input" aria-label="Subir imágenes de la galería">
    <p class="admin-form__hint" data-gallery-keywords-hint></p>
    <span class="field-error" data-gallery-error></span>
    <div class="gallery-uploader__list" id="gallery-list" aria-busy="false"></div>
  `;

  const input = container.querySelector('.gallery-uploader__input');
  const list = container.querySelector('#gallery-list');
  const errorEl = container.querySelector('[data-gallery-error]');
  const keywordsHintEl = container.querySelector('[data-gallery-keywords-hint]');
  const categorySelectEl = document.getElementById('category_id');

  // fix (Andrea, SEO): mismo hint de palabras clave que en la imagen principal,
  // leído directamente del select (no depende del orden de carga entre módulos)
  function refreshKeywordsHint() {
    const keywords = categorySelectEl?.selectedOptions?.[0]?.dataset.keywords || '';
    if (keywordsHintEl) {
      keywordsHintEl.textContent = keywords ? `Palabras clave de referencia de esta categoría: ${keywords}` : '';
    }
  }
  refreshKeywordsHint();
  categorySelectEl?.addEventListener('change', refreshKeywordsHint);

  async function loadImages() {
    list.setAttribute('aria-busy', 'true');
    try {
      const res = await fetch(`/api/project-images.php?project_id=${projectId}`);
      const images = await res.json();
      renderList(images);
    } catch {
      list.innerHTML = '<p class="field-error">No se pudo cargar la galería.</p>';
    } finally {
      list.setAttribute('aria-busy', 'false');
    }
  }

  function renderList(images) {
    if (!images.length) {
      list.innerHTML = '<p class="admin-form__hint">Todavía no has añadido imágenes a la galería.</p>';
      return;
    }
    list.innerHTML = images.map((img) => `
      <div class="gallery-item" data-id="${img.id}">
        <img src="/uploads/${img.image_path}" alt="" width="72" height="72">
        <div class="gallery-item__fields">
          <label>Alt (ES)
            <input type="text" data-alt-es value="${escapeAttr(img.alt_es)}">
          </label>
          <label>Alt (EN)
            <input type="text" data-alt-en value="${escapeAttr(img.alt_en)}">
          </label>
        </div>
        <button type="button" class="admin-btn admin-btn--link" data-remove aria-label="Eliminar imagen">Eliminar</button>
      </div>
    `).join('');

    list.querySelectorAll('.gallery-item').forEach((item) => {
      const id = item.dataset.id;
      item.querySelector('[data-alt-es]').addEventListener('change', (e) => updateImage(id, { alt_es: e.target.value }));
      item.querySelector('[data-alt-en]').addEventListener('change', (e) => updateImage(id, { alt_en: e.target.value }));
      item.querySelector('[data-remove]').addEventListener('click', () => removeImage(id));
    });
  }

  function escapeAttr(str) {
    const div = document.createElement('div');
    div.textContent = str ?? '';
    return div.innerHTML;
  }

  async function updateImage(id, fields) {
    await fetch('/api/project-images.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id, ...fields }),
    });
  }

  async function removeImage(id) {
    if (!confirm('¿Eliminar esta imagen de la galería?')) return;
    await fetch(`/api/project-images.php?id=${id}`, { method: 'DELETE' });
    loadImages();
  }

  input.addEventListener('change', async () => {
    errorEl.textContent = '';
    const validTypes = ['image/jpeg', 'image/png', 'image/webp'];

    const files = Array.from(input.files);
    for (let i = 0; i < files.length; i++) {
      const file = files[i];
      if (!validTypes.includes(file.type)) {
        errorEl.textContent = 'Formato no soportado, usa JPG, PNG o WebP.';
        continue;
      }
      const formData = new FormData();
      formData.append('image', file);
      formData.append('seo_hint', buildGallerySeoHint(i + 1)); // fix (Andrea, SEO): nombre de archivo descriptivo, numerado

      try {
        const uploadRes = await fetch('/api/upload.php', { method: 'POST', body: formData });
        const uploadData = await uploadRes.json();
        if (!uploadRes.ok) {
          errorEl.textContent = uploadData.message || 'No se pudo subir una de las imágenes.';
          continue;
        }
        await fetch('/api/project-images.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ project_id: projectId, image_path: uploadData.thumb }),
        });
      } catch {
        errorEl.textContent = 'Error de conexión al subir una de las imágenes.';
      }
    }
    input.value = '';
    loadImages();
  });

  loadImages();
}

// Expuesto globalmente: proyecto-form.js lo llama al cargar y tras cada guardado exitoso
window.renderGalleryUploader = renderGalleryUploader;

/**
 * fix (Andrea, SEO Google Images): mismo criterio que en ImageCropper —
 * título del proyecto + categoría + nº de imagen dentro del lote subido.
 * Ej: "Editorial" + "Oliva Playa" + 2 → "editorial-oliva-playa-2-a3f9.webp".
 */
function buildGallerySeoHint(index) {
  const title = document.getElementById('title_es')?.value || '';
  const categorySelect = document.getElementById('category_id');
  const categoryLabel = categorySelect?.selectedOptions?.[0]?.textContent || '';
  return [categoryLabel, title, index].filter(Boolean).join(' ');
}
