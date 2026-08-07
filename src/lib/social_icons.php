<?php
/** Plataformas de redes sociales soportadas y su icono (mismo estilo lineal del resto del sitio). */

const SOCIAL_PLATFORMS = [
    'instagram' => 'Instagram',
    'youtube' => 'YouTube',
    'tiktok' => 'TikTok',
    'facebook' => 'Facebook',
    'twitter' => 'X / Twitter',
    'linkedin' => 'LinkedIn',
    'pinterest' => 'Pinterest',
    'vimeo' => 'Vimeo',
    'behance' => 'Behance',
    'substack' => 'Substack',
    'website' => 'Sitio web / otro',
];

function socialIconSvg(string $platform, int $size = 17): string {
    $s = $size;
    $paths = [
        'instagram' => '<rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1"/>',
        'youtube' => '<rect x="2" y="5" width="20" height="14" rx="4"/><path d="M10 9l6 3-6 3z" fill="currentColor" stroke="none"/>',
        'tiktok' => '<path d="M14 3v11.5a3.5 3.5 0 1 1-3-3.46"/><path d="M14 3c0 3 2.5 5 5 5"/>',
        'facebook' => '<path d="M16 8h-2a2 2 0 0 0-2 2v3H9v3h3v6h3v-6h2.5l.5-3H15v-2.5a.5.5 0 0 1 .5-.5H16z"/><circle cx="12" cy="12" r="10"/>',
        'twitter' => '<path d="M4 4l16 16M20 4L4 20"/>',
        'linkedin' => '<rect x="2" y="2" width="20" height="20" rx="3"/><circle cx="7" cy="8" r="1"/><path d="M7 11v6M12 11v6M12 13.5c0-1.5 1-2.5 2.5-2.5s2.5 1 2.5 2.5V17"/>',
        'pinterest' => '<circle cx="12" cy="12" r="10"/><path d="M9 17c1-4 1.5-6 1.5-8a2.5 2.5 0 0 1 5 0c0 2-1 4-1 5.5a1.8 1.8 0 0 0 3.5-.5c0-3-2-6-6-6a6 6 0 0 0-4 10.5"/>',
        'vimeo' => '<path d="M22 7.5c-.1 2-1.5 4.7-4.2 8.1-2.8 3.5-5.1 5.2-7.1 5.2-1.2 0-2.2-1.1-3.1-3.4L6 12.2C5.4 10 4.8 8.9 4.2 8.9c-.1 0-.6.3-1.4.9L2 8.6c1-.9 3.9-3.6 5.2-3.7 1.4-.1 2.2.8 2.6 2.7.4 2.1.7 3.4.9 4 .5 1.7.9 2.6 1.3 2.6.3 0 .8-.5 1.4-1.5.6-1 1-1.8 1-2.3.1-.9-.3-1.4-1.2-1.4-.4 0-.8.1-1.3.3.8-2.7 2.4-4.1 4.7-4 1.7 0 2.5 1.1 2.4 3.3z" stroke="none" fill="currentColor"/>',
        'behance' => '<rect x="2" y="7" width="8" height="10" rx="1"/><path d="M2 11h8M14 12a4 4 0 1 1 7.9 1H14a3 3 0 0 0 5.5 1.5M14.5 8h4"/>',
        'substack' => '<path d="M4 4h16v3H4zM4 9h16v3H4zM4 14h16l-8 7z"/>',
        'website' => '<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20 15 15 0 0 1 0-20z"/>',
    ];
    $path = $paths[$platform] ?? $paths['website'];
    return "<svg width=\"{$s}\" height=\"{$s}\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"1.5\" aria-hidden=\"true\">{$path}</svg>";
}
