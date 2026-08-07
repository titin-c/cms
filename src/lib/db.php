<?php
/**
 * Conexión PDO a MySQL/MariaDB.
 * Prioridad de configuración:
 *   1. src/lib/config.local.php (generado por /install.php — el asistente web)
 *   2. Variables de entorno (DB_HOST, DB_NAME, DB_USER, DB_PASS)
 * Si no hay ninguna de las dos, se redirige a /install.php automáticamente
 * (salvo que ya estemos dentro del propio instalador).
 */

function getDb(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $config = [];
    $configFile = __DIR__ . '/config.local.php';
    if (file_exists($configFile)) {
        $config = require $configFile;
    }

    $host = $config['DB_HOST'] ?? (getenv('DB_HOST') ?: null);
    $name = $config['DB_NAME'] ?? (getenv('DB_NAME') ?: null);
    $user = $config['DB_USER'] ?? (getenv('DB_USER') ?: null);
    $pass = $config['DB_PASS'] ?? (getenv('DB_PASS') ?: '');

    // fix: sin ninguna configuración → asistente de instalación, no un error críptico
    if (!$host || !$name || !$user) {
        $isInstaller = str_contains($_SERVER['SCRIPT_NAME'] ?? '', 'install.php');
        if (!$isInstaller && php_sapi_name() !== 'cli') {
            header('Location: /install.php');
            exit;
        }
        if (!$isInstaller) {
            die("Falta configuración de base de datos. Ejecuta database/seed_content_pages.php tras configurar src/lib/config.local.php, o visita /install.php desde el navegador.\n");
        }
        // dentro del propio instalador: devolver null-safe, cada paso comprueba antes de usarlo
        throw new PDOException('not_configured_yet');
    }

    $dsn = "mysql:host=$host;dbname=$name;charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $e) {
        error_log('DB connection error: ' . $e->getMessage());
        if (str_contains($_SERVER['SCRIPT_NAME'] ?? '', 'install.php')) {
            throw $e; // el instalador necesita el mensaje real para mostrarlo al usuario
        }
        http_response_code(500);
        die('Error de conexión. Inténtalo de nuevo más tarde.');
    }

    return $pdo;
}

/** Usado solo por el instalador: probar una conexión con credenciales concretas sin guardarlas todavía. */
function testDbConnection(string $host, string $name, string $user, string $pass): array {
    try {
        $dsn = "mysql:host=$host;dbname=$name;charset=utf8mb4";
        new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        return ['ok' => true];
    } catch (PDOException $e) {
        return ['ok' => false, 'message' => $e->getMessage()];
    }
}
