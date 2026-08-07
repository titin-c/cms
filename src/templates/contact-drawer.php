<?php
/**
 * fix (accessibility-agent/aria-patterns): role=dialog + aria-modal + focus trap
 * gestionado en contact-drawer.js. Email/teléfono se rellenan por JS, ofuscados
 * (nunca texto plano en el HTML fuente — requisito de protección de contacto).
 *
 * fix (CMS genérico): el email/teléfono ya no están fijos en el código —
 * vienen de los ajustes (/admin/ajustes.php → Identidad del sitio).
 */
$__contactEmail = $__themeSettings['contact_email'] ?? '';
$__contactPhone = $__themeSettings['contact_phone'] ?? '';
[$__emailUser, $__emailDomain] = str_contains($__contactEmail, '@') ? explode('@', $__contactEmail, 2) : ['', ''];
?>
<div id="contact-drawer" class="contact-drawer" role="dialog" aria-modal="true" aria-labelledby="contact-drawer-title" hidden>
  <div class="contact-drawer__overlay" data-close-contact></div>
  <div class="contact-drawer__panel">
    <button type="button" class="contact-drawer__close" data-close-contact aria-label="Cerrar">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
    </button>
    <h2 id="contact-drawer-title"><?= t('nav.contact') ?></h2>

    <?php if ($__contactEmail): ?>
      <p class="contact-drawer__direct">
        <a href="#" id="contact-email-link" data-email-user="<?= htmlspecialchars($__emailUser) ?>" data-email-domain="<?= htmlspecialchars($__emailDomain) ?>">...</a>
      </p>
    <?php endif; ?>
    <?php if ($__contactPhone): ?>
      <p class="contact-drawer__direct">
        <a href="#" id="contact-phone-link" data-phone="<?= htmlspecialchars($__contactPhone) ?>">...</a>
      </p>
    <?php endif; ?>

    <form id="contact-form" novalidate>
      <!-- honeypot invisible, nunca visible ni accesible para humanos -->
      <div class="contact-form__honeypot" aria-hidden="true">
        <label for="website">Website</label>
        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
      </div>

      <label for="contact-name"><?= t('contact.name') ?></label>
      <input type="text" id="contact-name" name="name" required>

      <label for="contact-email"><?= t('contact.email') ?></label>
      <input type="email" id="contact-email" name="email" required aria-describedby="contact-email-error">
      <span id="contact-email-error" role="alert" class="field-error"></span>

      <label for="contact-message"><?= t('contact.message') ?></label>
      <textarea id="contact-message" name="message" rows="4" required></textarea>

      <button type="submit" id="contact-submit"><?= t('contact.send') ?></button>
      <span id="contact-status" class="save-status" aria-live="polite"></span>
    </form>
  </div>
</div>
