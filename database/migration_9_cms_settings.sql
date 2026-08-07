-- Migración: sitio configurable como CMS multi-cliente.
-- Ejecutar UNA VEZ sobre una base de datos ya creada con migration_7.

INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES
  ('site_name', 'Mi Sitio'),
  ('show_language_menu', '1');

CREATE TABLE social_links (
  id INT AUTO_INCREMENT PRIMARY KEY,
  platform VARCHAR(40) NOT NULL,
  url VARCHAR(500) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- fix (CMS genérico): sin redes sociales de ejemplo — se añaden desde
-- /admin/ajustes.php según cada cliente.
