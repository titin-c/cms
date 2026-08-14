<?php
require_once __DIR__ . '/../src/lib/auth.php';
requireAuth();
require_once __DIR__ . '/../src/lib/db.php';

$pdo = getDb();
$id = $_GET['id'] ?? null;
$category = null;
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $category = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title><?= $category ? 'Editar categoría' : 'Nueva categoría' ?> — Panel</title>
  <link rel="stylesheet" href="/assets/css/tokens.css">
  <link rel="stylesheet" href="assets/css/admin.css">
  <link href="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.bubble.css" rel="stylesheet">
</head>
<body class="admin-page">
  <header class="admin-header">
    <a href="categorias.php" class="admin-header__back">&larr; Volver a categorías</a>
    <div style="display:flex; align-items:center; gap:16px;">
      <?php if ($category && $category['status'] === 'published'): ?>
        <a href="/categoria/<?= urlencode($category['slug']) ?>" target="_blank" rel="noopener" class="admin-header__back">Ver en la web ↗</a>
      <?php endif; ?>
      <span id="save-status" class="save-status" aria-live="polite"></span>
    </div>
  </header>

  <main class="admin-form" data-category-id="<?= $category['id'] ?? '' ?>">
    <form id="category-form">

      <section class="admin-form__block">
        <h2>Página de categoría (/categoria/...)</h2>
        <div class="admin-form__bilingual">
          <div class="admin-form__lang-col">
            <span class="admin-form__lang-badge">Español <span class="admin-form__required">obligatorio</span></span>

            <label for="cat-title-es">Título (H1)</label>
            <div class="admin-form__inline-toggle-row">
              <input type="text" id="cat-title-es" name="title_es" required placeholder="ej: Fotografía de Moda Editorial en Valencia" value="<?= htmlspecialchars($category['title_es'] ?? '') ?>">
              <label class="admin-toggle admin-toggle--compact" title="Mostrar este título en la página">
                <input type="checkbox" id="cat-show-title" <?= ($category === null || !empty($category['show_title'])) ? 'checked' : '' ?>>
                <span class="admin-toggle__track"><span class="admin-toggle__thumb"></span></span>
                <span class="admin-toggle__compact-label">Mostrar título</span>
              </label>
            </div>

            <label>Descripción larga — opcional</label>
            <div id="cat-desc-es-editor" class="admin-richtext" data-richtext><?= $category['description_es'] ?? '' ?></div>
            <input type="hidden" name="description_es" id="cat-desc-es">
          </div>

          <div class="admin-form__lang-col">
            <span class="admin-form__lang-badge">English <span class="admin-form__optional">opcional</span></span>

            <label for="cat-title-en">Título (H1)</label>
            <input type="text" id="cat-title-en" name="title_en" value="<?= htmlspecialchars($category['title_en'] ?? '') ?>">

            <label>Descripción larga</label>
            <div id="cat-desc-en-editor" class="admin-richtext" data-richtext><?= $category['description_en'] ?? '' ?></div>
            <input type="hidden" name="description_en" id="cat-desc-en">

            <label for="cat-slug-en">URL slug — se autogenera del título si se deja vacío</label>
            <input type="text" id="cat-slug-en" name="slug_en" placeholder="ej: fashion" value="<?= htmlspecialchars($category['slug_en'] ?? '') ?>">
          </div>
        </div>
      </section>

      <section class="admin-form__block">
        <h2>Dónde aparece</h2>
        <label for="cat-header-placement">Cabecera</label>
        <?php $placement = $category['header_placement'] ?? 'submenu'; ?>
        <select id="cat-header-placement">
          <option value="none" <?= $placement === 'none' ? 'selected' : '' ?>>No aparece en la cabecera</option>
          <option value="direct" <?= $placement === 'direct' ? 'selected' : '' ?>>Enlace directo en la cabecera</option>
          <option value="submenu" <?= $placement === 'submenu' ? 'selected' : '' ?>>Dentro del submenú "Proyectos" (si está activado)</option>
        </select>
        <p class="admin-form__hint">El submenú "Proyectos" se activa desde el listado de categorías. Si está desactivado, esta categoría simplemente no aparece aunque elijas "submenú".</p>

        <div class="admin-toggle-row" style="margin-top:16px;">
          <label class="admin-toggle">
            <input type="checkbox" id="cat-show-footer" <?= !empty($category['show_in_footer']) ? 'checked' : '' ?>>
            <span class="admin-toggle__track"><span class="admin-toggle__thumb"></span></span>
            Footer
          </label>
          <label class="admin-toggle">
            <input type="checkbox" id="cat-show-home" <?= ($category === null || !empty($category['show_in_home'])) ? 'checked' : '' ?>>
            <span class="admin-toggle__track"><span class="admin-toggle__thumb"></span></span>
            Fila en la home
          </label>
        </div>
      </section>

      <section class="admin-form__block">
        <h2>Fila de la home</h2>
        <label class="admin-toggle">
          <input type="checkbox" id="cat-show-home-2" <?= ($category === null || !empty($category['show_in_home'])) ? 'checked' : '' ?>>
          <span class="admin-toggle__track"><span class="admin-toggle__thumb"></span></span>
          Mostrar en la home
        </label>

        <div id="cat-home-row-fields" style="margin-top:16px;">
          <p class="admin-form__hint">Si dejas estos campos vacíos, la home usará el título/descripción de la página de categoría de arriba.</p>
          <div class="admin-form__bilingual">
            <div class="admin-form__lang-col">
              <span class="admin-form__lang-badge">Español</span>

              <label for="cat-home-title-es">Título corto para la home — opcional</label>
              <input type="text" id="cat-home-title-es" name="home_title_es" placeholder="ej: Moda" value="<?= htmlspecialchars($category['home_title_es'] ?? '') ?>">

              <label>Descripción breve — opcional</label>
              <div id="cat-home-desc-es-editor" class="admin-richtext" data-richtext><?= $category['home_description_es'] ?? '' ?></div>
              <input type="hidden" name="home_description_es" id="cat-home-desc-es">

              <label for="cat-button-es">Texto del botón "Ver todo" — opcional</label>
              <input type="text" id="cat-button-es" name="button_label_es" placeholder="Ver todo" value="<?= htmlspecialchars($category['button_label_es'] ?? '') ?>">
            </div>

            <div class="admin-form__lang-col">
              <span class="admin-form__lang-badge">English</span>

              <label for="cat-home-title-en">Título corto — opcional</label>
              <input type="text" id="cat-home-title-en" name="home_title_en" value="<?= htmlspecialchars($category['home_title_en'] ?? '') ?>">

              <label>Descripción breve — opcional</label>
              <div id="cat-home-desc-en-editor" class="admin-richtext" data-richtext><?= $category['home_description_en'] ?? '' ?></div>
              <input type="hidden" name="home_description_en" id="cat-home-desc-en">

              <label for="cat-button-en">Texto del botón — opcional</label>
              <input type="text" id="cat-button-en" name="button_label_en" placeholder="View all" value="<?= htmlspecialchars($category['button_label_en'] ?? '') ?>">
            </div>
          </div>
        </div>
      </section>

      <section class="admin-form__block">
        <h2>SEO</h2>
        <p class="admin-form__hint">Los campos de meta descripción van dentro de una etiqueta &lt;meta&gt; sin formato — se mantienen como texto plano a propósito.</p>
        <div class="admin-form__bilingual">
          <div class="admin-form__lang-col">
            <span class="admin-form__lang-badge">Español</span>

            <label for="cat-meta-es">Meta descripción — ~155 caracteres</label>
            <textarea id="cat-meta-es" name="meta_description_es" rows="2" maxlength="300"><?= htmlspecialchars($category['meta_description_es'] ?? '') ?></textarea>

            <label for="cat-kw-es">Palabras clave de referencia — separadas por comas</label>
            <input type="text" id="cat-kw-es" name="seo_keywords_es" placeholder="fotografía moda, editorial, Valencia" value="<?= htmlspecialchars($category['seo_keywords_es'] ?? '') ?>">
          </div>

          <div class="admin-form__lang-col">
            <span class="admin-form__lang-badge">English</span>

            <label for="cat-meta-en">Meta description</label>
            <textarea id="cat-meta-en" name="meta_description_en" rows="2" maxlength="300"><?= htmlspecialchars($category['meta_description_en'] ?? '') ?></textarea>

            <label for="cat-kw-en">Reference keywords</label>
            <input type="text" id="cat-kw-en" name="seo_keywords_en" placeholder="fashion photography, editorial, Valencia" value="<?= htmlspecialchars($category['seo_keywords_en'] ?? '') ?>">
          </div>
        </div>
      </section>

      <section class="admin-form__block">
        <h2>Organización</h2>
        <label for="cat-order">Orden</label>
        <input type="number" id="cat-order" name="sort_order" value="<?= htmlspecialchars($category['sort_order'] ?? 0) ?>">
      </section>

      <div class="admin-form__actions">
        <label class="admin-toggle" style="margin-right:auto;">
          <input type="checkbox" id="cat-draft" <?= ($category['status'] ?? '') === 'draft' ? 'checked' : '' ?>>
          <span class="admin-toggle__track"><span class="admin-toggle__thumb"></span></span>
          Guardar como borrador
        </label>
        <?php if ($category && !$category['is_default_uncategorized']): ?>
          <button type="button" id="delete-category-btn" class="admin-btn admin-btn--link" style="color:#B71C1C;">Eliminar</button>
        <?php endif; ?>
        <button type="submit" class="admin-btn admin-btn--primary">Guardar</button>
      </div>
    </form>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.js"></script>
  <script type="module" src="assets/js/categoria-form.js"></script>
</body>
</html>
