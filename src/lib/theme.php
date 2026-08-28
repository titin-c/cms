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
        'header_show_social' => '1',
        'footer_show_social' => '1',

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

        // fix (Andrea, web en construcción): dos interruptores independientes —
        // noindex (la web se ve, pero no se indexa) y "Próximamente" (sustituye
        // toda la web pública por una página de aviso; /admin sigue funcionando
        // siempre). Se pueden activar por separado o los dos a la vez.
        'site_noindex' => '0',
        'site_coming_soon' => '0',
        'coming_soon_message_es' => '',
        'coming_soon_message_en' => '',

        // fix (Andrea): fondo del Hero configurable — 'mosaic' (el de siempre),
        // 'random_photo' (una foto destacada al azar en cada visita, con el
        // mismo overlay oscuro que el mosaico) o 'none' (color de fondo liso,
        // el texto se adapta automáticamente a claro/oscuro).
        'hero_background_mode' => 'mosaic',

        // fix (Andrea): nuevo módulo de home — mosaico de todos los proyectos
        // publicados con efecto parallax al hacer scroll.
        'home_show_projects_mosaic' => '0',
        'projects_mosaic_columns' => '3', // '1' | '2' | '3'

        // fix (Andrea): orden en que aparecen los módulos de la home,
        // reordenable por drag & drop desde Ajustes → Home. Los módulos
        // apagados simplemente se saltan; si faltara alguno en la lista
        // guardada (ajuste nuevo, sitio ya existente) se añade al final.
        'home_modules_order' => 'hero,categories,videos,simple,projects_mosaic',

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

        // fix (Andrea): tamaño y grosor de letra configurables por tipo de
        // texto, con vista previa en vivo en Ajustes → Estilos. Los
        // encabezados (H1-H4) van en modo "proporcional" por defecto (solo
        // se ajusta el H1 y los demás se escalan a partir de él) o en modo
        // "personalizado" (cada nivel con su propio tamaño/grosor).
        'type_hero_title_size' => '34', 'type_hero_title_weight' => '700',
        'type_hero_subtitle_size' => '14', 'type_hero_subtitle_weight' => '400',
        'type_header_title_size' => '23', 'type_header_title_weight' => '700',
        'type_header_menu_size' => '14', 'type_header_menu_weight' => '400',
        'type_headings_mode' => 'proportional', // 'proportional' | 'custom'
        'type_h1_size' => '40', 'type_h1_weight' => '700',
        'type_h2_size' => '28', 'type_h2_weight' => '700',
        'type_h3_size' => '22', 'type_h3_weight' => '700',
        'type_h4_size' => '18', 'type_h4_weight' => '700',
        'type_body_size' => '16', 'type_body_weight' => '400',
        'type_footer_legal_size' => '12', 'type_footer_legal_weight' => '400',
        'type_footer_menu_size' => '12', 'type_footer_menu_weight' => '400',
        'type_breadcrumb_size' => '12', 'type_breadcrumb_weight' => '400',
        'type_grid_title_size' => '14', 'type_grid_title_weight' => '600',
        'type_grid_summary_size' => '14', 'type_grid_summary_weight' => '400',

        // fix (Andrea): código externo (Google Tag Manager, píxeles de
        // publicidad...) para pegar tal cual, sin tener que tocar el código
        // del CMS cada vez que una plataforma pide un script nuevo.
        // tracking_head_code: se inserta justo después de <head> en todas
        // las páginas públicas (así lo pide Google Tag Manager).
        // tracking_body_code: se inserta justo después de <body>, también en
        // todas las páginas públicas (segunda parte obligatoria del propio
        // Tag Manager — su <noscript>—, y donde suelen pedir otros píxeles).
        'tracking_head_code' => '',
        'tracking_body_code' => '',
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
 * fix (Andrea): ¿el fondo general del sitio (color_surface) es oscuro?
 * Se reutiliza tanto para el Hero en modo "sin fondo" (color del texto) como
 * para el hover del mosaico de proyectos con parallax (dirección del overlay)
 * — un único criterio de claro/oscuro para todo el sitio, coherente con el
 * resto del sistema de color en colors.php.
 */
function isSurfaceDark(array $settings): bool {
    require_once __DIR__ . '/colors.php';
    return relativeLuminance($settings['color_surface'] ?? '#FFFFFF') < 0.5;
}

