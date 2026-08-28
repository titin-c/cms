<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../src/lib/auth.php';
require_once __DIR__ . '/../../src/lib/db.php';

requireAuth();
$pdo = getDb();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $projectId = $_GET['project_id'] ?? null;
        if (!$projectId) { http_response_code(422); echo json_encode(['error' => 'missing_project_id']); exit; }
        $stmt = $pdo->prepare("SELECT * FROM project_images WHERE project_id = ? ORDER BY sort_order ASC, id ASC");
        $stmt->execute([$projectId]);
        echo json_encode($stmt->fetchAll());
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        if (!empty($input['id'])) {
            // Actualiza alt text / orden de una imagen ya existente
            $stmt = $pdo->prepare("UPDATE project_images SET alt_es = ?, alt_en = ?, sort_order = ? WHERE id = ?");
            $stmt->execute([
                $input['alt_es'] ?? null,
                $input['alt_en'] ?? null,
                $input['sort_order'] ?? 0,
                $input['id'],
            ]);
            echo json_encode(['ok' => true]);
            break;
        }

        // Nueva imagen añadida a la galería
        if (empty($input['project_id']) || empty($input['image_path'])) {
            http_response_code(422);
            echo json_encode(['error' => 'missing_fields']);
            exit;
        }
        $maxOrder = $pdo->prepare("SELECT COALESCE(MAX(sort_order), -1) FROM project_images WHERE project_id = ?");
        $maxOrder->execute([$input['project_id']]);
        $nextOrder = (int) $maxOrder->fetchColumn() + 1;

        $stmt = $pdo->prepare("
            INSERT INTO project_images (project_id, image_path, alt_es, alt_en, sort_order)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $input['project_id'],
            $input['image_path'],
            $input['alt_es'] ?? null,
            $input['alt_en'] ?? null,
            $nextOrder,
        ]);
        echo json_encode(['ok' => true, 'id' => $pdo->lastInsertId()]);
        break;

    case 'DELETE':
        $id = $_GET['id'] ?? null;
        if (!$id) { http_response_code(422); echo json_encode(['error' => 'missing_id']); exit; }
        $pdo->prepare("DELETE FROM project_images WHERE id = ?")->execute([$id]);
        echo json_encode(['ok' => true]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'method_not_allowed']);
}
