<?php
/**
 * Módulo de home "Mosaico de proyectos" — todos los proyectos publicados
 * (de cualquier categoría) a pantalla completa, en 1/2/3 columnas
 * configurables desde Ajustes → Home. Al hacer scroll, cada miniatura entra
 * desde la izquierda o la derecha según su columna (efecto parallax básico
 * gestionado en projects-mosaic.js). Clicables — llevan a la página del
 * proyecto, igual que las filas de Categorías.
 *
 * $projectsMosaicItems: proyectos publicados (slug, slug_en, title_es/en,
 * excerpt_es/en, main_image, main_image_alt), ya ordenados.
 * $projectsMosaicColumns: 1 | 2 | 3.
 */
$locale = $GLOBALS['__locale'] ?? 'es';
$columns = $projectsMosaicColumns ?? 3;
?>
<section class="projects-mosaic projects-mosaic--cols-<?= (int) $columns ?>" aria-label="<?= $locale === 'en' ? 'All projects' : 'Todos los proyectos' ?>">
  <?php foreach ($projectsMosaicItems as $i => $p):
    $title = ($locale === 'en' && $p['title_en']) ? $p['title_en'] : $p['title_es'];
    $excerpt = ($locale === 'en' && $p['excerpt_en']) ? $p['excerpt_en'] : $p['excerpt_es'];
    $siteNameForAlt = $themeSettings['site_name'] ?? 'Mi Sitio';
    $alt = $p['main_image_alt'] ?: ($title . ' — fotografía de ' . $siteNameForAlt);

    // fix (Andrea): primera columna entra desde la izquierda, última desde la
    // derecha, columnas intermedias (con 3 columnas) solo aparecen con fundo.
    $colIndex = $i % $columns;
    $side = 'center';
    if ($colIndex === 0) { $side = 'left'; }
    elseif ($colIndex === $columns - 1) { $side = 'right'; }
  ?>
    <div class="projects-mosaic__wrap" data-reveal="<?= $side ?>">
      <a href="<?= htmlspecialchars(projectUrl($p, $locale)) ?>" class="projects-mosaic__item" data-parallax>
        <img
          src="/uploads/<?= htmlspecialchars(variantFromThumbFilename($p['main_image'], 'mobile')) ?>"
          srcset="<?= htmlspecialchars(fullSrcsetFromThumbFilename($p['main_image'])) ?>"
          sizes="(max-width: 768px) 100vw, <?= round(100 / $columns) ?>vw"
          loading="lazy"
          alt="<?= htmlspecialchars($alt) ?>">
        <span class="projects-mosaic__overlay">
          <span class="projects-mosaic__title"><?= htmlspecialchars($title) ?></span>
          <?php if ($excerpt): ?><span class="projects-mosaic__excerpt"><?= htmlspecialchars($excerpt) ?></span><?php endif; ?>
        </span>
      </a>
    </div>
  <?php endforeach; ?>
</section>
