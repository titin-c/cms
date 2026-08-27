# Registro de versiones

Este archivo deja constancia de qué incluye cada entrega, para poder comprobar
de un vistazo si lo que hay subido en el repositorio remoto está actualizado.

## 1.10.5 — actual

- 🔴 Encontrada la causa real (y definitiva) del error al guardar páginas:
  no era la longitud de ningún campo (eso era un problema real pero distinto,
  arreglado en la 1.10.4) — era que a la base de datos le faltaba la columna
  `noindex` de la tabla de páginas. Esa columna la añade una migración SQL
  que hay que ejecutar una sola vez a mano sobre la base de datos, y que
  nunca llegó a ejecutarse. Por eso fallaba siempre, solo en páginas, pasara
  lo que pasara con la sesión o con la longitud de los textos.
- 🔧 Nueva herramienta de diagnóstico: **/admin/db-check.php**. Comprueba que
  la base de datos tiene todas las tablas y columnas que el panel necesita, y
  las añade automáticamente con un botón si falta algo — sin tener que
  ejecutar SQL a mano nunca más. Es segura de visitar y repetir cuando
  quieras: si no falta nada, no toca nada. Recomendado visitarla después de
  cada actualización del CMS, y siempre que algo dé un error raro al guardar.
  Se ha usado ya para confirmar y arreglar el problema de esta versión, tanto
  en local como se recomienda hacer también en producción tras subir este
  parche.

## 1.10.4

- 🔴 Encontrada y arreglada la causa real de "error de conexión" al guardar
  "Sobre mí" (y, en general, cualquier página, categoría, proyecto o vídeo):
  no era la sesión caducada. El verdadero problema era que algunos campos de
  texto (título, slug o meta descripción/subtítulo) tienen un límite de
  caracteres en la base de datos, y si un valor ya guardado lo superaba —por
  ejemplo, de antes de que el formulario limitara cuánto se podía escribir—
  el guardado se rompía siempre, en cualquier intento, aunque solo cambiaras
  una casilla que no tiene nada que ver (como la de "mostrar en el footer"):
  el formulario reenvía todos los campos de la página en cada guardado, no
  solo el que has tocado, así que ese valor de sobra volvía a fallar cada
  vez. Ahora, si esto ocurre, el campo se acorta automáticamente al máximo
  permitido y se avisa con claridad de qué se ha acortado, en vez de perder
  el guardado entero. Se ha revisado y aplicado el mismo arreglo en las
  cuatro secciones que pueden verse afectadas (páginas, categorías,
  proyectos y vídeos), además de una red de seguridad general para que
  ningún otro error de base de datos imprevisto vuelva a romper la respuesta
  del servidor de esta forma.
  - El diagnóstico de la versión 1.10.2/1.10.3 (sesión caducada) era
    razonable dado lo que se veía en pantalla, y las mejoras de esa versión
    (sesión más larga, aviso de mantenimiento de sesión) siguen siendo
    útiles por sí mismas, pero no eran la causa de este fallo concreto.

## 1.10.3

- 🔴 Arreglada la causa real del "Tu sesión ha caducado" al guardar páginas
  largas (como "Sobre mí"): por defecto, PHP da de baja la sesión del
  servidor a los 24 minutos de inactividad — de sobra si solo cambias un
  interruptor, pero se queda corto si llevas un rato largo redactando texto.
  Ahora la sesión del panel dura 4 horas, y mientras tienes abierta una
  pantalla de edición (páginas, categorías, proyectos, vídeos) se manda un
  aviso silencioso de vez en cuando para mantenerla viva de verdad — no solo
  para que salga un mensaje más claro si caduca, sino para que no llegue a
  caducar en el uso normal.

## 1.10.2

- 🔴 Arreglado: la opción "Categorías" del menú de cabecera (la que abre un
  desplegable con las categorías) no tenía el mismo tamaño de letra que el
  resto de opciones del menú — usaba el tamaño del cuerpo de la página en
  vez del ajustado en Tipografía → "Cabecera — menú". Ahora coincide.
