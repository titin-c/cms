<?php
require_once __DIR__ . '/../lib/content_pages.php';
require_once __DIR__ . '/../lib/social_icons.php';

/**
 * Nav unificada — usada en TODAS las páginas públicas.
 *
 * Variables esperadas del caller:
 * - $navOnHero (bool, opcional): true = transparente sobre imagen oscura.
 * - $navOverlayStandalone (bool, opcional): true = el propio nav.php se
 *   posiciona flotando sobre lo primero que venga después (usado cuando el
 *   módulo Mosaico de proyectos es el primero de la home y hace de "hero" —
 *   el Hero de siempre no lo necesita, ya que se posiciona a sí mismo).
 * - $langSwitchUrl (string|null, opcional): URL de la versión alternativa de
 *   idioma de ESTA página. Si es null, no se muestra el toggle.
 * - $themeSettings (array): ya cargado por el caller antes de incluir esto.
 */
$navOnHero = $navOnHero ?? false;
$navOverlayStandalone = $navOverlayStandalone ?? false;
$locale = $GLOBALS['__locale'] ?? 'es';
$themeSettings = $themeSettings ?? (isset($pdo) && $pdo instanceof PDO ? getSiteSettings($pdo) : []);

$headerPages = (isset($pdo) && $pdo instanceof PDO) ? fetchMenuPages($pdo, $locale, 'header') : [];
$socialLinksNav = (isset($pdo) && $pdo instanceof PDO) ? getSocialLinks($pdo) : [];
$showLangMenu = ($themeSettings['show_language_menu'] ?? '1') === '1';
$siteName = $themeSettings['site_name'] ?? 'Mi Sitio';
$siteSubtitle = $locale === 'en'
    ? ($themeSettings['site_subtitle_en'] ?? $themeSettings['site_subtitle_es'] ?? '')
    : ($themeSettings['site_subtitle_es'] ?? '');
?>
<header class="site-nav <?= $navOnHero ? 'site-nav--on-hero' : 'site-nav--solid' ?><?= $navOverlayStandalone ? ' site-nav--overlay-standalone' : '' ?>">
  <a href="<?= localeHomeUrl($locale) ?>" class="site-nav__brand-block">
    <span class="site-nav__brand-block-name"><?= htmlspecialchars($siteName) ?></span>
    <span class="site-nav__brand-block-tagline"><?= htmlspecialchars($siteSubtitle) ?></span>
  </a>

  <!-- fix (Andrea): botón de menú, visible solo en móvil — abre .site-nav__actions como panel desplegable -->
  <button type="button" class="site-nav__toggle" id="site-nav-toggle" aria-expanded="false" aria-controls="site-nav-actions" aria-label="<?= $locale === 'en' ? 'Open menu' : 'Abrir menú' ?>">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
  </button>

  <div class="site-nav__actions" id="site-nav-actions">
    <?php
      // fix (Andrea): cada categoría elige su ubicación (direct/submenu/none)
      // — más libre que un simple sí/no, permite tener categorías fuera del submenú
      $directCategories = [];
      $submenuCategories = [];
      if (isset($pdo) && $pdo instanceof PDO) {
          $localeColCat = $locale === 'en' ? "COALESCE(title_en, title_es)" : "title_es";
          $catsStmt = $pdo->prepare("
              SELECT id, slug, slug_en, header_placement, {$localeColCat} AS title
              FROM categories
              WHERE status = 'published' AND is_default_uncategorized = 0 AND header_placement != 'none'
              ORDER BY sort_order ASC, id ASC
          ");
          $catsStmt->execute();
          foreach ($catsStmt->fetchAll() as $cat) {
              if ($cat['header_placement'] === 'direct') { $directCategories[] = $cat; }
              else { $submenuCategories[] = $cat; }
          }
      }
    ?>
    <?php foreach ($directCategories as $directCat): ?>
      <a href="<?= htmlspecialchars(categoryUrl($directCat, $locale)) ?>" class="site-nav__link"><?= htmlspecialchars($directCat['title']) ?></a>
    <?php endforeach; ?>
    <?php if (($themeSettings['categories_show_in_header'] ?? '0') === '1' && !empty($submenuCategories)):
      $categoriesLabel = $locale === 'en'
          ? ($themeSettings['categories_h1_en'] ?: $themeSettings['categories_h1_es'] ?: 'Categories')
          : ($themeSettings['categories_h1_es'] ?: 'Categorías');
      $categoriesSlug = $locale === 'en' ? ($themeSettings['categories_slug_en'] ?? 'categories') : ($themeSettings['categories_slug_es'] ?? 'categorias');
      $categoriesUrl = $locale === 'en' ? '/en/' . rawurlencode($categoriesSlug) : '/' . rawurlencode($categoriesSlug);
    ?>
      <div class="site-nav__dropdown">
        <button type="button" class="site-nav__link site-nav__dropdown-trigger" aria-expanded="false" aria-haspopup="true">
          <?= htmlspecialchars($categoriesLabel) ?>
          <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
        </button>
        <div class="site-nav__dropdown-menu">
          <a href="<?= htmlspecialchars($categoriesUrl) ?>" class="site-nav__dropdown-item site-nav__dropdown-item--all"><?= htmlspecialchars($categoriesLabel) ?></a>
          <?php foreach ($submenuCategories as $subCat): ?>
            <a href="<?= htmlspecialchars(categoryUrl($subCat, $locale)) ?>" class="site-nav__dropdown-item"><?= htmlspecialchars($subCat['title']) ?></a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
    <?php foreach ($headerPages as $navPage): ?>
      <a href="<?= htmlspecialchars($navPage['url']) ?>" class="site-nav__link"><?= htmlspecialchars($navPage['title']) ?></a>
    <?php endforeach; ?>
    <?php if (($themeSettings['videos_show_in_header'] ?? '0') === '1'): ?>
      <a href="<?= $locale === 'en' ? '/en/' . rawurlencode($themeSettings['videos_slug_en'] ?? 'videos') : '/' . rawurlencode($themeSettings['videos_slug_es'] ?? 'videos') ?>" class="site-nav__link"><?= $locale === 'en' ? 'Videos' : 'Vídeos' ?></a>
    <?php endif; ?>
    <a href="#contact-drawer" class="site-nav__link" data-open-contact><?= t('nav.contact') ?></a>

    <?php if (($themeSettings['header_show_social'] ?? '1') === '1'): ?>
      <div class="site-nav__socials">
        <?php foreach ($socialLinksNav as $social): ?>
          <a href="<?= htmlspecialchars($social['url']) ?>" class="site-nav__icon" aria-label="<?= htmlspecialchars(SOCIAL_PLATFORMS[$social['platform']] ?? $social['platform']) ?>" target="_blank" rel="noopener">
            <?= socialIconSvg($social['platform']) ?>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($showLangMenu && !empty($langSwitchUrl)): ?>
      <a href="<?= htmlspecialchars($langSwitchUrl) ?>" class="site-nav__lang">
        <span class="<?= $locale === 'es' ? 'is-active' : '' ?>">ES</span> / <span class="<?= $locale === 'en' ? 'is-active' : '' ?>">EN</span>
      </a>
    <?php endif; ?>
  </div>
</header>
