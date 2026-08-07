# CMS de portfolio — genérico, multi-cliente

Web pública + panel de administración. Stack: **PHP 8+ / MySQL o MariaDB / HTML-CSS-JS nativo** (sin frameworks de frontend).

Todo lo relativo a marca (nombre, tipografías, colores, redes sociales, dominio, email de contacto) se configura desde `/admin/ajustes.php` — nada queda fijo en el código.

## Estructura

```
/public    → raíz servida por el hosting (web pública)
/admin     → panel de administración (protegido por login)
/api       → endpoints JSON consumidos por fetch() desde el JS del panel
/src/lib   → lógica PHP compartida (BD, auth, i18n, imágenes)
/src/templates → partials PHP reutilizables (nav, hero, category-row, footer...)
/database  → schema.sql
```

## Instalación en un hosting real

**Guía completa paso a paso: ver [`INSTALL.md`](./INSTALL.md).**

Resumen: sube `/public` a la raíz pública de tu hosting, crea una base de datos desde cPanel, y visita `https://tudominio.com/install.php` — un asistente web (como el instalador de 5 minutos de WordPress) te guía por el resto.

## Instalación local (XAMPP / similar)

1. Crea la base de datos e importa el esquema:
   ```
   mysql -u root -p -e "CREATE DATABASE andrea_savall CHARACTER SET utf8mb4"
   mysql -u root -p andrea_savall < database/schema.sql
   ```
2. Rellena las páginas fijas del sitio (Sobre mí, Privacidad, Cookies, Aviso legal) con su contenido inicial editable:
   ```
   php database/seed_content_pages.php
   ```
3. Crea el primer usuario admin (ejecuta una vez, luego bórralo o protégelo):
   ```php
   php -r "echo password_hash('TU_PASSWORD', PASSWORD_DEFAULT);"
   ```
   e insértalo manualmente en `admin_users` (email + el hash generado).
4. Configura las variables de entorno (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `ANDREA_EMAIL`) — en XAMPP, vía `.htaccess`/`SetEnv` o un `.env` cargado al inicio.
5. Apunta el document root del hosting/XAMPP a `/public` (la web pública). El panel vive en `/admin` y la API en `/api`, ambos **fuera** del document root si tu hosting lo permite, o protegidos por `.htaccess` si no.
6. La carpeta `/public/uploads` debe tener permisos de escritura para el servidor web.

## Notas de producción

- **Hosting recomendado:** cualquier hosting compartido con PHP 8+, MySQL/MariaDB y GD habilitado (para el redimensionado de imágenes) es suficiente para el volumen esperado (decenas a ~100 proyectos). No hace falta VPS salvo que el tráfico crezca mucho.
- **HTTPS obligatorio:** las cookies de sesión están configuradas con `secure => true` (ver `src/lib/auth.php`) — el sitio debe servirse por HTTPS.
- **Backups:** al ser MySQL, un `mysqldump` periódico cubre todo el contenido (proyectos, categorías, mensajes).
