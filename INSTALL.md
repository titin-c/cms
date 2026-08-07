# Guía de instalación

Esta guía cubre la instalación en un **hosting compartido normal** (cPanel, Plesk o similar) y también en **local (XAMPP/MAMP)**. Si es la primera vez que instalas una web PHP, sigue los pasos en orden — no hace falta saber programar.

---

## 1. Qué necesitas antes de empezar

- Un hosting o servidor con **PHP 8.0 o superior** y **MySQL/MariaDB**.
- Acceso a **cPanel** (o el panel equivalente de tu hosting) para crear la base de datos.
- Un cliente FTP (por ejemplo [FileZilla](https://filezilla-project.org/), gratuito) o el "Administrador de archivos" que trae tu hosting.

---

## 2. Sube los archivos

1. Descomprime el `.zip` del proyecto en tu ordenador.
2. Conéctate a tu hosting por FTP (o usa el Administrador de archivos de cPanel).
3. **El contenido de la carpeta `/public` debe subirse a la carpeta raíz pública de tu hosting** (normalmente se llama `public_html`, `www` o `htdocs`).
   - Es decir: `public_html/index.php`, `public_html/proyecto.php`, `public_html/assets/`, etc. — **no** subas la carpeta `public` entera dentro de `public_html` (quedaría en `public_html/public/index.php`, y no funcionaría).
4. Las carpetas `/admin`, `/api`, `/src` y `/database` van **un nivel por encima** de `public_html`, si tu hosting lo permite (así no son accesibles directamente desde el navegador salvo por las rutas que el propio código define). Si tu hosting no te deja subir nada fuera de `public_html`, súbelas dentro de `public_html` igualmente — seguirá funcionando, solo es un extra de seguridad, no algo obligatorio.

**Estructura final típica:**
```
public_html/           ← lo que había en /public
  index.php
  admin/                ← si no pudiste subirlo un nivel por encima
  api/
  assets/
  uploads/
  install.php
src/                    ← si tu hosting lo permite fuera de public_html
database/
```

---

## 3. Crea la base de datos

Desde cPanel → **"Bases de datos MySQL"**:

1. Crea una base de datos nueva (anota el nombre completo, cPanel suele añadirle un prefijo tipo `usuario_nombrebd`).
2. Crea un usuario de MySQL nuevo con una contraseña segura.
3. Añade ese usuario a la base de datos con **todos los privilegios**.
4. Anota los 4 datos: **servidor** (normalmente `localhost`), **nombre de la base de datos**, **usuario**, **contraseña** — los necesitarás en el siguiente paso.

---

## 4. Ejecuta el asistente de instalación

1. Ve a `https://tudominio.com/install.php` desde el navegador.
2. **Paso 1 — Requisitos**: el asistente comprueba automáticamente que el servidor cumple lo necesario (versión de PHP, extensiones, permisos de carpetas). Si algo falla, contacta con el soporte de tu hosting pidiendo que lo activen — es un mensaje que ellos entienden directamente.
3. **Paso 2 — Base de datos**: introduce los 4 datos que anotaste en el paso 3. El asistente prueba la conexión antes de continuar.
4. **Paso 3 — Cuenta de administrador**: pon el nombre del sitio y crea tu email/contraseña de acceso al panel.
5. Al terminar, verás una pantalla de confirmación con un botón para entrar directamente al panel.

El asistente crea las tablas de la base de datos, tu cuenta de acceso, y rellena las páginas legales con un contenido de ejemplo que **deberás editar** después (ver punto 7).

**Por seguridad, borra `install.php` del servidor una vez termines** — si alguien más lo encuentra activo, no podrá reinstalar nada porque el asistente se bloquea solo en cuanto existe la configuración, pero es buena práctica quitarlo igualmente.

---

## 5. Apunta el dominio (si hace falta)

Si compraste el dominio en el mismo sitio que el hosting, normalmente ya está todo conectado. Si el dominio está en otro proveedor (por ejemplo lo compraste en Namecheap pero el hosting es otro), tendrás que cambiar los **DNS** del dominio para que apunten a los servidores de tu hosting — tu proveedor de hosting te dará esos datos exactos (normalmente 2 direcciones tipo `ns1.tuhosting.com` / `ns2.tuhosting.com`).

---

## 6. Activa el certificado SSL (HTTPS)

Casi todos los hostings actuales ofrecen un certificado gratuito (Let's Encrypt) activable con un clic desde cPanel → **"SSL/TLS Status"** o **"Let's Encrypt"**. Actívalo — sin HTTPS, el navegador marcará el sitio como "no seguro" y el SEO se resiente.

---

## 7. Primeros pasos dentro del panel

Entra a `https://tudominio.com/admin` con el email/contraseña que creaste, y ve a **Ajustes** primero:

- **Identidad del sitio**: nombre, subtítulo, dominio (déjalo vacío si no usas un proxy — se detecta solo), email/teléfono de contacto.
- **Tipografías y colores**: elige las dos tipografías y los dos colores.
- **Redes sociales**: añade las que quieras.
- **Módulos de la home**: activa/desactiva Hero, Categorías y Vídeos según lo que necesite este cliente.
- **Módulos activos**: si este sitio no necesita Proyectos o Vídeos, desactívalos aquí para que desaparezcan del menú del panel.

Luego edita las páginas legales (**Páginas** en el menú) — tienen contenido de ejemplo con datos entre `[corchetes]` que debes sustituir por los reales.

---

## 8. Instalación en local (XAMPP/MAMP) — atajo

Para desarrollo local no hace falta el asistente completo si prefieres ir más rápido:

```
mysql -u root -p < database/schema.sql
php database/seed_content_pages.php
```

Y crea el usuario admin a mano:
```
php -r "echo password_hash('tu_contraseña', PASSWORD_DEFAULT);"
```
Copia el resultado e insértalo en `admin_users` desde phpMyAdmin (columna `password_hash`, junto con tu email).

En este caso, en vez de un `config.local.php`, puedes seguir usando variables de entorno (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`) tal como se hacía antes — ambos métodos funcionan indistintamente.

---

## Problemas frecuentes

| Síntoma | Causa probable |
|---|---|
| Página en blanco al entrar a `/install.php` | Revisa el log de errores de PHP en cPanel — normalmente falta alguna extensión de PHP |
| "No se pudo conectar" en el paso 2 | Datos de la base de datos incorrectos, o el usuario no tiene privilegios sobre esa base de datos |
| Las imágenes no se suben | La carpeta `/uploads` no tiene permisos de escritura — desde el Administrador de archivos, clic derecho → Permisos → 755 (o 775 si 755 no funciona) |
| El panel `/admin` da error 500 | Revisa que las carpetas `/src` y `/admin` estén subidas correctamente y con la misma estructura de carpetas que en el `.zip` original |
