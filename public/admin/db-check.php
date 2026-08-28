<?php
/**
 * fix (Andrea): herramienta de diagnóstico y reparación de la base de datos.
 *
 * Motivo: varias funciones nuevas del CMS (categorías, páginas, proyectos...)
 * se han ido ampliando con migraciones SQL que hay que ejecutar A MANO una
 * sola vez sobre la base de datos (no se aplican solas al subir los archivos
 * por FTP, a diferencia del código). Es fácil que una de esas migraciones se
 * quede sin ejecutar en algún sitio (local, producción, o ambos) — es
 * exactamente lo que causó el error "No se pudo guardar por un error del
 * servidor" al guardar páginas: faltaba la columna "noindex" en la tabla de
 * páginas, porque esa migración nunca llegó a ejecutarse aquí.
 *
 * Esta página comprueba, tabla por tabla y columna por columna, que la base
 * de datos tiene todo lo que el código espera encontrar, y permite arreglar
 * automáticamente lo que falte con un solo botón — sin tener que escribir ni
 * ejecutar SQL a mano. Es segura de visitar y de repetir tantas veces como
 * haga falta: si no falta nada, no toca nada.
 */
require_once __DIR__ . '/../../src/lib/auth.php';
require_once __DIR__ . '/../../src/lib/db.php';

requireAuth();
$pdo = getDb();

// Columnas que distintas migraciones han ido añadiendo a tablas que ya
// existían de antes. Todas son NULL o tienen un valor por defecto, así que
// añadirlas es seguro incluso con la tabla ya llena de datos.
$expectedColumns = [
    'categories' => [
        'slug_en' => "VARCHAR(120) NULL UNIQUE",
        'home_title_es' => "VARCHAR(120) NULL",
        'home_title_en' => "VARCHAR(120) NULL",
        'home_description_es' => "TEXT NULL",
        'home_description_en' => "TEXT NULL",
        'description_es' => "TEXT NULL",
        'description_en' => "TEXT NULL",
        'meta_description_es' => "VARCHAR(300) NULL",
        'meta_description_en' => "VARCHAR(300) NULL",
        'button_label_es' => "VARCHAR(60) NULL",
        'button_label_en' => "VARCHAR(60) NULL",
        'seo_keywords_es' => "VARCHAR(300) NULL",
        'seo_keywords_en' => "VARCHAR(300) NULL",
        'show_in_header' => "BOOLEAN NOT NULL DEFAULT 0",
        'header_placement' => "ENUM('none','direct','submenu') NOT NULL DEFAULT 'submenu'",
        'show_in_footer' => "BOOLEAN NOT NULL DEFAULT 0",
        'show_in_home' => "BOOLEAN NOT NULL DEFAULT 1",
        'show_title' => "BOOLEAN NOT NULL DEFAULT 1",
        'status' => "ENUM('draft','published') NOT NULL DEFAULT 'published'",
    ],
    'projects' => [
        'slug_en' => "VARCHAR(160) NULL UNIQUE",
        'main_image_alt' => "VARCHAR(255) NULL",
        'excerpt_es' => "TEXT NULL",
        'excerpt_en' => "TEXT NULL",
        'seo_keywords' => "VARCHAR(255) NULL",
        'seo_keywords_en' => "VARCHAR(255) NULL",
        'seo_description_es' => "VARCHAR(300) NULL",
        'seo_description_en' => "VARCHAR(300) NULL",
    ],
    'content_pages' => [
        'slug_en' => "VARCHAR(160) NULL UNIQUE",
        'show_in_header' => "BOOLEAN NOT NULL DEFAULT 0",
        'show_in_footer' => "BOOLEAN NOT NULL DEFAULT 1",
        'noindex' => "BOOLEAN NOT NULL DEFAULT 0",
        'sort_order' => "INT NOT NULL DEFAULT 0",
    ],
];

// Tablas completas que algunas migraciones añaden desde cero (módulo de
// vídeos, categorías adicionales de un proyecto, redes sociales...).
$expectedTables = [
    'videos' => "CREATE TABLE videos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        slug VARCHAR(160) NOT NULL UNIQUE,
        slug_en VARCHAR(160) NULL UNIQUE,
        title_es VARCHAR(200) NOT NULL,
        title_en VARCHAR(200) NULL,
        subtitle_es VARCHAR(300) NULL,
        subtitle_en VARCHAR(300) NULL,
        thumbnail VARCHAR(255) NOT NULL,
        thumbnail_alt VARCHAR(255) NULL,
        video_url VARCHAR(500) NOT NULL,
        video_provider ENUM('youtube', 'vimeo', 'other') NOT NULL DEFAULT 'youtube',
        display_mode ENUM('lightbox', 'external') NOT NULL DEFAULT 'lightbox',
        featured BOOLEAN NOT NULL DEFAULT FALSE,
        sort_order INT NOT NULL DEFAULT 0,
        status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB",
    'project_extra_categories' => "CREATE TABLE project_extra_categories (
        project_id INT NOT NULL,
        category_id INT NOT NULL,
        PRIMARY KEY (project_id, category_id),
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
    ) ENGINE=InnoDB",
    'social_links' => "CREATE TABLE social_links (
        id INT AUTO_INCREMENT PRIMARY KEY,
        platform VARCHAR(40) NOT NULL,
        url VARCHAR(500) NOT NULL,
        sort_order INT NOT NULL DEFAULT 0
    ) ENGINE=InnoDB",
    'site_settings' => "CREATE TABLE site_settings (
        setting_key VARCHAR(60) PRIMARY KEY,
        setting_value TEXT NULL
    ) ENGINE=InnoDB",
];

