<?php
/**
 * Hero mosaico — puramente decorativo (confirmado por Andrea): sin proyectos
 * clicables, sin títulos/descripciones superpuestas.
 * $mosaicImages: array de rutas de imágenes destacadas (featured=1), ya mezcladas.
 * fix (accessibility-agent): aria-hidden SOLO en las imágenes, nunca en el H1/subtítulo.
 * fix (Andrea): si hay pocas imágenes destacadas, se repiten en bucle para
 * llenar el grid — el placeholder gris solo se usa si no hay NINGUNA imagen
 * destacada todavía (estado vacío real).
 * fix (Andrea): altura 100vh en desktop.
 */
$minTiles = 30; // con piezas de tamaño variable hacen falta más "unidades" para llenar el grid 10x6 sin huecos
$mosaicImages = $mosaicImages ?? []; // defensivo: nunca null, aunque no haya proyectos destacados aún
$imageCount = count($mosaicImages);
?>
<section class="hero-mosaic">
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
