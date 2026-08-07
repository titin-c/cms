/**
 * Lightbox sencillo, sin librerías: abre las miniaturas de la galería del
 * proyecto a tamaño completo, con navegación anterior/siguiente y teclado.
 * fix (aria-patterns): focus trap + foco vuelve al trigger al cerrar,
 * mismo patrón que el drawer de contacto.
 */

const triggers = Array.from(document.querySelectorAll('[data-lightbox-trigger]'));
const lightbox = document.getElementById('lightbox');

if (lightbox && triggers.length) {
  const imageEl = document.getElementById('lightbox-image');
  const closeBtn = lightbox.querySelector('[data-lightbox-close]');
  const prevBtn = lightbox.querySelector('[data-lightbox-prev]');
  const nextBtn = lightbox.querySelector('[data-lightbox-next]');

  let currentIndex = 0;
  let lastFocusedTrigger = null;

  function show(index) {
    currentIndex = (index + triggers.length) % triggers.length;
    const trigger = triggers[currentIndex];
    imageEl.src = trigger.dataset.full;
    imageEl.alt = trigger.dataset.alt || '';
  }

  const ANIMATION_MS = 200; // fix (Andrea): duración de la transición, coordinada con el CSS

  function open(index, trigger) {
    lastFocusedTrigger = trigger;
    show(index);
    lightbox.hidden = false;
    document.body.style.overflow = 'hidden';
    requestAnimationFrame(() => lightbox.classList.add('is-open')); // fix (Andrea): anima la entrada
    closeBtn.focus();
    document.addEventListener('keydown', onKeydown);
  }

  function close() {
    lightbox.classList.remove('is-open'); // fix (Andrea): anima la salida antes de ocultar de verdad
    document.body.style.overflow = '';
    document.removeEventListener('keydown', onKeydown);
    lastFocusedTrigger?.focus();
    setTimeout(() => { lightbox.hidden = true; }, ANIMATION_MS);
  }

  function onKeydown(e) {
    if (e.key === 'Escape') close();
    if (e.key === 'ArrowRight') show(currentIndex + 1);
    if (e.key === 'ArrowLeft') show(currentIndex - 1);
    if (e.key === 'Tab') trapFocus(e);
  }

  function trapFocus(e) {
    const focusable = [closeBtn, prevBtn, nextBtn];
    const first = focusable[0];
    const last = focusable[focusable.length - 1];
    if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
    else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
  }

  triggers.forEach((trigger, index) => {
    trigger.addEventListener('click', () => open(index, trigger));
  });

  closeBtn.addEventListener('click', close);
  prevBtn.addEventListener('click', () => show(currentIndex - 1));
  nextBtn.addEventListener('click', () => show(currentIndex + 1));
  lightbox.addEventListener('click', (e) => { if (e.target === lightbox) close(); });
}
