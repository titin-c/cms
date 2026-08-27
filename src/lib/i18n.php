<?php
/**
 * Internacionalización ES/EN.
 * fix (Andrea, SEO): el idioma se determina por la URL (prefijo /en/ vía
 * .htaccess), no por cookie ni detección de navegador. Ver resolveLocale().
 */

const SUPPORTED_LOCALES = ['es', 'en'];
const DEFAULT_LOCALE = 'es';

$GLOBALS['__strings'] = [
    'es' => [
        'nav.contact' => 'Contacto',
        'hero.subtitle' => 'Fotógrafa profesional',
        'home.meta_description' => 'Portfolio profesional — proyectos, trabajo y contacto.',
        'a11y.skip_gallery' => 'Saltar galería de :category',
        'a11y.prev' => 'Anterior',
        'a11y.next' => 'Siguiente',
        'badge.language_notice' => 'Contenido disponible en español',
        'footer.rights' => 'Todos los derechos reservados',
        'cookie_banner.text' => 'Usamos cookies estrictamente necesarias para el funcionamiento del sitio. Si en el futuro añadimos analítica, te pediremos tu consentimiento aquí mismo.',
        'cookie_banner.accept' => 'Aceptar todo',
        'cookie_banner.reject' => 'Rechazar no esenciales',
        'cookie_banner.configure' => 'Configurar',
        'cookie_banner.policy_link' => 'Más información',
        'cookie_banner.settings_title' => 'Preferencias de cookies',
        'cookie_banner.necessary_title' => 'Necesarias',
        'cookie_banner.necessary_desc' => 'Imprescindibles para que el sitio funcione. Siempre activas.',
        'cookie_banner.analytics_title' => 'Analítica',
        'cookie_banner.analytics_desc' => 'Nos ayudaría a entender cómo se usa el sitio. Actualmente no se usa ninguna herramienta de este tipo.',
        'cookie_banner.save' => 'Guardar preferencias',
        'footer.cookie_prefs' => 'Preferencias de cookies',
        'contact.name' => 'Nombre',
        'contact.email' => 'Email',
        'contact.message' => 'Mensaje',
        'contact.send' => 'Enviar',
        'contact.sending' => 'Enviando...',
        'contact.sent' => 'Mensaje enviado. ¡Gracias!',
        'contact.error' => 'No se pudo enviar. Inténtalo de nuevo.',
        'coming_soon.default_message' => 'Estamos preparando algo nuevo. Vuelve pronto.',
    ],
    'en' => [
        'nav.contact' => 'Contact',
        'hero.subtitle' => 'Professional photographer',
        'home.meta_description' => 'Professional portfolio — projects, work and contact.',
        'a11y.skip_gallery' => 'Skip :category gallery',
        'a11y.prev' => 'Previous',
        'a11y.next' => 'Next',
        'badge.language_notice' => 'Content available in Spanish',
        'footer.rights' => 'All rights reserved',
        'cookie_banner.text' => 'We use strictly necessary cookies for the site to function. If we add analytics in the future, we\'ll ask for your consent right here.',
        'cookie_banner.accept' => 'Accept all',
        'cookie_banner.reject' => 'Reject non-essential',
        'cookie_banner.configure' => 'Configure',
        'cookie_banner.policy_link' => 'Learn more',
        'cookie_banner.settings_title' => 'Cookie preferences',
        'cookie_banner.necessary_title' => 'Necessary',
        'cookie_banner.necessary_desc' => 'Essential for the site to work. Always active.',
        'cookie_banner.analytics_title' => 'Analytics',
        'cookie_banner.analytics_desc' => 'Would help us understand how the site is used. No such tool is currently in use.',
        'cookie_banner.save' => 'Save preferences',
        'footer.cookie_prefs' => 'Cookie preferences',
        'contact.name' => 'Name',
        'contact.email' => 'Email',
        'contact.message' => 'Message',
        'contact.send' => 'Send',
        'contact.sending' => 'Sending...',
        'contact.sent' => 'Message sent. Thank you!',
        'contact.error' => 'Could not send. Please try again.',
        'coming_soon.default_message' => 'We\'re working on something new. Check back soon.',
    ],
];

/**
 * fix (Andrea, SEO): el idioma ahora lo determina la URL (prefijo /en/ vía
 * .htaccess), no una cookie ni la cabecera Accept-Language del navegador.
 * Google desaconseja explícitamente servir/redirigir contenido según
 * Accept-Language porque puede confundir al rastreador (Googlebot no manda
 * esa cabecera con contexto de usuario real). Con URLs distintas por idioma,
 * cada una se indexa por separado y el rastreo es consistente.
 */
function resolveLocale(): string {
    $lang = $_GET['lang'] ?? DEFAULT_LOCALE;
    return in_array($lang, SUPPORTED_LOCALES, true) ? $lang : DEFAULT_LOCALE;
}

function t(string $key, array $replacements = []): string {
    $locale = $GLOBALS['__locale'] ?? DEFAULT_LOCALE;
    $str = $GLOBALS['__strings'][$locale][$key] ?? $key;
    foreach ($replacements as $k => $v) {
        $str = str_replace(":$k", $v, $str);
    }
    return $str;
}

/** URL absoluta de la home en el idioma indicado — usada en hreflang y en el logo/marca. */
function localeHomeUrl(string $locale): string {
    return $locale === 'en' ? '/en/' : '/';
}
