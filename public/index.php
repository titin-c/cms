<?php
require_once __DIR__ . '/../src/lib/db.php';
require_once __DIR__ . '/../src/lib/i18n.php';
require_once __DIR__ . '/../src/lib/image.php';
require_once __DIR__ . '/../src/lib/urls.php';
require_once __DIR__ . '/../src/lib/theme.php';
require_once __DIR__ . '/../src/lib/video_embed.php';

$GLOBALS['__locale'] = resolveLocale();
$locale = $GLOBALS['__locale'];
$pdo = getDb();
$themeSettings = getSiteSettings($pdo);

// fix (Andrea, CMS multi-cliente): 3 módulos independientes y combinables —
// cualquier combinación es válida, incluso ninguno (home solo con footer).
$showHero = ($themeSettings['home_show_hero'] ?? '1') === '1';
$showCategories = ($themeSettings['home_show_categories'] ?? '1') === '1';
$showVideos = ($themeSettings['home_show_videos'] ?? '0') === '1';
$showSimple = ($themeSettings['home_show_simple'] ?? '0') === '1';

$categories = [];
$videos = [];
$mosaicImages = [];

if ($showCategories) {
    // Categorías CON al menos un proyecto publicado (regla: vacías nunca se renderizan)
    $categoriesStmt = $pdo->query("
        SELECT c.id, c.slug, c.slug_en,
               " . ($locale === 'en'
                    ? "COALESCE(c.home_title_en, c.title_en, c.home_title_es, c.title_es)"
                    : "COALESCE(c.home_title_es, c.title_es)") . " AS title,
               " . ($locale === 'en'
                    ? "COALESCE(c.home_description_en, c.description_en, c.home_description_es, c.description_es)"
                    : "COALESCE(c.home_description_es, c.description_es)") . " AS description,
               " . ($locale === 'en'
                    ? "COALESCE(c.button_label_en, c.button_label_es)"
                    : "c.button_label_es") . " AS button_label
        FROM categories c
        WHERE c.is_default_uncategorized = 0
          AND c.show_in_home = 1
          AND c.status = 'published'
          AND EXISTS (
            SELECT 1 FROM projects p
            WHERE p.status = 'published'
              AND (p.category_id = c.id OR EXISTS (
                SELECT 1 FROM project_extra_categories pec WHERE pec.project_id = p.id AND pec.category_id = c.id
              ))
          )
        ORDER BY c.sort_order ASC
    ");
    $categories = $categoriesStmt->fetchAll();

    // fix (Andrea): un proyecto puede aparecer en categorías adicionales además
    // de la principal — se listan aquí ambos casos, sin duplicados
    $projectStmt = $pdo->prepare("
        SELECT DISTINCT p.id, p.slug, p.slug_en, p.title_es, p.title_en, p.main_image AS thumb, p.main_image_alt,
               p.sort_order, p.project_date
        FROM projects p
        WHERE p.status = 'published'
          AND (p.category_id = ? OR EXISTS (
            SELECT 1 FROM project_extra_categories pec WHERE pec.project_id = p.id AND pec.category_id = ?
          ))
        ORDER BY p.sort_order ASC, p.project_date DESC
    ");
    foreach ($categories as &$cat) {
        $projectStmt->execute([$cat['id'], $cat['id']]);
        $cat['projects'] = $projectStmt->fetchAll();
    }
    unset($cat);
}

if ($showVideos) {
    $videos = $pdo->query("SELECT * FROM videos WHERE status = 'published' ORDER BY sort_order ASC, id DESC")->fetchAll();
}

