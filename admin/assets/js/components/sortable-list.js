/**
 * Arrastrar para reordenar — nativo (HTML5 Drag & Drop), sin librerías.
 * Cada fila debe tener: data-id="..." y un elemento con class="drag-handle"
 * y draggable="true" dentro (el "tirador" ⠿) — así arrastrar no interfiere
 * con hacer clic en los enlaces/botones de la propia fila.
 *
 * Uso:
 *   makeSortable(tbody, { type: 'projects', onReordered: () => loadProjects() });
 */
export function makeSortable(container, { type, onReordered } = {}) {
  let draggedRow = null;

  container.querySelectorAll('.drag-handle').forEach((handle) => {
    handle.addEventListener('dragstart', (e) => {
      draggedRow = handle.closest('[data-id]');
      draggedRow.classList.add('is-dragging');
      e.dataTransfer.effectAllowed = 'move';
    });
    handle.addEventListener('dragend', async () => {
      draggedRow?.classList.remove('is-dragging');
      draggedRow = null;
      await saveOrder();
    });
  });

  container.addEventListener('dragover', (e) => {
    e.preventDefault();
    if (!draggedRow) return;
    const target = e.target.closest('[data-id]');
    if (!target || target === draggedRow) return;
    const rect = target.getBoundingClientRect();
    const isAfter = e.clientY - rect.top > rect.height / 2;
    target.parentNode.insertBefore(draggedRow, isAfter ? target.nextSibling : target);
  });

  async function saveOrder() {
    const order = Array.from(container.querySelectorAll('[data-id]')).map((el) => el.dataset.id);
    try {
      await fetch('/api/reorder.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ type, order }),
      });
      onReordered?.();
    } catch {
      // fix: si falla el guardado del orden, no rompemos la UI — el siguiente
      // recargado del listado restaurará el orden real guardado en servidor
    }
  }
}
