/**
 * SaveStatusIndicator — fix (usability-agent 🟠): feedback consistente y
 * persistente para toda acción asíncrona (autoguardado, publicar, contacto).
 * No es un toast que desaparece: el usuario debe poder verificar el estado
 * en cualquier momento.
 */

const MESSAGES = {
  saving: 'Guardando...',
  saved: 'Guardado hace un momento',
  sending: 'Publicando...',
  sent: 'Publicado',
  error: 'No se pudo guardar',
};

window.setSaveStatus = function setSaveStatus(el, state, customMessage) {
  if (!el) return;
  el.dataset.state = state;
  el.textContent = customMessage || MESSAGES[state] || '';
};
