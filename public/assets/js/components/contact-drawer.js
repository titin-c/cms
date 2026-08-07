/**
 * Drawer de contacto: apertura/cierre con focus trap, envío protegido
 * (honeypot server-side ya cubierto en /api/contact.php), y
 * fix (protección de contacto): email/teléfono nunca en texto plano en el HTML.
 * Se reconstruyen aquí a partir de fragmentos, evitando scraping simple.
 */

const drawer = document.getElementById('contact-drawer');
const openTriggers = document.querySelectorAll('[data-open-contact]');
const closeTriggers = drawer?.querySelectorAll('[data-close-contact]');
let lastFocusedTrigger = null;

// fix (protección de contacto): email/teléfono vienen de los ajustes del
// admin, se ensamblan aquí en runtime como enlaces mailto:/tel: reales —
// cada uno es independiente, no depende de un contenedor compartido.
function renderContactInfo() {
  const emailLink = document.getElementById('contact-email-link');
  if (emailLink) {
    const { emailUser, emailDomain } = emailLink.dataset;
    if (emailUser && emailDomain) {
      const email = `${emailUser}@${emailDomain}`;
      emailLink.href = `mailto:${email}`;
      emailLink.textContent = email;
    }
  }

  const phoneLink = document.getElementById('contact-phone-link');
  if (phoneLink) {
    const phone = phoneLink.dataset.phone;
    if (phone) {
      phoneLink.href = `tel:${phone.replace(/\s+/g, '')}`;
      phoneLink.textContent = phone;
    }
  }
}

const ANIMATION_MS = 220; // fix (Andrea): duración de la transición, coordinada con el CSS

function openDrawer(trigger) {
  lastFocusedTrigger = trigger;
  drawer.hidden = false;
  document.body.style.overflow = 'hidden';
  // fix (Andrea): anima la entrada — el frame siguiente añade la clase para que el navegador pueda transicionar
  requestAnimationFrame(() => drawer.classList.add('is-open'));
  drawer.querySelector('#contact-name').focus();
  document.addEventListener('keydown', onKeydown);
}

function closeDrawer() {
  drawer.classList.remove('is-open'); // fix (Andrea): anima la salida antes de ocultar de verdad
  document.body.style.overflow = '';
  document.removeEventListener('keydown', onKeydown);
  lastFocusedTrigger?.focus(); // fix (aria-patterns): foco vuelve al trigger al cerrar
  setTimeout(() => { drawer.hidden = true; }, ANIMATION_MS);
}

function onKeydown(e) {
  if (e.key === 'Escape') closeDrawer();
  if (e.key === 'Tab') trapFocus(e); // focus trap básico (aria-patterns)
}

function trapFocus(e) {
  const focusable = drawer.querySelectorAll('button, [href], input, textarea, [tabindex]:not([tabindex="-1"])');
  const first = focusable[0];
  const last = focusable[focusable.length - 1];
  if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
  else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
}

if (drawer) {
  renderContactInfo();
  openTriggers.forEach((t) => t.addEventListener('click', (e) => { e.preventDefault(); openDrawer(t); }));
  closeTriggers.forEach((t) => t.addEventListener('click', closeDrawer));

  const form = document.getElementById('contact-form');
  const submitBtn = document.getElementById('contact-submit');
  const status = document.getElementById('contact-status');
  const emailError = document.getElementById('contact-email-error');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const emailInput = form.email;
    if (!emailInput.checkValidity()) {
      emailError.textContent = 'Introduce un email válido.';
      emailInput.setAttribute('aria-invalid', 'true');
      return;
    }
    emailError.textContent = '';
    emailInput.removeAttribute('aria-invalid');

    submitBtn.disabled = true;
    status.dataset.state = 'saving';
    status.textContent = 'Enviando...'; // fix (usability-agent 🟠): feedback inmediato de envío

    try {
      const res = await fetch('/api/contact.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          name: form.name.value,
          email: form.email.value,
          message: form.message.value,
          website: form.website.value, // honeypot
        }),
      });
      if (!res.ok) throw new Error('send_failed');
      status.dataset.state = 'success';
      status.textContent = 'Mensaje enviado. ¡Gracias!';
      form.reset();
    } catch {
      status.dataset.state = 'error';
      status.textContent = 'No se pudo enviar. Inténtalo de nuevo.';
    } finally {
      submitBtn.disabled = false;
    }
  });
}
