-- Esquema de base de datos MySQL / MariaDB — CMS genérico

CREATE TABLE admin_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(120) NOT NULL UNIQUE,
  slug_en VARCHAR(120) NULL UNIQUE,

  -- fix (Andrea, SEO): antes title_es/description_es se reutilizaban tanto en
  -- la home como en la página de categoría — contenido duplicado, mala
  -- práctica SEO. Ahora cada contexto tiene sus propios campos.

  -- 1-2: fila de la home (título corto + descripción breve)
  home_title_es VARCHAR(120) NULL,      -- si está vacío, cae a title_es
  home_title_en VARCHAR(120) NULL,      -- si está vacío, cae a title_en
  home_description_es TEXT NULL,
  home_description_en TEXT NULL,

  -- 3: título de la propia página de categoría (H1, más descriptivo/largo si conviene)
  title_es VARCHAR(160) NOT NULL,
  title_en VARCHAR(160) NULL,

  -- 4: descripción larga, texto introductorio real de la página de categoría
  description_es TEXT NULL,
  description_en TEXT NULL,

  -- 5: meta descripción propia (para el <meta name="description">, no visible en la página)
  meta_description_es VARCHAR(300) NULL,
  meta_description_en VARCHAR(300) NULL,

  -- 6: texto del botón "Ver todo" de la home hacia esta categoría
  button_label_es VARCHAR(60) NULL,     -- si está vacío, cae a "Ver todo"
  button_label_en VARCHAR(60) NULL,     -- si está vacío, cae a "View all"

  -- 7: palabras clave de referencia para nombrar archivos y redactar alt text
  -- de las fotos de esta categoría (uso interno del panel, no se muestra en la web)
  seo_keywords_es VARCHAR(300) NULL,
  seo_keywords_en VARCHAR(300) NULL,

  -- fix (Andrea, CMS multi-cliente): dónde aparece esta categoría, y si se
  -- muestra su título como H1 en su propia página (algunas clientas no
  -- quieren ver "Proyectos" como encabezado, solo el grid).
  show_in_header BOOLEAN NOT NULL DEFAULT 0,
  -- fix (Andrea): cada categoría elige si sale como enlace directo en la
  -- cabecera, dentro del submenú "Proyectos", o en ningún sitio — más libre
  -- que un simple sí/no, así puedes tener categorías fuera del submenú.
  header_placement ENUM('none', 'direct', 'submenu') NOT NULL DEFAULT 'submenu',
  show_in_footer BOOLEAN NOT NULL DEFAULT 0,
  show_in_home BOOLEAN NOT NULL DEFAULT 1,
  show_title BOOLEAN NOT NULL DEFAULT 1,

  -- fix: borrador/publicado — antes una categoría "existía" y ya se listaba
  -- en todas partes; ahora se puede preparar sin publicarla todavía.
  status ENUM('draft', 'published') NOT NULL DEFAULT 'published',

  sort_order INT NOT NULL DEFAULT 0,
  is_default_uncategorized BOOLEAN NOT NULL DEFAULT FALSE,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- fix: categoría "Sin categorizar" por defecto (qa-agent, adenda de Andrea) — se crea en el seed inicial
-- y nunca puede eliminarse desde el panel.

CREATE TABLE projects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(160) NOT NULL UNIQUE,
  slug_en VARCHAR(160) NULL UNIQUE,
  category_id INT NOT NULL,
  main_image VARCHAR(255) NOT NULL,
  main_image_alt VARCHAR(255) NULL,
  featured BOOLEAN NOT NULL DEFAULT FALSE,
  sort_order INT NOT NULL DEFAULT 0,
  status ENUM('draft','published') NOT NULL DEFAULT 'draft',
  project_date DATE NULL,

  title_es VARCHAR(200) NOT NULL,
  content_es LONGTEXT NOT NULL,      -- HTML enriquecido (Quill)
  excerpt_es TEXT NULL,

  title_en VARCHAR(200) NULL,
  content_en LONGTEXT NULL,
  excerpt_en TEXT NULL,

  seo_keywords VARCHAR(255) NULL,
  seo_keywords_en VARCHAR(255) NULL,
  seo_description_es VARCHAR(300) NULL,
  seo_description_en VARCHAR(300) NULL,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY (category_id) REFERENCES categories(id)
) ENGINE=InnoDB;

-- fix: title_en/content_en/excerpt_en son NULL-ables — inglés opcional (decisión UX de Andrea).
-- Un proyecto "tiene traducción EN" si title_en Y content_en no son NULL.

