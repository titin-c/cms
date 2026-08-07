<?php
/**
 * Asistente de instalación — equivalente al "famoso instalador de 5 minutos"
 * de WordPress, pero para este CMS. Se ejecuta UNA VEZ desde el navegador,
 * justo después de subir los archivos al hosting.
 *
 * Flujo: requisitos → datos de conexión a la BD → cuenta de administrador → fin.
 * Al terminar, escribe src/lib/config.local.php y se bloquea a sí mismo (no
 * se puede volver a ejecutar mientras ese archivo exista) — mismo principio
 * de seguridad que wp-config.php en WordPress.
 */
session_start();
$root = __DIR__;
$configFile = $root . '/../src/lib/config.local.php';
// fix: una instalación ya puede estar configurada por variables de entorno
// (setup local típico con XAMPP) en vez de config.local.php — sin esto, el
// instalador no se daba cuenta y dejaba repetir todo el proceso sin avisar.
$alreadyInstalled = file_exists($configFile)
    || (getenv('DB_HOST') && getenv('DB_NAME') && getenv('DB_USER'));

$step = $_GET['step'] ?? 'requirements';
$errors = [];

// ---- Paso: ya instalado ----
if ($alreadyInstalled && $step !== 'done') {
    $step = 'already-installed';
}

// ---- Procesar el formulario de base de datos ----
if ($step === 'database' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once $root . '/../src/lib/db.php';
    $dbHost = trim($_POST['db_host'] ?? '');
    $dbName = trim($_POST['db_name'] ?? '');
    $dbUser = trim($_POST['db_user'] ?? '');
    $dbPass = $_POST['db_pass'] ?? '';

    if (!$dbHost || !$dbName || !$dbUser) {
        $errors[] = 'Rellena al menos el servidor, el nombre de la base de datos y el usuario.';
    } else {
        $test = testDbConnection($dbHost, $dbName, $dbUser, $dbPass);
        if (!$test['ok']) {
            $errors[] = 'No se pudo conectar: ' . htmlspecialchars($test['message']);
        } else {
            $_SESSION['install_db'] = compact('dbHost', 'dbName', 'dbUser', 'dbPass');
            header('Location: install.php?step=account');
            exit;
        }
    }
}

// ---- Procesar el formulario de cuenta admin + ejecutar la instalación completa ----
if ($step === 'account' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_SESSION['install_db'])) {
        header('Location: install.php?step=database');
        exit;
    }
    $siteName = trim($_POST['site_name'] ?? '');
    $adminEmail = trim($_POST['admin_email'] ?? '');
    $adminPass = $_POST['admin_pass'] ?? '';
    $adminPass2 = $_POST['admin_pass2'] ?? '';

    if (!$siteName) $errors[] = 'Escribe un nombre para el sitio.';
    if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) $errors[] = 'El email del administrador no es válido.';
    if (strlen($adminPass) < 8) $errors[] = 'La contraseña debe tener al menos 8 caracteres.';
    if ($adminPass !== $adminPass2) $errors[] = 'Las contraseñas no coinciden.';

    if (empty($errors)) {
        try {
            ['dbHost' => $dbHost, 'dbName' => $dbName, 'dbUser' => $dbUser, 'dbPass' => $dbPass] = $_SESSION['install_db'];
            $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            // 1) Crear las tablas si no existen todavía (instalación limpia == seguro re-ejecutar)
            $existingTables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('projects', $existingTables)) {
                $sql = file_get_contents($root . '/../database/schema.sql');
                $statements = array_filter(array_map('trim', explode(";\n", preg_replace('/^--.*$/m', '', $sql))));
                foreach ($statements as $statement) {
                    if ($statement !== '') $pdo->exec($statement);
                }
            }

            // 2) Guardar el nombre del sitio (sobreescribe el seed por defecto "Mi Sitio")
            $pdo->prepare("
                INSERT INTO site_settings (setting_key, setting_value) VALUES ('site_name', ?)
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
            ")->execute([$siteName]);

            // 3) Crear la cuenta de administrador (o actualizar la contraseña si el email ya existía)
            $hash = password_hash($adminPass, PASSWORD_DEFAULT);
            $pdo->prepare("
                INSERT INTO admin_users (email, password_hash) VALUES (?, ?)
                ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)
            ")->execute([$adminEmail, $hash]);

            // 4) Escribir la configuración persistente (equivalente a wp-config.php)
            $configContent = "<?php\n// Generado automáticamente por install.php — no subir este archivo a un repositorio público.\nreturn [\n"
                . "    'DB_HOST' => " . var_export($dbHost, true) . ",\n"
                . "    'DB_NAME' => " . var_export($dbName, true) . ",\n"
                . "    'DB_USER' => " . var_export($dbUser, true) . ",\n"
                . "    'DB_PASS' => " . var_export($dbPass, true) . ",\n"
                . "];\n";
            file_put_contents($configFile, $configContent);

            // 5) Sembrar el contenido inicial de las páginas fijas (Sobre mí, legales) si aún no existen
            $pageCount = (int) $pdo->query("SELECT COUNT(*) FROM content_pages")->fetchColumn();
            if ($pageCount === 0) {
                ob_start();
                require $root . '/../database/seed_content_pages.php';
                ob_end_clean();
            }

            unset($_SESSION['install_db']);
            header('Location: install.php?step=done');
            exit;
        } catch (Throwable $e) {
            $errors[] = 'Error durante la instalación: ' . htmlspecialchars($e->getMessage());
        }
    }
}

