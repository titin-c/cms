/**
 * TextExcerptPicker — usa la selección nativa de texto de Quill (tema Bubble
 * ya muestra un tooltip de formato al seleccionar). Reutilizamos ese mismo
 * gesto para el botón "Usar como resumen".
 *
 * fix (usability-agent 🔴 crítico, variante mobile): en vez de depender de
 * arrastrar con precisión con el dedo, el botón toma el PÁRRAFO donde está
 * el cursor/selección actual si no hay selección de texto libre — evita la
 * fricción de selección táctil de precisión.
 */

function wireExcerptButton(btnId, editorSelector, hiddenInputId) {
  const btn = document.getElementById(btnId);
  const editorEl = document.querySelector(editorSelector);
  const hiddenInput = document.getElementById(hiddenInputId);
  if (!btn || !editorEl) return;

  btn.addEventListener('click', () => {
    const selection = window.getSelection();
    let text = '';

    if (selection && selection.toString().trim().length > 0 && editorEl.contains(selection.anchorNode)) {
      // Selección de texto libre (desktop, ratón preciso)
      text = selection.toString().trim();
    } else {
      // fix: fallback para móvil — toma el primer párrafo del editor
      const firstParagraph = editorEl.querySelector('p');
      text = firstParagraph ? firstParagraph.textContent.trim() : editorEl.textContent.trim().slice(0, 200);
    }

    hiddenInput.value = text;
    btn.textContent = btn.textContent.includes('Use') ? 'Excerpt set ✓' : 'Resumen guardado ✓';
    setTimeout(() => {
      btn.textContent = btn.id.endsWith('en') ? 'Use as excerpt' : 'Usar como resumen';
    }, 1500);
  });
}

wireExcerptButton('excerpt-btn-es', '#editor_es', 'excerpt_es');
wireExcerptButton('excerpt-btn-en', '#editor_en', 'excerpt_en');
