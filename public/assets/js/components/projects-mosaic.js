/**
 * Módulo "Mosaico de proyectos": revelado desde el lado (izquierda/derecha
 * según columna) al entrar en el viewport, + parallax continuo de la imagen
 * mientras se hace scroll.
 * fix (accessibility-agent): respeta prefers-reduced-motion — sin revelado
 * animado ni parallax, todo visible y estático desde el principio (ya lo
 * cubre también el CSS, esto además evita el coste de cálculo en JS).
 */
const REDUCED_MOTION = matchMedia('(prefers-reduced-motion: reduce)').matches;

const mosaic = document.querySelector('.projects-mosaic');
if (mosaic && !REDUCED_MOTION) {
  const wraps = mosaic.querySelectorAll('.projects-mosaic__wrap');

  // --- Revelado al entrar en el viewport ---
  const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        revealObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.15, rootMargin: '0px 0px -10% 0px' });

  wraps.forEach((wrap) => revealObserver.observe(wrap));

  // --- Parallax continuo ---
  // fix (Andrea): columnas alternas se mueven a distinta velocidad para dar
  // sensación de profundidad; factor pequeño para que nunca se note "roto".
  const PARALLAX_FACTORS = [0.06, -0.05, 0.08];
  const items = Array.from(mosaic.querySelectorAll('[data-parallax]'));

  let ticking = false;
  function updateParallax() {
    const viewportCenter = window.innerHeight / 2;
    items.forEach((item, i) => {
      const rect = item.getBoundingClientRect();
      // fix: solo se calcula para lo que está cerca del viewport — barato incluso con muchos proyectos
      if (rect.bottom < -200 || rect.top > window.innerHeight + 200) return;
      const distanceFromCenter = (rect.top + rect.height / 2) - viewportCenter;
      const factor = PARALLAX_FACTORS[i % PARALLAX_FACTORS.length];
      const offset = distanceFromCenter * factor;
      const img = item.querySelector('img');
      if (img) img.style.transform = `translateY(${offset.toFixed(1)}px)`;
    });
    ticking = false;
  }

  function onScroll() {
    if (!ticking) {
      requestAnimationFrame(updateParallax);
      ticking = true;
    }
  }

  updateParallax();
  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', onScroll);
}