/**
 * Etiqueta <meta name="robots"> cuando la web está marcada como noindex o en
 * modo "Próximamente" (este último también lleva su propio noindex, ver
 * maybeRenderComingSoon()). Se incrusta en el <head> de cada página pública.
 */
function robotsMetaTag(array $settings): string {
    if (($settings['site_noindex'] ?? '0') === '1' || ($settings['site_coming_soon'] ?? '0') === '1') {
        return '<meta name="robots" content="noindex, nofollow">' . "\n";
    }
    return '';
}

/**
 * fix (Andrea, web en construcción): si el modo "Próximamente" está activo,
 * sustituye toda la página pública por un aviso simple y termina la
 * ejecución — /admin y /api nunca pasan por aquí (no llaman a esta función),
 * así que el panel sigue funcionando con normalidad mientras la web está
 * "a puerta cerrada" de cara al público.
 */
function maybeRenderComingSoon(array $settings, string $locale): void {
    if (($settings['site_coming_soon'] ?? '0') !== '1') return;

    require_once __DIR__ . '/i18n.php';
    $siteName = $settings['site_name'] ?? 'Mi Sitio';
    $message = $locale === 'en'
        ? ($settings['coming_soon_message_en'] ?: $settings['coming_soon_message_es'])
        : $settings['coming_soon_message_es'];
    if (!$message) { $message = t('coming_soon.default_message'); }
    ?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($locale) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title><?= htmlspecialchars($siteName) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="<?= htmlspecialchars(buildThemeFontsUrl($settings)) ?>" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/tokens.css">
  <?= renderThemeOverridesStyleTag($settings) ?>
  <link rel="stylesheet" href="/assets/css/components/coming-soon.css">
</head>
<body>
  <main class="coming-soon">
    <h1 class="coming-soon__title"><?= htmlspecialchars($siteName) ?></h1>
    <p class="coming-soon__message"><?= nl2br(htmlspecialchars($message)) ?></p>
  </main>
</body>
</html>
    <?php
    exit;
}

/**
 * URL de Google Fonts para las dos tipografías elegidas.
 * fix (Andrea): con los controles de grosor por tipo de texto (Fina a
 * Extra-negrita), cualquiera de las dos tipografías puede usarse con
 * cualquiera de los 6 grosores — se cargan todos para las dos familias.
 */
function buildThemeFontsUrl(array $settings): string {
    $content = str_replace(' ', '+', $settings['font_content']);
    $ui = str_replace(' ', '+', $settings['font_ui']);
    $weights = '300;400;500;600;700;800';
    return "https://fonts.googleapis.com/css2?family={$content}:wght@{$weights}&family={$ui}:wght@{$weights}&display=swap";
}

/**
 * fix (Andrea): tamaños/grosores de encabezados H1-H4 — en modo
 * "proportional" solo se guarda el H1 y los demás se derivan con una escala
 * fija (misma proporción que los valores de fábrica: 28/40, 22/40, 18/40);
 * en modo "custom" se usa el valor guardado de cada nivel tal cual.
 * Se calcula en PHP (no en CSS) para que el mismo número exacto se pueda
 * replicar en la vista previa en vivo del admin con JS.
 */
