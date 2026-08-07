<?php
/**
 * Convierte una URL de YouTube/Vimeo (en cualquier formato habitual) en la
 * URL de embed correspondiente, para usar en el lightbox.
 */
function videoEmbedUrl(string $url, string $provider): ?string {
    if ($provider === 'youtube') {
        if (preg_match('#(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/))([A-Za-z0-9_-]{6,})#', $url, $m)) {
            return "https://www.youtube.com/embed/{$m[1]}?autoplay=1&rel=0";
        }
        return null;
    }
    if ($provider === 'vimeo') {
        if (preg_match('#vimeo\.com/(?:video/)?(\d+)#', $url, $m)) {
            return "https://player.vimeo.com/video/{$m[1]}?autoplay=1";
        }
        return null;
    }
    return null; // 'other': no se puede garantizar que admita embeberse — usar "enlace externo"
}