function tableExists(PDO $pdo, string $table): bool {
    $stmt = $pdo->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?");
    $stmt->execute([$table]);
    return (bool) $stmt->fetchColumn();
}

function existingColumns(PDO $pdo, string $table): array {
    $stmt = $pdo->prepare("SELECT column_name FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ?");
    $stmt->execute([$table]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Diagnóstico: qué falta ahora mismo
$missingTables = [];
foreach ($expectedTables as $table => $ddl) {
    if (!tableExists($pdo, $table)) { $missingTables[$table] = $ddl; }
}
$missingColumns = [];
foreach ($expectedColumns as $table => $columns) {
    if (!tableExists($pdo, $table)) continue; // se crea entera si falta la tabla, no columna a columna
    $existing = existingColumns($pdo, $table);
    foreach ($columns as $col => $definition) {
        if (!in_array($col, $existing, true)) {
            $missingColumns[$table][$col] = $definition;
        }
    }
}

$log = [];
$didRepair = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'repair') {
    $didRepair = true;

    foreach ($missingTables as $table => $ddl) {
        try {
            $pdo->exec($ddl);
            $log[] = "✅ Tabla creada: $table";
        } catch (PDOException $e) {
            $log[] = "❌ No se pudo crear la tabla $table: " . $e->getMessage();
        }
    }

    foreach ($missingColumns as $table => $columns) {
        foreach ($columns as $col => $definition) {
            try {
                $pdo->exec("ALTER TABLE $table ADD COLUMN $col $definition");
                $log[] = "✅ Columna añadida: $table.$col";
            } catch (PDOException $e) {
                $log[] = "❌ No se pudo añadir $table.$col: " . $e->getMessage();
            }
        }
    }

    if (!$missingTables && !$missingColumns) {
        $log[] = "No había nada que reparar.";
    }

    // Recalcular tras reparar, para mostrar el estado real actualizado
    $missingTables = [];
    foreach ($expectedTables as $table => $ddl) {
        if (!tableExists($pdo, $table)) { $missingTables[$table] = $ddl; }
    }
    $missingColumns = [];
    foreach ($expectedColumns as $table => $columns) {
        if (!tableExists($pdo, $table)) continue;
        $existing = existingColumns($pdo, $table);
        foreach ($columns as $col => $definition) {
            if (!in_array($col, $existing, true)) {
                $missingColumns[$table][$col] = $definition;
            }
        }
    }
}

$allOk = !$missingTables && !$missingColumns;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Comprobación de la base de datos — Admin</title>
<style>
  body { font-family: -apple-system, Segoe UI, Arial, sans-serif; max-width: 760px; margin: 40px auto; padding: 0 20px; color: #1a1a1a; }
  h1 { font-size: 22px; }
  .ok { background: #E8F5E9; border: 1px solid #A5D6A7; color: #1B5E20; padding: 16px 20px; border-radius: 8px; }
  .warn { background: #FFF3E0; border: 1px solid #FFCC80; color: #8A5A00; padding: 16px 20px; border-radius: 8px; }
  .log { background: #F5F5F5; border: 1px solid #ddd; padding: 16px 20px; border-radius: 8px; font-family: monospace; font-size: 13px; white-space: pre-wrap; }
  ul { padding-left: 20px; }
  button { background: #8A5A00; color: #fff; border: none; padding: 12px 24px; border-radius: 6px; font-size: 15px; cursor: pointer; margin-top: 16px; }
  button:hover { background: #6e4800; }
  a { color: #0A0A0A; }
  .back { display: inline-block; margin-top: 24px; }
</style>
</head>
<body>
  <h1>Comprobación de la base de datos</h1>
  <p>Revisa que la base de datos tiene todas las tablas y columnas que el panel necesita para guardar sin errores. No cambia nada hasta que pulses "Reparar ahora".</p>

  <?php if ($didRepair): ?>
    <h2>Resultado de la reparación</h2>
    <div class="log"><?= htmlspecialchars(implode("\n", $log)) ?></div>
  <?php endif; ?>

  <?php if ($allOk): ?>
    <p class="ok">✅ Todo en orden — no falta ninguna tabla ni columna.</p>
  <?php else: ?>
    <p class="warn">⚠️ Falta lo siguiente en esta base de datos:</p>
    <ul>
      <?php foreach ($missingTables as $table => $ddl): ?>
        <li>Tabla completa: <strong><?= htmlspecialchars($table) ?></strong></li>
      <?php endforeach; ?>
      <?php foreach ($missingColumns as $table => $columns): ?>
        <?php foreach ($columns as $col => $definition): ?>
          <li>Columna <strong><?= htmlspecialchars($col) ?></strong> en la tabla <strong><?= htmlspecialchars($table) ?></strong></li>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </ul>
    <form method="post">
      <input type="hidden" name="action" value="repair">
      <button type="submit">Reparar ahora</button>
    </form>
  <?php endif; ?>

  <a class="back" href="dashboard.php">&larr; Volver al panel</a>
</body>
</html>
