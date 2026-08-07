-- Migración: URLs traducidas al inglés (slug_en)
-- Ejecutar UNA VEZ sobre una base de datos ya creada con el schema.sql anterior.
-- Si vas a crear la base de datos desde cero, no hace falta este archivo:
-- el schema.sql ya incluye estas columnas.

ALTER TABLE projects ADD COLUMN slug_en VARCHAR(160) NULL UNIQUE AFTER slug;
ALTER TABLE categories ADD COLUMN slug_en VARCHAR(120) NULL UNIQUE AFTER slug;
