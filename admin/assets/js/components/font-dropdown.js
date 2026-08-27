/**
 * fix (Andrea): en un <select> nativo, el tamaño de letra de las opciones no
 * se puede controlar de forma fiable entre navegadores — con fuentes muy
 * parecidas entre sí, no se apreciaba la diferencia al elegir. Esto sustituye
 * el <select> (agrupado con <optgroup>, ya construido desde PHP) por un menú
 * desplegable propio que muestra cada tipografía en su propio tamaño grande.
 *
 * El <select> original se queda en el DOM (oculto) y sigue siendo la fuente
 * de la verdad: el resto del código (carga/guardado de ajustes, vista previa
 * en vivo) sigue escuchando su evento 'change' tal cual, sin tocar nada más.
 */
export function initFontDropdown(selectId) {
  const select = document.getElementById(selectId);
  if (!select) return;

  const wrapper = document.createElement('div');
  wrapper.className = 'font-dropdown';

  const button = document.createElement('button');
  button.type = 'button';
  button.className = 'font-dropdown__button';
  button.setAttribute('aria-haspopup', 'listbox');
  button.setAttribute('aria-expanded', 'false');

  const buttonLabel = document.createElement('span');
  buttonLabel.className = 'font-dropdown__button-label';
  button.appendChild(buttonLabel);
  button.insertAdjacentHTML(
    'beforeend',
    '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="font-dropdown__chevron"><path d="M6 9l6 6 6-6"/></svg>'
  );

  const panel = document.createElement('div');
  panel.className = 'font-dropdown__panel';
  panel.setAttribute('role', 'listbox');
  panel.hidden = true;

  const optionEls = [];

  function appendOption(opt) {
    const item = document.createElement('div');
    item.className = 'font-dropdown__option';
    item.setAttribute('role', 'option');
    item.tabIndex = 0;
    item.dataset.value = opt.value;
    item.style.fontFamily = opt.style.fontFamily || `'${opt.value}'`;
    item.textContent = opt.textContent.trim();
    item.addEventListener('click', () => selectValue(opt.value));
    panel.appendChild(item);
    optionEls.push(item);
  }

  Array.from(select.children).forEach((child) => {
    if (child.tagName === 'OPTGROUP') {
      const groupLabel = document.createElement('div');
      groupLabel.className = 'font-dropdown__group-label';
      groupLabel.textContent = child.label;
      panel.appendChild(groupLabel);
      Array.from(child.children).forEach(appendOption);
    } else if (child.tagName === 'OPTION') {
      appendOption(child);
    }
  });

  function syncButtonLabel() {
    const current = select.options[select.selectedIndex];
    if (current) {
      buttonLabel.textContent = current.textContent.trim();
      buttonLabel.style.fontFamily = current.style.fontFamily || `'${current.value}'`;
    }
    optionEls.forEach((el) => {
      const isSelected = el.dataset.value === select.value;
      el.classList.toggle('is-selected', isSelected);
      el.setAttribute('aria-selected', isSelected ? 'true' : 'false');
    });
  }

  function selectValue(value) {
    select.value = value;
    select.dispatchEvent(new Event('change', { bubbles: true }));
    closePanel();
    button.focus();
  }

  function openPanel() {
    panel.hidden = false;
    button.setAttribute('aria-expanded', 'true');
    wrapper.classList.add('is-open');
    const selected = optionEls.find((el) => el.classList.contains('is-selected'));
    (selected || optionEls[0])?.focus();
  }

  function closePanel() {
    panel.hidden = true;
    button.setAttribute('aria-expanded', 'false');
    wrapper.classList.remove('is-open');
  }

  button.addEventListener('click', () => {
    if (panel.hidden) openPanel();
    else closePanel();
  });

  // fix: navegación por teclado dentro del panel — flechas, Enter/Espacio, Escape
  panel.addEventListener('keydown', (e) => {
    const idx = optionEls.indexOf(document.activeElement);
    if (e.key === 'ArrowDown') {
      e.preventDefault();
      (optionEls[idx + 1] || optionEls[0]).focus();
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      (optionEls[idx - 1] || optionEls[optionEls.length - 1]).focus();
    } else if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      document.activeElement?.click();
    } else if (e.key === 'Escape') {
      e.preventDefault();
      closePanel();
      button.focus();
    }
  });

  document.addEventListener('click', (e) => {
    if (!wrapper.contains(e.target)) closePanel();
  });

  select.insertAdjacentElement('afterend', wrapper);
  wrapper.appendChild(button);
  wrapper.appendChild(panel);
  select.style.display = 'none'; // fix: el <select> sigue existiendo (fuente de la verdad), solo se oculta visualmente

  select.addEventListener('change', syncButtonLabel);
  syncButtonLabel();
}
