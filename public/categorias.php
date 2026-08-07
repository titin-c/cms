<?php
require_once __DIR__ . '/../src/lib/db.php';
require_once __DIR__ . '/../src/lib/i18n.php';
require_once __DIR__ . '/../src/lib/urls.php';
require_once __DIR__ . '/../src/lib/theme.php';
require_once __DIR__ . '/../src/lib/image.php';

$GLOBALS['__locale'] = resolveLocale();
$locale = $GLOBALS['__locale'];
$pdo = getDb();
$themeSettings = getSiteSettings($pdo);

$localeCol = $locale === 'en' ? "COALESCE(title_en, title_es)" : "title_es";
$homeDescCol = $locale === 'en' ? "COALESCE(home_description_en, description_en, home_description_es, description_es)" : "COALESCE(home_description_es, description_es)";
$categories = $pdo->query("
    SELECT id, slug, slug_en, {$localeCol} AS title, {$homeDescCol} AS description
    FROM categories
    WHERE status = 'published' AND is_default_uncategorized = 0
    ORDER BY sort_order ASC, id ASC
")->fetchAll();

$slugEs = $themeSettings['categories_slug_es'] ?: 'categorias';
$slugEn = $themeSettings['categories_slug_en'] ?: 'categories';
$esUrl = '/' . rawurlencode($slugEs);
$enUrl = '/en/' . rawurlencode($slugEn);

$pageH1 = $locale === 'en' ? ($themeSettings['categories_h1_en'] ?: $themeSettings['categories_h1_es']) : $themeSettings['categories_h1_es'];
$pageDescription = $locale === 'en' ? ($themeSettings['categories_description_en'] ?: $themeSettings['categories_description_es']) : $themeSettings['categories_description_es'];
$pageMetaTitle = $locale === 'en' ? ($themeSettings['categories_meta_title_en'] ?: $themeSettings['categories_meta_title_es']) : $themeSettings['categories_meta_title_es'];
$pageMetaDescription = $locale === 'en' ? ($themeSettings['categories_meta_description_en'] ?: $themeSettings['categories_meta_description_es']) : $themeSettings['categories_meta_description_es'];
$defaultLabel = $locale === 'en' ? 'Categories' : 'Categorías';
$pageTitle = $pageMetaTitle ?: ($pageH1 ?: $defaultLabel);
?>
<!DOCTYPE html>
<html lang="<?= $locale ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle) ?> — <?= htmlspecialchars($themeSettings['site_name'] ?? 'Mi Sitio') ?></title>
  <?php if ($pageMetaDescription): ?><meta name="description" content="<?= htmlspecialchars($pageMetaDescription) ?>"><?php endif; ?>
  <link rel="canonical" href="<?= getSiteDomain($themeSettings) ?><?= $locale === 'en' ? $enUrl : $esUrl ?>">
  <link rel="alternate" hreflang="es" href="<?= getSiteDomain($themeSettings) ?><?= $esUrl ?>">
  <link rel="alternate" hreflang="en" href="<?= getSiteDomain($themeSettings) ?><?= $enUrl ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="<?= htmlspecialchars(buildThemeFontsUrl($themeSettings)) ?>" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/tokens.css">
  <?= renderThemeOverridesStyleTag($themeSettings) ?>
  <link rel="stylesheet" href="/assets/css/base.css">
  <link rel="stylesheet" href="/assets/css/components/nav.css">
  <link rel="stylesheet" href="/assets/css/components/category-page.css">
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

    <?php if (empty($categories)): ?>
      <p class="empty-state"><?= $locale === 'en' ? 'No categories published yet.' : 'Todavía no hay categorías publicadas.' ?></p>
    <?php else: ?>
      <ul class="categories-list">
        <?php foreach ($categories as $cat): ?>
          <li>
            <a href="<?= htmlspecialchars(categoryUrl($cat, $locale)) ?>" class="categories-list__item">
              <span class="categories-list__title"><?= htmlspecialchars($cat['title']) ?></span>
              <?php if ($cat['description']): ?><span class="categories-list__desc"><?= htmlspecialchars($cat['description']) ?></span><?php endif; ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </main>

  <?php include __DIR__ . '/../src/templates/footer.php'; ?>
  <script type="module" src="/assets/js/main.js"></script>
</body>
</html>
