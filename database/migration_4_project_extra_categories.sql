-- Migración: un proyecto puede aparecer en categorías adicionales.
-- Ejecutar UNA VEZ sobre una base de datos ya creada con schema.sql anterior.

CREATE TABLE project_extra_categories (
  project_id INT NOT NULL,
  category_id INT NOT NULL,
  PRIMARY KEY (project_id, category_id),
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;
