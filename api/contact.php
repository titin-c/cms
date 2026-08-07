<?php
/**
 * fix (protección de contacto, decisión UX): honeypot + rate limit por IP,
 * sin CAPTCHA visible.
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../src/lib/db.php';
require_once __DIR__ . '/../src/lib/theme.php';

$pdo = getDb();
$themeSettings = getSiteSettings($pdo);
$input = json_decode(file_get_contents('php://input'), true) ?? [];

// Honeypot: si el campo oculto "website" viene relleno, es un bot.
// Respondemos 200 sin guardar nada, para no delatar la trampa a quien la programó.
if (!empty($input['website'])) {
    http_response_code(200);
    echo json_encode(['ok' => true]);
    exit;
}

$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

$stmt = $pdo->prepare("SELECT COUNT(*) FROM contact_attempts WHERE ip = ? AND created_at > NOW() - INTERVAL 10 MINUTE");
$stmt->execute([$ip]);
if ((int) $stmt->fetchColumn() >= 5) {
    http_response_code(429);
    echo json_encode(['error' => 'rate_limited']);
    exit;
}

$name = trim($input['name'] ?? '');
$email = trim($input['email'] ?? '');
$message = trim($input['message'] ?? '');

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $message === '') {
    http_response_code(422);
    echo json_encode(['error' => 'invalid_input']);
    exit;
}

$pdo->prepare("INSERT INTO contact_attempts (ip, created_at) VALUES (?, NOW())")->execute([$ip]);
$pdo->prepare("INSERT INTO messages (name, email, message, created_at) VALUES (?, ?, ?, NOW())")
    ->execute([$name, $email, $message]);

// fix (CMS genérico): el email de destino viene de los ajustes del sitio
// (/admin/ajustes.php), no de un dominio fijo — ANDREA_EMAIL se mantiene
// como variable de entorno opcional que, si existe, tiene prioridad.
$adminEmail = getenv('ANDREA_EMAIL') ?: ($themeSettings['contact_email'] ?: null);
if (!$adminEmail) {
    error_log('Formulario de contacto: no hay email de destino configurado en /admin/ajustes.php');
} else {
    $subject = 'Nuevo mensaje desde la web';
    $body = "Nombre: $name\nEmail: $email\n\n$message";
    $domain = parse_url(getSiteDomain($themeSettings), PHP_URL_HOST) ?: 'localhost';
    @mail($adminEmail, $subject, $body, "From: no-reply@{$domain}");
}

echo json_encode(['ok' => true]);