- 🔴 Arreglado: al guardar cualquier formulario del panel (páginas, categorías,
  proyectos, vídeos, ajustes) con la sesión ya caducada, salía un
  desconcertante "Error de conexión." que hacía pensar que algo había fallado
  al guardar. Ahora se detecta ese caso concreto y avisa de lo que ha pasado
  de verdad, con la forma correcta de recuperarlo sin perder lo escrito:
  iniciar sesión de nuevo en otra pestaña y volver a pulsar "Guardar" en la
  pestaña original (recargarla sí perdería los cambios, al traer de vuelta la
  última versión guardada). En Ajustes además había un caso peor: con la
  sesión caducada podía llegar a decir "Ajustes guardados" sin haberse
  guardado nada; también queda arreglado.

## 1.10.1

- 🔴 Arreglado: en los controles de tipografía nuevos, el desplegable de
  grosor "empujaba" su propio ancho por la opción más larga (Extra-negrita)
  y dejaba el deslizador de tamaño reducido a un punto apenas visible. Ahora
  la fila usa una rejilla con anchos fijos para el valor y el desplegable,
  así el deslizador siempre se queda con el espacio que sobra.

## 1.10.0

- Ajustes → Estilos → nueva sección "Tipografía — tamaños y grosores": un
  control de tamaño (deslizador, en píxeles) y grosor de letra (de Fina a
  Extra-negrita, 6 opciones) para cada tipo de texto del sitio: título y
  subtítulo del Hero, título y menú de la cabecera, encabezados H1-H4,
  textos/párrafos, legal y menú del pie de página, miga de pan, y título y
  resumen de los grids (home, categorías y proyectos). Incluye un visor con
  todos los elementos a la vez, a tamaño real, que se actualiza al momento.
  Los encabezados H1-H4 van en modo "proporcional" por defecto (ajustas solo
  el H1 y los demás se escalan automáticamente con la misma proporción de
  siempre) o "personalizado" (cada nivel con su propio tamaño y grosor).
  🔴 Nota: los tamaños de fábrica reproducen el aspecto actual del sitio en
  casi todos los casos; la única excepción es el título de cada categoría en
  el listado de /categorias, que antes se veía más grande (28px) y ahora
  usa el mismo tamaño que el resto de títulos de grid (14px) — se puede
  volver a subir desde el nuevo control si se prefería más grande.
- Ajustes → selectores de tipografía: lista de fuentes revisada — se quitan
  varias sans-serif geométricas que se veían casi idénticas entre sí (DM
  Sans, Sora, Outfit, Plus Jakarta Sans, IBM Plex Sans, Public Sans) y se
  añaden tipografías populares que faltaban (Montserrat, Poppins, Raleway,
  Nunito), además de Merriweather entre las serif. Si el sitio ya tenía
  guardada una tipografía que ya no está en la lista curada, se sigue
  mostrando (en un grupo "Actual") para no perder la selección real.

## 1.9.4

- Ajustes → selector de tipografías: ahora es un menú desplegable propio con
  cada fuente mostrada en un tamaño grande, para poder apreciar bien las
  diferencias entre tipografías parecidas (el `<select>` nativo las mostraba
  todas al mismo tamaño pequeño). Funciona igual que antes por dentro (mismo
  campo, mismo guardado, misma vista previa en vivo).
- Hero modo "sin fondo": la cabecera ya no se superpone al Hero (no hay foto
  detrás que oscurecer) — ahora sale sólida justo debajo, y se queda fija
  arriba de la pantalla al hacer scroll. Este cambio es exclusivo de este
  modo; en "mosaico" y "foto al azar" la cabecera se sigue superponiendo
  como siempre.

## 1.9.3

- 🔴 Arreglo de fondo (no solo un parche puntual) del aviso "Contenido muy
  pronto.": en vez de mirar si algún módulo está *activado*, ahora se
  comprueba si de verdad se ha *mostrado* algo. Antes de este cambio
  también salía de más con el Hero solo (sin ningún otro módulo) — el
  mismo motivo que el arreglo de la 1.9.2 con el Mosaico de proyectos, pero
  sin cubrir el caso del Hero. De paso, se ha revisado y comprobado
  sistemáticamente las 64 combinaciones posibles de los 5 módulos de la
  home (incluyendo Simple activado pero sin título/texto/imagen rellenos,
  que se quedaba sin avisar de nada).

## 1.9.2

