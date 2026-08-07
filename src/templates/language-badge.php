<?php
/**
 * fix (Andrea, adenda paso 4): NUNCA visible en español (el texto ES es
 * obligatorio, siempre hay contenido). Solo aparece cuando el visitante
 * navega en un idioma no hispanohablante y el proyecto no tiene traducción
 * a ese idioma. El propio badge se muestra en inglés, dirigido a ese público.
 *
 * $hasTranslation: bool — si el proyecto tiene title_en + content_en rellenos.
 */
$locale = $GLOBALS['__locale'] ?? 'es';
if ($locale !== 'es' && !$hasTranslation):
?>
  <span class="language-badge"><?= t('badge.language_notice') ?></span>
<?php endif; ?>
