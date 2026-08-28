-- fix (Andrea, SEO): título propio (<title> / resultado de Google) distinto
-- del H1, para categorías, proyectos y páginas. Sustituye a las "palabras
-- clave" (seo_keywords), que Google ignora desde hace años — en categorías
-- ni siquiera llegaban a mostrarse en la web. Las columnas de keywords se
-- dejan tal cual (no se borran, por si acaso), simplemente el panel deja de
-- mostrarlas y de guardarlas.
--
-- Ejecutar una sola vez a mano sobre la base de datos de producción, o
-- visitar /admin/db-check.php tras subir esta versión, que la detecta y la
-- aplica sola.

ALTER TABLE categories
  ADD COLUMN meta_title_es VARCHAR(200) NULL,
  ADD COLUMN meta_title_en VARCHAR(200) NULL;

ALTER TABLE projects
  ADD COLUMN meta_title_es VARCHAR(200) NULL,
  ADD COLUMN meta_title_en VARCHAR(200) NULL;

ALTER TABLE content_pages
  ADD COLUMN meta_title_es VARCHAR(200) NULL,
  ADD COLUMN meta_title_en VARCHAR(200) NULL;
