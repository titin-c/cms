<?php
require_once __DIR__ . '/../src/lib/auth.php';
require_once __DIR__ . '/../src/lib/social_icons.php';
require_once __DIR__ . '/../src/lib/db.php';
require_once __DIR__ . '/../src/lib/theme.php';
requireAuth();

$pdo = getDb();
$currentSettings = getSiteSettings($pdo);

// fix (Andrea): la lista anterior tenía demasiadas sans-serif geométricas
// casi idénticas entre sí (DM Sans/Sora/Outfit/Plus Jakarta Sans/IBM Plex
// Sans/Public Sans) y le faltaban tipografías populares y muy reconocibles
// (Montserrat, Poppins...). Nueva selección: menos fuentes pero cada una
// visualmente distinta de las demás — mezcla de serifs desde muy dramáticas
// (Bodoni Moda) a suaves y legibles (Merriweather), y sans-serifs desde
// geométricas conocidas (Montserrat, Poppins) a redondeadas (Nunito) o con
// más personalidad (Space Grotesk).
$curatedFonts = [
    ['name' => 'Playfair Display', 'category' => 'Serif'],
    ['name' => 'Bodoni Moda', 'category' => 'Serif'],
    ['name' => 'DM Serif Display', 'category' => 'Serif'],
    ['name' => 'Cormorant Garamond', 'category' => 'Serif'],
    ['name' => 'Lora', 'category' => 'Serif'],
    ['name' => 'Merriweather', 'category' => 'Serif'],
    ['name' => 'Libre Baskerville', 'category' => 'Serif'],
    ['name' => 'EB Garamond', 'category' => 'Serif'],
    ['name' => 'Fraunces', 'category' => 'Serif'],
    ['name' => 'Crimson Pro', 'category' => 'Serif'],
    ['name' => 'Montserrat', 'category' => 'Sans-serif'],
    ['name' => 'Poppins', 'category' => 'Sans-serif'],
    ['name' => 'Inter', 'category' => 'Sans-serif'],
    ['name' => 'Raleway', 'category' => 'Sans-serif'],
    ['name' => 'Nunito', 'category' => 'Sans-serif'],
    ['name' => 'Space Grotesk', 'category' => 'Sans-serif'],
    ['name' => 'Work Sans', 'category' => 'Sans-serif'],
    ['name' => 'Archivo', 'category' => 'Sans-serif'],
    ['name' => 'Manrope', 'category' => 'Sans-serif'],
    ['name' => 'Karla', 'category' => 'Sans-serif'],
];

// fix (Andrea): si el sitio ya tenía guardada una tipografía que no está en
// la lista curada (por ejemplo, una de las quitadas en esta revisión), no
// desaparece silenciosamente del desplegable — se añade en su propio grupo
// para no perder la selección real al guardar.
$curatedNames = array_column($curatedFonts, 'name');
$keptFonts = [];
foreach (['font_content', 'font_ui'] as $fontField) {
    $current = trim($currentSettings[$fontField] ?? '');
    if ($current && !in_array($current, $curatedNames, true) && !in_array($current, $keptFonts, true)) {
        $curatedFonts[] = ['name' => $current, 'category' => 'Actual'];
        $keptFonts[] = $current;
    }
}

