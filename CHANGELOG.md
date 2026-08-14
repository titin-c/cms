# Registro de versiones

Este archivo deja constancia de qué incluye cada entrega, para poder comprobar
de un vistazo si lo que hay subido en el repositorio remoto está actualizado.

## 1.5.1 — actual

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
