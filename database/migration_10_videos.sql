-- Migración: módulo de vídeos (YouTube/Vimeo/otros), lightbox o enlace externo.
-- Ejecutar UNA VEZ.

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