$familyParams = array_map(fn($f) => 'family=' . str_replace(' ', '+', $f['name']) . ':wght@300;400;500;600;700;800', $curatedFonts);
$fontsUrl = 'https://fonts.googleapis.com/css2?' . implode('&', $familyParams) . '&display=swap';
$groups = ['Serif' => [], 'Sans-serif' => []];
if ($keptFonts) { $groups['Actual'] = []; }
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
          Selector de idioma ES/EN
        </label>
        <p class="admin-toggle__desc">Se muestra en la web.</p>

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
        <p class="admin-form__hint">En este orden, donde estén activadas.</p>
        <div id="social-links-list"></div>
        <button type="button" id="add-social-btn" class="admin-btn admin-btn--secondary" style="margin-top:8px;">+ Añadir red social</button>

        <!-- fix (Andrea): antes salían siempre en cabecera y pie — ahora cada
             sitio elige dónde mostrarlas, por separado. -->
        <div style="margin-top:16px; display:flex; gap:24px; flex-wrap:wrap;">
          <label class="admin-toggle admin-toggle--compact">
            <input type="checkbox" id="header-show-social" checked>
            <span class="admin-toggle__track"><span class="admin-toggle__thumb"></span></span>
            Mostrar en la cabecera
          </label>
          <label class="admin-toggle admin-toggle--compact">
            <input type="checkbox" id="footer-show-social" checked>
            <span class="admin-toggle__track"><span class="admin-toggle__thumb"></span></span>
            Mostrar en el pie de página
          </label>
        </div>
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

      <!-- fix (Andrea): web en construcción — dos interruptores independientes,
           se pueden activar por separado o los dos a la vez. /admin siempre
           sigue funcionando aunque "Próximamente" esté activado. -->
      <section class="admin-form__block">
        <h2>Web en construcción</h2>
        <label class="admin-toggle">
          <input type="checkbox" id="site-noindex">
          <span class="admin-toggle__track"><span class="admin-toggle__thumb"></span></span>
          No indexar en buscadores (noindex, nofollow)
        </label>
        <p class="admin-toggle__desc">La web se ve normal, pero pide a Google y compañía que no la indexen todavía.</p>

        <label class="admin-toggle" style="margin-top:12px;">
          <input type="checkbox" id="site-coming-soon">
          <span class="admin-toggle__track"><span class="admin-toggle__thumb"></span></span>
          Página "Próximamente"
        </label>
        <p class="admin-toggle__desc">Sustituye toda la web pública por un aviso de "en construcción". El panel de administración sigue funcionando con normalidad.</p>

        <div id="coming-soon-fields" hidden>
          <div class="admin-form__bilingual" style="margin-top:12px;">
            <div class="admin-form__lang-col">
              <span class="admin-form__lang-badge">Español</span>
              <label for="coming-soon-message-es">Mensaje — opcional</label>
              <textarea id="coming-soon-message-es" rows="3" placeholder="Estamos preparando algo nuevo. Vuelve pronto."></textarea>
            </div>
            <div class="admin-form__lang-col">
              <span class="admin-form__lang-badge">English</span>
              <label for="coming-soon-message-en">Message — optional</label>
              <textarea id="coming-soon-message-en" rows="3" placeholder="We're working on something new. Check back soon."></textarea>
            </div>
          </div>
        </div>
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

      <!-- fix (Andrea): tamaño y grosor de letra por tipo de texto, con vista
           previa en vivo de todos los sitios donde aparece cada uno. -->
      <section class="admin-form__block">
        <h2>Tipografía — tamaños y grosores</h2>
        <p class="admin-form__hint">Tamaño (en píxeles) y grosor de letra de cada tipo de texto del sitio. La vista previa de abajo se actualiza al momento.</p>

        <?php
        $simpleTypeControls = [
            'hero_title' => ['Hero — título', 20, 100],
            'hero_subtitle' => ['Hero — subtítulo', 10, 24],
            'header_title' => ['Cabecera — título', 14, 40],
            'header_menu' => ['Cabecera — menú', 10, 20],
            'body' => ['Textos (párrafos y descripciones)', 12, 22],
            'footer_legal' => ['Pie de página — legal', 10, 18],
            'footer_menu' => ['Pie de página — menú', 10, 18],
            'breadcrumb' => ['Miga de pan', 10, 16],
            'grid_title' => ['Grids (home, categorías, proyectos) — título', 10, 24],
            'grid_summary' => ['Grids (home, categorías, proyectos) — resumen', 10, 20],
        ];
        $weightOptions = [
            '300' => 'Fina', '400' => 'Normal', '500' => 'Medio',
            '600' => 'Semi-negrita', '700' => 'Negrita', '800' => 'Extra-negrita',
        ];
        $renderWeightOptions = function () use ($weightOptions) {
            foreach ($weightOptions as $val => $wlabel) {
                echo '<option value="' . $val . '">' . $wlabel . '</option>';
            }
        };
        ?>

        <?php foreach ($simpleTypeControls as $key => [$label, $min, $max]): $dash = str_replace('_', '-', $key); ?>
          <div class="type-control">
            <label for="type-<?= $dash ?>-size"><?= htmlspecialchars($label) ?></label>
            <div class="type-control__row">
              <input type="range" id="type-<?= $dash ?>-size" min="<?= $min ?>" max="<?= $max ?>" step="1">
              <span id="type-<?= $dash ?>-size-value" class="type-control__value"></span>
              <select id="type-<?= $dash ?>-weight" class="type-control__weight">
                <?php $renderWeightOptions(); ?>
              </select>
            </div>
          </div>
        <?php endforeach; ?>

        <div class="type-control type-control--headings">
          <label for="type-headings-mode">Encabezados (H1, H2, H3, H4)</label>
          <select id="type-headings-mode">
            <option value="proportional">Proporcional a partir de H1 (recomendado)</option>
            <option value="custom">Personalizar cada nivel por separado</option>
          </select>
          <p class="admin-form__hint" id="headings-proportional-hint">H2, H3 y H4 se escalan automáticamente a partir del tamaño y grosor de H1.</p>

          <div class="type-control" style="margin-top:12px;">
            <label for="type-h1-size">H1</label>
            <div class="type-control__row">
              <input type="range" id="type-h1-size" min="24" max="72" step="1">
              <span id="type-h1-size-value" class="type-control__value"></span>
              <select id="type-h1-weight" class="type-control__weight">
                <?php $renderWeightOptions(); ?>
              </select>
            </div>
          </div>

          <div id="headings-custom-fields" hidden>
            <?php foreach (['h2' => [18, 56], 'h3' => [16, 40], 'h4' => [14, 32]] as $level => [$min, $max]): ?>
              <div class="type-control" style="margin-top:12px;">
                <label for="type-<?= $level ?>-size"><?= strtoupper($level) ?></label>
                <div class="type-control__row">
                  <input type="range" id="type-<?= $level ?>-size" min="<?= $min ?>" max="<?= $max ?>" step="1">
                  <span id="type-<?= $level ?>-size-value" class="type-control__value"></span>
                  <select id="type-<?= $level ?>-weight" class="type-control__weight">
                    <?php $renderWeightOptions(); ?>
                  </select>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- fix (Andrea): visor con todos los elementos a la vez, a tamaño real -->
        <div class="type-preview-panel" id="type-preview-panel">
          <p class="type-preview-panel__label">Vista previa</p>

          <span class="type-preview-item__label">Hero — título</span>
          <p class="type-preview-item" id="type-preview-hero_title" style="font-family:var(--font-display);">Nombre del sitio</p>

          <span class="type-preview-item__label">Hero — subtítulo</span>
          <p class="type-preview-item" id="type-preview-hero_subtitle" style="font-family:var(--font-ui); letter-spacing:0.15em; text-transform:uppercase;">Fotografía y visual</p>

          <span class="type-preview-item__label">Cabecera — título</span>
          <p class="type-preview-item" id="type-preview-header_title" style="font-family:var(--font-display);">Nombre del sitio</p>

          <span class="type-preview-item__label">Cabecera — menú</span>
          <p class="type-preview-item" id="type-preview-header_menu" style="font-family:var(--font-ui);">Sobre mí &nbsp;·&nbsp; Categorías &nbsp;·&nbsp; Contacto</p>

          <span class="type-preview-item__label">H1</span>
          <p class="type-preview-item" id="type-preview-h1" style="font-family:var(--font-display);">Título de página</p>

          <span class="type-preview-item__label">H2</span>
          <p class="type-preview-item" id="type-preview-h2" style="font-family:var(--font-display);">Subtítulo de sección</p>

          <span class="type-preview-item__label">H3</span>
          <p class="type-preview-item" id="type-preview-h3" style="font-family:var(--font-display);">Encabezado de apartado</p>

          <span class="type-preview-item__label">H4</span>
          <p class="type-preview-item" id="type-preview-h4" style="font-family:var(--font-display);">Encabezado menor</p>

          <span class="type-preview-item__label">Textos</span>
          <p class="type-preview-item" id="type-preview-body" style="font-family:var(--font-body);">Este es un párrafo de ejemplo, como los que aparecen en la descripción de un proyecto o en una página de contenido.</p>

          <span class="type-preview-item__label">Pie de página — legal</span>
          <p class="type-preview-item" id="type-preview-footer_legal" style="font-family:var(--font-ui);">© 2026 Nombre del sitio</p>

          <span class="type-preview-item__label">Pie de página — menú</span>
          <p class="type-preview-item" id="type-preview-footer_menu" style="font-family:var(--font-ui);">Aviso legal &nbsp;·&nbsp; Privacidad</p>

          <span class="type-preview-item__label">Miga de pan</span>
          <p class="type-preview-item" id="type-preview-breadcrumb" style="font-family:var(--font-ui);">Inicio / Categoría / Proyecto</p>

          <span class="type-preview-item__label">Grids (título)</span>
          <p class="type-preview-item" id="type-preview-grid_title" style="font-family:var(--font-ui); color:var(--color-ink-900);">Nombre del proyecto</p>

          <span class="type-preview-item__label">Grids (resumen)</span>
          <p class="type-preview-item" id="type-preview-grid_summary" style="font-family:var(--font-body);">Breve descripción de este proyecto o categoría, tal como aparece bajo cada miniatura.</p>
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
        <p class="admin-form__hint">Actívalos o desactívalos como quieras, y arrastra <span aria-hidden="true">⠿</span> para cambiar el orden en que aparecen en la página.</p>
        <p class="admin-form__hint">El primero que quede activo usa la cabecera transparente superpuesta (como ahora el Hero) si es el Hero o el Mosaico de proyectos; cualquier otro caso usa la cabecera sólida normal.</p>

        <ul class="admin-drag-list" id="home-modules-list">
          <li class="admin-drag-item" draggable="true" data-module="hero">
            <span class="admin-drag-handle" aria-hidden="true" title="Arrastrar para reordenar">⠿</span>
            <div class="admin-drag-item__content">
              <label class="admin-toggle">
                <input type="checkbox" id="home-show-hero">
                <span class="admin-toggle__track"><span class="admin-toggle__thumb"></span></span>
                Hero
              </label>
              <p class="admin-toggle__desc">Sección grande a pantalla completa con el nombre del sitio.</p>

              <!-- fix (Andrea): fondo del Hero configurable — mosaico (de siempre),
                   foto destacada al azar, o sin fondo (color liso). -->
              <div id="hero-background-fields" style="margin:8px 0 0 0;">
                <label for="hero-background-mode">Fondo del Hero</label>
                <select id="hero-background-mode">
                  <option value="mosaic">Mosaico de fotos (como siempre)</option>
                  <option value="random_photo">Foto destacada al azar</option>
                  <option value="none">Sin fondo (color liso)</option>
                </select>
                <p class="admin-form__hint" id="hero-background-none-hint" hidden>El texto se adapta automáticamente para verse bien tanto si el color de fondo es claro como oscuro.</p>
              </div>
            </div>
          </li>

          <li class="admin-drag-item" draggable="true" data-module="categories">
            <span class="admin-drag-handle" aria-hidden="true" title="Arrastrar para reordenar">⠿</span>
            <div class="admin-drag-item__content">
              <label class="admin-toggle">
                <input type="checkbox" id="home-show-categories">
                <span class="admin-toggle__track"><span class="admin-toggle__thumb"></span></span>
                Categorías
              </label>
              <p class="admin-toggle__desc">Filas de proyectos con scroll horizontal.</p>
            </div>
          </li>

          <li class="admin-drag-item" draggable="true" data-module="videos">
            <span class="admin-drag-handle" aria-hidden="true" title="Arrastrar para reordenar">⠿</span>
            <div class="admin-drag-item__content">
              <label class="admin-toggle">
                <input type="checkbox" id="home-show-videos">
                <span class="admin-toggle__track"><span class="admin-toggle__thumb"></span></span>
                Vídeos
              </label>
              <p class="admin-toggle__desc">Rejilla con todos los vídeos publicados.</p>
            </div>
          </li>

          <li class="admin-drag-item" draggable="true" data-module="simple">
            <span class="admin-drag-handle" aria-hidden="true" title="Arrastrar para reordenar">⠿</span>
            <div class="admin-drag-item__content">
              <label class="admin-toggle">
                <input type="checkbox" id="home-show-simple">
                <span class="admin-toggle__track"><span class="admin-toggle__thumb"></span></span>
                Simple
              </label>
              <p class="admin-toggle__desc">Imagen destacada + título + texto.</p>
            </div>
          </li>

          <li class="admin-drag-item" draggable="true" data-module="projects_mosaic">
            <span class="admin-drag-handle" aria-hidden="true" title="Arrastrar para reordenar">⠿</span>
            <div class="admin-drag-item__content">
              <label class="admin-toggle">
                <input type="checkbox" id="home-show-projects-mosaic">
                <span class="admin-toggle__track"><span class="admin-toggle__thumb"></span></span>
                Mosaico de proyectos
              </label>
              <p class="admin-toggle__desc">Todos los proyectos publicados a pantalla completa, con efecto parallax al hacer scroll.</p>

              <div id="projects-mosaic-fields" style="margin:8px 0 0 0;" hidden>
                <label for="projects-mosaic-columns">Miniaturas por fila</label>
                <select id="projects-mosaic-columns">
                  <option value="1">1 por fila</option>
                  <option value="2">2 por fila</option>
                  <option value="3">3 por fila</option>
                </select>
              </div>
            </div>
          </li>
        </ul>

        <p class="admin-form__hint">Si ningún módulo con cabecera transparente queda el primero, la home usa la misma cabecera sólida que el resto de páginas.</p>
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
