<?php
/**
 * Hero — puramente decorativo (confirmado por Andrea): sin proyectos
 * clicables, sin títulos/descripciones superpuestas.
 * fix (Andrea): fondo configurable desde Ajustes → Home → Hero:
 *   - 'mosaic' (de siempre): $mosaicImages, array de rutas ya mezcladas.
 *   - 'random_photo': $heroPhoto, una única foto destacada al azar.
 *   - 'none': sin imagen, color de fondo liso (var(--color-surface)) y el
 *     texto se adapta a claro/oscuro vía [data-theme-tone] en <html>.
 * fix (accessibility-agent): aria-hidden SOLO en las imágenes, nunca en el H1/subtítulo.
 * fix (Andrea): si hay pocas imágenes destacadas, se repiten en bucle para
 * llenar el grid — el placeholder gris solo se usa si no hay NINGUNA imagen
 * destacada todavía (estado vacío real).
 * fix (Andrea): altura 100vh en desktop.
 */
$heroBackgroundMode = $heroBackgroundMode ?? 'mosaic';
$minTiles = 30; // con piezas de tamaño variable hacen falta más "unidades" para llenar el grid 10x6 sin huecos
$mosaicImages = $mosaicImages ?? []; // defensivo: nunca null, aunque no haya proyectos destacados aún
$imageCount = count($mosaicImages);
$heroPhoto = $heroPhoto ?? null;
?>
<section class="hero-mosaic hero-mosaic--<?= htmlspecialchars($heroBackgroundMode) ?>">
  <?php if ($heroBackgroundMode === 'mosaic'): ?>
    <div class="hero-mosaic__grid" aria-hidden="true">
      <?php for ($i = 0; $i < $minTiles; $i++): ?>
        <?php if ($imageCount > 0): ?>
          <div class="hero-mosaic__tile" style="background-image:url('<?= htmlspecialchars($mosaicImages[$i % $imageCount]) ?>')"></div>
        <?php else: ?>
          <div class="hero-mosaic__tile hero-mosaic__tile--placeholder"></div>
        <?php endif; ?>
      <?php endfor; ?>
    </div>
    <div class="hero-mosaic__overlay" aria-hidden="true"></div>
  <?php elseif ($heroBackgroundMode === 'random_photo' && $heroPhoto): ?>
    <div class="hero-mosaic__photo" aria-hidden="true" style="background-image:url('<?= htmlspecialchars($heroPhoto) ?>')"></div>
    <div class="hero-mosaic__overlay" aria-hidden="true"></div>
  <?php elseif ($heroBackgroundMode === 'random_photo'): ?>
    <?php // fix (Andrea): sin ningún proyecto destacado todavía, cae al mismo placeholder que el mosaico vacío ?>
    <div class="hero-mosaic__grid" aria-hidden="true">
      <div class="hero-mosaic__tile hero-mosaic__tile--placeholder" style="grid-column:1/-1; grid-row:1/-1;"></div>
    </div>
    <div class="hero-mosaic__overlay" aria-hidden="true"></div>
  <?php endif; ?>
  <?php // modo 'none': sin imagen ni overlay, solo el color de fondo del propio .hero-mosaic--none ?>

  <?php
    $navOnHero = true;
    $langSwitchUrl = localeHomeUrl($locale === 'es' ? 'en' : 'es');
    include __DIR__ . '/nav.php';
  ?>

  <div class="hero-mosaic__content">
    <h1 class="hero-mosaic__title"><?= htmlspecialchars($themeSettings['site_name'] ?? 'Mi Sitio') ?></h1>
    <p class="hero-mosaic__subtitle"><?= htmlspecialchars($locale === 'en'
        ? ($themeSettings['site_subtitle_en'] ?? $themeSettings['site_subtitle_es'] ?? '')
        : ($themeSettings['site_subtitle_es'] ?? '')) ?></p>
  </div>

  <a href="#main-content" class="hero-mosaic__scroll" aria-label="<?= $locale === 'en' ? 'Scroll to content' : 'Ir al contenido' ?>">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
  </a>
</section>