// ---- Comprobación de requisitos (paso 1) ----
function checkRequirements(): array {
    $checks = [
        'PHP 8.0 o superior' => version_compare(PHP_VERSION, '8.0.0', '>='),
        'Extensión PDO MySQL' => extension_loaded('pdo_mysql'),
        'Extensión GD (procesado de imágenes)' => extension_loaded('gd'),
        'Extensión mbstring' => extension_loaded('mbstring'),
        'Carpeta /public/uploads con permisos de escritura' => is_writable(__DIR__ . '/uploads'),
        'Carpeta /src/lib con permisos de escritura (para guardar la configuración)' => is_writable(__DIR__ . '/../src/lib'),
    ];
    return $checks;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Instalación — CMS de portfolio</title>
  <style>
    * { box-sizing: border-box; }
    body { font-family: -apple-system, 'Segoe UI', Helvetica, Arial, sans-serif; background: #F5F5F5; color: #0A0A0A; margin: 0; padding: 40px 20px; }
    .installer { max-width: 480px; margin: 0 auto; background: #FFFFFF; border-radius: 12px; padding: 40px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
    h1 { font-size: 20px; margin: 0 0 4px; }
    .installer__subtitle { font-size: 14px; color: #666; margin: 0 0 28px; }
    .steps { display: flex; gap: 6px; margin-bottom: 28px; }
    .steps span { flex: 1; height: 4px; border-radius: 2px; background: #E0E0E0; }
    .steps span.is-done, .steps span.is-active { background: #0A0A0A; }
    label { display: block; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em; color: #444; margin: 16px 0 6px; }
    label:first-of-type { margin-top: 0; }
    input[type="text"], input[type="email"], input[type="password"] {
      width: 100%; padding: 11px 14px; border: 1px solid #DDD; border-radius: 6px; font-size: 15px;
    }
    input:focus { outline: none; border-color: #0A0A0A; }
    button, .btn {
      display: inline-block; width: 100%; padding: 13px; margin-top: 24px; border: none; border-radius: 6px;
      background: #0A0A0A; color: #FFF; font-size: 15px; font-weight: 600; cursor: pointer; text-align: center; text-decoration: none;
    }
    button:hover, .btn:hover { background: #333; }
    .req-list { list-style: none; padding: 0; margin: 0; }
    .req-list li { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #F0F0F0; font-size: 14px; }
    .req-ok { color: #1B5E20; font-weight: 600; }
    .req-fail { color: #B71C1C; font-weight: 600; }
    .errors { background: #FDECEC; color: #7F0000; padding: 12px 16px; border-radius: 6px; font-size: 14px; margin-bottom: 16px; }
    .hint { font-size: 13px; color: #777; margin: 4px 0 0; }
    .success-icon { font-size: 40px; text-align: center; margin-bottom: 8px; }
  </style>
</head>
<body>
  <div class="installer">

    <?php if ($step === 'already-installed'): ?>
      <div class="success-icon">✅</div>
      <h1 style="text-align:center;">Ya está instalado</h1>
      <p class="installer__subtitle" style="text-align:center;">
        Este sitio ya tiene una configuración guardada (por <code>config.local.php</code> o por variables de entorno).
        Si necesitas reinstalar desde cero, borra <code>src/lib/config.local.php</code> del servidor — y si usas
        variables de entorno, quítalas también — y vuelve a cargar esta página.
      </p>
      <a href="/admin/login.php" class="btn">Ir al panel de administración</a>

    <?php elseif ($step === 'done'): ?>
      <div class="success-icon">🎉</div>
      <h1 style="text-align:center;">¡Instalación completada!</h1>
      <p class="installer__subtitle" style="text-align:center;">
        Ya puedes entrar al panel de administración con el email y la contraseña que has creado.
      </p>
      <a href="/admin/login.php" class="btn">Entrar al panel</a>
      <p class="hint" style="text-align:center; margin-top:16px;">
        Por seguridad, borra este archivo (<code>public/install.php</code>) del servidor cuando termines de configurar el sitio.
      </p>

    <?php elseif ($step === 'account'): ?>
      <div class="steps"><span class="is-done"></span><span class="is-done"></span><span class="is-active"></span></div>
      <h1>Cuenta de administrador</h1>
      <p class="installer__subtitle">El último paso — con esto ya puedes entrar al panel.</p>

      <?php foreach ($errors as $e): ?><div class="errors"><?= $e /* ya escapado arriba */ ?></div><?php endforeach; ?>

      <form method="POST" action="install.php?step=account">
        <label for="site_name">Nombre del sitio</label>
        <input type="text" id="site_name" name="site_name" placeholder="ej: Estudio Fulanita" value="<?= htmlspecialchars($_POST['site_name'] ?? '') ?>" required>

        <label for="admin_email">Tu email (para entrar al panel)</label>
        <input type="email" id="admin_email" name="admin_email" value="<?= htmlspecialchars($_POST['admin_email'] ?? '') ?>" required>

        <label for="admin_pass">Contraseña</label>
        <input type="password" id="admin_pass" name="admin_pass" required minlength="8">
        <p class="hint">Mínimo 8 caracteres.</p>

        <label for="admin_pass2">Repite la contraseña</label>
        <input type="password" id="admin_pass2" name="admin_pass2" required minlength="8">

        <button type="submit">Finalizar instalación</button>
      </form>

    <?php elseif ($step === 'database'): ?>
      <div class="steps"><span class="is-done"></span><span class="is-active"></span><span></span></div>
      <h1>Conexión a la base de datos</h1>
      <p class="installer__subtitle">Estos datos te los da tu proveedor de hosting (o son los de tu XAMPP/MAMP en local).</p>

      <?php foreach ($errors as $e): ?><div class="errors"><?= $e /* ya escapado arriba */ ?></div><?php endforeach; ?>

      <form method="POST" action="install.php?step=database">
        <label for="db_host">Servidor de base de datos</label>
        <input type="text" id="db_host" name="db_host" placeholder="ej: localhost o 127.0.0.1" value="<?= htmlspecialchars($_POST['db_host'] ?? 'localhost') ?>" required>

        <label for="db_name">Nombre de la base de datos</label>
        <input type="text" id="db_name" name="db_name" value="<?= htmlspecialchars($_POST['db_name'] ?? '') ?>" required>
        <p class="hint">Debe existir ya — créala antes desde cPanel/phpMyAdmin si tu hosting lo requiere.</p>

        <label for="db_user">Usuario</label>
        <input type="text" id="db_user" name="db_user" value="<?= htmlspecialchars($_POST['db_user'] ?? '') ?>" required>

        <label for="db_pass">Contraseña</label>
        <input type="password" id="db_pass" name="db_pass">

        <button type="submit">Probar conexión y continuar</button>
      </form>

    <?php else: /* requirements */ ?>
      <div class="steps"><span class="is-active"></span><span></span><span></span></div>
      <h1>Bienvenido/a</h1>
      <p class="installer__subtitle">Antes de empezar, comprobamos que el servidor cumple los requisitos.</p>

      <?php $checks = checkRequirements(); $allOk = !in_array(false, $checks, true); ?>
      <ul class="req-list">
        <?php foreach ($checks as $label => $ok): ?>
          <li><span><?= htmlspecialchars($label) ?></span> <span class="<?= $ok ? 'req-ok' : 'req-fail' ?>"><?= $ok ? '✓ OK' : '✗ Falta' ?></span></li>
        <?php endforeach; ?>
      </ul>

      <?php if ($allOk): ?>
        <a href="install.php?step=database" class="btn">Continuar</a>
      <?php else: ?>
        <p class="hint" style="color:#B71C1C;">Corrige lo que falte (normalmente lo activa tu hosting, o pide soporte) y recarga esta página.</p>
      <?php endif; ?>
    <?php endif; ?>

  </div>
</body>
</html>
