<?php
require_once __DIR__ . '/../src/lib/db.php';
require_once __DIR__ . '/../src/lib/i18n.php';
require_once __DIR__ . '/../src/lib/image.php';
require_once __DIR__ . '/../src/lib/urls.php';
require_once __DIR__ . '/../src/lib/theme.php';

$GLOBALS['__locale'] = resolveLocale();
$locale = $GLOBALS['__locale'];
$pdo = getDb();
$themeSettings = getSiteSettings($pdo);
maybeRenderComingSoon($themeSettings, $locale); // fix (Andrea, web en construcción)

if (!empty($_GET['slug_en'])) {
    $stmt = $pdo->prepare("
        SELECT id, slug, slug_en, is_default_uncategorized, show_title, status,
               COALESCE(title_en, title_es) AS title,
               COALESCE(description_en, description_es) AS description,
               COALESCE(meta_description_en, meta_description_es) AS meta_description
        FROM categories WHERE slug_en = ? LIMIT 1
    ");
    $stmt->execute([$_GET['slug_en']]);
} else {
    $localeCol = $locale === 'en' ? "COALESCE(title_en, title_es)" : "title_es";
    $descCol = $locale === 'en' ? "COALESCE(description_en, description_es)" : "description_es";
    $metaCol = $locale === 'en' ? "COALESCE(meta_description_en, meta_description_es)" : "meta_description_es";
    $stmt = $pdo->prepare("
        SELECT id, slug, slug_en, is_default_uncategorized, show_title, status, {$localeCol} AS title, {$descCol} AS description, {$metaCol} AS meta_description
        FROM categories WHERE slug = ? LIMIT 1
    ");
    $stmt->execute([$_GET['slug'] ?? '']);
}
$category = $stmt->fetch();

// fix (seo-agent [audit] 🟡): "Sin categorizar" nunca es indexable — es una
// categoría técnica de reasignación (ver api/categories.php), no un tema
// real que merezca posicionar en buscadores.
// fix (Andrea): una categoría en borrador tampoco es accesible por URL directa.
if (!$category || $category['is_default_uncategorized'] || $category['status'] !== 'published') {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

$projectStmt = $pdo->prepare("
    SELECT DISTINCT p.slug, p.slug_en, p.title_es, p.title_en, p.main_image AS thumb, p.main_image_alt,
           p.sort_order, p.project_date
    FROM projects p
    WHERE p.status = 'published'
      AND (p.category_id = ? OR EXISTS (
        SELECT 1 FROM project_extra_categories pec WHERE pec.project_id = p.id AND pec.category_id = ?
      ))
    ORDER BY p.sort_order ASC, p.project_date DESC
");
$projectStmt->execute([$category['id'], $category['id']]);
$projects = $projectStmt->fetchAll();

// fix (Andrea, SEO): categorías sin proyectos no tienen página pública indexable
if (empty($projects)) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

$pageTitle = htmlspecialchars($category['title']) . ' — ' . htmlspecialchars($themeSettings['site_name'] ?? 'Mi Sitio');
$esUrl = '/categoria/' . rawurlencode($category['slug']);
$enUrl = !empty($category['slug_en']) ? '/category/' . rawurlencode($category['slug_en']) : null;
?>
<!DOCTYPE html>
<html lang="<?= $locale ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $pageTitle ?></title>
  <?= robotsMetaTag($themeSettings) ?>
  <meta name="description" content="<?= htmlspecialchars($category['meta_description'] ?: strip_tags($category['description'] ?? '') ?: $category['title']) ?>">
  <link rel="canonical" href="<?= getSiteDomain($themeSettings) ?><?= $locale === 'en' && $enUrl ? $enUrl : $esUrl ?>">
  <link rel="alternate" hreflang="es" href="<?= getSiteDomain($themeSettings) ?><?= $esUrl ?>">
  <?php if ($enUrl): ?>
    <link rel="alternate" hreflang="en" href="<?= getSiteDomain($themeSettings) ?><?= $enUrl ?>">
  <?php endif; ?>
  <link rel="alternate" hreflang="x-default" href="<?= getSiteDomain($themeSettings) ?><?= $esUrl ?>">
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
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
      { "@type": "ListItem", "position": 1, "name": "<?= addslashes($themeSettings['site_name'] ?? 'Mi Sitio') ?>", "item": "<?= getSiteDomain($themeSettings) ?><?= localeHomeUrl($locale) ?>" },
      { "@type": "ListItem", "position": 2, "name": <?= json_encode($category['title'], JSON_UNESCAPED_UNICODE) ?> }
    ]
  }
  </script>
</head>
<body>

  <?php
    $navOnHero = false;
    $langSwitchUrl = $enUrl ? ($locale === 'es' ? $enUrl : $esUrl) : null;
    include __DIR__ . '/../src/templates/nav.php';
  ?>

  <main id="main-content" class="category-page">
    <nav class="breadcrumb" aria-label="<?= $locale === 'en' ? 'Breadcrumb' : 'Migas de pan' ?>">
      <a href="<?= localeHomeUrl($locale) ?>"><?= htmlspecialchars($themeSettings['site_name'] ?? 'Mi Sitio') ?></a>
      <span aria-hidden="true">/</span>
      <span aria-current="page"><?= htmlspecialchars($category['title']) ?></span>
    </nav>

    <?php if ($category['show_title']): ?><h1><?= htmlspecialchars($category['title']) ?></h1><?php endif; ?>
    <?php if ($category['description']): ?>
      <div class="category-page__intro"><?= $category['description'] /* HTML enriquecido de Quill, ya sanitizado al guardar */ ?></div>
    <?php endif; ?>

    <div class="category-page__grid">
      <?php foreach ($projects as $p):
        $pTitle = ($locale === 'en' && $p['title_en']) ? $p['title_en'] : $p['title_es'];
      ?>
        <a href="<?= htmlspecialchars(projectUrl($p, $locale)) ?>" class="category-page__item">
          <img src="/uploads/<?= htmlspecialchars(variantFromThumbFilename($p['thumb'], 'mobile')) ?>"
               srcset="<?= htmlspecialchars(fullSrcsetFromThumbFilename($p['thumb'])) ?>"
               sizes="(max-width: 768px) 50vw, 25vw"
               alt="<?= htmlspecialchars($p['main_image_alt'] ?: ($pTitle . ' — fotografía de ' . ($themeSettings['site_name'] ?? 'Mi Sitio'))) ?>"
               loading="lazy">
          <span class="category-page__item-title"><?= htmlspecialchars($pTitle) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </main>

  <?php include __DIR__ . '/../src/templates/footer.php'; ?>
  <script type="module" src="/assets/js/main.js"></script>
</body>
</html>
