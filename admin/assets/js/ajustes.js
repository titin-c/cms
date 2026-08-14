import './components/image-cropper.js';
import './components/save-status.js';
import { linkToggleVisibility } from './components/toggle-visibility.js';

/**
 * Derivación de tonos claros/oscuros a partir de un color base — mismo
 * principio que la escala manual de grises del design-system-agent
 * (ink-900/700/500/300/100), pero calculada dinámicamente sobre el color
 * que elija Andrea, ajustando la luminosidad (HSL) del color base.
 */
function hexToHsl(hex) {
  const r = parseInt(hex.slice(1, 3), 16) / 255;
  const g = parseInt(hex.slice(3, 5), 16) / 255;
  const b = parseInt(hex.slice(5, 7), 16) / 255;
  const max = Math.max(r, g, b), min = Math.min(r, g, b);
  let h = 0, s = 0;
  const l = (max + min) / 2;
  if (max !== min) {
    const d = max - min;
    s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
    switch (max) {
      case r: h = (g - b) / d + (g < b ? 6 : 0); break;
      case g: h = (b - r) / d + 2; break;
      case b: h = (r - g) / d + 4; break;
    }
    h /= 6;
  }
  return [h * 360, s * 100, l * 100];
}

function hslToHex(h, s, l) {
  s /= 100; l /= 100;
  const c = (1 - Math.abs(2 * l - 1)) * s;
  const x = c * (1 - Math.abs((h / 60) % 2 - 1));
  const m = l - c / 2;
  let [r, g, b] = h < 60 ? [c, x, 0] : h < 120 ? [x, c, 0] : h < 180 ? [0, c, x] : h < 240 ? [0, x, c] : h < 300 ? [x, 0, c] : [c, 0, x];
  const toHex = (v) => Math.round((v + m) * 255).toString(16).padStart(2, '0');
  return `#${toHex(r)}${toHex(g)}${toHex(b)}`;
}

/** Genera una escala de 5 tonos: muy oscuro, oscuro, base, claro, muy claro */
function generateScale(hex) {
  const [h, s, l] = hexToHsl(hex);
  const deltas = [-30, -15, 0, 20, 38]; // asimétrico: el color base suele ser oscuro (texto), así que hacia claro hace falta más recorrido
  const labels = ['Muy oscuro', 'Oscuro', 'Base', 'Claro', 'Muy claro'];
  return deltas.map((d, i) => ({
    label: labels[i],
    hex: hslToHex(h, s, Math.max(4, Math.min(96, l + d))),
  }));
}

function renderScale(container, hex) {
  const scale = generateScale(hex);
  container.innerHTML = scale.map((s) => `
    <div class="color-scale__swatch">
      <div class="color-scale__chip" style="background:${s.hex}"></div>
      <span>${s.label}</span>
      <span>${s.hex}</span>
    </div>
  `).join('');
  return scale;
}

function relativeLuminance(hex) {
  const [r, g, b] = [1, 3, 5].map((i) => {
    const c = parseInt(hex.slice(i, i + 2), 16) / 255;
    return c <= 0.04045 ? c / 12.92 : ((c + 0.055) / 1.055) ** 2.4;
  });
  return 0.2126 * r + 0.7152 * g + 0.0722 * b;
}

function contrastRatioJs(hex1, hex2) {
  const l1 = relativeLuminance(hex1);
  const l2 = relativeLuminance(hex2);
  const [lighter, darker] = l1 > l2 ? [l1, l2] : [l2, l1];
  return (lighter + 0.05) / (darker + 0.05);
}

/**
 * fix (Andrea): chivato de contraste — explica en una frase si el color
 * elegido se lee bien, sin dar por hecho que sepas qué es "AA" o "AAA".
 */
