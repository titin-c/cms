/**
 * Menú móvil — botón de hamburguesa que despliega .site-nav__actions como
 * panel bajo la cabecera. Se cierra al pulsar Escape, al hacer clic fuera,
 * o al pulsar cualquier enlace de dentro (para no dejarlo abierto tras navegar).
 */

const toggle = document.getElementById('site-nav-toggle');
const actions = document.getElementById('site-nav-actions');

if (toggle && actions) {
  function closeMenu() {
    actions.classList.remove('is-open');
    toggle.setAttribute('aria-expanded', 'false');
  }
  function openMenu() {
    actions.classList.add('is-open');
    toggle.setAttribute('aria-expanded', 'true');
  }

  toggle.addEventListener('click', () => {
    const isOpen = actions.classList.contains('is-open');
    isOpen ? closeMenu() : openMenu();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && actions.classList.contains('is-open')) {
      closeMenu();
      toggle.focus();
    }
  });

  document.addEventListener('click', (e) => {
    if (!actions.classList.contains('is-open')) return;
    if (!actions.contains(e.target) && e.target !== toggle && !toggle.contains(e.target)) {
      closeMenu();
    }
  });

  // fix: al pulsar un enlace del menú (que no sea el propio toggle de contacto), cerrar el panel
  actions.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', closeMenu);
  });

  // Si la ventana pasa a tamaño desktop con el menú abierto, lo cerramos (evita quedar "atascado" abierto)
  window.matchMedia('(min-width: 769px)').addEventListener('change', (e) => {
    if (e.matches) closeMenu();
  });
}

// fix (Andrea): desplegable "Categorías" — clic/teclado además del hover
// (el hover por CSS ya cubre desktop con ratón; esto cubre táctil y teclado)
document.querySelectorAll('.site-nav__dropdown-trigger').forEach((trigger) => {
  const menu = trigger.nextElementSibling;
  function closeDropdown() {
    menu.classList.remove('is-open');
    trigger.setAttribute('aria-expanded', 'false');
  }
  trigger.addEventListener('click', (e) => {
    e.stopPropagation();
    const isOpen = menu.classList.toggle('is-open');
    trigger.setAttribute('aria-expanded', String(isOpen));
  });
  document.addEventListener('click', (e) => {
    if (!trigger.contains(e.target) && !menu.contains(e.target)) closeDropdown();
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && menu.classList.contains('is-open')) {
      closeDropdown();
      trigger.focus();
    }
  });
});
