<?php
require_once __DIR__ . '/../src/lib/db.php';
require_once __DIR__ . '/../src/lib/i18n.php';
require_once __DIR__ . '/../src/lib/urls.php';
require_once __DIR__ . '/../src/lib/theme.php';
require_once __DIR__ . '/../src/lib/content_pages.php';

$GLOBALS['__locale'] = resolveLocale();
$locale = $GLOBALS['__locale'];
$pdo = getDb();
$themeSettings = getSiteSettings($pdo);
maybeRenderComingSoon($themeSettings, $locale); // fix (Andrea, web en construcción)

// fix (Andrea): el slug de la página de vídeos es configurable desde
// /admin/videos.php — se resuelve aquí mismo antes de buscar en content_pages
$requestedSlug = $_GET['slug'] ?? '';
$videoPageSlug = $locale === 'en' ? ($themeSettings['videos_slug_en'] ?? 'videos') : ($themeSettings['videos_slug_es'] ?? 'videos');
if ($requestedSlug !== '' && $requestedSlug === $videoPageSlug) {
    include __DIR__ . '/videos.php';
    exit;
}

$categoriesPageSlug = $locale === 'en' ? ($themeSettings['categories_slug_en'] ?? 'categories') : ($themeSettings['categories_slug_es'] ?? 'categorias');
if ($requestedSlug !== '' && $requestedSlug === $categoriesPageSlug) {
    include __DIR__ . '/categorias.php';
    exit;
}

$page = fetchContentPageBySlug($pdo, $requestedSlug, $locale);

if (!$page) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

$esUrl = '/' . rawurlencode($page['slug']);
$enUrl = $page['slug_en'] ? '/en/' . rawurlencode($page['slug_en']) : null;
?>
<!DOCTYPE html>
<html lang="<?= $locale ?>">
<head>
  <?= $themeSettings['tracking_head_code'] ?? '' ?>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($page['title']) ?> — <?= htmlspecialchars($themeSettings['site_name'] ?? 'Mi Sitio') ?></title>
  <?= robotsMetaTag($themeSettings) ?>
  <?php if ($page['noindex']): ?><meta name="robots" content="noindex, follow"><?php endif; ?>
  <meta name="description" content="<?= htmlspecialchars($page['meta_description'] ?: strip_tags($page['content'])) ?>">
  <link rel="canonical" href="<?= getSiteDomain($themeSettings) ?><?= $locale === 'en' && $enUrl ? $enUrl : $esUrl ?>">
  <link rel="alternate" hreflang="es" href="<?= getSiteDomain($themeSettings) ?><?= $esUrl ?>">
  <?php if ($enUrl): ?>
    <link rel="alternate" hreflang="en" href="<?= getSiteDomain($themeSettings) ?><?= $enUrl ?>">
  <?php endif; ?>
  <link rel="alternate" hreflang="x-default" href="<?= getSiteDomain($themeSettings) ?><?= $esUrl ?>">
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
      { "@type": "ListItem", "position": 1, "name": "<?= addslashes($themeSettings['site_name'] ?? 'Mi Sitio') ?>", "item": "<?= getSiteDomain($themeSettings) ?><?= localeHomeUrl($locale) ?>" },
      { "@type": "ListItem", "position": 2, "name": "<?= addslashes($page['title']) ?>", "item": "<?= getSiteDomain($themeSettings) ?><?= ($locale === 'en' && $enUrl) ? $enUrl : $esUrl ?>" }
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
  <link rel="stylesheet" href="/assets/css/components/content-page.css">
  <link rel="stylesheet" href="/assets/css/components/footer.css">
  <link rel="stylesheet" href="/assets/css/components/contact-drawer.css">
  <link rel="stylesheet" href="/assets/css/components/cookie-banner.css">
</head>
<body>
  <?= $themeSettings['tracking_body_code'] ?? '' ?>

  <?php
    $navOnHero = false;
    $langSwitchUrl = $locale === 'es' ? $enUrl : $esUrl;
    include __DIR__ . '/../src/templates/nav.php';
  ?>

  <main id="main-content" class="content-page">
    <h1><?= htmlspecialchars($page['title']) ?></h1>
    <div class="content-page__body"><?= $page['content'] /* HTML enriquecido, editable en /admin/paginas.php */ ?></div>
  </main>

  <?php include __DIR__ . '/../src/templates/footer.php'; ?>
  <script type="module" src="/assets/js/main.js"></script>
</body>
</html>