function renderContrastIndicator(elId, textHex, bgHex) {
  const el = document.getElementById(elId);
  if (!el) return;
  const ratio = contrastRatioJs(textHex, bgHex);
  const r = ratio.toFixed(1);

  let className, message;
  if (ratio >= 7) {
    className = 'contrast-indicator--good';
    message = `✅ Contraste excelente (${r}:1, nivel AAA). Se lee perfectamente, incluso con baja visión.`;
  } else if (ratio >= 4.5) {
    className = 'contrast-indicator--ok';
    message = `🟡 Contraste aceptable (${r}:1, nivel AA). Se lee bien en la mayoría de los casos, aunque no es el máximo posible.`;
  } else if (ratio >= 3) {
    className = 'contrast-indicator--warn';
    message = `⚠️ Contraste bajo (${r}:1). Vale para elementos grandes o decorativos, pero como texto costaría de leer.`;
  } else {
    className = 'contrast-indicator--bad';
    message = `❌ Contraste insuficiente (${r}:1). Sería difícil de leer para muchas personas — prueba con colores más alejados entre sí.`;
  }

  el.className = `contrast-indicator ${className}`;
  el.textContent = message;
}

// --- Elementos ---
const colorSurface = document.getElementById('color-surface');
const colorSurfaceHex = document.getElementById('color-surface-hex');
const colorPrimary = document.getElementById('color-primary');
const colorPrimaryHex = document.getElementById('color-primary-hex');
const colorSecondary = document.getElementById('color-secondary');
const colorSecondaryHex = document.getElementById('color-secondary-hex');
const scalePrimaryEl = document.getElementById('scale-primary');
const scaleSecondaryEl = document.getElementById('scale-secondary');

function updateSurfacePreview() {
  const hex = colorSurface.value;
  colorSurfaceHex.value = hex.toUpperCase();
  document.getElementById('theme-mockup').style.background = hex;
  renderContrastIndicator('contrast-primary', colorPrimary.value, hex);
  renderContrastIndicator('contrast-secondary', colorSecondary.value, hex);
}
colorSurface.addEventListener('input', updateSurfacePreview);
colorSurfaceHex.addEventListener('change', () => {
  if (/^#[0-9A-Fa-f]{6}$/.test(colorSurfaceHex.value)) {
    colorSurface.value = colorSurfaceHex.value;
    updateSurfacePreview();
  }
});

function updatePrimaryPreview() {
  const hex = colorPrimary.value;
  colorPrimaryHex.value = hex.toUpperCase();
  const scale = renderScale(scalePrimaryEl, hex);
  document.getElementById('mockup-title').style.color = hex;
  document.getElementById('mockup-text').style.color = scale[1].hex; // "oscuro" para texto secundario
  document.getElementById('mockup-box').style.border = `1px solid ${scale[3].hex}`;
  document.getElementById('mockup-link').style.color = hex;
  renderContrastIndicator('contrast-primary', hex, colorSurface.value);
}

function updateSecondaryPreview() {
  const hex = colorSecondary.value;
  colorSecondaryHex.value = hex.toUpperCase();
  const scale = renderScale(scaleSecondaryEl, hex);
  const btn = document.getElementById('mockup-btn');
  btn.style.background = hex;
  btn.style.color = '#FFFFFF';
  btn.onmouseenter = () => { btn.style.background = scale[0].hex; }; // hover: tono más oscuro
  btn.onmouseleave = () => { btn.style.background = hex; };
  renderContrastIndicator('contrast-secondary', hex, colorSurface.value);
}

colorPrimary.addEventListener('input', updatePrimaryPreview);
colorSecondary.addEventListener('input', updateSecondaryPreview);

colorPrimaryHex.addEventListener('change', () => {
  if (/^#[0-9A-Fa-f]{6}$/.test(colorPrimaryHex.value)) {
    colorPrimary.value = colorPrimaryHex.value;
    updatePrimaryPreview();
  }
});
colorSecondaryHex.addEventListener('change', () => {
  if (/^#[0-9A-Fa-f]{6}$/.test(colorSecondaryHex.value)) {
    colorSecondary.value = colorSecondaryHex.value;
    updateSecondaryPreview();
  }
});

// --- Selección de tipografía ---
const fontSelectContent = document.getElementById('font-select-content');
const fontSelectUi = document.getElementById('font-select-ui');

