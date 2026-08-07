/**
 * Consentimiento de cookies — conforme a criterio AEPD:
 * - No se muestra ningún script no esencial hasta que hay consentimiento explícito.
 * - "Aceptar" y "Rechazar" tienen el mismo peso visual (ver CSS).
 * - La preferencia se guarda en localStorage (no requiere una cookie para
 *   recordar la decisión) y es reabrible en cualquier momento.
 * - API pública: window.cookieConsent.hasAnalyticsConsent() — úsala para
 *   condicionar la carga de cualquier script de analítica que se añada en
 *   el futuro (ej: if (window.cookieConsent.hasAnalyticsConsent()) { ...cargar Analytics... }).
 */

const STORAGE_KEY = 'cookie_consent_v1';

function getConsent() {
  try {
    return JSON.parse(localStorage.getItem(STORAGE_KEY));
  } catch {
    return null;
  }
}

function saveConsent(analytics) {
  localStorage.setItem(STORAGE_KEY, JSON.stringify({ necessary: true, analytics, timestamp: Date.now() }));
}

window.cookieConsent = {
  hasAnalyticsConsent: () => getConsent()?.analytics === true,
};

const banner = document.getElementById('cookie-banner');
const settings = document.getElementById('cookie-settings');
const analyticsToggle = document.getElementById('cookie-analytics-toggle');

if (banner && settings) {
  const existing = getConsent();
  if (!existing) {
    banner.hidden = false; // fix: solo se muestra si el visitante no ha decidido todavía
  }

  document.querySelector('[data-cookie-accept]')?.addEventListener('click', () => {
    saveConsent(true);
    banner.hidden = true;
  });

  document.querySelector('[data-cookie-reject]')?.addEventListener('click', () => {
    saveConsent(false);
    banner.hidden = true;
  });

  document.querySelector('[data-cookie-configure]')?.addEventListener('click', () => {
    openSettings();
  });

  // fix: enlace "Preferencias de cookies" del footer reabre el panel en cualquier momento
  document.querySelectorAll('[data-open-cookie-settings]').forEach((el) => {
    el.addEventListener('click', (e) => { e.preventDefault(); openSettings(); });
  });

  function openSettings() {
    const current = getConsent();
    if (analyticsToggle) analyticsToggle.checked = current?.analytics === true;
    settings.hidden = false;
  }
  function closeSettings() { settings.hidden = true; }

  settings.querySelectorAll('[data-cookie-close]').forEach((el) => el.addEventListener('click', closeSettings));
  document.querySelector('[data-cookie-save]')?.addEventListener('click', () => {
    saveConsent(!!analyticsToggle?.checked);
    closeSettings();
    banner.hidden = true;
  });
}
