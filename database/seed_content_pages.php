<?php
/**
 * Ejecutar UNA VEZ tras crear la tabla content_pages (schema.sql o
 * migration_6_content_pages.sql):
 *
 *   php database/seed_content_pages.php
 *
 * Es seguro volver a ejecutarlo: usa ON DUPLICATE KEY UPDATE, así que no
 * duplica filas ni sobreescribe contenido si ya lo has editado desde el
 * panel (solo actualiza si decides forzarlo — ver comentario más abajo).
 */
require_once __DIR__ . '/../src/lib/db.php';

$pdo = getDb();

$pages = [
    'about' => [
        'slug' => 'sobre-mi',
        'slug_en' => 'about',
        'show_in_header' => 1,
        'show_in_footer' => 1,
        'sort_order' => 1,
        'title_es' => 'Sobre mí',
        'title_en' => 'About',
        'content_es' => '<p>Aquí irá tu biografía: tu trayectoria, tu estilo y lo que te mueve a la hora de trabajar. Este texto es un contenido provisional — sustitúyelo por tu propia historia cuando quieras.</p>',
        'content_en' => '<p>Your biography will go here: your background, your style, and what drives your work. This is placeholder content — replace it with your own story whenever you\'re ready.</p>',
        'meta_description_es' => 'Sobre mí — mi trayectoria y mi trabajo.',
        'meta_description_en' => 'About me — my background and my work.',
    ],
    'privacy' => [
        'slug' => 'privacidad',
        'slug_en' => 'privacy',
        'show_in_header' => 0,
        'show_in_footer' => 1,
        'sort_order' => 2,
        'noindex' => 1,
        'title_es' => 'Política de privacidad',
        'title_en' => 'Privacy Policy',
        'content_es' => '<p><strong>Responsable del tratamiento:</strong> [NOMBRE COMPLETO / RAZÓN SOCIAL], con NIF [NIF/NIE], domicilio en [DIRECCIÓN], correo de contacto [EMAIL].</p>
<h2>¿Qué datos recogemos?</h2>
<p>A través del formulario de contacto de esta web recogemos el nombre, la dirección de correo electrónico y el contenido del mensaje que nos facilitas voluntariamente al ponerte en contacto.</p>
<h2>Finalidad del tratamiento</h2>
<p>Responder a tus consultas y, en su caso, gestionar una posible relación comercial derivada de tu solicitud.</p>
<h2>Legitimación</h2>
<p>Tu consentimiento, otorgado al enviar el formulario de contacto.</p>
<h2>Plazo de conservación</h2>
<p>Tus datos se conservarán mientras sea necesario para atender tu consulta y, posteriormente, durante el plazo exigido por la legislación aplicable.</p>
<h2>Destinatarios</h2>
<p>No se ceden datos a terceros, salvo obligación legal. [Si usas un proveedor externo de email, hosting o CRM, indícalo aquí.]</p>
<h2>Tus derechos</h2>
<p>Puedes ejercer tus derechos de acceso, rectificación, supresión, oposición, limitación del tratamiento y portabilidad escribiendo a [EMAIL], adjuntando copia de un documento identificativo. También puedes reclamar ante la Agencia Española de Protección de Datos (aepd.es) si consideras que no se han respetado tus derechos.</p>
<h2>Seguridad</h2>
<p>[NOMBRE COMPLETO / RAZÓN SOCIAL] aplica medidas técnicas y organizativas razonables para proteger los datos facilitados frente a accesos no autorizados, pérdida o alteración.</p>',
        'content_en' => '<p><strong>Data controller:</strong> [FULL NAME / BUSINESS NAME], with tax ID [NIF/NIE], address at [ADDRESS], contact email [EMAIL].</p>
<h2>What data do we collect?</h2>
<p>Through the contact form on this website we collect the name, email address and message content you voluntarily provide when getting in touch.</p>
<h2>Purpose of processing</h2>
<p>To respond to your enquiries and, where applicable, to manage a possible commercial relationship arising from your request.</p>
<h2>Legal basis</h2>
<p>Your consent, given by submitting the contact form.</p>
<h2>Retention period</h2>
<p>Your data will be kept for as long as necessary to respond to your enquiry and, afterwards, for the period required to comply with legal obligations.</p>
<h2>Recipients</h2>
<p>No data is transferred to third parties, except where required by law. [If you use a third-party email provider, hosting, or CRM, list it here.]</p>
<h2>Your rights</h2>
<p>You may exercise your rights of access, rectification, erasure, objection, restriction of processing and portability by writing to [EMAIL], attaching a copy of an ID document. You may also lodge a complaint with the Spanish Data Protection Agency (aepd.es) if you believe your rights have not been respected.</p>
<h2>Security</h2>
<p>[FULL NAME / BUSINESS NAME] applies reasonable technical and organisational measures to protect the data provided against unauthorised access, loss or alteration.</p>',
        'meta_description_es' => null,
        'meta_description_en' => null,
    ],
    'cookies' => [
        'slug' => 'cookies',
        'slug_en' => 'cookies',
        'show_in_header' => 0,
        'show_in_footer' => 1,
        'sort_order' => 3,
        'noindex' => 1,
        'title_es' => 'Política de cookies',
        'title_en' => 'Cookie Policy',
        'content_es' => '<p>Esta web, publicada por [NOMBRE COMPLETO / RAZÓN SOCIAL], actualmente <strong>no utiliza cookies no esenciales</strong> — no hay analítica, ni publicidad, ni rastreo de terceros.</p>
<h2>Cookies actualmente en uso</h2>
<p><code>PHPSESSID</code> (estrictamente necesaria): mantiene activa la sesión del panel de administración de la propietaria de la web. No se establece para los visitantes de la parte pública del sitio. Duración: sesión.</p>
<p>Las cookies estrictamente necesarias están exentas del deber de consentimiento según la normativa española y europea (art. 22.2 LSSICE), al ser imprescindibles para el funcionamiento del sitio.</p>
<h2>Panel de consentimiento de cookies</h2>
<p>Verás un banner de consentimiento si en el futuro esta web empieza a usar cookies no esenciales (por ejemplo, analítica). Podrás aceptar, rechazar o configurar tus preferencias en cualquier momento desde el enlace "Preferencias de cookies" del pie de página.</p>
<h2>Gestionar cookies desde tu navegador</h2>
<p>También puedes eliminar o bloquear cookies en cualquier momento desde la configuración de tu navegador (Chrome, Firefox, Safari).</p>',
        'content_en' => '<p>This website, published by [FULL NAME / BUSINESS NAME], currently uses <strong>no non-essential cookies</strong> — no analytics, no advertising, no third-party tracking.</p>
<h2>Cookies currently in use</h2>
<p><code>PHPSESSID</code> (strictly necessary): keeps the site owner\'s admin session active. Not set for regular visitors browsing the public site. Duration: session.</p>
<p>Strictly necessary cookies are exempt from consent requirements under Spanish and EU law (LSSICE art. 22.2), as they are essential for the site to function.</p>
<h2>Cookie consent banner</h2>
<p>You\'ll see a consent banner if this site starts using non-essential cookies (e.g. analytics) in the future. You\'ll be able to accept, reject, or configure your preferences at any time from the "Cookie preferences" link in the footer.</p>
<h2>Managing cookies from your browser</h2>
<p>You can also delete or block cookies at any time from your browser settings (Chrome, Firefox, Safari).</p>',
        'meta_description_es' => null,
        'meta_description_en' => null,
    ],
    'legal-notice' => [
        'slug' => 'aviso-legal',
        'slug_en' => 'legal-notice',
        'show_in_header' => 0,
        'show_in_footer' => 1,
        'sort_order' => 4,
        'noindex' => 1,
        'title_es' => 'Aviso legal',
        'title_en' => 'Legal Notice',
        'content_es' => '<h2>Datos identificativos</h2>
<p>En cumplimiento de la Ley 34/2002 de Servicios de la Sociedad de la Información (LSSICE), se facilitan los siguientes datos:</p>
<ul>
<li>Titular: [NOMBRE COMPLETO / RAZÓN SOCIAL]</li>
<li>NIF: [NIF/NIE]</li>
<li>Domicilio: [DIRECCIÓN]</li>
<li>Correo de contacto: [EMAIL]</li>
<li>[Si está dada de alta como empresa: datos de inscripción en el Registro Mercantil]</li>
</ul>
<h2>Objeto del sitio web</h2>
<p>Esta web muestra un portfolio fotográfico profesional y permite a los visitantes ponerse en contacto con [NOMBRE COMPLETO / RAZÓN SOCIAL] a través del formulario de contacto.</p>
<h2>Propiedad intelectual</h2>
<p>Todas las fotografías, textos y material gráfico publicados en este sitio son propiedad de [NOMBRE COMPLETO / RAZÓN SOCIAL], salvo indicación contraria, y no pueden reproducirse, distribuirse ni utilizarse sin autorización previa por escrito.</p>
<h2>Responsabilidad</h2>
<p>[NOMBRE COMPLETO / RAZÓN SOCIAL] no se hace responsable de los daños derivados del uso de este sitio web por parte de terceros, ni del contenido de sitios web externos enlazados desde esta página.</p>
<h2>Legislación y jurisdicción aplicable</h2>
<p>Estas condiciones se rigen por la legislación española. Cualquier controversia se someterá a los juzgados y tribunales de [CIUDAD], salvo que la normativa de protección de consumidores establezca otra cosa.</p>',
        'content_en' => '<h2>Ownership details</h2>
<p>In compliance with Spain\'s Law 34/2002 on Information Society Services (LSSICE), the following details are provided:</p>
<ul>
<li>Owner: [FULL NAME / BUSINESS NAME]</li>
<li>Tax ID: [NIF/NIE]</li>
<li>Registered address: [ADDRESS]</li>
<li>Contact email: [EMAIL]</li>
<li>[If registered as a company: Commercial Registry details]</li>
</ul>
<h2>Purpose of the website</h2>
<p>This website shows a professional photography portfolio and allows visitors to get in touch with [FULL NAME / BUSINESS NAME] through the contact form.</p>
<h2>Intellectual property</h2>
<p>All photographs, texts and graphic material published on this site are the property of [FULL NAME / BUSINESS NAME], unless stated otherwise, and may not be reproduced, distributed or used without prior written authorisation.</p>
<h2>Liability</h2>
<p>[FULL NAME / BUSINESS NAME] is not liable for damages arising from the use of this website by third parties, nor for the content of external websites linked from this site.</p>
<h2>Applicable law and jurisdiction</h2>
<p>These terms are governed by Spanish law. Any dispute will be submitted to the courts of [CITY], unless the applicable consumer protection regulations establish otherwise.</p>',
        'meta_description_es' => null,
        'meta_description_en' => null,
    ],
];

