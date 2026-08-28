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

// fix (Andrea, SEO): la ruta en inglés (/project/slug-en) llega con slug_en;
// la ruta en español (/proyecto/slug) llega con slug — ver public/.htaccess
if (!empty($_GET['slug_en'])) {
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE slug_en = ? AND status = 'published' LIMIT 1");
    $stmt->execute([$_GET['slug_en']]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE slug = ? AND status = 'published' LIMIT 1");
    $stmt->execute([$_GET['slug'] ?? '']);
}
$project = $stmt->fetch();

if (!$project) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

$hasTranslation = !empty($project['title_en']) && !empty($project['content_en']);
$hasTranslatedUrl = $hasTranslation && !empty($project['slug_en']);

// fix (ux-agent, decisión UX): si no hay traducción EN, se muestra el contenido ES
// igualmente, con el LanguageBadge indicándolo — nunca se oculta el proyecto.
$title   = ($locale === 'en' && $hasTranslation) ? $project['title_en']   : $project['title_es'];
$content = ($locale === 'en' && $hasTranslation) ? $project['content_en'] : $project['content_es'];
$excerpt = ($locale === 'en' && $hasTranslation) ? $project['excerpt_en'] : $project['excerpt_es'];
$seoDesc = ($locale === 'en' && $hasTranslation) ? $project['seo_description_en'] : $project['seo_description_es'];
$seoKeywords = ($locale === 'en' && !empty($project['seo_keywords_en'])) ? $project['seo_keywords_en'] : $project['seo_keywords'];

$imagesStmt = $pdo->prepare("SELECT * FROM project_images WHERE project_id = ? ORDER BY sort_order ASC");
$imagesStmt->execute([$project['id']]);
$galleryImages = $imagesStmt->fetchAll();

// fix (Andrea): la versión anterior buscaba el proyecto con sort_order más
// "cercano" — con valores por defecto (todos en 0) siempre devolvía el mismo,
// creando un bucle entre solo 2 proyectos. Ahora se recorre la lista completa
// y ordenada de la categoría, y se avanza al siguiente real (con vuelta al
// primero al llegar al final).
$categoryProjectsStmt = $pdo->prepare("
    SELECT id, slug, slug_en, title_es, title_en FROM projects
    WHERE category_id = ? AND status = 'published'
    ORDER BY sort_order ASC, id ASC
");
$categoryProjectsStmt->execute([$project['category_id']]);
$categoryProjects = $categoryProjectsStmt->fetchAll();

$nextProject = null;
$currentIndex = array_search($project['id'], array_column($categoryProjects, 'id'));
if ($currentIndex !== false && count($categoryProjects) > 1) {
    $nextIndex = ($currentIndex + 1) % count($categoryProjects); // vuelve al primero al llegar al final
    $nextProject = $categoryProjects[$nextIndex];
}

$catStmt = $pdo->prepare("
    SELECT slug, slug_en,
           " . ($locale === 'en' ? "COALESCE(title_en, title_es)" : "title_es") . " AS title
    FROM categories WHERE id = ? LIMIT 1
");
$catStmt->execute([$project['category_id']]);
$projectCategory = $catStmt->fetch();

$esUrl = '/proyecto/' . rawurlencode($project['slug']);
$enUrl = $hasTranslatedUrl ? '/project/' . rawurlencode($project['slug_en']) : null;
?>
<!DOCTYPE html>
<html lang="<?= $locale ?>">
<head>
  <?= $themeSettings['tracking_head_code'] ?? '' ?>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title) ?> — <?= htmlspecialchars($themeSettings['site_name'] ?? 'Mi Sitio') ?></title>
  <?= robotsMetaTag($themeSettings) ?>
  <meta name="description" content="<?= htmlspecialchars($seoDesc ?: '') ?>">
  <meta name="keywords" content="<?= htmlspecialchars($seoKeywords ?: '') ?>">
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
  <link rel="stylesheet" href="/assets/css/components/project-detail.css">
  <link rel="stylesheet" href="/assets/css/components/footer.css">
  <link rel="stylesheet" href="/assets/css/components/contact-drawer.css">
  <link rel="stylesheet" href="/assets/css/components/cookie-banner.css">
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "CreativeWork",
    "name": <?= json_encode($title, JSON_UNESCAPED_UNICODE) ?>,
    "description": <?= json_encode($seoDesc ?: $excerpt ?: '', JSON_UNESCAPED_UNICODE) ?>,
    "image": "<?= getSiteDomain($themeSettings) ?>/uploads/<?= variantFromThumbFilename($project['main_image'], 'desktop') ?>",
    "url": "<?= getSiteDomain($themeSettings) ?><?= $locale === 'en' && $enUrl ? $enUrl : $esUrl ?>",
    "dateCreated": "<?= htmlspecialchars($project['project_date'] ?: substr($project['created_at'], 0, 10)) ?>",
    "author": { "@type": "Person", "name": "<?= addslashes($themeSettings['site_name'] ?? 'Mi Sitio') ?>", "url": "<?= getSiteDomain($themeSettings) ?><?= localeHomeUrl($locale) ?>" }
  }
  </script>
  <?php if ($projectCategory): ?>
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
      { "@type": "ListItem", "position": 1, "name": "<?= addslashes($themeSettings['site_name'] ?? 'Mi Sitio') ?>", "item": "<?= getSiteDomain($themeSettings) ?><?= localeHomeUrl($locale) ?>" },
      { "@type": "ListItem", "position": 2, "name": <?= json_encode($projectCategory['title'], JSON_UNESCAPED_UNICODE) ?>, "item": "<?= getSiteDomain($themeSettings) ?><?= htmlspecialchars(categoryUrl($projectCategory, $locale)) ?>" },
      { "@type": "ListItem", "position": 3, "name": <?= json_encode($title, JSON_UNESCAPED_UNICODE) ?> }
    ]
  }
  </script>
  <?php endif; ?>
