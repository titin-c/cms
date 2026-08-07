-- Migración: keywords SEO separadas por idioma
-- Ejecutar UNA VEZ sobre una base de datos ya creada con schema.sql anterior.

ALTER TABLE projects ADD COLUMN seo_keywords_en VARCHAR(255) NULL AFTER seo_keywords;
