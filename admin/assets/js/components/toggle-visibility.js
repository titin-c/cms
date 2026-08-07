/**
 * Enlaza un checkbox/toggle con mostrar-u-ocultar uno o varios bloques.
 * Se sincroniza al cargar y en cada cambio.
 *
 * Uso: linkToggleVisibility('#mi-toggle', ['#bloque-1', '#bloque-2']);
 *      linkToggleVisibility('#mi-toggle', ['#bloque-solo-si-apagado'], { invert: true });
 */
export function linkToggleVisibility(toggleSelector, blockSelectors, { invert = false } = {}) {
  const toggle = document.querySelector(toggleSelector);
  if (!toggle) return;
  const blocks = blockSelectors.map((s) => document.querySelector(s)).filter(Boolean);

  function sync() {
    const show = invert ? !toggle.checked : toggle.checked;
    blocks.forEach((block) => { block.hidden = !show; });
  }

  toggle.addEventListener('change', sync);
  sync();
}