</head>
<body class="project-detail-page">
  <?= $themeSettings['tracking_body_code'] ?? '' ?>

  <header class="project-hero" style="background-image:url('/uploads/<?= htmlspecialchars(variantFromThumbFilename($project['main_image'], 'desktop')) ?>')">
    <div class="project-hero__overlay" aria-hidden="true"></div>

    <?php
      $navOnHero = true;
      // fix: el toggle de idioma solo se muestra si existe traducción real de este proyecto
      $langSwitchUrl = $enUrl ? ($locale === 'es' ? $enUrl : $esUrl) : null;
      include __DIR__ . '/../src/templates/nav.php';
    ?>

    <div class="project-hero__content">
      <h1><?= htmlspecialchars($title) ?></h1>
      <?php include __DIR__ . '/../src/templates/language-badge.php'; ?>
      <?php if ($excerpt): ?>
        <p class="project-hero__excerpt"><?= htmlspecialchars($excerpt) ?></p>
      <?php endif; ?>
    </div>
  </header>

  <main id="main-content" class="project-detail">
    <?php if ($projectCategory): ?>
      <nav class="breadcrumb" aria-label="<?= $locale === 'en' ? 'Breadcrumb' : 'Migas de pan' ?>">
        <a href="<?= localeHomeUrl($locale) ?>"><?= htmlspecialchars($themeSettings['site_name'] ?? 'Mi Sitio') ?></a>
        <span aria-hidden="true">/</span>
        <a href="<?= htmlspecialchars(categoryUrl($projectCategory, $locale)) ?>"><?= htmlspecialchars($projectCategory['title']) ?></a>
        <span aria-hidden="true">/</span>
        <span aria-current="page"><?= htmlspecialchars($title) ?></span>
      </nav>
    <?php endif; ?>

    <div class="project-detail__content"><?= $content /* HTML enriquecido de Quill, ya sanitizado al guardar */ ?></div>

    <?php if ($galleryImages): ?>
      <div class="project-gallery-grid">
        <?php foreach ($galleryImages as $img):
          $alt = ($locale === 'en' ? $img['alt_en'] : $img['alt_es']) ?: $title;
        ?>
          <button type="button" class="project-gallery-grid__item"
                  data-lightbox-trigger
                  data-full="/uploads/<?= htmlspecialchars(variantFromThumbFilename($img['image_path'], 'desktop')) ?>"
                  data-alt="<?= htmlspecialchars($alt) ?>">
            <img src="/uploads/<?= htmlspecialchars(variantFromThumbFilename($img['image_path'], 'mobile')) ?>"
                 srcset="<?= htmlspecialchars(fullSrcsetFromThumbFilename($img['image_path'])) ?>"
                 sizes="(max-width: 768px) 50vw, 25vw"
                 alt="<?= htmlspecialchars($alt) ?>"
                 loading="lazy">
          </button>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($nextProject): ?>
      <?php
        $nextTitle = ($locale === 'en' && $nextProject['title_en']) ? $nextProject['title_en'] : $nextProject['title_es'];
      ?>
      <a class="project-detail__next" href="<?= htmlspecialchars(projectUrl($nextProject, $locale)) ?>">
        <?= $locale === 'en' ? 'Next project' : 'Siguiente proyecto' ?> — <?= htmlspecialchars($nextTitle) ?> &rarr;
      </a>
    <?php endif; ?>
  </main>

  <div id="lightbox" class="lightbox" role="dialog" aria-modal="true" aria-label="Visor de imagen" hidden>
    <button type="button" class="lightbox__close" data-lightbox-close aria-label="Cerrar">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
    </button>
    <button type="button" class="lightbox__nav lightbox__nav--prev" data-lightbox-prev aria-label="<?= t('a11y.prev') ?>">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
    </button>
    <img id="lightbox-image" class="lightbox__image" src="" alt="">
    <button type="button" class="lightbox__nav lightbox__nav--next" data-lightbox-next aria-label="<?= t('a11y.next') ?>">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
    </button>
  </div>

  <?php include __DIR__ . '/../src/templates/footer.php'; ?>
  <script type="module" src="/assets/js/main.js"></script>
</body>
</html>