- 🔴 Arreglado: si el Mosaico de proyectos era el único módulo activo de la
  home, se pintaba bien como cabecera a pantalla completa pero debajo
  seguía saliendo el aviso "Contenido muy pronto." — la comprobación de
  "no hay ningún módulo con contenido" no distinguía entre el Hero
  (decorativo, no cuenta) y el Mosaico de proyectos (contenido real, si es
  el único activo ya se ha mostrado arriba).

## 1.9.1

- 🔴 Arreglado: en el Hero modo "sin fondo" con un color de fondo claro, la
  cabecera seguía forzando texto blanco (heredado del tratamiento sobre
  foto oscura) y se perdía contra el fondo claro. Ahora se adapta igual que
  el resto del contenido del Hero en ese modo — texto oscuro sobre fondo
  claro, blanco sobre fondo oscuro.

## 1.9.0

- Redes sociales: ahora se pueden mostrar u ocultar por separado en la
  cabecera y en el pie de página (Ajustes → Sitio → Redes sociales).
- 🔴 Arreglado: el Hero en modo "mosaico" o "foto al azar" podía quedarse
  sin imágenes si el módulo Categorías estaba apagado en la home (por
  ejemplo, al usar en su lugar el nuevo Mosaico de proyectos) — el Hero
  ahora busca sus fotos de forma independiente de qué otros módulos estén
  visibles, y si todavía no hay ningún proyecto marcado como "Destacado",
  usa cualquier proyecto publicado como alternativa en vez de quedarse vacío.
- Cabecera transparente superpuesta también sobre el Mosaico de proyectos
  cuando es el primer módulo de la home (antes ese tratamiento era exclusivo
  del Hero).
- Arreglada la pixelación de las miniaturas del Mosaico de proyectos al
  mostrarse grandes (1 o 2 columnas): la imagen de reserva usa ya la
  variante de mayor resolución disponible.
- Módulos de la home reordenables por drag & drop desde Ajustes → Home
  (Hero, Categorías, Vídeos, Simple, Mosaico de proyectos) — el que quede
  primero decide si la cabecera es transparente (Hero o Mosaico de
  proyectos) o sólida (cualquier otro caso).

## 1.8.0

**Tres funcionalidades nuevas para clientes con la web ya publicada (Andrea):**

- Modo "en construcción": dos interruptores independientes en Ajustes →
  Sitio — "No indexar en buscadores" (noindex/nofollow) y página
  "Próximamente" (con mensaje ES/EN configurable), combinables entre sí;
  `/admin` sigue accesible siempre.
- Hero con fondo configurable en Ajustes → Home: mosaico de fotos (de
  siempre), foto destacada al azar, o sin fondo (color liso, con el texto
  adaptado automáticamente a claro/oscuro).
- Nuevo módulo de home "Mosaico de proyectos": todos los proyectos
  publicados a pantalla completa, en 1/2/3 columnas configurables, con
  revelado lateral + efecto parallax al hacer scroll, overlay oscuro/claro
  según el tema del sitio al pasar el ratón, y resumen con elipsis CSS.

## 1.7.0

**Control de indexación por página fija (seo-agent):**

- Nuevo interruptor "No mostrar esta página en Google" en cada página fija
  (`/admin/paginas.php` → editar → sección "Buscadores") — emite
  `<meta name="robots" content="noindex, follow">` real.
- Privacidad, Cookies y Aviso legal quedan marcadas `noindex` por defecto en
  instalaciones nuevas (no aportan valor de posicionamiento — texto legal
  genérico obligatorio, sin motivo para competir en el índice). "Sobre mí"
  se mantiene indexable, al ser contenido real y único.
- Las páginas `noindex` se excluyen del sitemap — pedirle a Google que
  indexe algo marcado como "no indexar" es inconsistente.

## 1.6.1

- 🔴 Arreglado el motivo real más probable de que el inglés no posicionara
  bien: el sitemap generaba **una sola entrada `<url>`** por contenido (la
  de español), con el inglés metido solo como anotación `alternate` dentro
  de ella — la URL en inglés nunca aparecía como `<loc>` propio en ningún
  sitio, así que Google tenía muchas menos papeletas de rastrearla e
  indexarla de forma independiente. Ahora cada idioma disponible genera su
  propia entrada `<url>` completa (patrón oficial de Google para sitios
  multi-idioma), cada una apuntándose a sí misma y a las demás.
