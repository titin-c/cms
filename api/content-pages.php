<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../src/lib/auth.php';
require_once __DIR__ . '/../src/lib/db.php';

requireAuth();
$pdo = getDb();
$method = $_SERVER['REQUEST_METHOD'];

function slugifyPhp(string $text): string {
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text) ?: $text;
    $text = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $text));
    return trim($text, '-');
}

switch ($method) {
    case 'GET':
        echo json_encode($pdo->query("SELECT * FROM content_pages ORDER BY sort_order ASC, id ASC")->fetchAll());
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        if (empty($input['title_es'])) {
            http_response_code(422);
            echo json_encode(['error' => 'missing_title', 'message' => 'El título en español es obligatorio.']);
            exit;
        }

        $slug = trim((string) ($input['slug'] ?? ''));
        if ($slug === '') { $slug = slugifyPhp($input['title_es']); }

        $slugEn = trim((string) ($input['slug_en'] ?? ''));
        if ($slugEn === '' && !empty($input['title_en'])) { $slugEn = slugifyPhp($input['title_en']); }

        $fields = [
            'slug' => $slug,
            'slug_en' => $slugEn !== '' ? $slugEn : null,
            'title_es' => $input['title_es'],
            'title_en' => $input['title_en'] ?? null,
            'content_es' => $input['content_es'] ?? '',
            'content_en' => $input['content_en'] ?? null,
            'meta_description_es' => $input['meta_description_es'] ?? null,
            'meta_description_en' => $input['meta_description_en'] ?? null,
            'show_in_header' => !empty($input['show_in_header']) ? 1 : 0,
            'show_in_footer' => !empty($input['show_in_footer']) ? 1 : 0,
            'noindex' => !empty($input['noindex']) ? 1 : 0,
            'sort_order' => $input['sort_order'] ?? 0,
        ];

        try {
            if (!empty($input['id'])) {
                $set = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($fields)));
                $stmt = $pdo->prepare("UPDATE content_pages SET $set WHERE id = :id");
                $stmt->execute($fields + ['id' => $input['id']]);
                echo json_encode(['ok' => true, 'id' => $input['id']]);
                break;
            }

            $cols = implode(', ', array_keys($fields));
            $placeholders = implode(', ', array_map(fn($k) => ":$k", array_keys($fields)));
            $stmt = $pdo->prepare("INSERT INTO content_pages ($cols) VALUES ($placeholders)");
            $stmt->execute($fields);
            echo json_encode(['ok' => true, 'id' => $pdo->lastInsertId()]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                http_response_code(409);
                echo json_encode(['error' => 'duplicate_slug', 'message' => 'Ya existe una página con esa URL (slug ES o EN). Cambia el título o el slug.']);
                exit;
            }
            throw $e;
        }
        break;

    case 'DELETE':
        $id = $_GET['id'] ?? null;
        if (!$id) { http_response_code(422); echo json_encode(['error' => 'missing_id']); exit; }
        $pdo->prepare("DELETE FROM content_pages WHERE id = ?")->execute([$id]);
        echo json_encode(['ok' => true]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'method_not_allowed']);
}
