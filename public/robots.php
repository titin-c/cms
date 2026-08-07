<?php
require_once __DIR__ . '/../src/lib/db.php';
require_once __DIR__ . '/../src/lib/theme.php';

header('Content-Type: text/plain; charset=utf-8');
$pdo = getDb();
$themeSettings = getSiteSettings($pdo);
$base = getSiteDomain($themeSettings);

echo "User-agent: *\n";
echo "Allow: /\n";
echo "Disallow: /admin/\n";
echo "Disallow: /api/\n";
echo "\n";
echo "Sitemap: {$base}/sitemap.xml\n";
