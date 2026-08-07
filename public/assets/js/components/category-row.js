/**
 * Auto-scroll pausable + flechas manuales.
 * fix (usability-agent 🟠): pausa instantánea al hover/touch/foco, sin delay.
 * fix (accessibility-agent 🟠): :focus-within también dispara la pausa.
 * fix (accessibility-agent): respeta prefers-reduced-motion — desactiva el
 * auto-scroll por completo, deja solo navegación manual con las flechas.
 * En móvil (≤768px) el auto-scroll no se activa: el CSS ya usa scroll-snap táctil.
 *
 * fix (Andrea): "¿caben todos los proyectos sin scroll?" se mide de verdad
 * (scrollWidth vs clientWidth del track), no contando proyectos — así en
 * pantallas muy anchas, donde más de 4 proyectos pueden caber igualmente,
 * las flechas y el auto-scroll se desactivan solos porque no hacen falta.
 */

const REDUCED_MOTION = matchMedia('(prefers-reduced-motion: reduce)').matches;
const MOBILE_QUERY = matchMedia('(max-width: 768px)');

function updateFit(gallery) {
  if (MOBILE_QUERY.matches) {
    gallery.classList.remove('category-row__gallery--fits');
    return false;
  }
  const track = gallery.querySelector('.category-row__track');

  // fix: se quita la clase antes de medir, para forzar el tamaño natural de
  // las miniaturas (si ya estuviera en modo "fits", el ancho dinámico
  // falsearía la medición de si realmente caben sin scroll)
  gallery.classList.remove('category-row__gallery--fits');
  const fits = track.scrollWidth <= track.clientWidth + 1;

  gallery.classList.toggle('category-row__gallery--fits', fits);
  gallery.querySelectorAll('.category-row__arrow').forEach((btn) => { btn.hidden = fits; });
  return fits;
}

function debounce(fn, ms) {
  let t;
  return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
}

document.querySelectorAll('[data-autoscroll]').forEach((gallery) => {
  const track = gallery.querySelector('.category-row__track');
  const prevBtn = gallery.querySelector('.category-row__arrow--prev');
  const nextBtn = gallery.querySelector('.category-row__arrow--next');

  const SPEED_PX_PER_FRAME = 0.4;
  let paused = true; // se decide en cada recálculo de recalc()

  function tick() {
    const fits = gallery.classList.contains('category-row__gallery--fits');
    if (!paused && !fits) {
      track.scrollLeft += SPEED_PX_PER_FRAME;
      if (track.scrollLeft + track.clientWidth >= track.scrollWidth - 1) {
        track.scrollLeft = 0;
      }
    }
    requestAnimationFrame(tick);
  }
  requestAnimationFrame(tick);

  function recalc() {
    const fits = updateFit(gallery);
    paused = fits || REDUCED_MOTION || MOBILE_QUERY.matches;
  }

  recalc();
  window.addEventListener('resize', debounce(recalc, 200));

  const pause = () => { paused = true; };
  const resume = () => {
    if (!REDUCED_MOTION && !MOBILE_QUERY.matches && !gallery.classList.contains('category-row__gallery--fits')) {
      paused = false;
    }
  };

  ['mouseenter', 'touchstart', 'focusin'].forEach((evt) => gallery.addEventListener(evt, pause));
  ['mouseleave', 'touchend', 'focusout'].forEach((evt) => gallery.addEventListener(evt, resume));

  prevBtn?.addEventListener('click', () => {
    pause();
    track.scrollBy({ left: -300, behavior: 'smooth' });
  });
  nextBtn?.addEventListener('click', () => {
    pause();
    track.scrollBy({ left: 300, behavior: 'smooth' });
  });
});
