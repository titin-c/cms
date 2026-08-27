<?php
require_once __DIR__ . '/../src/lib/db.php';
require_once __DIR__ . '/../src/lib/i18n.php';
require_once __DIR__ . '/../src/lib/urls.php';
require_once __DIR__ . '/../src/lib/theme.php';
require_once __DIR__ . '/../src/lib/image.php';
require_once __DIR__ . '/../src/lib/video_embed.php';

$GLOBALS['__locale'] = resolveLocale();
$locale = $GLOBALS['__locale'];
$pdo = getDb();
$themeSettings = getSiteSettings($pdo);
maybeRenderComingSoon($themeSettings, $locale); // fix (Andrea, web en construcción)

// fix (seo-agent [audit] 🔴): si el cliente desactivó Vídeos desde Ajustes,
// esta página tampoco debe seguir accesible/indexable
if (($themeSettings['module_videos_enabled'] ?? '1') !== '1') {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

$videos = $pdo->query("SELECT * FROM videos WHERE status = 'published' ORDER BY sort_order ASC, id DESC")->fetchAll();

$slugEs = $themeSettings['videos_slug_es'] ?: 'videos';
$slugEn = $themeSettings['videos_slug_en'] ?: 'videos';
$esUrl = '/' . rawurlencode($slugEs);
$enUrl = '/en/' . rawurlencode($slugEn);

// fix (Andrea): H1/descripción/meta son opcionales — si no se rellenan, no aparecen
$pageH1 = $locale === 'en' ? ($themeSettings['videos_h1_en'] ?: $themeSettings['videos_h1_es']) : $themeSettings['videos_h1_es'];
$pageDescription = $locale === 'en' ? ($themeSettings['videos_description_en'] ?: $themeSettings['videos_description_es']) : $themeSettings['videos_description_es'];
$pageMetaDescription = $locale === 'en' ? ($themeSettings['videos_meta_description_en'] ?: $themeSettings['videos_meta_description_es']) : $themeSettings['videos_meta_description_es'];
$pageMetaTitle = $locale === 'en' ? ($themeSettings['videos_meta_title_en'] ?: $themeSettings['videos_meta_title_es']) : $themeSettings['videos_meta_title_es'];
$pageTitle = $pageMetaTitle ?: ($pageH1 ?: ($locale === 'en' ? 'Videos' : 'Vídeos'));
?>
<!DOCTYPE html>
<html lang="<?= $locale ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle) ?> — <?= htmlspecialchars($themeSettings['site_name'] ?? 'Mi Sitio') ?></title>
  <?= robotsMetaTag($themeSettings) ?>
  <?php if ($pageMetaDescription): ?>
    <meta name="description" content="<?= htmlspecialchars($pageMetaDescription) ?>">
  <?php endif; ?>
  <link rel="canonical" href="<?= getSiteDomain($themeSettings) ?><?= $locale === 'en' ? $enUrl : $esUrl ?>">
  <link rel="alternate" hreflang="es" href="<?= getSiteDomain($themeSettings) ?><?= $esUrl ?>">
  <link rel="alternate" hreflang="en" href="<?= getSiteDomain($themeSettings) ?><?= $enUrl ?>">
  <link rel="alternate" hreflang="x-default" href="<?= getSiteDomain($themeSettings) ?><?= $esUrl ?>">
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
      { "@type": "ListItem", "position": 1, "name": "<?= addslashes($themeSettings['site_name'] ?? 'Mi Sitio') ?>", "item": "<?= getSiteDomain($themeSettings) ?><?= localeHomeUrl($locale) ?>" },
      { "@type": "ListItem", "position": 2, "name": "<?= addslashes($pageTitle) ?>", "item": "<?= getSiteDomain($themeSettings) ?><?= $locale === 'en' ? $enUrl : $esUrl ?>" }
    ]
  }
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="<?= htmlspecialchars(buildThemeFontsUrl($themeSettings)) ?>" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/tokens.css">
  <?= renderThemeOverridesStyleTag($themeSettings) ?>
  <link rel="stylesheet" href="/assets/css/base.css">
  <link rel="stylesheet" href="/assets/css/components/nav.css">
  <link rel="stylesheet" href="/assets/css/components/category-page.css">
  <link rel="stylesheet" href="/assets/css/components/video-lightbox.css">
  <link rel="stylesheet" href="/assets/css/components/footer.css">
  <link rel="stylesheet" href="/assets/css/components/contact-drawer.css">
  <link rel="stylesheet" href="/assets/css/components/cookie-banner.css">