fontSelectContent.addEventListener('change', (e) => {
  const font = e.target.value;
  document.getElementById('preview-content-heading').style.fontFamily = `'${font}', serif`;
  document.getElementById('preview-content-body').style.fontFamily = `'${font}', serif`;
  document.getElementById('mockup-title').style.fontFamily = `'${font}', serif`;
});

fontSelectUi.addEventListener('change', (e) => {
  const font = e.target.value;
  document.getElementById('preview-ui-button').style.fontFamily = `'${font}', sans-serif`;
  document.getElementById('preview-ui-menu').style.fontFamily = `'${font}', sans-serif`;
  document.getElementById('mockup-btn').style.fontFamily = `'${font}', sans-serif`;
  document.getElementById('mockup-link').style.fontFamily = `'${font}', sans-serif`;
});

// --- Redes sociales (lista dinámica: añadir/quitar) ---
const socialList = document.getElementById('social-links-list');

function addSocialRow(platform = 'instagram', url = '') {
  const row = document.createElement('div');
  row.className = 'social-link-row';
  const options = Object.entries(window.SOCIAL_PLATFORMS || {})
    .map(([value, label]) => `<option value="${value}" ${value === platform ? 'selected' : ''}>${label}</option>`)
    .join('');
  row.innerHTML = `
    <select class="social-link-platform">${options}</select>
    <input type="url" class="social-link-url" placeholder="https://..." value="${url.replace(/"/g, '&quot;')}">
    <button type="button" class="social-link-remove" aria-label="Quitar">&times;</button>
  `;
  row.querySelector('.social-link-remove').addEventListener('click', () => row.remove());
  socialList.appendChild(row);
}

document.getElementById('add-social-btn').addEventListener('click', () => addSocialRow());

function collectSocialLinks() {
  return Array.from(socialList.querySelectorAll('.social-link-row')).map((row) => ({
    platform: row.querySelector('.social-link-platform').value,
    url: row.querySelector('.social-link-url').value.trim(),
  })).filter((l) => l.url);
}

async function loadSocialLinks() {
  const res = await fetch('/api/social-links.php');
  const links = await res.json();
  socialList.innerHTML = '';
  if (links.length) {
    links.forEach((l) => addSocialRow(l.platform, l.url));
  } else {
    addSocialRow();
  }
}

// --- Carga y guardado ---
const statusEl = document.getElementById('save-status');

