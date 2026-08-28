<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../src/lib/auth.php';
require_once __DIR__ . '/../../src/lib/image.php';

requireAuth();

const MAX_SIZE_BYTES = 15 * 1024 * 1024; // 15MB — margen amplio para foto profesional sin editar
const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

if (empty($_FILES['image'])) {
    http_response_code(422);
    echo json_encode(['error' => 'no_file']);
    exit;
}

$file = $_FILES['image'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(422);
    echo json_encode(['error' => 'upload_error']);
    exit;
}
if ($file['size'] > MAX_SIZE_BYTES) {
    http_response_code(422);
    echo json_encode(['error' => 'file_too_large', 'message' => 'Imagen demasiado grande, máximo 15MB.']);
    exit;
}
$mime = mime_content_type($file['tmp_name']);
if (!in_array($mime, ALLOWED_TYPES, true)) {
    http_response_code(422);
    echo json_encode(['error' => 'invalid_format', 'message' => 'Formato no soportado, usa JPG, PNG o WebP.']);
    exit;
}

$uploadDir = __DIR__ . '/../uploads';

// fix (Andrea, SEO Google Images): antes el nombre de archivo era un hash
// aleatorio sin ningún valor semántico (ej. "a1b2c3d4-thumb.webp"). Ahora se
// construye a partir de un "hint" descriptivo que manda el frontend (título
// del proyecto + categoría), dando nombres como
// "fotografia-moda-editorial-oliva-playa-a3f9.webp" — el sufijo corto
// garantiza que nunca colisione con otro archivo, aunque el hint se repita.
$seoHint = trim((string) ($_POST['seo_hint'] ?? ''));
$slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $seoHint));
$slug = trim($slug, '-');
$slug = substr($slug, 0, 80); // evita nombres de archivo absurdamente largos

$uniqueSuffix = bin2hex(random_bytes(3)); // 6 caracteres, suficiente para evitar colisiones
$baseName = $slug !== '' ? "{$slug}-{$uniqueSuffix}" : bin2hex(random_bytes(8));

try {
    $variants = generateImageVariants($file['tmp_name'], $uploadDir, $baseName);
    echo json_encode([
        'ok' => true,
        'thumb' => $variants['thumb'],
        'mobile' => $variants['mobile'],
        'desktop' => $variants['desktop'],
    ]);
} catch (InvalidArgumentException $e) {
    http_response_code(422);
    echo json_encode(['error' => 'processing_failed', 'message' => $e->getMessage()]);
}
