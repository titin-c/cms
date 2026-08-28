<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../src/lib/auth.php';

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

if ($email === '' || $password === '') {
    http_response_code(422);
    echo json_encode(['error' => 'invalid_input']);
    exit;
}

try {
    $result = attemptLogin($email, $password);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'server_error']);
    exit;
}

if ($result['locked']) {
    // fix (ux-agent): mensaje genérico de bloqueo temporal, sin revelar detalles del intento
    http_response_code(429);
    echo json_encode(['error' => 'locked', 'message' => 'Demasiados intentos. Inténtalo de nuevo en unos minutos.']);
    exit;
}

if (!$result['success']) {
    // fix (ux-agent): no revela si el email existe o si fue la contraseña
    http_response_code(401);
    echo json_encode(['error' => 'invalid_credentials', 'message' => 'Credenciales incorrectas.']);
    exit;
}

echo json_encode(['ok' => true, 'redirect' => '/admin/dashboard.php']);
