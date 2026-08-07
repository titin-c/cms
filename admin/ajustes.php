<?php
require_once __DIR__ . '/../src/lib/auth.php';
require_once __DIR__ . '/../src/lib/social_icons.php';
require_once __DIR__ . '/../src/lib/db.php';
require_once __DIR__ . '/../src/lib/theme.php';
requireAuth();

$pdo = getDb();
$currentSettings = getSiteSettings($pdo);

// fix (Andrea): antes cada selector solo ofrecía serif o sans por separado —
// ahora ambos ofrecen la lista completa (agrupada por categoría), para poder
// elegir cualquier combinación libremente.
$curatedFonts = [
    ['name' => 'Playfair Display', 'category' => 'Serif'],
    ['name' => 'Lora', 'category' => 'Serif'],
    ['name' => 'Cormorant Garamond', 'category' => 'Serif'],
    ['name' => 'EB Garamond', 'category' => 'Serif'],
    ['name' => 'Libre Baskerville', 'category' => 'Serif'],
    ['name' => 'Fraunces', 'category' => 'Serif'],
    ['name' => 'DM Serif Display', 'category' => 'Serif'],
    ['name' => 'Newsreader', 'category' => 'Serif'],
    ['name' => 'Spectral', 'category' => 'Serif'],
    ['name' => 'Bodoni Moda', 'category' => 'Serif'],
    ['name' => 'Prata', 'category' => 'Serif'],
    ['name' => 'Crimson Pro', 'category' => 'Serif'],
    ['name' => 'Inter', 'category' => 'Sans-serif'],
    ['name' => 'Work Sans', 'category' => 'Sans-serif'],
    ['name' => 'Manrope', 'category' => 'Sans-serif'],
    ['name' => 'Space Grotesk', 'category' => 'Sans-serif'],
    ['name' => 'DM Sans', 'category' => 'Sans-serif'],
    ['name' => 'Sora', 'category' => 'Sans-serif'],
    ['name' => 'Outfit', 'category' => 'Sans-serif'],
    ['name' => 'Plus Jakarta Sans', 'category' => 'Sans-serif'],
    ['name' => 'Karla', 'category' => 'Sans-serif'],
    ['name' => 'IBM Plex Sans', 'category' => 'Sans-serif'],
    ['name' => 'Archivo', 'category' => 'Sans-serif'],
    ['name' => 'Public Sans', 'category' => 'Sans-serif'],
];
$familyParams = array_map(fn($f) => 'family=' . str_replace(' ', '+', $f['name']) . ':wght@400;700', $curatedFonts);
$fontsUrl = 'https://fonts.googleapis.com/css2?' . implode('&', $familyParams) . '&display=swap';
$groups = ['Serif' => [], 'Sans-serif' => []];
foreach ($curatedFonts as $f) { $groups[$f['category']][] = $f['name']; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Ajustes — Panel de administración</title>
  <link rel="stylesheet" href="/assets/css/tokens.css">
  <link rel="stylesheet" href="assets/css/admin.css">
  <link rel="stylesheet" href="assets/css/ajustes.css">
  <link href="https://cdn.jsdelivr.net/npm/cropperjs@1/dist/cropper.min.css" rel="stylesheet">
  <link href="<?= htmlspecialchars($fontsUrl) ?>" rel="stylesheet">
</head>
<body class="admin-page">
  <header class="admin-header">
    <a href="dashboard.php" class="admin-header__back">&larr; Volver a proyectos</a>
    <span id="save-status" class="save-status" aria-live="polite"></span>
  </header>

  <main class="admin-form" style="max-width: 1100px;">
    <h1 style="font-size:18px;margin:0 0 16px;">Ajustes</h1>

    <!-- fix (Andrea): pestañas — antes todo estaba en una sola página larguísima -->
    <div class="admin-tabs" role="tablist">
      <button type="button" class="admin-tab-btn is-active" data-tab="site" role="tab" aria-selected="true">Sitio</button>
      <button type="button" class="admin-tab-btn" data-tab="styles" role="tab" aria-selected="false">Estilos</button>
      <button type="button" class="admin-tab-btn" data-tab="home" role="tab" aria-selected="false">Home</button>
    </div>

    <!-- ============ PESTAÑA: SITIO ============ -->
    <div class="admin-tab-panel is-active" data-tab-panel="site">

      <section class="admin-form__block">
        <h2>Identidad del sitio</h2>
        <label for="site-name">Nombre (aparece en el hero grande y en la cabecera)</label>
        <input type="text" id="site-name" placeholder="ej: Nombre del sitio o de la marca">

        <div class="admin-form__bilingual">
          <div class="admin-form__lang-col">
            <span class="admin-form__lang-badge">Español</span>
            <label for="site-subtitle-es">Subtítulo</label>
            <input type="text" id="site-subtitle-es" placeholder="ej: Fotógrafa profesional">
          </div>
          <div class="admin-form__lang-col">
            <span class="admin-form__lang-badge">English</span>
            <label for="site-subtitle-en">Subtitle</label>
            <input type="text" id="site-subtitle-en" placeholder="ej: Professional photographer">
          </div>
        </div>

        <label class="admin-toggle" style="margin-top:12px;">
          <input type="checkbox" id="show-language-menu" checked>
          <span class="admin-toggle__track"><span class="admin-toggle__thumb"></span></span>
          Mostrar el selector de idioma ES/EN en la web
        </label>

        <label for="site-domain" style="margin-top:12px;">Dominio del sitio — opcional</label>
        <input type="text" id="site-domain" placeholder="https://midominio.com">
        <p class="admin-form__hint">Se usa para las URLs de SEO. Si lo dejas vacío, se detecta automáticamente.</p>

        <label for="contact-email">Email de contacto</label>
        <input type="email" id="contact-email" placeholder="contacto@midominio.com">
        <p class="admin-form__hint">Se muestra (protegido de bots) en el formulario de contacto, y es donde llegan los mensajes.</p>

        <label for="contact-phone">Teléfono de contacto — opcional</label>
        <input type="text" id="contact-phone" placeholder="+34 600 00 00 00">
      </section>

      <section class="admin-form__block">
        <h2>Redes sociales</h2>
        <p class="admin-form__hint">Aparecen en la cabecera y en el footer, en este orden.</p>
        <div id="social-links-list"></div>
        <button type="button" id="add-social-btn" class="admin-btn admin-btn--secondary" style="margin-top:8px;">+ Añadir red social</button>
      </section>

      <section class="admin-form__block">
        <h2>Módulos activos</h2>
        <p class="admin-form__hint">Desactiva lo que este cliente concreto no necesite — desaparece del menú del panel.</p>
        <label class="admin-toggle">
          <input type="checkbox" id="module-projects-enabled" checked>
          <span class="admin-toggle__track"><span class="admin-toggle__thumb"></span></span>
          Proyectos y categorías
        </label>
        <label class="admin-toggle">
          <input type="checkbox" id="module-videos-enabled" checked>
          <span class="admin-toggle__track"><span class="admin-toggle__thumb"></span></span>
          Vídeos
        </label>
        <label class="admin-toggle">
          <input type="checkbox" id="module-pages-enabled" checked>
          <span class="admin-toggle__track"><span class="admin-toggle__thumb"></span></span>
          Páginas
        </label>
      </section>

    </div>

    <!-- ============ PESTAÑA: ESTILOS ============ -->
    <div class="admin-tab-panel" data-tab-panel="styles">

      <section class="admin-form__block">
        <h2>Tipografía — Títulos y textos</h2>
        <p class="admin-form__hint">Se usa en el nombre del sitio (hero grande), títulos de proyecto/categoría y el cuerpo de texto.</p>
        <select id="font-select-content" class="theme-font-select" data-field="font_content">
          <?php foreach ($groups as $groupLabel => $names): ?>
            <optgroup label="<?= $groupLabel ?>">
              <?php foreach ($names as $name): ?>
                <option value="<?= htmlspecialchars($name) ?>" style="font-family:'<?= htmlspecialchars($name) ?>', <?= $groupLabel === 'Serif' ? 'serif' : 'sans-serif' ?>;">
                  <?= htmlspecialchars($name) ?>
                </option>
              <?php endforeach; ?>
            </optgroup>
          <?php endforeach; ?>
        </select>
        <div class="font-preview" id="preview-content">
          <p class="font-preview__heading" id="preview-content-heading">Nombre del sitio</p>
          <p class="font-preview__body" id="preview-content-body">Este es un ejemplo de cómo se vería un párrafo de texto con la tipografía elegida.</p>
        </div>
      </section>

      <section class="admin-form__block">
        <h2>Tipografía — Formularios, botones y menús</h2>
        <select id="font-select-ui" class="theme-font-select" data-field="font_ui">
          <?php foreach ($groups as $groupLabel => $names): ?>
            <optgroup label="<?= $groupLabel ?>">
              <?php foreach ($names as $name): ?>
                <option value="<?= htmlspecialchars($name) ?>" style="font-family:'<?= htmlspecialchars($name) ?>', <?= $groupLabel === 'Serif' ? 'serif' : 'sans-serif' ?>;">
                  <?= htmlspecialchars($name) ?>
                </option>
              <?php endforeach; ?>
            </optgroup>
          <?php endforeach; ?>
        </select>
        <div class="font-preview" id="preview-ui">
          <button type="button" class="font-preview__button" id="preview-ui-button">Contacto</button>
          <span class="font-preview__menu" id="preview-ui-menu">Sobre mí &nbsp;·&nbsp; Contacto &nbsp;·&nbsp; ES / EN</span>
        </div>
      </section>

      <section class="admin-form__block">
        <h2>Color de fondo</h2>
        <div class="color-picker-row">
          <input type="color" id="color-surface" value="#FFFFFF">
          <input type="text" id="color-surface-hex" class="color-hex-input" value="#FFFFFF" maxlength="7">
        </div>
      </section>

      <section class="admin-form__block">
        <h2>Color principal (textos y títulos)</h2>
        <div class="color-picker-row">
          <input type="color" id="color-primary" value="#0A0A0A">
          <input type="text" id="color-primary-hex" class="color-hex-input" value="#0A0A0A" maxlength="7">
        </div>
        <div class="color-scale" id="scale-primary"></div>
        <div class="contrast-indicator" id="contrast-primary"></div>
      </section>

      <section class="admin-form__block">
        <h2>Color secundario (hover de botones, foco, acentos)</h2>
        <div class="color-picker-row">
          <input type="color" id="color-secondary" value="#0A0A0A">
          <input type="text" id="color-secondary-hex" class="color-hex-input" value="#0A0A0A" maxlength="7">
        </div>
        <div class="color-scale" id="scale-secondary"></div>
        <div class="contrast-indicator" id="contrast-secondary"></div>
      </section>

      <section class="admin-form__block">
        <h2>Separadores</h2>
        <p class="admin-form__hint">La línea y el hueco antes de ella, en la cabecera, el footer y entre filas de categoría de la home. El grid de fotos no se ve afectado.</p>
        <label class="admin-toggle">
          <input type="checkbox" id="separator-lines-enabled" checked>
          <span class="admin-toggle__track"><span class="admin-toggle__thumb"></span></span>
          Líneas separadoras
        </label>

        <label for="separator-size" style="margin-top:16px;">Tamaño del hueco</label>
        <div style="display:flex; align-items:center; gap:12px;">
          <input type="range" id="separator-size" min="0" max="80" step="4" value="24" style="width:100%;">
          <span id="separator-size-value" style="font-family:var(--font-ui); font-size:13px; color:var(--color-ink-500); min-width:44px;">24px</span>
        </div>

        <!-- fix (Andrea #10): ejemplo visual en vivo -->
        <div class="separator-preview">
          <div class="separator-preview__content">Contenido de ejemplo</div>
          <div class="separator-preview__line" id="separator-preview-line"></div>
          <div class="separator-preview__content">Siguiente bloque</div>
        </div>
      </section>

      <section class="admin-form__block">
        <h2>Grids de fotos y vídeos</h2>
        <p class="admin-form__hint">Afecta a todos los grids del sitio: categorías, vídeos, y el módulo de vídeos de la home.</p>

        <label for="grid-columns">Miniaturas por fila (desktop grande)</label>
        <div style="display:flex; align-items:center; gap:12px;">
          <input type="range" id="grid-columns" min="2" max="6" step="1" value="4" style="width:100%;">
          <span id="grid-columns-value" style="font-family:var(--font-ui); font-size:13px; color:var(--color-ink-500); min-width:44px;">4</span>
        </div>
        <p class="admin-form__hint">En tablet se reduce una columna automáticamente, y en móvil siempre es 1 por fila.</p>

        <label for="grid-gap" style="margin-top:16px;">Hueco entre miniaturas</label>
        <div style="display:flex; align-items:center; gap:12px;">
          <input type="range" id="grid-gap" min="0" max="64" step="4" value="24" style="width:100%;">
          <span id="grid-gap-value" style="font-family:var(--font-ui); font-size:13px; color:var(--color-ink-500); min-width:44px;">24px</span>
        </div>

        <!-- fix (Andrea #10): mini-ejemplo visual del grid a escala -->
        <div class="grid-preview" id="grid-preview"></div>
      </section>

    </div>

    <!-- fix (Andrea #11): la vista previa combinada sale de la columna
         principal y se queda fija en pantalla mientras editas (sticky) —
         antes quedaba varias pantallas más abajo y no se veía el efecto
         en vivo al tocar un color -->
    <aside class="admin-sticky-preview" id="styles-sticky-preview" hidden>
      <div class="theme-mockup" id="theme-mockup">
        <p class="theme-mockup__title" id="mockup-title">Editorial</p>
        <p class="theme-mockup__text" id="mockup-text">Un ejemplo de cómo se combinan la tipografía y los colores elegidos.</p>
        <div class="theme-mockup__row">
          <button type="button" class="theme-mockup__btn" id="mockup-btn">Ver proyecto</button>
          <a href="#" class="theme-mockup__link" id="mockup-link">Sobre mí</a>
        </div>
        <div class="theme-mockup__box" id="mockup-box">Caja con borde derivado</div>
        <p class="theme-mockup__hover-hint">Pasa el ratón por el botón y el enlace para ver el hover ↑</p>
      </div>
    </aside>

    <!-- ============ PESTAÑA: HOME ============ -->
    <div class="admin-tab-panel" data-tab-panel="home">

      <section class="admin-form__block">
        <h2>Módulos de la home</h2>
        <p class="admin-form__hint">Cada uno se puede activar o desactivar de forma independiente — combínalos como quieras.</p>
        <label class="admin-toggle">
          <input type="checkbox" id="home-show-hero">
          <span class="admin-toggle__track"><span class="admin-toggle__thumb"></span></span>
          Hero (mosaico grande con el nombre del sitio)
        </label>
        <label class="admin-toggle">
          <input type="checkbox" id="home-show-categories">
          <span class="admin-toggle__track"><span class="admin-toggle__thumb"></span></span>
          Categorías de proyectos (filas con scroll horizontal)
        </label>
        <label class="admin-toggle">
          <input type="checkbox" id="home-show-videos">
          <span class="admin-toggle__track"><span class="admin-toggle__thumb"></span></span>
          Todos los vídeos publicados (rejilla)
        </label>
        <label class="admin-toggle">
          <input type="checkbox" id="home-show-simple">
          <span class="admin-toggle__track"><span class="admin-toggle__thumb"></span></span>
          Simple (imagen destacada + título + texto)
        </label>
        <p class="admin-form__hint">Si desactivas el Hero, la home usa la misma cabecera sólida que el resto de páginas.</p>
      </section>

      <!-- fix (Andrea): módulo Simple — para quien no quiere ni categorías ni
           vídeos, solo una imagen + un título + un texto. Los 3 son
           opcionales por separado: lo que no rellenes, no sale. -->
      <section class="admin-form__block">
        <h2>Módulo Simple</h2>
        <p class="admin-form__hint">Imagen destacada, título y texto — cada uno opcional. Si no rellenas alguno, simplemente no aparece.</p>

        <div id="simple-module-fields">
        <div class="admin-form__bilingual">
          <div class="admin-form__lang-col">
            <span class="admin-form__lang-badge">Español</span>
            <label for="home-simple-title-es">Título — opcional</label>
            <input type="text" id="home-simple-title-es">
          </div>
          <div class="admin-form__lang-col">
            <span class="admin-form__lang-badge">English</span>
            <label for="home-simple-title-en">Title — optional</label>
            <input type="text" id="home-simple-title-en">
          </div>
        </div>

        <label class="admin-toggle" style="margin-top:16px;">
          <input type="checkbox" id="simple-image-mode-random">
          <span class="admin-toggle__track"><span class="admin-toggle__thumb"></span></span>
          Imágenes destacadas aleatorias
        </label>
        <p class="admin-form__hint" id="simple-image-random-hint" hidden>Cada visita muestra una foto distinta, elegida al azar entre los proyectos marcados como "Destacado". No hace falta subir nada abajo.</p>

        <div id="simple-image-upload-block">
          <label for="home-simple-image-alt" style="margin-top:12px;">Imagen fija — opcional</label>
          <div id="simple-image-cropper" data-cropper></div>
          <input type="hidden" id="main_image" value="<?= htmlspecialchars($currentSettings['home_simple_image'] ?? '') ?>">
          <label for="home-simple-image-alt">Alt de la imagen</label>
          <input type="text" id="home-simple-image-alt" placeholder="Describe la imagen">
        </div>

        <div class="admin-form__bilingual" style="margin-top:16px;">
          <div class="admin-form__lang-col">
            <span class="admin-form__lang-badge">Español</span>
            <label for="home-simple-desc-es">Texto — opcional</label>
            <textarea id="home-simple-desc-es" rows="4"></textarea>
          </div>
          <div class="admin-form__lang-col">
            <span class="admin-form__lang-badge">English</span>
            <label for="home-simple-desc-en">Text — optional</label>
            <textarea id="home-simple-desc-en" rows="4"></textarea>
          </div>
        </div>
        </div>
      </section>

      <section class="admin-form__block">
        <h2>SEO de la home</h2>
        <div class="admin-form__bilingual">
          <div class="admin-form__lang-col">
            <span class="admin-form__lang-badge">Español</span>
            <label for="home-title-es">Meta título — opcional</label>
            <input type="text" id="home-title-es" placeholder="Se genera automático si lo dejas vacío">
            <label for="home-meta-es">Meta descripción — opcional</label>
            <textarea id="home-meta-es" rows="2" maxlength="300"></textarea>
          </div>
          <div class="admin-form__lang-col">
            <span class="admin-form__lang-badge">English</span>
            <label for="home-title-en">Meta title — optional</label>
            <input type="text" id="home-title-en" placeholder="Auto-generated if left blank">
            <label for="home-meta-en">Meta description — optional</label>
            <textarea id="home-meta-en" rows="2" maxlength="300"></textarea>
          </div>
        </div>
      </section>

    </div>

    <div class="admin-form__actions">
      <button type="button" id="save-settings-btn" class="admin-btn admin-btn--primary">Guardar ajustes</button>
    </div>
  </main>

  <script>window.SOCIAL_PLATFORMS = <?= json_encode(SOCIAL_PLATFORMS) ?>;</script>
  <script src="https://cdn.jsdelivr.net/npm/cropperjs@1/dist/cropper.min.js"></script>
  <script type="module" src="assets/js/ajustes.js"></script>
</body>
</html>
