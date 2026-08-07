-- Migración: ajustes de tema (tipografías + colores).
-- Ejecutar UNA VEZ sobre una base de datos ya creada con schema.sql anterior.

CREATE TABLE site_settings (
  setting_key VARCHAR(60) PRIMARY KEY,
  setting_value TEXT NULL
) ENGINE=InnoDB;

INSERT INTO site_settings (setting_key, setting_value) VALUES
  ('font_content', 'Playfair Display'),
  ('font_ui', 'Inter'),
  ('color_primary', '#0A0A0A'),
  ('color_secondary', '#0A0A0A');
