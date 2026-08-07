<?php
/**
 * Migración: la home pasa de "un solo diseño" (home_layout) a tres módulos
 * independientes y combinables (hero / categorías / vídeos).
 *
 * Ejecutar UNA VEZ:
 *   php database/migration_11_home_modules.php
 *
 * Es segura de repetir: si las claves nuevas ya existen, no las toca.
 */
require_once __DIR__ . '/../src/lib/db.php';

$pdo = getDb();

$stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
$stmt->execute(['home_layout']);
$oldLayout = $stmt->fetchColumn() ?: 'categories';

$defaults = [
    'home_show_hero' => '1',
    'home_show_categories' => $oldLayout === 'videos' ? '0' : '1',
    'home_show_videos' => $oldLayout === 'videos' ? '1' : '0',
];

$insert = $pdo->prepare("
    INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)
    ON DUPLICATE KEY UPDATE setting_key = setting_key
");
foreach ($defaults as $key => $value) {
    $insert->execute([$key, $value]);
    echo "OK: {$key} = {$value}\n";
}

echo "Listo. El ajuste antiguo 'home_layout' ya no se usa (puedes dejarlo, no molesta).\n";