- Confirmado (no era un fallo): a nivel de cada página individual, el
  `hreflang` recíproco ya se generaba igual de bien visitando la versión en
  español que la versión en inglés — el problema estaba únicamente en el
  sitemap.

## 1.6.0

**Auditoría SEO completa (seo-agent, MODO audit):**

- 🔴 `categorias.php` (hub) y `videos.php` no tenían *fallback* de meta
  descripción — si no se rellenaba en Ajustes, la etiqueta desaparecía por
  completo de la página. Corregido con la misma cadena de *fallback* que
  usan home/proyecto/categoría.
- 🔴 Ninguna página pública comprobaba si su módulo estaba activado desde
  Ajustes — desactivar "Proyectos" o "Vídeos" solo ocultaba el menú del
  admin, pero las páginas seguían 100% accesibles e indexables. Ahora
  `proyecto.php`, `categoria.php`, `categorias.php` y `videos.php` dan 404
  si su módulo está desactivado, y la home deja de mostrar esas secciones
  también.
- 🔴 `/videos` y `/categorias` (el hub) no estaban en el sitemap — páginas
  reales, invisibles al rastreador. Añadidas, respetando también los
  módulos activos.
- 🟠 `hreflang x-default` ausente en `categorias.php`, `videos.php` y
  `pagina.php` (sí estaba en home/proyecto/categoría) — añadido en las 3.
- 🟠 Sin datos estructurados (`BreadcrumbList`) en esas mismas 3 páginas —
  añadido, consistente con el resto del sitio.
- 🟠 Sitemap sin extensión de imagen — añadida por proyecto (`<image:image>`
  con la variante de mayor resolución), relevante para tráfico de Google
  Images en un portfolio fotográfico.
- Confirmado (no era un fallo): `robots.txt` ya existía como archivo
  dinámico (`public/robots.php`); un único sitemap combinado con
  alternativas de idioma incrustadas es el patrón correcto (no hacen falta
  sitemaps separados por idioma); los campos `changefreq`/`priority` del
  formato clásico de sitemap se omiten a propósito — Google los ignora por
  completo desde hace años.

## 1.5.5

- Arreglado de raíz el aviso flotante que no aparecía en Ajustes:
  `ajustes.js` nunca importaba `save-status.js` y tocaba el texto de estado
  a mano, saltándose por completo la función que lanza el aviso.
- Arreglado además un bug relacionado que afectaba a **todos** los
  formularios (proyecto, categoría, vídeo, página): el CSS esperaba
  `data-state="success"` para poner el texto en verde, pero el JS real
  siempre pusó `"saved"`/`"sent"` — así que ese texto nunca se coloreaba en
  ningún formulario del panel.

## 1.5.4

- Vista previa combinada de Ajustes/Estilos: movida de abajo-derecha (se
  solapaba con el botón "Guardar ajustes") a arriba-derecha.
- Aviso flotante de guardado: además del texto discreto de siempre en la
  cabecera, ahora aparece un aviso arriba a la derecha que se ve claramente
  y desaparece solo a los pocos segundos — antes costaba notar que ya se
  había guardado y se acababa pulsando el botón varias veces.

## 1.5.3

- Arreglado de raíz el toggle apilado en columna: la regla genérica
  `.admin-page main label` (pensada para "Título" + campo debajo) tenía más
  especificidad CSS que `.admin-toggle`, así que ganaba ella y forzaba
  columna en vez de fila — sin importar el orden en el archivo. Añadida una
  regla `.admin-page main label.admin-toggle` que iguala y supera esa
  especificidad, para que el interruptor quede siempre en fila (track a la
  izquierda, texto a la derecha) de verdad.

## 1.5.2

- Arreglado: el `<input>` oculto del interruptor se posicionaba respecto al
  ancestro posicionado más cercano (a veces muy lejos) en vez de anclarse a
  su propio interruptor — por eso podía aparecer solapado con el track en
  vez de quedar invisible en su sitio. Añadido `position: relative` al
  contenedor + coordenadas exactas del input.

## 1.5.1

- Arreglado: el componente de interruptor (toggle) se estiraba a todo el
  ancho de su contenedor por ser `display:flex` sin ancho definido, dando
  sensación de quedar descolocado/centrado según el bloque donde estuviera.
  Ahora se ajusta al contenido (`width: fit-content`) y queda pegado a la
  izquierda en cualquier contexto.
