-- Migración: cada categoría elige si aparece como enlace directo en la
-- cabecera, dentro del submenú "Proyectos", o en ningún sitio.
-- Ejecutar UNA VEZ.

ALTER TABLE categories
  ADD COLUMN header_placement ENUM('none', 'direct', 'submenu') NOT NULL DEFAULT 'submenu' AFTER show_in_header;

-- Tus categorías existentes quedan en 'submenu' (el comportamiento que ya
-- tenían) — cambia a 'direct' las que quieras sacar del submenú como enlace
-- suelto, o a 'none' las que no quieras que aparezcan en la cabecera.
