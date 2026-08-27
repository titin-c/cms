<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../src/lib/auth.php';
require_once __DIR__ . '/../src/lib/db.php';

requireAuth();
$pdo = getDb();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $rows = $pdo->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll();
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        echo json_encode($settings);
        break;

    case 'POST':
        // fix: solo actualiza claves existentes (no crea claves arbitrarias
        // desde el cliente) — lista blanca de ajustes editables
        $allowedKeys = [
            'font_content', 'font_ui', 'color_primary', 'color_secondary', 'color_surface',
            'separator_style', 'separator_size', 'grid_gap', 'grid_columns',
            'site_name', 'site_subtitle_es', 'site_subtitle_en', 'site_domain',
            'contact_email', 'contact_phone', 'show_language_menu',
            'home_show_hero', 'home_show_categories', 'home_show_videos', 'home_show_simple',
            'home_show_projects_mosaic', 'projects_mosaic_columns',
            'hero_background_mode',
            'site_noindex', 'site_coming_soon', 'coming_soon_message_es', 'coming_soon_message_en',
            'home_simple_image_mode',
            'home_simple_title_es', 'home_simple_title_en',
            'home_simple_description_es', 'home_simple_description_en',
            'home_simple_image', 'home_simple_image_alt',
            'home_title_es', 'home_title_en',
            'home_meta_description_es', 'home_meta_description_en',
            'module_projects_enabled', 'module_videos_enabled', 'module_pages_enabled',
            'videos_show_in_header', 'videos_show_in_footer',
            'videos_slug_es', 'videos_slug_en', 'videos_h1_es', 'videos_h1_en',
            'videos_description_es', 'videos_description_en',
            'videos_meta_description_es', 'videos_meta_description_en',
            'videos_meta_title_es', 'videos_meta_title_en',
            'categories_show_in_header', 'categories_show_in_footer',
            'categories_slug_es', 'categories_slug_en',
            'categories_h1_es', 'categories_h1_en',
            'categories_description_es', 'categories_description_en',
            'categories_meta_title_es', 'categories_meta_title_en',
            'categories_meta_description_es', 'categories_meta_description_en',
        ];
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        $stmt = $pdo->prepare("
            INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");
        foreach ($allowedKeys as $key) {
            if (isset($input[$key])) {
                $stmt->execute([$key, $input[$key]]);
            }
        }
        echo json_encode(['ok' => true]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'method_not_allowed']);
}
