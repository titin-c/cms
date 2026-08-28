<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../src/lib/auth.php';
require_once __DIR__ . '/../../src/lib/db.php';
require_once __DIR__ . '/../../src/lib/validation.php';

requireAuth();
$pdo = getDb();
$method = $_SERVER['REQUEST_METHOD'];

function slugifyVideo(string $text): string {
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text) ?: $text;
    $text = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $text));
    return trim($text, '-');
}

try {
switch ($method) {
    case 'GET':
        $search = $_GET['q'] ?? '';
        $stmt = $pdo->prepare("SELECT * FROM videos WHERE title_es LIKE ? ORDER BY sort_order ASC, id DESC");
        $stmt->execute(["%$search%"]);
        echo json_encode($stmt->fetchAll());
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $status = $input['status'] ?? 'draft';

        if ($status === 'published') {
            foreach (['thumbnail', 'title_es', 'video_url'] as $required) {
                if (empty($input[$required])) {
                    http_response_code(422);
                    echo json_encode(['error' => 'missing_required_field', 'field' => $required, 'message' => "Falta un campo obligatorio: $required."]);
                    exit;
                }
            }
        }

        // fix (Andrea, SEO): al editar, se mantiene el slug (URL) ya
        // publicado en vez de regenerarlo del título — el formulario nunca
        // ha tenido un campo para editarlo a mano, así que antes CADA
        // guardado lo recalculaba desde el título, y arreglar una errata
        // cambiaba la URL pública del vídeo sin avisar, rompiendo cualquier
        // enlace ya compartido. Solo se genera desde el título la primera
        // vez que se crea.
        if (!empty($input['id'])) {
            $existingSlugStmt = $pdo->prepare("SELECT slug FROM videos WHERE id = ?");
            $existingSlugStmt->execute([$input['id']]);
            $slug = $existingSlugStmt->fetchColumn() ?: slugifyVideo($input['title_es'] ?? 'video-' . time());
        } else {
            $slug = slugifyVideo($input['title_es'] ?? 'video-' . time());
        }
        $slugEn = trim((string) ($input['slug_en'] ?? ''));
        if ($slugEn === '' && !empty($input['title_en'])) { $slugEn = slugifyVideo($input['title_en']); }

        $fields = [
            'slug' => $slug,
            'slug_en' => $slugEn !== '' ? $slugEn : null,
            'title_es' => $input['title_es'] ?? '',
            'title_en' => $input['title_en'] ?? null,
            'subtitle_es' => $input['subtitle_es'] ?? null,
            'subtitle_en' => $input['subtitle_en'] ?? null,
            'thumbnail' => $input['thumbnail'] ?? '',
            'thumbnail_alt' => $input['thumbnail_alt'] ?? null,
            'video_url' => $input['video_url'] ?? '',
            'video_provider' => in_array($input['video_provider'] ?? '', ['youtube', 'vimeo', 'other']) ? $input['video_provider'] : 'youtube',
            'display_mode' => in_array($input['display_mode'] ?? '', ['lightbox', 'external']) ? $input['display_mode'] : 'lightbox',
            'featured' => !empty($input['featured']) ? 1 : 0,
            'sort_order' => $input['sort_order'] ?? 0,
            'status' => $status,
        ];

        // fix (Andrea): acorta lo que sobre en vez de dejar que MySQL rechace
        // el guardado entero — ver comentario en src/lib/validation.php.
        [$fields, $truncatedFields] = truncateFieldsToLimits($fields, [
            'slug' => 160, 'slug_en' => 160,
            'title_es' => 200, 'title_en' => 200,
            'subtitle_es' => 300, 'subtitle_en' => 300,
            'thumbnail' => 255, 'thumbnail_alt' => 255,
            'video_url' => 500,
        ], [
            'slug' => 'URL slug (ES)', 'slug_en' => 'URL slug (EN)',
            'title_es' => 'Título (ES)', 'title_en' => 'Título (EN)',
            'subtitle_es' => 'Subtítulo (ES)', 'subtitle_en' => 'Subtítulo (EN)',
            'thumbnail' => 'Miniatura', 'thumbnail_alt' => 'Alt de la miniatura',
            'video_url' => 'URL del vídeo',
        ]);
        $response = ['ok' => true, 'status' => $status];
        if ($warning = truncationWarningMessage($truncatedFields)) {
            $response['warning'] = $warning;
        }

        try {
            if (!empty($input['id'])) {
                $set = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($fields)));
                $stmt = $pdo->prepare("UPDATE videos SET $set WHERE id = :id");
                $stmt->execute($fields + ['id' => $input['id']]);
                echo json_encode($response + ['id' => $input['id']]);
                break;
            }
            $cols = implode(', ', array_keys($fields));
            $placeholders = implode(', ', array_map(fn($k) => ":$k", array_keys($fields)));
            $stmt = $pdo->prepare("INSERT INTO videos ($cols) VALUES ($placeholders)");
            $stmt->execute($fields);
            echo json_encode($response + ['id' => $pdo->lastInsertId()]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                http_response_code(409);
                echo json_encode(['error' => 'duplicate_slug', 'message' => 'Ya existe un vídeo con esa URL (slug). Cambia el título o el slug.']);
                exit;
            }
            respondUnexpectedDbError($e, 'videos.php save error');
        }
        break;

    case 'DELETE':
        $id = $_GET['id'] ?? null;
        if (!$id) { http_response_code(422); echo json_encode(['error' => 'missing_id']); exit; }
        $pdo->prepare("DELETE FROM videos WHERE id = ?")->execute([$id]);
        echo json_encode(['ok' => true]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'method_not_allowed']);
}
} catch (\Throwable $e) {
    respondUnexpectedError($e, 'videos.php');
}
