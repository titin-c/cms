# Registro de versiones

Este archivo deja constancia de qué incluye cada entrega, para poder comprobar
de un vistazo si lo que hay subido en el repositorio remoto está actualizado.

## 1.7.0 — actual

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