function computeHeadingSizes(array $settings): array {
    $h1Size = (int) ($settings['type_h1_size'] ?? 40);
    $h1Weight = (int) ($settings['type_h1_weight'] ?? 700);

    if (($settings['type_headings_mode'] ?? 'proportional') === 'custom') {
        return [
            'h1' => ['size' => $h1Size, 'weight' => $h1Weight],
            'h2' => ['size' => (int) ($settings['type_h2_size'] ?? 28), 'weight' => (int) ($settings['type_h2_weight'] ?? 700)],
            'h3' => ['size' => (int) ($settings['type_h3_size'] ?? 22), 'weight' => (int) ($settings['type_h3_weight'] ?? 700)],
            'h4' => ['size' => (int) ($settings['type_h4_size'] ?? 18), 'weight' => (int) ($settings['type_h4_weight'] ?? 700)],
        ];
    }

    $ratios = ['h2' => 0.7, 'h3' => 0.55, 'h4' => 0.45];
    return [
        'h1' => ['size' => $h1Size, 'weight' => $h1Weight],
        'h2' => ['size' => (int) round($h1Size * $ratios['h2']), 'weight' => $h1Weight],
        'h3' => ['size' => (int) round($h1Size * $ratios['h3']), 'weight' => $h1Weight],
        'h4' => ['size' => (int) round($h1Size * $ratios['h4']), 'weight' => $h1Weight],
    ];
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

    // fix (Andrea): tamaño/grosor por tipo de texto — ver computeHeadingSizes()
    // para la lógica de encabezados proporcional/personalizada.
    $headings = computeHeadingSizes($settings);
    $heroTitleSize = (int) ($settings['type_hero_title_size'] ?? 34) . 'px';
    $heroTitleWeight = (int) ($settings['type_hero_title_weight'] ?? 700);
    $heroSubtitleSize = (int) ($settings['type_hero_subtitle_size'] ?? 14) . 'px';
    $heroSubtitleWeight = (int) ($settings['type_hero_subtitle_weight'] ?? 400);
    $headerTitleSize = (int) ($settings['type_header_title_size'] ?? 23) . 'px';
    $headerTitleWeight = (int) ($settings['type_header_title_weight'] ?? 700);
    $headerMenuSize = (int) ($settings['type_header_menu_size'] ?? 14) . 'px';
    $headerMenuWeight = (int) ($settings['type_header_menu_weight'] ?? 400);
    $bodySize = (int) ($settings['type_body_size'] ?? 16) . 'px';
    $bodyWeight = (int) ($settings['type_body_weight'] ?? 400);
    $footerLegalSize = (int) ($settings['type_footer_legal_size'] ?? 12) . 'px';
    $footerLegalWeight = (int) ($settings['type_footer_legal_weight'] ?? 400);
    $footerMenuSize = (int) ($settings['type_footer_menu_size'] ?? 12) . 'px';
    $footerMenuWeight = (int) ($settings['type_footer_menu_weight'] ?? 400);
    $breadcrumbSize = (int) ($settings['type_breadcrumb_size'] ?? 12) . 'px';
    $breadcrumbWeight = (int) ($settings['type_breadcrumb_weight'] ?? 400);
    $gridTitleSize = (int) ($settings['type_grid_title_size'] ?? 14) . 'px';
    $gridTitleWeight = (int) ($settings['type_grid_title_weight'] ?? 600);
    $gridSummarySize = (int) ($settings['type_grid_summary_size'] ?? 14) . 'px';
    $gridSummaryWeight = (int) ($settings['type_grid_summary_weight'] ?? 400);

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

        --type-hero-title-size: {$heroTitleSize};
        --type-hero-title-weight: {$heroTitleWeight};
        --type-hero-subtitle-size: {$heroSubtitleSize};
        --type-hero-subtitle-weight: {$heroSubtitleWeight};
        --type-header-title-size: {$headerTitleSize};
        --type-header-title-weight: {$headerTitleWeight};
        --type-header-menu-size: {$headerMenuSize};
        --type-header-menu-weight: {$headerMenuWeight};
        --type-h1-size: {$headings['h1']['size']}px;
        --type-h1-weight: {$headings['h1']['weight']};
        --type-h2-size: {$headings['h2']['size']}px;
        --type-h2-weight: {$headings['h2']['weight']};
        --type-h3-size: {$headings['h3']['size']}px;
        --type-h3-weight: {$headings['h3']['weight']};
        --type-h4-size: {$headings['h4']['size']}px;
        --type-h4-weight: {$headings['h4']['weight']};
        --type-body-size: {$bodySize};
        --type-body-weight: {$bodyWeight};
        --type-footer-legal-size: {$footerLegalSize};
        --type-footer-legal-weight: {$footerLegalWeight};
        --type-footer-menu-size: {$footerMenuSize};
        --type-footer-menu-weight: {$footerMenuWeight};
        --type-breadcrumb-size: {$breadcrumbSize};
        --type-breadcrumb-weight: {$breadcrumbWeight};
        --type-grid-title-size: {$gridTitleSize};
        --type-grid-title-weight: {$gridTitleWeight};
        --type-grid-summary-size: {$gridSummarySize};
        --type-grid-summary-weight: {$gridSummaryWeight};

        --grid-gap: {$gridGap};
        --grid-columns: {$gridColumns};
      }
    </style>
    CSS;
}
