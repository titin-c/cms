-- Migración: contenido editable de páginas fijas (Sobre mí, legales).
-- Ejecutar UNA VEZ sobre una base de datos ya creada con schema.sql anterior.
-- Después de crear la tabla, ejecuta database/seed_content_pages.php para
-- rellenarla con el contenido inicial (el mismo que ya tenías en el código).

CREATE TABLE content_pages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  page_key VARCHAR(40) NOT NULL UNIQUE,
  title_es VARCHAR(200) NOT NULL,
  title_en VARCHAR(200) NULL,
  content_es LONGTEXT NOT NULL,
  content_en LONGTEXT NULL,
  meta_description_es VARCHAR(300) NULL,
  meta_description_en VARCHAR(300) NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
