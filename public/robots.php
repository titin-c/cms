<?php
require_once __DIR__ . '/../src/lib/db.php';
require_once __DIR__ . '/../src/lib/theme.php';

header('Content-Type: text/plain; charset=utf-8');
$pdo = getDb();
$themeSettings = getSiteSettings($pdo);
$base = getSiteDomain($themeSettings);

// fix (Andrea, web en construcción): con noindex global o "Próximamente"
// activos, se bloquea el rastreo de todo el sitio, no solo /admin y /api.
$siteWide = ($themeSettings['site_noindex'] ?? '0') === '1' || ($themeSettings['site_coming_soon'] ?? '0') === '1';

echo "User-agent: *\n";
if ($siteWide) {
    echo "Disallow: /\n";
} else {
    echo "Allow: /\n";
    echo "Disallow: /admin/\n";
    echo "Disallow: /api/\n";
    echo "\n";
    echo "Sitemap: {$base}/sitemap.xml\n";
}