async function loadSettings() {
  try {
    const res = await fetch('/api/settings.php');
    const settings = await res.json();

    if (settings.font_content) fontSelectContent.value = settings.font_content;
    if (settings.font_ui) fontSelectUi.value = settings.font_ui;
    if (settings.color_primary) colorPrimary.value = settings.color_primary;
    if (settings.color_secondary) colorSecondary.value = settings.color_secondary;
    if (settings.color_surface) colorSurface.value = settings.color_surface;
    document.getElementById('site-name').value = settings.site_name || '';
    document.getElementById('site-subtitle-es').value = settings.site_subtitle_es || '';
    document.getElementById('site-subtitle-en').value = settings.site_subtitle_en || '';
    document.getElementById('show-language-menu').checked = (settings.show_language_menu ?? '1') === '1';
    document.getElementById('site-domain').value = settings.site_domain || '';
    document.getElementById('contact-email').value = settings.contact_email || '';
    document.getElementById('contact-phone').value = settings.contact_phone || '';
    document.getElementById('home-show-hero').checked = (settings.home_show_hero ?? '1') === '1';
    document.getElementById('home-show-categories').checked = (settings.home_show_categories ?? '1') === '1';
    document.getElementById('home-show-videos').checked = (settings.home_show_videos ?? '0') === '1';
    document.getElementById('home-show-simple').checked = (settings.home_show_simple ?? '0') === '1';
    document.getElementById('home-simple-title-es').value = settings.home_simple_title_es || '';
    document.getElementById('home-simple-title-en').value = settings.home_simple_title_en || '';
    document.getElementById('home-simple-desc-es').value = settings.home_simple_description_es || '';
    document.getElementById('home-simple-desc-en').value = settings.home_simple_description_en || '';
    document.getElementById('home-simple-image-alt').value = settings.home_simple_image_alt || '';
    const imageMode = settings.home_simple_image_mode || 'fixed';
    document.getElementById('simple-image-mode-random').checked = imageMode === 'random_featured';
    updateSimpleImageModeUI();
    linkToggleVisibility('#home-show-simple', ['#simple-module-fields']); // fix (#13)
    document.getElementById('home-title-es').value = settings.home_title_es || '';
    document.getElementById('home-title-en').value = settings.home_title_en || '';
    // fix: home_simple_image (main_image) ya se precarga desde PHP, no aquí —
    // evita la carrera de tiempos con la inicialización del recorte
    document.getElementById('home-meta-es').value = settings.home_meta_description_es || '';
    document.getElementById('home-meta-en').value = settings.home_meta_description_en || '';
    document.getElementById('module-projects-enabled').checked = (settings.module_projects_enabled ?? '1') === '1';
    document.getElementById('module-videos-enabled').checked = (settings.module_videos_enabled ?? '1') === '1';
    document.getElementById('module-pages-enabled').checked = (settings.module_pages_enabled ?? '1') === '1';
    const sepStyle = settings.separator_style || 'line';
    document.getElementById('separator-lines-enabled').checked = sepStyle !== 'none';
    const sepSize = settings.separator_size || '24';
    document.getElementById('separator-size').value = sepSize;
    document.getElementById('separator-size-value').textContent = `${sepSize}px`;
    updateSeparatorPreview();
    const gridColumns = settings.grid_columns || '4';
    document.getElementById('grid-columns').value = gridColumns;
    document.getElementById('grid-columns-value').textContent = gridColumns;
    const gridGap = settings.grid_gap || '24';
    document.getElementById('grid-gap').value = gridGap;
    document.getElementById('grid-gap-value').textContent = `${gridGap}px`;
    updateGridPreview();

    // dispara los eventos "change" manualmente para pintar las vistas previas iniciales
    fontSelectContent.dispatchEvent(new Event('change'));
    fontSelectUi.dispatchEvent(new Event('change'));
    updatePrimaryPreview();
    updateSecondaryPreview();
    updateSurfacePreview();
    await loadSocialLinks();
  } catch {
    window.setSaveStatus?.(statusEl, 'error', 'No se pudieron cargar los ajustes.');
  }
}

document.getElementById('save-settings-btn').addEventListener('click', async () => {
  window.setSaveStatus(statusEl, 'saving');

  try {
    const settingsRes = await fetch('/api/settings.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        font_content: fontSelectContent.value,
        font_ui: fontSelectUi.value,
        color_primary: colorPrimary.value,
        color_secondary: colorSecondary.value,
        color_surface: colorSurface.value,
        site_name: document.getElementById('site-name').value,
        site_subtitle_es: document.getElementById('site-subtitle-es').value,
        site_subtitle_en: document.getElementById('site-subtitle-en').value,
        show_language_menu: document.getElementById('show-language-menu').checked ? '1' : '0',
        site_domain: document.getElementById('site-domain').value.trim(),
        contact_email: document.getElementById('contact-email').value.trim(),
        contact_phone: document.getElementById('contact-phone').value.trim(),
        home_show_hero: document.getElementById('home-show-hero').checked ? '1' : '0',
        home_show_categories: document.getElementById('home-show-categories').checked ? '1' : '0',
        home_show_videos: document.getElementById('home-show-videos').checked ? '1' : '0',
        home_meta_description_es: document.getElementById('home-meta-es').value,
        home_meta_description_en: document.getElementById('home-meta-en').value,
        module_projects_enabled: document.getElementById('module-projects-enabled').checked ? '1' : '0',
        module_videos_enabled: document.getElementById('module-videos-enabled').checked ? '1' : '0',
        module_pages_enabled: document.getElementById('module-pages-enabled').checked ? '1' : '0',
        home_show_simple: document.getElementById('home-show-simple').checked ? '1' : '0',
        home_simple_title_es: document.getElementById('home-simple-title-es').value,
        home_simple_title_en: document.getElementById('home-simple-title-en').value,
        home_simple_description_es: document.getElementById('home-simple-desc-es').value,
        home_simple_description_en: document.getElementById('home-simple-desc-en').value,
        home_simple_image: document.getElementById('main_image').value,
        home_simple_image_alt: document.getElementById('home-simple-image-alt').value,
        home_simple_image_mode: document.getElementById('simple-image-mode-random').checked ? 'random_featured' : 'fixed',
        home_title_es: document.getElementById('home-title-es').value,
        home_title_en: document.getElementById('home-title-en').value,
        separator_style: document.getElementById('separator-lines-enabled').checked ? 'line' : 'none',
        separator_size: document.getElementById('separator-size').value,
        grid_columns: document.getElementById('grid-columns').value,
        grid_gap: document.getElementById('grid-gap').value,
      }),
    });
    const socialRes = await fetch('/api/social-links.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ links: collectSocialLinks() }),
    });
    if (!settingsRes.ok || !socialRes.ok) throw new Error('save_failed');
    window.setSaveStatus(statusEl, 'saved', 'Ajustes guardados');
  } catch {
    window.setSaveStatus(statusEl, 'error', 'No se pudo guardar.');
  }
});