if ($showHero) {
    // El mosaico se nutre de las fuentes activas: proyectos destacados y/o
    // miniaturas de vídeo, según qué módulos estén encendidos.
    if ($showCategories) {
        $featuredProjectImages = $pdo->query("
            SELECT main_image FROM projects WHERE featured = 1 AND status = 'published' ORDER BY RAND() LIMIT 30
        ")->fetchAll(PDO::FETCH_COLUMN);
        $mosaicImages = array_merge($mosaicImages, $featuredProjectImages);
    }
    if ($showVideos) {
        $videoThumbs = array_column($videos, 'thumbnail');
        shuffle($videoThumbs);
        $mosaicImages = array_merge($mosaicImages, array_slice($videoThumbs, 0, 30));
    }
    shuffle($mosaicImages);
    // fix (seo-agent [audit] 🟠): variante 'mobile' (768w), no 'desktop' — cada
    // pieza del mosaico se muestra muy pequeña, 1600w era peso desperdiciado
    $mosaicImages = array_map(fn($img) => '/uploads/' . variantFromThumbFilename($img, 'mobile'), array_slice($mosaicImages, 0, 30));
}

$siteNameForTitle = $themeSettings['site_name'] ?? 'Mi Sitio';
$subtitleForTitle = $locale === 'en'
    ? ($themeSettings['site_subtitle_en'] ?? $themeSettings['site_subtitle_es'] ?? '')
    : ($themeSettings['site_subtitle_es'] ?? '');
$pageTitle = $subtitleForTitle ? "{$siteNameForTitle} — {$subtitleForTitle}" : $siteNameForTitle;
// fix (Andrea, SEO): meta título configurable, con fallback al generado automáticamente
$homeMetaTitle = $locale === 'en'
    ? ($themeSettings['home_title_en'] ?: $themeSettings['home_title_es'])
    : $themeSettings['home_title_es'];
if ($homeMetaTitle) { $pageTitle = $homeMetaTitle; }

// fix (Andrea, admin): meta descripción de la home ajustable desde el panel
$homeMetaDescription = $locale === 'en'
    ? ($themeSettings['home_meta_description_en'] ?: $themeSettings['home_meta_description_es'])
    : $themeSettings['home_meta_description_es'];
if (!$homeMetaDescription) {
    $homeMetaDescription = t('home.meta_description'); // fallback genérico si no se ha configurado
}
?>
<!DOCTYPE html>
<html lang="<?= $locale ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <meta name="description" content="<?= htmlspecialchars($homeMetaDescription) ?>">
  <link rel="canonical" href="<?= getSiteDomain($themeSettings) ?><?= localeHomeUrl($locale) ?>">
  <link rel="alternate" hreflang="es" href="<?= getSiteDomain($themeSettings) ?>/">
  <link rel="alternate" hreflang="en" href="<?= getSiteDomain($themeSettings) ?>/en/">
  <link rel="alternate" hreflang="x-default" href="<?= getSiteDomain($themeSettings) ?>/">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="<?= htmlspecialchars(buildThemeFontsUrl($themeSettings)) ?>" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/tokens.css">
  <?= renderThemeOverridesStyleTag($themeSettings) ?>
  <link rel="stylesheet" href="/assets/css/base.css">
  <link rel="stylesheet" href="/assets/css/components/nav.css">
  <?php if ($showHero): ?><link rel="stylesheet" href="/assets/css/components/hero-mosaic.css"><?php endif; ?>
  <?php if ($showCategories): ?><link rel="stylesheet" href="/assets/css/components/category-row.css"><?php endif; ?>
  <?php if ($showVideos): ?><link rel="stylesheet" href="/assets/css/components/video-lightbox.css"><?php endif; ?>
  <?php if ($showSimple): ?><link rel="stylesheet" href="/assets/css/components/home-simple.css"><?php endif; ?>
  <link rel="stylesheet" href="/assets/css/components/footer.css">
  <link rel="stylesheet" href="/assets/css/components/contact-drawer.css">
  <link rel="stylesheet" href="/assets/css/components/cookie-banner.css">
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Person",
    "name": "<?= addslashes($themeSettings['site_name'] ?? 'Mi Sitio') ?>",
    "jobTitle": "<?= addslashes($locale === 'en'
        ? ($themeSettings['site_subtitle_en'] ?? $themeSettings['site_subtitle_es'] ?? '')
        : ($themeSettings['site_subtitle_es'] ?? '')) ?>",
    "url": "<?= getSiteDomain($themeSettings) ?><?= localeHomeUrl($locale) ?>",
    "sameAs": [<?= implode(', ', array_map(fn($s) => '"' . addslashes($s['url']) . '"', getSocialLinks($pdo))) ?>]
  }
  </script>
