<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../src/lib/auth.php';
require_once __DIR__ . '/../../src/lib/db.php';

requireAuth();
$pdo = getDb();

// fix: lista blanca de tablas reordenables — nunca aceptar el nombre de tabla
// directamente del cliente sin validar
$ALLOWED_TABLES = [
    'projects' => 'sort_order',
    'categories' => 'sort_order',
    'videos' => 'sort_order',
    'content_pages' => 'sort_order',
    'project_images' => 'sort_order',
    'social_links' => 'sort_order',
];

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$type = $input['type'] ?? '';
$order = $input['order'] ?? [];

if (!isset($ALLOWED_TABLES[$type]) || !is_array($order) || empty($order)) {
    http_response_code(422);
    echo json_encode(['error' => 'invalid_request']);
    exit;
}

$table = $type; // ya validado contra la lista blanca de arriba
$column = $ALLOWED_TABLES[$type];

$stmt = $pdo->prepare("UPDATE `$table` SET `$column` = ? WHERE id = ?");
$pdo->beginTransaction();
foreach ($order as $index => $id) {
    $stmt->execute([$index, (int) $id]);
}
$pdo->commit();

echo json_encode(['ok' => true]);
