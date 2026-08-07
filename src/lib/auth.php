<?php
/**
 * Autenticación del panel de administración.
 * fix (usability-agent / ux-agent): no revela si el email existe; bloqueo temporal
 * tras 5 intentos fallidos por email, sin CAPTCHA visible.
 */
require_once __DIR__ . '/db.php';

const MAX_LOGIN_ATTEMPTS = 5;
const LOCKOUT_MINUTES = 15;

function startSecureSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => !empty($_SERVER['HTTPS']), // fix: exige HTTPS solo si la conexión real es HTTPS (permite HTTP en desarrollo local)
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        session_start();
    }
}

/**
 * @return array{success: bool, locked: bool}
 */
function attemptLogin(string $email, string $password): array {
    startSecureSession();
    $pdo = getDb();

    $failKey = 'login_fail_' . md5(strtolower($email));
    $fails = $_SESSION[$failKey]['count'] ?? 0;
    $lastAttempt = $_SESSION[$failKey]['time'] ?? 0;

    if ($fails >= MAX_LOGIN_ATTEMPTS && (time() - $lastAttempt) < LOCKOUT_MINUTES * 60) {
        return ['success' => false, 'locked' => true];
    }

    $stmt = $pdo->prepare('SELECT * FROM admin_users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        $_SESSION[$failKey] = ['count' => $fails + 1, 'time' => time()];
        return ['success' => false, 'locked' => false];
    }

    unset($_SESSION[$failKey]);
    session_regenerate_id(true);
    $_SESSION['admin_id'] = $user['id'];
    return ['success' => true, 'locked' => false];
}

function requireAuth(): void {
    startSecureSession();
    if (empty($_SESSION['admin_id'])) {
        header('Location: /admin/login.php');
        exit;
    }
}

function logout(): void {
    startSecureSession();
    $_SESSION = [];
    session_destroy();
}