- Etiquetas de los interruptores acortadas en toda la interfaz — el texto
  largo que antes iba entre paréntesis dentro del propio toggle ahora vive
  en una línea de apoyo más pequeña debajo (mismo patrón que ya usábamos
  para los `admin-form__hint`).

## 1.5.0

- Módulo "Páginas" añadido a los módulos activables/desactivables de Ajustes.
- Formulario de categoría: toggle "mostrar título" junto al propio título
  (antes debajo); toggle "fila en la home" duplicado (arriba y junto a sus
  campos) y sincronizado; los campos de la fila de home se ocultan si esa
  fila está desactivada.
- Página fija (Sobre mí, legales...): convertida de modal a página completa,
  mismo patrón que categoría/proyecto/vídeo.
- Arreglado: guardar un proyecto/categoría/vídeo/página ya no te saca de la
  pantalla de edición — antes redirigía sola tras publicar.
- Checkboxes → toggles unificados en toda la interfaz para acciones de
  mostrar/ocultar (destacar, selector de idioma, módulos, ajustes de
  vídeos/categorías...). Las selecciones múltiples reales (categorías
  adicionales de un proyecto) se mantienen como checkboxes a propósito.
- Botón "Nueva página" corregido — se veía más grande que el resto por ser
  un `<button>` nativo sin resetear del todo.
- Ajustes → Estilos: separadores simplificados a un único interruptor
  (antes eran dos radios que parecían independientes) con ejemplo visual en
  vivo; mini-ejemplo visual del grid a escala; la "vista previa combinada"
  ahora es un panel flotante fijo en pantalla mientras editas colores.
- Módulo Simple de la home: los campos se ocultan si el módulo está
  desactivado; el modo de imagen fija/aleatoria pasa de 2 radios a un único
  interruptor "Imágenes destacadas aleatorias".

## 1.4.0

- Categorías: cada una elige su ubicación en cabecera con 3 opciones
  independientes — sin aparecer, enlace directo, o dentro del submenú
  "Proyectos" (antes solo había un sí/no).
- Arreglado: el submenú "Proyectos" se cerraba solo al mover el ratón hacia
  él (bug de CSS — un `margin` fuera del área detectada por `:hover`).
- Ajustes → Estilos: nueva sección de grids (columnas + hueco), aplicada a
  categoría, vídeos y el módulo de vídeos de la home.

## 1.3.0

- Página "Proyectos" (`/categorias`) — como Vídeos, con sus propios ajustes
  de menú (cabecera/footer), H1, descripción, meta title y meta descripción,
  todos opcionales.
- Meta title añadido también a la página de Vídeos (antes solo tenía meta
  descripción).
- Categoría rediseñada como página completa (`categoria-form.php`), no modal.
- Arrastrar para reordenar (proyectos, categorías, vídeos).
- Ajustes reorganizado en pestañas: Sitio / Estilos / Home.
- Módulo "Simple" de la home: imagen destacada (fija o aleatoria de
  proyectos destacados) + título + texto, cada uno opcional.
- Separadores (con/sin línea + tamaño del hueco) configurables para
  cabecera, footer y filas de categoría de la home.

## 1.2.0

- Instalador web (`public/install.php`) + manual (`INSTALL.md`).
- Soporte de `src/lib/config.local.php` como alternativa a variables de
  entorno para la configuración de base de datos.
- `admin/index.php` — arregla el 403 al visitar `/admin/` sin archivo.

## 1.1.0

- CMS multi-cliente: nombre/subtítulo del sitio, dominio, email/teléfono de
  contacto, redes sociales, y "módulos activos" (Proyectos/Vídeos)
  configurables desde Ajustes, en vez de fijos en el código.
- Módulo de vídeos (YouTube/Vimeo/otro), con lightbox propio.
- Home con 3 módulos combinables: Hero / Categorías / Vídeos.
- Páginas fijas (Sobre mí, legales) totalmente gestionables — crear, editar,
  eliminar, con control de aparición en menús.
- Tipografías y colores configurables, con contraste WCAG AAA garantizado
  matemáticamente (funciona con fondo claro u oscuro).

## 1.0.0

- Versión inicial: portfolio de fotografía con proyectos, categorías,
  panel de administración, SEO, multi-idioma ES/EN.
