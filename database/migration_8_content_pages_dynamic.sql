-- Migración: páginas fijas gestionables (crear/editar/eliminar) con control
-- de aparición en cabecera/footer.
-- Ejecutar UNA VEZ sobre una base de datos ya creada con la migración 6.

ALTER TABLE content_pages
  ADD COLUMN slug VARCHAR(160) NULL AFTER page_key,
  ADD COLUMN slug_en VARCHAR(160) NULL AFTER slug,
  ADD COLUMN show_in_header BOOLEAN NOT NULL DEFAULT 0 AFTER meta_description_en,
  ADD COLUMN show_in_footer BOOLEAN NOT NULL DEFAULT 1 AFTER show_in_header,
  ADD COLUMN sort_order INT NOT NULL DEFAULT 0 AFTER show_in_footer;

-- Traslada las 4 páginas existentes a sus rutas/ubicaciones de menú actuales
UPDATE content_pages SET slug = 'sobre-mi', slug_en = 'about', show_in_header = 1, show_in_footer = 1, sort_order = 1 WHERE page_key = 'about';
UPDATE content_pages SET slug = 'privacidad', slug_en = 'privacy', show_in_header = 0, show_in_footer = 1, sort_order = 2 WHERE page_key = 'privacy';
UPDATE content_pages SET slug = 'cookies', slug_en = 'cookies', show_in_header = 0, show_in_footer = 1, sort_order = 3 WHERE page_key = 'cookies';
UPDATE content_pages SET slug = 'aviso-legal', slug_en = 'legal-notice', show_in_header = 0, show_in_footer = 1, sort_order = 4 WHERE page_key = 'legal-notice';

ALTER TABLE content_pages
  MODIFY COLUMN slug VARCHAR(160) NOT NULL,
  ADD UNIQUE KEY slug_unique (slug),
  ADD UNIQUE KEY slug_en_unique (slug_en);

-- page_key ya no se usa (sustituido por slug) — se puede eliminar sin riesgo
ALTER TABLE content_pages DROP COLUMN page_key;
