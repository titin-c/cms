<?php
/**
 * $category: ['id','slug','title','description','projects' => [...]]
 * Regla (ux-agent/qa-agent): categorías sin proyectos publicados NO se renderizan
 * — este template asume que el caller ya filtró categorías vacías.
 * fix (accessibility-agent, bloqueante): skip-link al final de la galería.
 * fix (Andrea): flechas superpuestas a las imágenes, visibles solo al hover/focus.
 */
$locale = $GLOBALS['__locale'] ?? 'es';
?>
<section class="category-row" aria-label="<?= htmlspecialchars($category['title']) ?>">
  <a href="#cat-end-<?= $category['id'] ?>" class="category-row__skip">
    <?= t('a11y.skip_gallery', ['category' => $category['title']]) ?>
  </a>

  <div class="category-row__grid">
    <div class="category-row__gallery" data-autoscroll>
      <button type="button" class="category-row__arrow category-row__arrow--prev" aria-label="<?= t('a11y.prev') ?>">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
      </button>

      <div class="category-row__track">
        <?php foreach ($category['projects'] as $p):
          $title = $locale === 'en' && $p['title_en'] ? $p['title_en'] : $p['title_es'];
          $siteNameForAlt = $themeSettings['site_name'] ?? 'Mi Sitio';
          $alt = $p['main_image_alt'] ?: ($title . ' — fotografía de ' . $siteNameForAlt);
        ?>
          <a href="<?= htmlspecialchars(projectUrl($p, $locale)) ?>" class="category-row__item">
            <img
              src="/uploads/<?= htmlspecialchars($p['thumb']) ?>"
              srcset="<?= htmlspecialchars(fullSrcsetFromThumbFilename($p['thumb'])) ?>"
              sizes="(max-width: 768px) 170px, 60vw"
              loading="lazy"
              alt="<?= htmlspecialchars($alt) ?>"
              width="200" height="320">
            <span class="category-row__item-title"><?= htmlspecialchars($title) ?></span>
          </a>
        <?php endforeach; ?>
      </div>

      <button type="button" class="category-row__arrow category-row__arrow--next" aria-label="<?= t('a11y.next') ?>">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
      </button>
    </div>

    <div class="category-row__text">
      <h2><?= htmlspecialchars($category['title']) ?></h2>
      <div class="category-row__text-description"><?= $category['description'] /* HTML enriquecido de Quill */ ?></div>
      <a class="category-row__more" href="<?= htmlspecialchars(categoryUrl($category, $locale)) ?>">
        <?= htmlspecialchars($category['button_label'] ?: ($locale === 'en' ? 'View all' : 'Ver todo')) ?> &rarr;
      </a>
    </div>
  </div>

  <span id="cat-end-<?= $category['id'] ?>" tabindex="-1" class="category-row__end-anchor"></span>
</section>
