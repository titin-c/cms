<?php
/**
 * Sitemap dinámico. Se regenera en cada petición a partir del contenido
 * publicado — no requiere mantenimiento manual al añadir proyectos.
 * Accesible en /sitemap.xml (ver rewrite en .htaccess).
 */
require_once __DIR__ . '/../src/lib/db.php';
require_once __DIR__ . '/../src/lib/urls.php';
require_once __DIR__ . '/../src/lib/theme.php';

header('Content-Type: application/xml; charset=utf-8');
$pdo = getDb();
$themeSettings = getSiteSettings($pdo);
$base = getSiteDomain($themeSettings);

$projects = $pdo->query("
    SELECT slug, slug_en, title_en, content_en, updated_at
    FROM projects WHERE status = 'published'
")->fetchAll();

$contentPages = $pdo->query("SELECT slug, slug_en, updated_at FROM content_pages")->fetchAll();

$categories = $pdo->query("
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
")->fetchAll();

function urlEntry(string $loc, ?string $altLoc, string $lastmod = ''): string {
    $xml = "  <url>\n    <loc>{$loc}</loc>\n";
    if ($altLoc) {
        $xml .= "    <xhtml:link rel=\"alternate\" hreflang=\"es\" href=\"{$loc}\" />\n";
        $xml .= "    <xhtml:link rel=\"alternate\" hreflang=\"en\" href=\"{$altLoc}\" />\n";
        $xml .= "    <xhtml:link rel=\"alternate\" hreflang=\"x-default\" href=\"{$loc}\" />\n";
    }
    if ($lastmod) {
        $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
    }
    $xml .= "  </url>\n";
    return $xml;
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
<?php
echo urlEntry("$base/", "$base/en/");

foreach ($contentPages as $page) {
    $enUrl = !empty($page['slug_en']) ? $base . '/en/' . rawurlencode($page['slug_en']) : null;
    $lastmod = date('Y-m-d', strtotime($page['updated_at']));
    echo urlEntry($base . '/' . rawurlencode($page['slug']), $enUrl, $lastmod);
}

foreach ($categories as $cat) {
    $enUrl = !empty($cat['slug_en']) ? $base . '/category/' . rawurlencode($cat['slug_en']) : null;
    echo urlEntry($base . '/categoria/' . rawurlencode($cat['slug']), $enUrl);
}

foreach ($projects as $p) {
    $hasTranslation = !empty($p['title_en']) && !empty($p['content_en']) && !empty($p['slug_en']);
    $enUrl = $hasTranslation ? $base . '/project/' . rawurlencode($p['slug_en']) : null;
    $lastmod = date('Y-m-d', strtotime($p['updated_at']));
    echo urlEntry($base . '/proyecto/' . rawurlencode($p['slug']), $enUrl, $lastmod);
}
?>
</urlset>
