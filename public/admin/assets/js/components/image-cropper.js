/**
 * ImageCropper — recorte real con Cropper.js (CDN, ~30kb, gratuito).
 * fix (usability-agent 🔴 crítico):
 * - Preview en contexto real: tras aplicar el recorte se sube la imagen YA
 *   recortada y se muestra el resultado final, no un recorte "a ciegas".
 * - Variante mobile: zoom con botones +/- (llamando a cropper.zoom() de
 *   verdad, no una simulación), controles de "Aplicar"/"Rehacer" con zona
 *   táctil ≥44px — evita depender del gesto de pellizco, impreciso con el dedo.
 */

document.querySelectorAll('[data-cropper]').forEach((container) => {
  const isMobile = matchMedia('(max-width: 768px)').matches;
  let cropperInstance = null;

  container.innerHTML = `
    <div class="image-cropper">
      <input type="file" accept="image/jpeg,image/png,image/webp" class="image-cropper__input" aria-label="Subir imagen principal">

      <div class="image-cropper__stage" hidden>
        <div class="image-cropper__canvas-wrap">
          <img class="image-cropper__source" alt="">
        </div>
        <div class="image-cropper__controls">
          ${isMobile ? `
            <button type="button" class="image-cropper__zoom-btn" data-zoom-out aria-label="Alejar">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M5 12h14"/></svg>
            </button>
            <button type="button" class="image-cropper__zoom-btn" data-zoom-in aria-label="Acercar">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            </button>
          ` : ''}
          <button type="button" class="admin-btn admin-btn--secondary" data-reset-crop>Restablecer</button>
          <button type="button" class="admin-btn admin-btn--primary" data-apply-crop>Aplicar recorte</button>
        </div>
      </div>

      <div class="image-cropper__result" hidden>
        <div class="image-cropper__preview"></div>
        <button type="button" class="admin-btn admin-btn--link" data-change-image>Cambiar imagen</button>
      </div>

      <span class="field-error" data-cropper-error></span>
    </div>
  `;

  const input = container.querySelector('.image-cropper__input');
  const stage = container.querySelector('.image-cropper__stage');
  const sourceImg = container.querySelector('.image-cropper__source');
  const resultBlock = container.querySelector('.image-cropper__result');
  const preview = container.querySelector('.image-cropper__preview');
  const errorEl = container.querySelector('[data-cropper-error]');
  const hiddenInput = document.getElementById('main_image');

  // fix: modo edición — si el proyecto ya tiene imagen principal, mostrarla en vez de un formulario vacío
  if (hiddenInput.value) {
    const desktopVariant = hiddenInput.value.replace('-thumb.webp', '-desktop.webp');
    preview.style.backgroundImage = `url('/uploads/${desktopVariant}')`;
    resultBlock.hidden = false;
  }

  input.addEventListener('change', () => {
    const file = input.files[0];
    if (!file) return;

    errorEl.textContent = '';
    const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!validTypes.includes(file.type)) {
      errorEl.textContent = 'Formato no soportado, usa JPG, PNG o WebP.';
      return;
    }

    const reader = new FileReader();
    reader.onload = () => {
      sourceImg.src = reader.result;
      stage.hidden = false;
      resultBlock.hidden = true;

      if (cropperInstance) cropperInstance.destroy();
      // fix (Andrea): si el contenedor pide una proporción concreta (ej: vídeos en 16:9),
      // el propio recorte ya la sugiere — si no, queda libre (fotos de proyecto)
      const aspectAttr = container.dataset.aspectRatio; // ej: "16/9"
      const aspectRatio = aspectAttr
        ? aspectAttr.split('/').reduce((a, b) => a / b)
        : NaN; // NaN = libre, Cropper.js lo interpreta como "sin restricción"

      cropperInstance = new Cropper(sourceImg, {
        viewMode: 1,
        dragMode: 'move',
        autoCropArea: 0.9,
        responsive: true,
        background: false,
        aspectRatio,
      });
    };
    reader.readAsDataURL(file);
  });

  container.querySelector('[data-reset-crop]').addEventListener('click', () => {
    cropperInstance?.reset();
  });

  container.querySelector('[data-zoom-in]')?.addEventListener('click', () => cropperInstance?.zoom(0.1));
  container.querySelector('[data-zoom-out]')?.addEventListener('click', () => cropperInstance?.zoom(-0.1));

  container.querySelector('[data-apply-crop]').addEventListener('click', () => {
    if (!cropperInstance) return;
    errorEl.textContent = '';

    const canvas = cropperInstance.getCroppedCanvas({ maxWidth: 2400, maxHeight: 2400, imageSmoothingQuality: 'high' });
    canvas.toBlob(async (blob) => {
      const formData = new FormData();
      formData.append('image', blob, 'crop.webp');
      formData.append('seo_hint', buildSeoHint()); // fix (Andrea, SEO): nombre de archivo descriptivo en vez de hash aleatorio

      try {
        const res = await fetch('/api/upload.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (!res.ok) {
          errorEl.textContent = data.message || 'No se pudo procesar el recorte.';
          return;
        }
        hiddenInput.value = data.thumb;
        preview.style.backgroundImage = `url('/uploads/${data.desktop}')`;
        stage.hidden = true;
        resultBlock.hidden = false;
        cropperInstance.destroy();
        cropperInstance = null;
      } catch {
        errorEl.textContent = 'Error de conexión al subir la imagen recortada.';
      }
    }, 'image/webp', 0.9);
  });

  container.querySelector('[data-change-image]').addEventListener('click', () => {
    resultBlock.hidden = true;
    input.value = '';
    input.click();
  });
});

/**
 * fix (Andrea, SEO Google Images): construye un texto descriptivo a partir
 * del título del proyecto + la categoría seleccionada, para usarlo como base
 * del nombre de archivo (ver api/upload.php). Ej: "Editorial" + "Oliva Playa"
 * → "editorial-oliva-playa" → "editorial-oliva-playa-a3f9.webp".
 */
function buildSeoHint() {
  const title = document.getElementById('title_es')?.value || '';
  const categorySelect = document.getElementById('category_id');
  const categoryLabel = categorySelect?.selectedOptions?.[0]?.textContent || '';
  return [categoryLabel, title].filter(Boolean).join(' ');
}
