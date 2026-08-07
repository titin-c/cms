<?php
/**
 * Generación de variantes de imagen para servir responsive (srcset).
 * fix (research/usability-agent): imágenes por tamaño real, nunca la original
 * en móvil — prioridad de rendimiento en conexiones lentas.
 * Usa GD (incluida por defecto en PHP), sin dependencias externas.
 */

const IMAGE_VARIANTS = [
    'thumb'   => 320,
    'mobile'  => 768,
    'desktop' => 1600,
];

/**
 * Genera las variantes de una imagen subida y devuelve las rutas relativas.
 */
function generateImageVariants(string $sourcePath, string $destDir, string $baseName): array {
    [$width, $height, $type] = getimagesize($sourcePath);

    $create = match ($type) {
        IMAGETYPE_JPEG => 'imagecreatefromjpeg',
        IMAGETYPE_PNG  => 'imagecreatefrompng',
        IMAGETYPE_WEBP => 'imagecreatefromwebp',
        default => null,
    };
    if (!$create) {
        throw new InvalidArgumentException('Formato de imagen no soportado. Usa JPG, PNG o WebP.');
    }

    $source = $create($sourcePath);
    $paths = [];

    foreach (IMAGE_VARIANTS as $label => $targetWidth) {
        $ratio = min(1, $targetWidth / $width);
        $newW = (int) round($width * $ratio);
        $newH = (int) round($height * $ratio);

        $resized = imagecreatetruecolor($newW, $newH);
        imagecopyresampled($resized, $source, 0, 0, 0, 0, $newW, $newH, $width, $height);

        $filename = "{$baseName}-{$label}.webp";
        $fullPath = rtrim($destDir, '/') . '/' . $filename;
        imagewebp($resized, $fullPath, 82);
        imagedestroy($resized);

        $paths[$label] = $filename;
    }

    imagedestroy($source);
    return $paths;
}

function buildSrcset(array $variantPaths, string $publicBaseUrl): string {
    $widths = ['thumb' => 320, 'mobile' => 768, 'desktop' => 1600];
    $parts = [];
    foreach ($variantPaths as $label => $filename) {
        $parts[] = rtrim($publicBaseUrl, '/') . '/' . $filename . ' ' . $widths[$label] . 'w';
    }
    return implode(', ', $parts);
}

/**
 * fix (Andrea): evita pixelación al mostrar una imagen a ancho completo.
 * Las tres variantes se generan con el mismo nombre base + sufijo
 * (ver generateImageVariants: "{base}-thumb.webp", "{base}-mobile.webp",
 * "{base}-desktop.webp"). Guardamos solo el nombre "-thumb.webp" en BD
 * (campo main_image); esta función deriva los otros dos a partir de ese.
 */
function variantFromThumbFilename(string $thumbFilename, string $variant): string {
    return str_replace('-thumb.webp', "-{$variant}.webp", $thumbFilename);
}

function fullSrcsetFromThumbFilename(string $thumbFilename): string {
    return sprintf(
        '/uploads/%s 320w, /uploads/%s 768w, /uploads/%s 1600w',
        $thumbFilename,
        variantFromThumbFilename($thumbFilename, 'mobile'),
        variantFromThumbFilename($thumbFilename, 'desktop')
    );
}