CREATE TABLE project_images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  project_id INT NOT NULL,
  image_path VARCHAR(255) NOT NULL,
  alt_es VARCHAR(255) NULL,
  alt_en VARCHAR(255) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- fix (Andrea): un proyecto puede aparecer en categorías adicionales, además
-- de su categoría principal (projects.category_id, la que se usa para el
-- breadcrumb y "siguiente proyecto"). No afecta al SEO: la URL del proyecto
-- (/proyecto/slug) es siempre la misma, independientemente de en cuántas
-- categorías aparezca listado.
CREATE TABLE project_extra_categories (
  project_id INT NOT NULL,
  category_id INT NOT NULL,
  PRIMARY KEY (project_id, category_id),
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  message TEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  read_at DATETIME NULL
) ENGINE=InnoDB;

-- fix (Andrea): páginas fijas totalmente gestionables desde el panel —
-- crear, editar y eliminar, con control de si aparecen en el menú de
-- cabecera, en el footer, en ambos o en ninguno. Antes eran 4 páginas
-- fijas en el código (Sobre mí, Privacidad, Cookies, Aviso legal); ahora
-- esas 4 siguen existiendo por defecto, pero como filas normales editables.
CREATE TABLE content_pages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(160) NOT NULL UNIQUE,
  slug_en VARCHAR(160) NULL UNIQUE,
  title_es VARCHAR(200) NOT NULL,
  title_en VARCHAR(200) NULL,
  content_es LONGTEXT NOT NULL,
  content_en LONGTEXT NULL,
  meta_description_es VARCHAR(300) NULL,
  meta_description_en VARCHAR(300) NULL,
  show_in_header BOOLEAN NOT NULL DEFAULT 0,
  show_in_footer BOOLEAN NOT NULL DEFAULT 1,
  -- fix (seo-agent [audit]): páginas legales (Privacidad/Cookies/Aviso legal)
  -- no aportan valor de posicionamiento — noindex,follow es lo recomendado:
  -- no compiten en el índice, pero sus enlaces internos se siguen rastreando
  noindex BOOLEAN NOT NULL DEFAULT 0,
  sort_order INT NOT NULL DEFAULT 0,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- fix (Andrea): ajustes de tema editables desde /admin/ajustes.php — tipografías
-- (Google Fonts) y colores base, de los que se derivan automáticamente tonos
-- más claros/oscuros para bordes, hovers, etc. Clave-valor: flexible para
-- añadir más ajustes en el futuro sin nueva migración.
CREATE TABLE site_settings (
  setting_key VARCHAR(60) PRIMARY KEY,
  setting_value TEXT NULL
) ENGINE=InnoDB;

INSERT INTO site_settings (setting_key, setting_value) VALUES
  ('font_content', 'Playfair Display'),
  ('font_ui', 'Inter'),
  ('color_primary', '#0A0A0A'),
  ('color_secondary', '#0A0A0A'),
  ('site_name', 'Mi Sitio');

-- fix (CMS genérico): sin redes sociales de ejemplo — se añaden desde
-- /admin/ajustes.php según cada cliente.
CREATE TABLE social_links (
  id INT AUTO_INCREMENT PRIMARY KEY,
  platform VARCHAR(40) NOT NULL,
  url VARCHAR(500) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- fix (Andrea): módulo de vídeos — para clientas que suben enlaces/embebidos
-- de YouTube, Vimeo u otros, en vez de (o además de) proyectos fotográficos.
CREATE TABLE videos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(160) NOT NULL UNIQUE,
  slug_en VARCHAR(160) NULL UNIQUE,
  title_es VARCHAR(200) NOT NULL,
  title_en VARCHAR(200) NULL,
  subtitle_es VARCHAR(300) NULL,
  subtitle_en VARCHAR(300) NULL,
  thumbnail VARCHAR(255) NOT NULL,
  thumbnail_alt VARCHAR(255) NULL,
  video_url VARCHAR(500) NOT NULL,
  video_provider ENUM('youtube', 'vimeo', 'other') NOT NULL DEFAULT 'youtube',
  display_mode ENUM('lightbox', 'external') NOT NULL DEFAULT 'lightbox',
  featured BOOLEAN NOT NULL DEFAULT FALSE,
  sort_order INT NOT NULL DEFAULT 0,
  status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE contact_attempts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ip VARCHAR(45) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE INDEX idx_contact_attempts_ip_time ON contact_attempts (ip, created_at);
CREATE INDEX idx_projects_category ON projects (category_id, status);
CREATE INDEX idx_projects_featured ON projects (featured, status);

-- Seed inicial obligatorio
INSERT INTO categories (slug, title_es, title_en, is_default_uncategorized, sort_order)
VALUES ('sin-categorizar', 'Sin categorizar', 'Uncategorized', TRUE, 999);
