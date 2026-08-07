<?php
require_once __DIR__ . '/urls.php';

/**
 * Páginas fijas gestionables desde /admin/paginas.php — crear, editar y
 * eliminar libremente, con control de si aparecen en el menú de cabecera,
 * en el footer, en ambos o en ninguno.
 */

/** Busca una página por su slug en español o en inglés (según cómo llegó la URL, ver .htaccess). */
function fetchContentPageBySlug(PDO $pdo, string $slug, string $locale): ?array {
    if ($locale === 'en') {
        $stmt = $pdo->prepare("SELECT * FROM content_pages WHERE slug_en = ? LIMIT 1");
        $stmt->execute([$slug]);
        $page = $stmt->fetch();
        if ($page) return localizeContentPage($page, $locale);

        // fix: fallback — si la página no tiene slug en inglés propio pero
        // alguien llega a /en/{slug-es} igualmente, no lo dejamos en 404 seco
        $stmt = $pdo->prepare("SELECT * FROM content_pages WHERE slug = ? LIMIT 1");
        $stmt->execute([$slug]);
        $page = $stmt->fetch();
        return $page ? localizeContentPage($page, $locale) : null;
    }

    $stmt = $pdo->prepare("SELECT * FROM content_pages WHERE slug = ? LIMIT 1");
    $stmt->execute([$slug]);
    $page = $stmt->fetch();
    return $page ? localizeContentPage($page, $locale) : null;
}

function localizeContentPage(array $page, string $locale): array {
    return [
        'id' => $page['id'],
        'slug' => $page['slug'],
        'slug_en' => $page['slug_en'],
        'title' => ($locale === 'en' && $page['title_en']) ? $page['title_en'] : $page['title_es'],
        'content' => ($locale === 'en' && $page['content_en']) ? $page['content_en'] : $page['content_es'],
        'meta_description' => ($locale === 'en' && $page['meta_description_en']) ? $page['meta_description_en'] : $page['meta_description_es'],
    ];
}

/** fix: aviso visible mientras el contenido siga teniendo datos [ENTRE CORCHETES] sin rellenar */
function hasPlaceholderBrackets(string $content): bool {
    return str_contains($content, '[') && str_contains($content, ']');
}

/**
 * Páginas a mostrar en el menú de cabecera o en el footer, ya localizadas
 * (título + URL) y en el orden configurado desde el panel.
 */
function fetchMenuPages(PDO $pdo, string $locale, string $location): array {
    $column = $location === 'header' ? 'show_in_header' : 'show_in_footer';
    $rows = $pdo->query("SELECT * FROM content_pages WHERE {$column} = 1 ORDER BY sort_order ASC, id ASC")->fetchAll();

    return array_map(function ($page) use ($locale) {
        $localized = localizeContentPage($page, $locale);
        $localized['url'] = pageUrl($page, $locale);
        return $localized;
    }, $rows);
}

/**
 * Categorías marcadas como "aparece en el header/footer" (fix Andrea) — se
 * listan junto a las páginas fijas en nav.php/footer.php.
 */
function fetchMenuCategories(PDO $pdo, string $locale, string $location): array {
    $column = $location === 'header' ? 'show_in_header' : 'show_in_footer';
    $localeCol = $locale === 'en' ? "COALESCE(title_en, title_es)" : "title_es";
    $rows = $pdo->query("
        SELECT id, slug, slug_en, {$localeCol} AS title
        FROM categories
        WHERE {$column} = 1 AND status = 'published' AND is_default_uncategorized = 0
        ORDER BY sort_order ASC, id ASC
    ")->fetchAll();

    return array_map(function ($cat) use ($locale) {
        $cat['url'] = categoryUrl($cat, $locale);
        return $cat;
    }, $rows);
}
