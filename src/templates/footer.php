<?php
/* Footer minimalista — enlaces legales + principales */
require_once __DIR__ . '/../lib/content_pages.php';
require_once __DIR__ . '/../lib/social_icons.php';
$__locale = $GLOBALS['__locale'] ?? 'es';
$__themeSettings = $themeSettings ?? (isset($pdo) && $pdo instanceof PDO ? getSiteSettings($pdo) : []);
$__siteName = $__themeSettings['site_name'] ?? 'Mi Sitio';

// fix (Andrea): antes Sobre mí + las 3 legales eran enlaces fijos — ahora el
// footer muestra las páginas marcadas como "aparece en el footer" desde
// /admin/paginas.php, en el orden que configures ahí.
$__footerPages = (isset($pdo) && $pdo instanceof PDO) ? fetchMenuPages($pdo, $__locale, 'footer') : [];
$__footerCategories = (isset($pdo) && $pdo instanceof PDO) ? fetchMenuCategories($pdo, $__locale, 'footer') : [];
$__socialLinksFooter = (isset($pdo) && $pdo instanceof PDO) ? getSocialLinks($pdo) : [];
?>
<footer class="site-footer">
  <span class="site-footer__copy">&copy; <?= date('Y') ?> <?= htmlspecialchars($__siteName) ?> — <?= t('footer.rights') ?></span>
  <nav class="site-footer__links" aria-label="Legal">
    <a href="#contact-drawer" data-open-contact><?= t('nav.contact') ?></a>
    <?php foreach ($__footerCategories as $footerCat): ?>
      <a href="<?= htmlspecialchars($footerCat['url']) ?>"><?= htmlspecialchars($footerCat['title']) ?></a>
    <?php endforeach; ?>
    <?php foreach ($__footerPages as $footerPage): ?>
      <a href="<?= htmlspecialchars($footerPage['url']) ?>"><?= htmlspecialchars($footerPage['title']) ?></a>
    <?php endforeach; ?>
    <?php if (($__themeSettings['categories_show_in_footer'] ?? '0') === '1'):
      $footerCategoriesLabel = $__locale === 'en'
          ? ($__themeSettings['categories_h1_en'] ?: $__themeSettings['categories_h1_es'] ?: 'Categories')
          : ($__themeSettings['categories_h1_es'] ?: 'Categorías');
      $footerCategoriesSlug = $__locale === 'en' ? ($__themeSettings['categories_slug_en'] ?? 'categories') : ($__themeSettings['categories_slug_es'] ?? 'categorias');
    ?>
      <a href="<?= $__locale === 'en' ? '/en/' . rawurlencode($footerCategoriesSlug) : '/' . rawurlencode($footerCategoriesSlug) ?>"><?= htmlspecialchars($footerCategoriesLabel) ?></a>
    <?php endif; ?>
    <?php if (($__themeSettings['videos_show_in_footer'] ?? '0') === '1'): ?>
      <a href="<?= $__locale === 'en' ? '/en/' . rawurlencode($__themeSettings['videos_slug_en'] ?? 'videos') : '/' . rawurlencode($__themeSettings['videos_slug_es'] ?? 'videos') ?>"><?= $__locale === 'en' ? 'Videos' : 'Vídeos' ?></a>
    <?php endif; ?>
    <a href="#" data-open-cookie-settings><?= t('footer.cookie_prefs') ?></a>
    <?php foreach ($__socialLinksFooter as $social): ?>
      <a href="<?= htmlspecialchars($social['url']) ?>" aria-label="<?= htmlspecialchars(SOCIAL_PLATFORMS[$social['platform']] ?? $social['platform']) ?>" target="_blank" rel="noopener">
        <?= socialIconSvg($social['platform'], 14) ?>
      </a>
    <?php endforeach; ?>
  </nav>
</footer>

<?php include __DIR__ . '/contact-drawer.php'; ?>
<?php include __DIR__ . '/cookie-banner.php'; ?>
