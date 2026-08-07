-- Migración: categorías con togles de header/footer/home, título opcionalmente
-- visible, y estado borrador/publicado propio.
-- Ejecutar UNA VEZ.

ALTER TABLE categories
  ADD COLUMN show_in_header BOOLEAN NOT NULL DEFAULT 0 AFTER seo_keywords_en,
  ADD COLUMN show_in_footer BOOLEAN NOT NULL DEFAULT 0 AFTER show_in_header,
  ADD COLUMN show_in_home BOOLEAN NOT NULL DEFAULT 1 AFTER show_in_footer,
  ADD COLUMN show_title BOOLEAN NOT NULL DEFAULT 1 AFTER show_in_home,
  ADD COLUMN status ENUM('draft', 'published') NOT NULL DEFAULT 'published' AFTER show_title;

-- Tus categorías existentes quedan igual que hasta ahora: publicadas, con
-- título visible, y mostrándose en la home (el comportamiento que ya tenían
-- por defecto) — header/footer quedan desactivados porque antes no existían
-- ahí, actívalos manualmente desde el panel si quieres que aparezcan.
