/**
 * Lightbox de vídeo — mismo patrón que el de imágenes (fade + zoom, focus
 * trap, foco vuelve al trigger al cerrar), pero con un <iframe> en vez de
 * <img>. Importante: al cerrar, se vacía el "src" del iframe para que el
 * vídeo deje de sonar de fondo, no basta con ocultarlo visualmente.
 */

const lightbox = document.getElementById('video-lightbox');
const triggers = Array.from(document.querySelectorAll('[data-video-trigger]'));

if (lightbox && triggers.length) {
  const iframe = document.getElementById('video-lightbox-iframe');
  const closeBtn = lightbox.querySelector('[data-video-lightbox-close]');
  const ANIMATION_MS = 200;
  let lastFocusedTrigger = null;

  function open(trigger) {
    lastFocusedTrigger = trigger;
    iframe.src = trigger.dataset.embedSrc;
    iframe.title = trigger.dataset.videoTitle || '';
    lightbox.hidden = false;
    document.body.style.overflow = 'hidden';
    requestAnimationFrame(() => lightbox.classList.add('is-open'));
    closeBtn.focus();
    document.addEventListener('keydown', onKeydown);
  }

  function close() {
    lightbox.classList.remove('is-open');
    document.body.style.overflow = '';
    document.removeEventListener('keydown', onKeydown);
    lastFocusedTrigger?.focus();
    setTimeout(() => {
      lightbox.hidden = true;
      iframe.src = ''; // fix: para el vídeo de verdad, no solo lo oculta
    }, ANIMATION_MS);
  }

  function onKeydown(e) {
    if (e.key === 'Escape') close();
    if (e.key === 'Tab') { e.preventDefault(); closeBtn.focus(); } // único elemento enfocable dentro
  }

  triggers.forEach((trigger) => trigger.addEventListener('click', () => open(trigger)));
  closeBtn.addEventListener('click', close);
  lightbox.addEventListener('click', (e) => { if (e.target === lightbox) close(); });
}
