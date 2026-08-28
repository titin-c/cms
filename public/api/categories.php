<?php
/**
 * fix (qa-agent, adenda de Andrea): al borrar una categoría con proyectos,
 * estos se reasignan automáticamente a "Sin categorizar" (categoría por
 * defecto, creada en el seed, no eliminable).
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../../src/lib/auth.php';
require_once __DIR__ . '/../../src/lib/db.php';
require_once __DIR__ . '/../../src/lib/validation.php';

requireAuth();
$pdo = getDb();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        echo json_encode($pdo->query("SELECT * FROM categories ORDER BY sort_order ASC")->fetchAll());
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $slug = $input['slug'] ?? strtolower(preg_replace('/[^a-z0-9]+/i', '-', $input['title_es']));

        // fix (Andrea, SEO): slug en inglés — auto-generado del título EN si se deja vacío
        $slugEn = trim((string) ($input['slug_en'] ?? ''));
        if ($slugEn === '' && !empty($input['title_en'])) {
            $slugEn = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($input['title_en'])));
        }
        $slugEn = $slugEn !== '' ? trim($slugEn, '-') : null;

        $fieldNames = [
            'title_en', 'description_es', 'description_en',
            'home_title_es', 'home_title_en', 'home_description_es', 'home_description_en',
            'meta_description_es', 'meta_description_en',
            'button_label_es', 'button_label_en',
            'seo_keywords_es', 'seo_keywords_en',
        ];
        $values = ['title_es' => $input['title_es'] ?? '']; // obligatorio, nunca NULL
        foreach ($fieldNames as $f) {
            $values[$f] = trim((string) ($input[$f] ?? '')) !== '' ? trim($input[$f]) : null;
        }
        $values['slug'] = $slug;
        $values['slug_en'] = $slugEn;

        // fix (Andrea): acorta lo que sobre en vez de dejar que MySQL rechace
        // el guardado entero — ver comentario en src/lib/validation.php.
        [$values, $truncatedFields] = truncateFieldsToLimits($values, [
            'slug' => 120, 'slug_en' => 120,
            'title_es' => 160, 'title_en' => 160,
            'home_title_es' => 120, 'home_title_en' => 120,
            'meta_description_es' => 300, 'meta_description_en' => 300,
            'button_label_es' => 60, 'button_label_en' => 60,
            'seo_keywords_es' => 300, 'seo_keywords_en' => 300,
        ], [
            'slug' => 'URL slug (ES)', 'slug_en' => 'URL slug (EN)',
            'title_es' => 'Título (ES)', 'title_en' => 'Título (EN)',
            'home_title_es' => 'Título en la home (ES)', 'home_title_en' => 'Título en la home (EN)',
            'meta_description_es' => 'Meta descripción (ES)', 'meta_description_en' => 'Meta descripción (EN)',
            'button_label_es' => 'Texto del botón (ES)', 'button_label_en' => 'Texto del botón (EN)',
            'seo_keywords_es' => 'Palabras clave (ES)', 'seo_keywords_en' => 'Palabras clave (EN)',
        ]);
        $slug = $values['slug'];
        $slugEn = $values['slug_en'];
        unset($values['slug'], $values['slug_en']); // se añaden aparte según sea alta o edición, como antes

        $response = ['ok' => true];
        if ($warning = truncationWarningMessage($truncatedFields)) {
            $response['warning'] = $warning;
        }

        try {
            if (!empty($input['id'])) {
                // fix (Andrea, adenda gestión de categorías): edición de categoría existente
                $set = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($values)));
                $stmt = $pdo->prepare("UPDATE categories SET $set, slug_en = :slug_en WHERE id = :id");
                $stmt->execute($values + ['slug_en' => $slugEn, 'id' => $input['id']]);
                echo json_encode($response + ['id' => $input['id']]);
                break;
            }

            $values['slug'] = $slug;
            $values['slug_en'] = $slugEn;
            $cols = implode(', ', array_keys($values));
            $placeholders = implode(', ', array_map(fn($k) => ":$k", array_keys($values)));
            $stmt = $pdo->prepare("INSERT INTO categories ($cols) VALUES ($placeholders)");
            $stmt->execute($values);
            echo json_encode($response + ['id' => $pdo->lastInsertId()]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                http_response_code(409);
                echo json_encode(['error' => 'duplicate_slug', 'message' => 'Ya existe una categoría con esa URL (slug ES o EN). Ajusta el título.']);
                exit;
            }
            respondUnexpectedDbError($e, 'categories.php save error');
        }
        break;

    case 'DELETE':
        $id = (int) ($_GET['id'] ?? 0);
        $check = $pdo->prepare("SELECT is_default_uncategorized FROM categories WHERE id = ?");
        $check->execute([$id]);
        $cat = $check->fetch();

        if (!$cat) { http_response_code(404); echo json_encode(['error' => 'not_found']); exit; }
        if ($cat['is_default_uncategorized']) {
            http_response_code(403);
            echo json_encode(['error' => 'cannot_delete_default']);
            exit;
        }

        $fallback = $pdo->query("SELECT id FROM categories WHERE is_default_uncategorized = 1 LIMIT 1")->fetchColumn();

        $pdo->beginTransaction();
        $pdo->prepare("UPDATE projects SET category_id = ? WHERE category_id = ?")->execute([$fallback, $id]);
        $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
        $pdo->commit();

        echo json_encode(['ok' => true, 'reassigned_to' => $fallback]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'method_not_allowed']);
}
