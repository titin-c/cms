<?php
/**
 * Sitemap dinámico. Se regenera en cada petición a partir del contenido
 * publicado — no requiere mantenimiento manual al añadir proyectos.
 * Accesible en /sitemap.xml (ver rewrite en .htaccess).
 *
 * fix (seo-agent [audit]): incluye ya /videos y /categorias (antes
 * invisibles al rastreador), y una extensión de imagen por proyecto — para
 * un portfolio fotográfico, Google Images es una fuente de tráfico real.
 */
require_once __DIR__ . '/../src/lib/db.php';
require_once __DIR__ . '/../src/lib/urls.php';
require_once __DIR__ . '/../src/lib/theme.php';
require_once __DIR__ . '/../src/lib/image.php';

header('Content-Type: application/xml; charset=utf-8');
$pdo = getDb();
$themeSettings = getSiteSettings($pdo);
$base = getSiteDomain($themeSettings);
$projectsEnabled = ($themeSettings['module_projects_enabled'] ?? '1') === '1';
$videosEnabled = ($themeSettings['module_videos_enabled'] ?? '1') === '1';

$projects = $projectsEnabled ? $pdo->query("
    SELECT slug, slug_en, title_en, content_en, updated_at, main_image, title_es
    FROM projects WHERE status = 'published'
")->fetchAll() : [];

$contentPages = $pdo->query("SELECT slug, slug_en, updated_at FROM content_pages")->fetchAll();

$categories = $projectsEnabled ? $pdo->query("
    SELECT c.slug, c.slug_en
    FROM categories c
    WHERE c.is_default_uncategorized = 0
      AND EXISTS (
        SELECT 1 FROM projects p
        WHERE p.status = 'published'
          AND (p.category_id = c.id OR EXISTS (
            SELECT 1 FROM project_extra_categories pec WHERE pec.project_id = p.id AND pec.category_id = c.id
          ))
      )
")->fetchAll() : [];

/**
 * fix (seo-agent [audit] 🔴): antes solo se generaba UNA entrada <url> (la
 * de español) con el inglés metido como simple anotación "alternate" — eso
 * significa que la URL en inglés nunca aparecía como <loc> propio en
 * ningún sitio del sitemap, reduciendo sus opciones reales de indexarse.
 * El formato correcto de Google exige una entrada <url> COMPLETA por cada
 * idioma disponible, cada una apuntándose a sí misma y a las demás.
 */
function sitemapEntries(string $esLoc, ?string $enLoc, string $lastmod = '', ?string $imageLoc = null): string {
    $alternates = "    <xhtml:link rel=\"alternate\" hreflang=\"es\" href=\"{$esLoc}\" />\n";
    if ($enLoc) {
        $alternates .= "    <xhtml:link rel=\"alternate\" hreflang=\"en\" href=\"{$enLoc}\" />\n";
    }
    $alternates .= "    <xhtml:link rel=\"alternate\" hreflang=\"x-default\" href=\"{$esLoc}\" />\n";

    $entry = function (string $loc) use ($alternates, $lastmod, $imageLoc) {
        $xml = "  <url>\n    <loc>{$loc}</loc>\n{$alternates}";
        if ($lastmod) { $xml .= "    <lastmod>{$lastmod}</lastmod>\n"; }
        if ($imageLoc) { $xml .= "    <image:image>\n      <image:loc>{$imageLoc}</image:loc>\n    </image:image>\n"; }
        $xml .= "  </url>\n";
        return $xml;
    };

    $xml = $entry($esLoc);
    if ($enLoc) {
        $xml .= $entry($enLoc); // fix: ahora el inglés SÍ tiene su propia entrada <loc>
    }
    return $xml;
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
<?php
echo sitemapEntries("$base/", "$base/en/");

foreach ($contentPages as $page) {
    $enUrl = !empty($page['slug_en']) ? $base . '/en/' . rawurlencode($page['slug_en']) : null;
    $lastmod = date('Y-m-d', strtotime($page['updated_at']));
    echo sitemapEntries($base . '/' . rawurlencode($page['slug']), $enUrl, $lastmod);
}

if ($projectsEnabled) {
    // fix (seo-agent [audit] 🔴): antes ausentes del sitemap
    $categoriesSlugEs = $themeSettings['categories_slug_es'] ?: 'categorias';
    $categoriesSlugEn = $themeSettings['categories_slug_en'] ?: 'categories';
    echo sitemapEntries("$base/" . rawurlencode($categoriesSlugEs), "$base/en/" . rawurlencode($categoriesSlugEn));

    foreach ($categories as $cat) {
        $enUrl = !empty($cat['slug_en']) ? $base . '/category/' . rawurlencode($cat['slug_en']) : null;
        echo sitemapEntries($base . '/categoria/' . rawurlencode($cat['slug']), $enUrl);
    }

    foreach ($projects as $p) {
        $hasTranslation = !empty($p['title_en']) && !empty($p['content_en']) && !empty($p['slug_en']);
        $enUrl = $hasTranslation ? $base . '/project/' . rawurlencode($p['slug_en']) : null;
        $lastmod = date('Y-m-d', strtotime($p['updated_at']));
        // fix (seo-agent [audit] 🟠): variante desktop = mayor resolución disponible, mejor para Google Images
        $imageLoc = $p['main_image'] ? $base . '/uploads/' . rawurlencode(variantFromThumbFilename($p['main_image'], 'desktop')) : null;
        echo sitemapEntries($base . '/proyecto/' . rawurlencode($p['slug']), $enUrl, $lastmod, $imageLoc);
    }
}

if ($videosEnabled) {
    // fix (seo-agent [audit] 🔴): antes ausente del sitemap
    $videosSlugEs = $themeSettings['videos_slug_es'] ?: 'videos';
    $videosSlugEn = $themeSettings['videos_slug_en'] ?: 'videos';
    echo sitemapEntries("$base/" . rawurlencode($videosSlugEs), "$base/en/" . rawurlencode($videosSlugEn));
}
?>
</urlset>
