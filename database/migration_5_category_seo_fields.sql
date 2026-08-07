-- Migración: separar los campos de categoría por contexto (home / página de
-- categoría / meta descripción / botón / keywords de imagen).
-- Ejecutar UNA VEZ sobre una base de datos ya creada con schema.sql anterior.
--
-- Nota: tus categorías existentes mantienen title_es/title_en/description_es/
-- description_en tal cual (se usarán ahora como título+descripción de la
-- PÁGINA de categoría). Los nuevos campos de home quedan vacíos — al estar
-- vacíos, la home seguirá mostrando automáticamente el mismo title_es/
-- description_es de siempre como fallback, así que no verás ningún cambio
-- hasta que edites cada categoría y rellenes los campos nuevos a propósito.

ALTER TABLE categories
  ADD COLUMN home_title_es VARCHAR(120) NULL AFTER slug_en,
  ADD COLUMN home_title_en VARCHAR(120) NULL AFTER home_title_es,
  ADD COLUMN home_description_es TEXT NULL AFTER home_title_en,
  ADD COLUMN home_description_en TEXT NULL AFTER home_description_es,
  ADD COLUMN meta_description_es VARCHAR(300) NULL AFTER description_en,
  ADD COLUMN meta_description_en VARCHAR(300) NULL AFTER meta_description_es,
  ADD COLUMN button_label_es VARCHAR(60) NULL AFTER meta_description_en,
  ADD COLUMN button_label_en VARCHAR(60) NULL AFTER button_label_es,
  ADD COLUMN seo_keywords_es VARCHAR(300) NULL AFTER button_label_en,
  ADD COLUMN seo_keywords_en VARCHAR(300) NULL AFTER seo_keywords_es;
