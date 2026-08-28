/**
 * fix (Andrea): si la sesión de administrador caduca mientras editas, el
 * servidor responde a la llamada de guardado con una redirección a la
 * pantalla de login (HTML), no con JSON. Antes eso se traducía siempre en
 * "Error de conexión." — un mensaje que hace pensar que ha fallado la red o
 * el guardado en sí, cuando en realidad solo hace falta volver a iniciar
 * sesión (los cambios del formulario siguen ahí, sin perderse, hasta que
 * recargas). Ahora se detecta este caso concreto y se avisa de qué ha
 * pasado de verdad.
 */
export class SessionExpiredError extends Error {}

/** Sustituye a `await res.json()` en las llamadas de guardado del admin. */
export async function parseApiJson(res) {
  if (res.redirected && /\/admin\/login\.php/.test(res.url)) {
    throw new SessionExpiredError();
  }
  try {
    return await res.json();
  } catch {
    // Respuesta que no es JSON válido — lo más probable, igual que arriba,
    // es una redirección a login que el navegador siguió en silencio.
    throw new SessionExpiredError();
  }
}

/**
 * Para llamadas que solo miran `res.ok` sin leer el cuerpo (p.ej. Ajustes,
 * que hace dos peticiones en paralelo): una redirección a login sigue
 * siendo un 200 OK normal y corriente para `fetch`, así que sin esto se
 * vería "Ajustes guardados" aunque en realidad no se haya guardado nada.
 */
export function assertSessionAlive(res) {
  if (res.redirected && /\/admin\/login\.php/.test(res.url)) {
    throw new SessionExpiredError();
  }
}

/**
 * Mensaje a mostrar en el bloque `catch` de cada guardado.
 * fix: si recargas ESTA pestaña para volver a entrar, se pierde lo que
 * habías escrito (el formulario se recarga desde lo último guardado en la
 * base de datos) — por eso el aviso pide iniciar sesión en OTRA pestaña y
 * volver a pulsar Guardar en esta, sin recargarla.
 */
export function saveErrorMessage(err) {
  return err instanceof SessionExpiredError
    ? 'Tu sesión ha caducado. Inicia sesión de nuevo en otra pestaña (sin recargar esta) y vuelve a pulsar "Guardar" aquí — así no pierdes lo que has escrito.'
    : 'Error de conexión.';
}
