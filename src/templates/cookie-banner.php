<?php
/**
 * Banner de consentimiento de cookies — conforme a criterio AEPD:
 * botones de Aceptar y Rechazar con el mismo peso visual, sin "cookie wall",
 * opción de configurar por categorías, accesible en cualquier momento
 * (enlace "Preferencias de cookies" en el footer).
 */
$__cookiesPolicyUrl = ($GLOBALS['__locale'] ?? 'es') === 'en' ? '/en/cookies' : '/cookies';
?>
<div id="cookie-banner" class="cookie-banner" role="region" aria-label="<?= t('cookie_banner.settings_title') ?>" hidden>
  <p class="cookie-banner__text">
    <?= t('cookie_banner.text') ?>
    <a href="<?= $__cookiesPolicyUrl ?>"><?= t('cookie_banner.policy_link') ?></a>
  </p>
  <div class="cookie-banner__actions">
    <button type="button" class="cookie-banner__btn cookie-banner__btn--secondary" data-cookie-configure><?= t('cookie_banner.configure') ?></button>
    <button type="button" class="cookie-banner__btn cookie-banner__btn--secondary" data-cookie-reject><?= t('cookie_banner.reject') ?></button>
    <button type="button" class="cookie-banner__btn cookie-banner__btn--primary" data-cookie-accept><?= t('cookie_banner.accept') ?></button>
  </div>
</div>

<div id="cookie-settings" class="cookie-settings" role="dialog" aria-modal="true" aria-labelledby="cookie-settings-title" hidden>
  <div class="cookie-settings__overlay" data-cookie-close></div>
  <div class="cookie-settings__panel">
    <h2 id="cookie-settings-title"><?= t('cookie_banner.settings_title') ?></h2>

    <div class="cookie-settings__category">
      <div class="cookie-settings__category-header">
        <span><?= t('cookie_banner.necessary_title') ?></span>
        <input type="checkbox" checked disabled aria-label="<?= t('cookie_banner.necessary_title') ?>">
      </div>
      <p><?= t('cookie_banner.necessary_desc') ?></p>
    </div>

    <div class="cookie-settings__category">
      <div class="cookie-settings__category-header">
        <span><?= t('cookie_banner.analytics_title') ?></span>
        <input type="checkbox" id="cookie-analytics-toggle" aria-label="<?= t('cookie_banner.analytics_title') ?>">
      </div>
      <p><?= t('cookie_banner.analytics_desc') ?></p>
    </div>

    <div class="cookie-settings__actions">
      <button type="button" class="cookie-banner__btn cookie-banner__btn--secondary" data-cookie-close>Cancelar</button>
      <button type="button" class="cookie-banner__btn cookie-banner__btn--primary" data-cookie-save><?= t('cookie_banner.save') ?></button>
    </div>
  </div>
</div>
