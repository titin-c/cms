const form = document.getElementById('login-form');
const errorEl = document.getElementById('login-error');
const submitBtn = document.getElementById('login-submit');

form.addEventListener('submit', async (e) => {
  e.preventDefault();
  errorEl.textContent = '';
  submitBtn.disabled = true;
  submitBtn.textContent = 'Entrando...';

  try {
    const res = await fetch('/api/auth.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email: form.email.value, password: form.password.value }),
    });
    const data = await res.json();

    if (!res.ok) {
      // fix (ux-agent): mensaje genérico, no revela si el fallo fue email o password
      errorEl.textContent = data.message || 'No se pudo iniciar sesión.';
      submitBtn.disabled = false;
      submitBtn.textContent = 'Entrar';
      return;
    }

    window.location.href = data.redirect;
  } catch {
    errorEl.textContent = 'Error de conexión. Inténtalo de nuevo.';
    submitBtn.disabled = false;
    submitBtn.textContent = 'Entrar';
  }
});
