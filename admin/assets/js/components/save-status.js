/**
 * SaveStatusIndicator — fix (usability-agent 🟠): feedback consistente y
 * persistente para toda acción asíncrona (autoguardado, publicar, contacto).
 * El texto discreto queda siempre visible para comprobar el estado en
 * cualquier momento.
 *
 * fix (Andrea): además, un aviso flotante arriba a la derecha que aparece y
 * desaparece solo — el texto discreto por sí solo pasaba desapercibido y
 * llevaba a pulsar "Guardar" varias veces esperando una reacción visible.
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

  // Solo para estados finales (no "guardando..."/"publicando...", que son
  // transitorios) — evita un aviso flotante por cada tecla del autoguardado
  if (state === 'saved' || state === 'sent') {
    showToast(customMessage || MESSAGES[state], 'success');
  } else if (state === 'error') {
    showToast(customMessage || MESSAGES[state], 'error');
  }
};

function showToast(message, type) {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.className = `toast toast--${type}`;
  toast.setAttribute('role', 'status');
  toast.textContent = message;
  container.appendChild(toast);

  requestAnimationFrame(() => toast.classList.add('is-visible'));
  setTimeout(() => {
    toast.classList.remove('is-visible');
    setTimeout(() => toast.remove(), 250);
  }, 2600);
}
