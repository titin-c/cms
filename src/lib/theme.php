<?php
/** Ajustes de tema editables desde /admin/ajustes.php, con fallback a los valores originales del proyecto. */

function getSiteSettings(PDO $pdo): array {
    $defaults = [
        'font_content' => 'Playfair Display',
        'font_ui' => 'Inter',
        'color_primary' => '#0A0A0A',
        'color_secondary' => '#0A0A0A',
        'color_surface' => '#FFFFFF',
        'separator_style' => 'line', // 'line' | 'none'
        'separator_size' => '24',    // px — hueco antes de la línea en header/footer/filas de categoría
        'grid_columns' => '4',       // miniaturas por fila en desktop grande (tablet/móvil se reducen solos)
        'grid_gap' => '24',          // px — hueco entre miniaturas de cualquier grid del sitio

        // fix (Andrea): página agregada /categorias (listado de todas las
        // categorías), mismo patrón que /videos — menús, SEO, y meta title
        // (que faltaba también en vídeos, añadido a la vez)
        'categories_show_in_header' => '0',
        'categories_show_in_footer' => '0',
        'categories_slug_es' => 'categorias',
        'categories_slug_en' => 'categories',
        'categories_h1_es' => '',
        'categories_h1_en' => '',
        'categories_description_es' => '',
        'categories_description_en' => '',
        'categories_meta_title_es' => '',
        'categories_meta_title_en' => '',
        'categories_meta_description_es' => '',
        'categories_meta_description_en' => '',

        'videos_meta_title_es' => '',
        'videos_meta_title_en' => '',
        'site_name' => 'Mi Sitio',
        'site_subtitle_es' => '',
        'site_subtitle_en' => '',
        'site_domain' => '', // vacío = se autodetecta del propio servidor, ver getSiteDomain()
        'contact_email' => '',
        'contact_phone' => '',
        'show_language_menu' => '1',

        // fix (Andrea, CMS multi-cliente): la home se compone de 3 módulos
        // independientes y combinables — hero, categorías de proyectos, y
        // vídeos. Cualquier combinación es válida (incluso ninguno).
        'home_show_hero' => '1',
        'home_show_categories' => '1',
        'home_show_videos' => '0',
        'home_show_simple' => '0',
        'home_simple_image_mode' => 'fixed', // 'fixed' | 'random_featured'
        'home_simple_title_es' => '',
        'home_simple_title_en' => '',
        'home_simple_description_es' => '',
        'home_simple_description_en' => '',
        'home_simple_image' => '',
        'home_simple_image_alt' => '',
        'home_title_es' => '',
        'home_title_en' => '',
        'home_meta_description_es' => '',
        'home_meta_description_en' => '',

        // fix (CMS multi-cliente): módulos activables/desactivables — al
        // desactivar uno, desaparece del menú del admin para ese cliente.
        'module_projects_enabled' => '1',
        'module_videos_enabled' => '1',
        'module_pages_enabled' => '1',
        'videos_show_in_header' => '0',
        'videos_show_in_footer' => '0',
        'videos_slug_es' => 'videos',
        'videos_slug_en' => 'videos',
        'videos_h1_es' => '',
        'videos_h1_en' => '',
        'videos_description_es' => '',
        'videos_description_en' => '',
        'videos_meta_description_es' => '',
        'videos_meta_description_en' => '',
    ];

    $rows = $pdo->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll();
    foreach ($rows as $row) {
        if ($row['setting_value'] !== null && $row['setting_value'] !== '') {
            $defaults[$row['setting_key']] = $row['setting_value'];
        }
    }
    return $defaults;
}

/**
 * Dominio absoluto para URLs de SEO (canonical, hreflang, sitemap, JSON-LD).
 * fix (CMS genérico): si no se configura uno explícito, se autodetecta a
 * partir de la petición real — así funciona igual de bien en local, en
 * cualquier hosting, o tras un dominio personalizado, sin tocar código.
 */
function getSiteDomain(array $settings): string {
    if (!empty($settings['site_domain'])) {
        return rtrim($settings['site_domain'], '/');
    }
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return "{$scheme}://{$host}";
}

function getSocialLinks(PDO $pdo): array {
    return $pdo->query("SELECT * FROM social_links ORDER BY sort_order ASC, id ASC")->fetchAll();
}

/**
 * URL de Google Fonts para las dos tipografías elegidas, cargando los pesos
 * necesarios: 400/500/700 para la de UI, 400/700 para la de títulos/texto.
 */
function buildThemeFontsUrl(array $settings): string {
    $content = str_replace(' ', '+', $settings['font_content']);
    $ui = str_replace(' ', '+', $settings['font_ui']);
    return "https://fonts.googleapis.com/css2?family={$content}:wght@400;700&family={$ui}:wght@400;500;700&display=swap";
}

/** Bloque <style> con las variables CSS de tokens.css sobreescritas según los ajustes elegidos. */
function renderThemeOverridesStyleTag(array $settings): string {
    require_once __DIR__ . '/colors.php';

    $primaryScale = generateAaaColorScale($settings['color_primary'], $settings['color_surface']);
    $surfaceAlt = deriveSurfaceAlt($settings['color_surface']);
    [$h, $s, $l] = hexToHsl($settings['color_secondary']);
    $accentHover = hslToHex($h, $s, max(0, $l - 12)); // tono más oscuro del secundario, para hover/active

    $contentFont = addslashes($settings['font_content']);
    $uiFont = addslashes($settings['font_ui']);

    // fix (Andrea): separadores del header/footer/filas de categoría — con o
    // sin línea, y el hueco antes de ella, configurables desde Ajustes → Estilos
    $separatorBorder = ($settings['separator_style'] ?? 'line') === 'none'
        ? 'none'
        : "1px solid {$primaryScale[100]}";
    $separatorGap = (int) ($settings['separator_size'] ?? 24) . 'px';
    $gridColumns = (int) ($settings['grid_columns'] ?? 4);
    $gridGap = (int) ($settings['grid_gap'] ?? 24) . 'px';
    $gridGap = (int) ($settings['grid_gap'] ?? 24) . 'px';
    $gridColumns = max(1, (int) ($settings['grid_columns'] ?? 4));

    return <<<CSS
    <style>
      :root {
        --font-display: '{$contentFont}', serif;
        --font-body: '{$contentFont}', serif;
        --font-ui: '{$uiFont}', sans-serif;

        --color-ink-900: {$primaryScale[900]};
        --color-ink-700: {$primaryScale[700]};
        --color-ink-500: {$primaryScale[500]};
        --color-ink-300: {$primaryScale[300]};
        --color-ink-100: {$primaryScale[100]};

        --color-surface: {$settings['color_surface']};
        --color-surface-alt: {$surfaceAlt};

        --color-accent: {$settings['color_secondary']};
        --color-accent-hover: {$accentHover};
        --color-focus: {$settings['color_secondary']};

        --separator-border: {$separatorBorder};
        --separator-gap: {$separatorGap};

        --grid-gap: {$gridGap};
        --grid-columns: {$gridColumns};
      }
    </style>
    CSS;
}
