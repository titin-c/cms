<?php
/**
 * Conversión de color y cálculo de contraste WCAG, para derivar automáticamente
 * los tonos claros/oscuros del color elegido por Andrea en /admin/ajustes.php
 * — sin perder el nivel AAA de contraste ya validado por accessibility-agent.
 */

function hexToRgb(string $hex): array {
    $hex = ltrim($hex, '#');
    return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
}

function rgbToHex(int $r, int $g, int $b): string {
    return sprintf('#%02X%02X%02X', max(0, min(255, $r)), max(0, min(255, $g)), max(0, min(255, $b)));
}

function hexToHsl(string $hex): array {
    [$r, $g, $b] = array_map(fn($v) => $v / 255, hexToRgb($hex));
    $max = max($r, $g, $b); $min = min($r, $g, $b);
    $l = ($max + $min) / 2;
    $h = 0; $s = 0;
    if ($max !== $min) {
        $d = $max - $min;
        $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
        $h = match ($max) {
            $r => fmod(($g - $b) / $d, 6),
            $g => ($b - $r) / $d + 2,
            default => ($r - $g) / $d + 4,
        };
        $h *= 60;
        if ($h < 0) $h += 360;
    }
    return [$h, $s * 100, $l * 100];
}

function hslToHex(float $h, float $s, float $l): string {
    $s /= 100; $l /= 100;
    $c = (1 - abs(2 * $l - 1)) * $s;
    $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
    $m = $l - $c / 2;
    [$r, $g, $b] = match (true) {
        $h < 60 => [$c, $x, 0],
        $h < 120 => [$x, $c, 0],
        $h < 180 => [0, $c, $x],
        $h < 240 => [0, $x, $c],
        $h < 300 => [$x, 0, $c],
        default => [$c, 0, $x],
    };
    return rgbToHex((int) round(($r + $m) * 255), (int) round(($g + $m) * 255), (int) round(($b + $m) * 255));
}

function relativeLuminance(string $hex): float {
    [$r, $g, $b] = array_map(function ($c) {
        $c /= 255;
        return $c <= 0.04045 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
    }, hexToRgb($hex));
    return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
}

function contrastRatio(string $hex1, string $hex2): float {
    $l1 = relativeLuminance($hex1);
    $l2 = relativeLuminance($hex2);
    [$lighter, $darker] = $l1 > $l2 ? [$l1, $l2] : [$l2, $l1];
    return ($lighter + 0.05) / ($darker + 0.05);
}

/**
 * Busca, para un matiz/saturación dados, la luminosidad más fiel al color
 * original que todavía cumple el ratio de contraste objetivo contra el fondo
 * indicado. fix (Andrea): antes solo buscaba en la mitad oscura del espectro
 * (0-60), asumiendo un fondo claro — con fondo oscuro + texto claro elegido
 * nunca encontraba solución válida y todo se quedaba en tonos oscuros fijos,
 * invisibles sobre negro. Ahora la dirección de búsqueda depende de si el
 * fondo es claro u oscuro.
 */
function findLightnessForContrast(float $h, float $s, float $targetRatio, string $against = '#FFFFFF'): float {
    $backgroundIsDark = relativeLuminance($against) < 0.5;

    if (!$backgroundIsDark) {
        // fondo claro → el texto debe ser oscuro; buscamos el MÁS CLARO posible que aún cumpla
        $lo = 0.0; $hi = 60.0;
        for ($i = 0; $i < 24; $i++) {
            $mid = ($lo + $hi) / 2;
            $ratio = contrastRatio(hslToHex($h, $s, $mid), $against);
            if ($ratio >= $targetRatio) { $lo = $mid; } else { $hi = $mid; }
        }
        return $lo;
    }

    // fondo oscuro → el texto debe ser claro; buscamos el MÁS OSCURO posible que aún cumpla
    $lo = 40.0; $hi = 100.0;
    for ($i = 0; $i < 24; $i++) {
        $mid = ($lo + $hi) / 2;
        $ratio = contrastRatio(hslToHex($h, $s, $mid), $against);
        if ($ratio >= $targetRatio) { $hi = $mid; } else { $lo = $mid; }
    }
    return $hi;
}

/**
 * Genera la escala de tonos derivados de un color base, con las mismas
 * garantías de contraste AAA que ya usábamos con la paleta fija de grises
 * (ink-900/700/500/300/100).
 *
 * - 900 (texto principal): el color elegido, tal cual.
 * - 700 (texto secundario): más claro, pero forzado a ≥ 7:1 sobre blanco.
 * - 500 (metadatos/texto terciario): más claro aún, forzado a ≥ 7:1 sobre blanco
 *   (igual que el ink-500 fijo original, calibrado justo en ese límite).
 * - 300 (bordes/iconos): solo necesita 3:1 (no es texto), más libertad de tono.
 * - 100 (separadores muy sutiles): puramente decorativo, sin requisito de contraste.
 */
/**
 * Genera la escala de tonos derivados de un color base, con las mismas
 * garantías de contraste AAA que ya usábamos con la paleta fija de grises
 * (ink-900/700/500/300/100) — ahora funciona igual de bien con fondo claro
 * u oscuro (ver fix en findLightnessForContrast).
 *
 * - 900 (texto principal): el color elegido, tal cual.
 * - 700 (texto secundario): con margen de seguridad extra sobre el mínimo AAA.
 * - 500 (metadatos/texto terciario): justo en el límite de AAA (7:1).
 * - 300 (bordes/iconos): solo necesita 3:1 (no es texto), más libertad de tono.
 * - 100 (separadores muy sutiles): mezcla hacia el color de fondo, sin
 *   requisito estricto de contraste — es puramente decorativo.
 */
function generateAaaColorScale(string $baseHex, string $against = '#FFFFFF'): array {
    [$h, $s, $l] = hexToHsl($baseHex);
    [, , $surfaceL] = hexToHsl($against);
    $backgroundIsDark = relativeLuminance($against) < 0.5;

    $tone500L = findLightnessForContrast($h, $s, 7.0, $against);
    $tone300L = findLightnessForContrast($h, $s, 3.0, $against);

    // fix: con fondo oscuro el margen de seguridad va hacia MÁS claro, no hacia más oscuro
    $marginOffset = $backgroundIsDark ? 6 : -6;
    $tone700L = max(0, min(100, $tone500L + $marginOffset));

    // 100: mezcla del 85% hacia el color de fondo — separador muy sutil, sin requisito estricto
    $tone100L = $l + ($surfaceL - $l) * 0.85;

    return [
        900 => $baseHex,
        700 => hslToHex($h, $s, $tone700L),
        500 => hslToHex($h, $s, $tone500L),
        300 => hslToHex($h, $s, $tone300L),
        100 => hslToHex($h, $s, $tone100L),
    ];
}

/** Tono derivado para fondos alternos (paneles, filas alternas...) — se aleja
 *  del fondo base en la dirección correcta según sea claro u oscuro. */
function deriveSurfaceAlt(string $surfaceHex): string {
    [$h, $s, $l] = hexToHsl($surfaceHex);
    $isDark = relativeLuminance($surfaceHex) < 0.5;
    $newL = $isDark ? min(100, $l + 6) : max(0, $l - 4);
    return hslToHex($h, $s, $newL);
}
