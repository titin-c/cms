/**
 * fix (Andrea): en editores de texto largo (páginas, categorías, proyectos,
 * vídeos) redactar el contenido puede llevar más tiempo del que el servidor
 * deja pasar en silencio antes de considerar la sesión caducada — al cabo de
 * un rato, "Guardar" fallaba aunque solo llevaras un rato escribiendo. Esto
 * hace una llamada ligera de vez en cuando mientras la pantalla de edición
 * sigue abierta, para mantener la sesión viva de verdad (no solo cambia el
 * mensaje de error, evita que llegue a caducar en el caso normal).
 * Si la sesión ya ha caducado de verdad (p.ej. llevas horas), este latido
 * simplemente fallará en silencio — el aviso real sale al pulsar "Guardar".
 */
export function startSessionKeepalive(intervalMinutes = 10) {
  const interval = setInterval(() => {
    fetch('/api/ping.php').catch(() => {});
  }, intervalMinutes * 60 * 1000);
  return () => clearInterval(interval);
}
