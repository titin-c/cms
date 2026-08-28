<?php
/**
 * API de proyectos — consumida por el panel de admin (fetch desde JS nativo).
 * Todas las rutas requieren sesión de admin activa.
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../../src/lib/auth.php';
require_once __DIR__ . '/../../src/lib/db.php';
require_once __DIR__ . '/../../src/lib/validation.php';

requireAuth();
$pdo = getDb();
$method = $_SERVER['REQUEST_METHOD'];

function slugify(string $text): string {
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    $text = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $text));
    return trim($text, '-');
}

try {
switch ($method) {
    case 'GET':
        // Listado con búsqueda/filtro (dashboard) — relevante desde el lanzamiento por el volumen esperado (research-agent)
        $search = $_GET['q'] ?? '';
        $stmt = $pdo->prepare("
            SELECT p.id, p.slug, p.slug_en, p.title_es, p.status, p.featured, p.sort_order, p.main_image,
                   c.title_es AS category_title
            FROM projects p JOIN categories c ON c.id = p.category_id
            WHERE p.title_es LIKE ?
            ORDER BY p.updated_at DESC
        ");
        $stmt->execute(["%$search%"]);
        echo json_encode($stmt->fetchAll());
        break;

    case 'POST':
        // Crear o actualizar como borrador/publicado.
        // fix (ux-agent): autoguardado — este mismo endpoint se llama tanto al
        // guardar borrador como al publicar; solo cambia "status".
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        $required = ['main_image', 'title_es', 'content_es'];
        $status = $input['status'] ?? 'draft';
        if ($status === 'published') {
            $fieldLabels = ['main_image' => 'Imagen principal', 'title_es' => 'Título', 'content_es' => 'Texto'];
            foreach ($required as $field) {
                $value = $input[$field] ?? '';
                $isEmpty = $field === 'content_es'
                    ? trim(strip_tags($value)) === '' // Quill vacío suele ser "<p><br></p>", sin texto real
                    : trim((string) $value) === '';
                if ($isEmpty) {
                    http_response_code(422);
                    echo json_encode([
                        'error' => 'missing_required_field',
                        'field' => $field,
                        'message' => "Falta un campo obligatorio: {$fieldLabels[$field]}.",
                    ]);
                    exit;
                }
            }
        }

        $slug = $input['slug'] ?? slugify($input['title_es'] ?? 'proyecto-' . time());
        $id = $input['id'] ?? null;

        // fix (Andrea, SEO): slug en inglés — si Andrea no lo rellena a mano,
        // se genera automáticamente a partir del título en inglés (si existe).
        $slugEn = trim((string) ($input['slug_en'] ?? ''));
        if ($slugEn === '' && !empty($input['title_en'])) {
            $slugEn = slugify($input['title_en']);
        }

        $fields = [
            'slug' => $slug,
            'slug_en' => $slugEn !== '' ? $slugEn : null,
            'category_id' => $input['category_id'],
            'main_image' => $input['main_image'] ?? '',
            'main_image_alt' => $input['main_image_alt'] ?? null,
            'featured' => !empty($input['featured']) ? 1 : 0,
            'sort_order' => $input['sort_order'] ?? 0,
            'status' => $status,
            'project_date' => $input['project_date'] ?? null,
            'title_es' => $input['title_es'] ?? '',
            'content_es' => $input['content_es'] ?? '',
            'excerpt_es' => $input['excerpt_es'] ?? null,
            'title_en' => $input['title_en'] ?? null,
            'content_en' => $input['content_en'] ?? null,
            'excerpt_en' => $input['excerpt_en'] ?? null,
            // fix (Andrea, SEO): sustituye a seo_keywords/seo_keywords_en (en
            // desuso desde la 1.10.9 — Google ignora las meta keywords desde
            // hace años).
            'meta_title_es' => $input['meta_title_es'] ?? null,
            'meta_title_en' => $input['meta_title_en'] ?? null,
            'seo_description_es' => $input['seo_description_es'] ?? null,
            'seo_description_en' => $input['seo_description_en'] ?? null,
        ];

        // fix (Andrea): acorta lo que sobre en vez de dejar que MySQL rechace
        // el guardado entero — ver comentario en src/lib/validation.php.
        [$fields, $truncatedFields] = truncateFieldsToLimits($fields, [
            'slug' => 160, 'slug_en' => 160,
            'main_image' => 255, 'main_image_alt' => 255,
            'title_es' => 200, 'title_en' => 200,
            'meta_title_es' => 200, 'meta_title_en' => 200,
            'seo_description_es' => 300, 'seo_description_en' => 300,
        ], [
            'slug' => 'URL slug (ES)', 'slug_en' => 'URL slug (EN)',
            'main_image' => 'Imagen principal', 'main_image_alt' => 'Alt de la imagen principal',
            'title_es' => 'Título (ES)', 'title_en' => 'Título (EN)',
            'meta_title_es' => 'Meta título (ES)', 'meta_title_en' => 'Meta título (EN)',
            'seo_description_es' => 'Meta descripción (ES)', 'seo_description_en' => 'Meta descripción (EN)',
        ]);
        $warning = truncationWarningMessage($truncatedFields);
        $slug = $fields['slug']; // por si se ha acortado arriba

        try {
            if ($id) {
                $set = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($fields)));
                $stmt = $pdo->prepare("UPDATE projects SET $set WHERE id = :id");
                $fields['id'] = $id;
                $stmt->execute($fields);
            } else {
                $cols = implode(', ', array_keys($fields));
                $placeholders = implode(', ', array_map(fn($k) => ":$k", array_keys($fields)));
                $stmt = $pdo->prepare("INSERT INTO projects ($cols) VALUES ($placeholders)");
                $stmt->execute($fields);
                $id = $pdo->lastInsertId();
            }
        } catch (PDOException $e) {
            // fix: mensaje claro en vez de un fatal error crudo (bug real que ya vivimos con slugs duplicados)
            if ($e->getCode() === '23000') {
                http_response_code(409);
                $isSlugEn = str_contains($e->getMessage(), 'slug_en');
                echo json_encode([
                    'error' => 'duplicate_slug',
                    'message' => $isSlugEn
                        ? 'Ya existe un proyecto con ese slug en inglés. Cámbialo manualmente.'
                        : 'Ya existe un proyecto con esa URL (slug). Cambia el título o edita el slug.',
                ]);
                exit;
            }
            respondUnexpectedDbError($e, 'projects.php save error');
        }

        // fix (Andrea): sincroniza categorías adicionales (many-to-many) —
        // se borran las anteriores y se insertan las seleccionadas, excluyendo
        // la categoría principal por si acaso se marcó también como "adicional"
        $extraCategoryIds = array_filter(
            array_map('intval', $input['extra_categories'] ?? []),
            fn($catId) => $catId != $fields['category_id']
        );
        $pdo->prepare("DELETE FROM project_extra_categories WHERE project_id = ?")->execute([$id]);
        if ($extraCategoryIds) {
            $insertExtra = $pdo->prepare("INSERT IGNORE INTO project_extra_categories (project_id, category_id) VALUES (?, ?)");
            foreach ($extraCategoryIds as $catId) {
                $insertExtra->execute([$id, $catId]);
            }
        }

        $response = ['ok' => true, 'id' => $id, 'slug' => $slug, 'status' => $status];
        if ($warning) $response['warning'] = $warning;
        echo json_encode($response);
        break;

    case 'DELETE':
        $id = $_GET['id'] ?? null;
        if (!$id) { http_response_code(422); echo json_encode(['error' => 'missing_id']); exit; }
        $pdo->prepare("DELETE FROM projects WHERE id = ?")->execute([$id]);
        echo json_encode(['ok' => true]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'method_not_allowed']);
}
} catch (\Throwable $e) {
    respondUnexpectedError($e, 'projects.php');
}