</head>
<body>
  <!-- diagnóstico: home_simple_image_mode = <?= htmlspecialchars($themeSettings['home_simple_image_mode'] ?? 'fixed') ?> -->

  <?php if ($showHero): ?>
    <?php include __DIR__ . '/../src/templates/hero-mosaic.php'; ?>
  <?php else: ?>
    <?php
      // fix (Andrea): sin hero, la home usa la misma cabecera sólida que el resto de páginas
      $navOnHero = false;
      $langSwitchUrl = localeHomeUrl($locale === 'es' ? 'en' : 'es');
      include __DIR__ . '/../src/templates/nav.php';
    ?>
  <?php endif; ?>

  <main id="main-content">
    <?php if ($showCategories): ?>
      <?php if (empty($categories)): ?>
        <p class="empty-state"><?= $locale === 'en' ? 'New projects coming soon.' : 'Nuevos proyectos muy pronto.' ?></p>
      <?php else: ?>
        <?php foreach ($categories as $category): ?>
          <?php include __DIR__ . '/../src/templates/category-row.php'; ?>
        <?php endforeach; ?>
      <?php endif; ?>
    <?php endif; ?>

    <?php if ($showVideos): ?>
      <?php if (empty($videos)): ?>
        <p class="empty-state"><?= $locale === 'en' ? 'No videos published yet.' : 'Todavía no hay vídeos publicados.' ?></p>
      <?php else: ?>
        <div class="home-video-grid">
          <?php foreach ($videos as $video):
            $title = ($locale === 'en' && $video['title_en']) ? $video['title_en'] : $video['title_es'];
            $subtitle = ($locale === 'en' && $video['subtitle_en']) ? $video['subtitle_en'] : $video['subtitle_es'];
            $embedUrl = $video['display_mode'] === 'lightbox' ? videoEmbedUrl($video['video_url'], $video['video_provider']) : null;
          ?>
            <?php if ($embedUrl): ?>
              <button type="button" class="video-grid__item" data-video-trigger data-embed-src="<?= htmlspecialchars($embedUrl) ?>" data-video-title="<?= htmlspecialchars($title) ?>">
            <?php else: ?>
              <a href="<?= htmlspecialchars($video['video_url']) ?>" target="_blank" rel="noopener" class="video-grid__item">
            <?php endif; ?>
              <span class="video-grid__thumb-wrap">
                <img src="/uploads/<?= htmlspecialchars(variantFromThumbFilename($video['thumbnail'], 'mobile')) ?>"
                     srcset="<?= htmlspecialchars(fullSrcsetFromThumbFilename($video['thumbnail'])) ?>"
                     sizes="(max-width: 768px) 100vw, (max-width: 1200px) 33vw, 25vw"
                     alt="<?= htmlspecialchars($video['thumbnail_alt'] ?: $title) ?>" loading="lazy">
                <span class="video-grid__play" aria-hidden="true">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                </span>
              </span>
              <span class="video-grid__item-title">
                <?= htmlspecialchars($title) ?><?php if ($subtitle): ?><br><small><?= htmlspecialchars($subtitle) ?></small><?php endif; ?>
              </span>
            <?= $embedUrl ? '</button>' : '</a>' ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div id="video-lightbox" class="video-lightbox" role="dialog" aria-modal="true" aria-label="<?= $locale === 'en' ? 'Video player' : 'Reproductor de vídeo' ?>" hidden>
        <button type="button" class="video-lightbox__close" data-video-lightbox-close aria-label="Cerrar">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
        </button>
        <div class="video-lightbox__frame-wrap">
          <iframe id="video-lightbox-iframe" src="" title="" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($showSimple):
      $simpleTitle = $locale === 'en' ? ($themeSettings['home_simple_title_en'] ?: $themeSettings['home_simple_title_es']) : $themeSettings['home_simple_title_es'];
      $simpleDesc = $locale === 'en' ? ($themeSettings['home_simple_description_en'] ?: $themeSettings['home_simple_description_es']) : $themeSettings['home_simple_description_es'];
      $simpleImageAlt = $themeSettings['home_simple_image_alt'] ?: $simpleTitle;

      if (($themeSettings['home_simple_image_mode'] ?? 'fixed') === 'random_featured') {
          // fix (Andrea): imagen aleatoria entre los proyectos destacados — distinta en cada visita
          $randomFeatured = $pdo->query("
              SELECT main_image FROM projects WHERE featured = 1 AND status = 'published' ORDER BY RAND() LIMIT 1
          ")->fetchColumn();
          $simpleImage = $randomFeatured ?: null;
      } else {
          $simpleImage = $themeSettings['home_simple_image'];
      }

      if ($simpleTitle || $simpleDesc || $simpleImage):
    ?>
      <section class="home-simple">
        <?php if ($simpleTitle): $tag = $showHero ? 'h2' : 'h1'; ?><<?= $tag ?> class="home-simple__title"><?= htmlspecialchars($simpleTitle) ?></<?= $tag ?>><?php endif; ?>
        <?php if ($simpleImage): ?>
          <img class="home-simple__image"
               src="/uploads/<?= htmlspecialchars(variantFromThumbFilename($simpleImage, 'desktop')) ?>"
               srcset="<?= htmlspecialchars(fullSrcsetFromThumbFilename($simpleImage)) ?>"
               sizes="(max-width: 768px) 100vw, 800px"
               alt="<?= htmlspecialchars($simpleImageAlt) ?>">
        <?php endif; ?>
        <?php if ($simpleDesc): ?><p class="home-simple__text"><?= nl2br(htmlspecialchars($simpleDesc)) ?></p><?php endif; ?>
      </section>
    <?php endif; endif; ?>

    <?php if (!$showCategories && !$showVideos && !$showSimple): ?>
      <p class="empty-state" style="padding-top:var(--spacing-24);"><?= $locale === 'en' ? 'Content coming soon.' : 'Contenido muy pronto.' ?></p>
    <?php endif; ?>
  </main>

  <?php include __DIR__ . '/../src/templates/footer.php'; ?>

  <script type="module" src="/assets/js/main.js"></script>
</body>
</html>