$stmt = $pdo->prepare("
    INSERT INTO content_pages (slug, slug_en, show_in_header, show_in_footer, noindex, sort_order, title_es, title_en, content_es, content_en, meta_description_es, meta_description_en)
    VALUES (:slug, :slug_en, :show_in_header, :show_in_footer, :noindex, :sort_order, :title_es, :title_en, :content_es, :content_en, :meta_description_es, :meta_description_en)
    ON DUPLICATE KEY UPDATE slug = slug
    -- fix: si la fila ya existe (por ejemplo ya la editaste desde el panel),
    -- este ON DUPLICATE KEY no la toca — solo evita el error de duplicado.
    -- Si quieres FORZAR el contenido original de nuevo, borra la fila
    -- correspondiente en phpMyAdmin antes de re-ejecutar este script.
");

foreach ($pages as $key => $data) {
    $stmt->execute([
        'slug' => $data['slug'],
        'slug_en' => $data['slug_en'],
        'show_in_header' => $data['show_in_header'],
        'show_in_footer' => $data['show_in_footer'],
        'noindex' => $data['noindex'] ?? 0,
        'sort_order' => $data['sort_order'],
        'title_es' => $data['title_es'],
        'title_en' => $data['title_en'],
        'content_es' => $data['content_es'],
        'content_en' => $data['content_en'],
        'meta_description_es' => $data['meta_description_es'],
        'meta_description_en' => $data['meta_description_en'],
    ]);
    echo "OK: {$key}\n";
}

echo "Listo.\n";