</head>
<body>

  <?php
    $navOnHero = false;
    $langSwitchUrl = $locale === 'es' ? $enUrl : $esUrl;
    include __DIR__ . '/../src/templates/nav.php';
  ?>

  <main id="main-content" class="category-page">
    <?php if ($pageH1): ?><h1><?= htmlspecialchars($pageH1) ?></h1><?php endif; ?>
    <?php if ($pageDescription): ?><p class="category-page__intro"><?= htmlspecialchars($pageDescription) ?></p><?php endif; ?>

    <?php if (empty($videos)): ?>
      <p class="empty-state"><?= $locale === 'en' ? 'No videos published yet.' : 'Todavía no hay vídeos publicados.' ?></p>
    <?php else: ?>
      <div class="category-page__grid">
        <?php foreach ($videos as $video):
          $title = ($locale === 'en' && $video['title_en']) ? $video['title_en'] : $video['title_es'];
          $subtitle = ($locale === 'en' && $video['subtitle_en']) ? $video['subtitle_en'] : $video['subtitle_es'];
          $embedUrl = $video['display_mode'] === 'lightbox' ? videoEmbedUrl($video['video_url'], $video['video_provider']) : null;
        ?>
          <?php if ($embedUrl): ?>
            <button type="button" class="category-page__item video-grid__item" data-video-trigger data-embed-src="<?= htmlspecialchars($embedUrl) ?>" data-video-title="<?= htmlspecialchars($title) ?>">
          <?php else: ?>
            <a href="<?= htmlspecialchars($video['video_url']) ?>" target="_blank" rel="noopener" class="category-page__item video-grid__item">
          <?php endif; ?>
            <span class="video-grid__thumb-wrap">
              <img src="/uploads/<?= htmlspecialchars(variantFromThumbFilename($video['thumbnail'], 'mobile')) ?>"
                   srcset="<?= htmlspecialchars(fullSrcsetFromThumbFilename($video['thumbnail'])) ?>"
                   sizes="(max-width: 768px) 50vw, 25vw"
                   alt="<?= htmlspecialchars($video['thumbnail_alt'] ?: $title) ?>" loading="lazy">
              <span class="video-grid__play" aria-hidden="true">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
              </span>
            </span>
            <span class="category-page__item-title">
              <?= htmlspecialchars($title) ?><?php if ($subtitle): ?><br><small><?= htmlspecialchars($subtitle) ?></small><?php endif; ?>
            </span>
          <?= $embedUrl ? '</button>' : '</a>' ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>

  <!-- fix (Andrea): visor de vídeo — iframe de YouTube/Vimeo en un lightbox -->
  <div id="video-lightbox" class="video-lightbox" role="dialog" aria-modal="true" aria-label="<?= $locale === 'en' ? 'Video player' : 'Reproductor de vídeo' ?>" hidden>
    <button type="button" class="video-lightbox__close" data-video-lightbox-close aria-label="Cerrar">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
    </button>
    <div class="video-lightbox__frame-wrap">
      <iframe id="video-lightbox-iframe" src="" title="" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>
    </div>
  </div>

  <?php include __DIR__ . '/../src/templates/footer.php'; ?>
  <script type="module" src="/assets/js/main.js"></script>
</body>
</html>
