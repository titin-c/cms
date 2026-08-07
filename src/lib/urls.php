<?php
/**
 * fix (Andrea, SEO): rutas con el segmento traducido al inglés
 * (/project/, /category/) y el slug también traducido, en vez de /en/proyecto/.
 * Si el contenido no tiene slug en inglés, la URL cae directamente a la ruta
 * en español — nunca se genera un enlace roto a una traducción inexistente.
 */

function projectUrl(array $project, string $locale): string {
    if ($locale === 'en' && !empty($project['slug_en'])) {
        return '/project/' . rawurlencode($project['slug_en']);
    }
    return '/proyecto/' . rawurlencode($project['slug']);
}

function categoryUrl(array $category, string $locale): string {
    if ($locale === 'en' && !empty($category['slug_en'])) {
        return '/category/' . rawurlencode($category['slug_en']);
    }
    return '/categoria/' . rawurlencode($category['slug']);
}

/**
 * fix (Andrea): páginas fijas dinámicas (Sobre mí, legales, y cualquier
 * página nueva creada desde /admin/paginas.php) — ruta plana /slug en
 * español, con prefijo /en/slug en inglés (evita cualquier colisión con
 * otras rutas del sitio, ver public/.htaccess).
 */
function pageUrl(array $page, string $locale): string {
    if ($locale === 'en' && !empty($page['slug_en'])) {
        return '/en/' . rawurlencode($page['slug_en']);
    }
    return '/' . rawurlencode($page['slug']);
}
