-- Migración: control de indexación (noindex) por página fija.
-- Las páginas legales no aportan valor de posicionamiento — se marcan
-- noindex por defecto (Privacidad, Cookies, Aviso legal). "Sobre mí" y
-- cualquier página personalizada que ya tuvieras se quedan indexables.
-- Ejecutar UNA VEZ.

ALTER TABLE content_pages
  ADD COLUMN noindex BOOLEAN NOT NULL DEFAULT 0 AFTER show_in_footer;

UPDATE content_pages SET noindex = 1
WHERE slug IN ('privacidad', 'cookies', 'aviso-legal')
   OR slug_en IN ('privacy', 'cookies', 'legal-notice');
