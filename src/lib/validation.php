<?php
/**
 * fix (Andrea): helpers compartidos para que un campo de texto que supera el
 * límite de caracteres de su columna no rompa el guardado entero. Antes cada
 * API dejaba que MySQL lanzara una excepción sin capturar ante un valor
 * demasiado largo (título, slug, meta descripción...): salía un error fatal
 * de PHP en vez de JSON, y el panel lo veía como una respuesta rota — en
 * páginas llegó a confundirse con una sesión caducada, porque el síntoma es
 * idéntico (una respuesta que no es JSON válido). Además, como los
 * formularios reenvían todos los campos en cada guardado (no solo lo que se
 * ha tocado), un valor que ya se hubiera guardado demasiado largo alguna vez
 * (por ejemplo, antes de que el formulario limitara los caracteres) volvía a
 * romper el guardado siempre, sin importar qué se cambiara.
 *
 * Ahora se acorta automáticamente lo que sobre y se avisa de qué se ha
 * acortado, para poder revisarlo con calma en vez de perder el guardado.
 */

function truncateField(?string $value, int $maxLength): ?string {
    if ($value === null) return null;
    return mb_substr($value, 0, $maxLength);
}

/**
 * Aplica truncateField() a los campos de $fields según $limits (clave => longitud
 * máxima de la columna) y devuelve [$fields ya acortados, etiquetas legibles
 * de qué se ha acortado]. $labels: clave => texto legible para el aviso.
 */
function truncateFieldsToLimits(array $fields, array $limits, array $labels): array {
    $truncatedLabels = [];
    foreach ($limits as $key => $maxLength) {
        if (!array_key_exists($key, $fields)) continue;
        $value = $fields[$key];
        if ($value !== null && mb_strlen((string) $value) > $maxLength) {
            $fields[$key] = truncateField((string) $value, $maxLength);
            $truncatedLabels[] = $labels[$key] ?? $key;
        }
    }
    return [$fields, $truncatedLabels];
}

function truncationWarningMessage(array $truncatedLabels): ?string {
    if (!$truncatedLabels) return null;
    return 'Se han acortado estos campos por superar el máximo permitido: '
        . implode(', ', $truncatedLabels) . '. Revísalos y guarda de nuevo si quieres ajustar el texto.';
}

/**
 * fix (Andrea): red de seguridad común — cualquier error de base de datos no
 * previsto ya no rompe la respuesta JSON con un error fatal de PHP. Se
 * registra en el log del servidor (para poder diagnosticarlo) y se devuelve
 * un mensaje claro en vez de dejar la pantalla a medias.
 */
function respondUnexpectedDbError(PDOException $e, string $context): void {
    error_log("$context: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'save_failed', 'message' => 'No se pudo guardar por un error del servidor. Si se repite, avisa — ya ha quedado registrado.']);
    exit;
}

/**
 * fix (Andrea): red de seguridad general para TODO el archivo (antes solo el
 * guardado —POST— atrapaba errores; un listado —GET— roto daba una pantalla
 * en blanco sin ninguna pista, como pasó con el fallo de "No se pudo cargar
 * el listado" en producción). Se registra el detalle completo en el log del
 * servidor, y como este endpoint solo es accesible con sesión de admin ya
 * iniciada, se incluye también un resumen técnico en la respuesta — así se
 * puede ver la causa real desde el propio navegador (pestaña Red) sin
 * necesitar acceso al log del servidor.
 */
function respondUnexpectedError(\Throwable $e, string $context): void {
    error_log("$context: " . get_class($e) . ': ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    echo json_encode([
        'error' => 'unexpected_error',
        'message' => 'Ha ocurrido un error inesperado. Si se repite, avisa — ya ha quedado registrado.',
        'debug' => get_class($e) . ': ' . $e->getMessage() . ' (' . basename($e->getFile()) . ':' . $e->getLine() . ')',
    ]);
    exit;
}
