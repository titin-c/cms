<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../src/lib/auth.php';
require_once __DIR__ . '/../src/lib/db.php';

requireAuth();
$pdo = getDb();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        echo json_encode($pdo->query("SELECT * FROM social_links ORDER BY sort_order ASC, id ASC")->fetchAll());
        break;

    case 'POST':
        // fix: se gestiona como lista completa — más simple que ir creando/
        // borrando filas sueltas desde un editor que las añade y quita libremente
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $links = $input['links'] ?? [];

        $pdo->beginTransaction();
        $pdo->exec("DELETE FROM social_links");
        $stmt = $pdo->prepare("INSERT INTO social_links (platform, url, sort_order) VALUES (?, ?, ?)");
        foreach ($links as $i => $link) {
            if (empty($link['url'])) continue;
            $stmt->execute([$link['platform'] ?? 'website', $link['url'], $i]);
        }
        $pdo->commit();

        echo json_encode(['ok' => true]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'method_not_allowed']);
}