loadSettings();

document.getElementById('separator-size').addEventListener('input', (e) => {
  document.getElementById('separator-size-value').textContent = `${e.target.value}px`;
  updateSeparatorPreview();
});
document.getElementById('separator-lines-enabled').addEventListener('change', updateSeparatorPreview);

function updateSeparatorPreview() {
  const line = document.getElementById('separator-preview-line');
  const enabled = document.getElementById('separator-lines-enabled').checked;
  const gap = document.getElementById('separator-size').value;
  line.style.borderTop = enabled ? '1px solid var(--color-ink-300)' : 'none';
  line.style.marginTop = `${gap}px`;
}
document.getElementById('grid-columns').addEventListener('input', (e) => {
  document.getElementById('grid-columns-value').textContent = e.target.value;
  updateGridPreview();
});
document.getElementById('grid-gap').addEventListener('input', (e) => {
  document.getElementById('grid-gap-value').textContent = `${e.target.value}px`;
  updateGridPreview();
});

function updateGridPreview() {
  const preview = document.getElementById('grid-preview');
  const columns = document.getElementById('grid-columns').value;
  const gap = document.getElementById('grid-gap').value;
  preview.style.setProperty('--preview-columns', columns);
  preview.style.setProperty('--preview-gap', `${Math.min(gap, 16)}px`); // a escala reducida, para que quepa
  const totalItems = Number(columns) * 2; // 2 filas de ejemplo
  preview.innerHTML = Array.from({ length: totalItems }, () => '<span></span>').join('');
}

function updateSimpleImageModeUI() {
  const isRandom = document.getElementById('simple-image-mode-random').checked;
  document.getElementById('simple-image-upload-block').hidden = isRandom;
  document.getElementById('simple-image-random-hint').hidden = !isRandom;
}
document.getElementById('simple-image-mode-random').addEventListener('change', updateSimpleImageModeUI);

// --- Pestañas ---
document.querySelectorAll('.admin-tab-btn').forEach((btn) => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.admin-tab-btn').forEach((b) => { b.classList.remove('is-active'); b.setAttribute('aria-selected', 'false'); });
    document.querySelectorAll('.admin-tab-panel').forEach((p) => p.classList.remove('is-active'));
    btn.classList.add('is-active');
    btn.setAttribute('aria-selected', 'true');
    document.querySelector(`[data-tab-panel="${btn.dataset.tab}"]`)?.classList.add('is-active');
    updateSimpleImageModeUI(); // fix: por si el navegador no aplicó bien el estado mientras la pestaña estaba oculta
    // fix (Andrea #11): el panel de vista previa flotante solo tiene sentido en Estilos
    document.getElementById('styles-sticky-preview').hidden = btn.dataset.tab !== 'styles';
  });
});
document.getElementById('styles-sticky-preview').hidden = document.querySelector('.admin-tab-btn.is-active')?.dataset.tab !== 'styles';
