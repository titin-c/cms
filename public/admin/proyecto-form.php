<?php
require_once __DIR__ . '/../../src/lib/auth.php';
requireAuth();
require_once __DIR__ . '/../../src/lib/db.php';

$pdo = getDb();
$id = $_GET['id'] ?? null;
$project = null;
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$id]);
    $project = $stmt->fetch();
}
$categories = $pdo->query("SELECT id, title_es, seo_keywords_es FROM categories ORDER BY sort_order ASC")->fetchAll();

// fix (Andrea): categorías adicionales ya asignadas, para marcar los checkboxes en modo edición
$extraCategoryIds = [];
if ($project) {
    $extraStmt = $pdo->prepare("SELECT category_id FROM project_extra_categories WHERE project_id = ?");
    $extraStmt->execute([$project['id']]);
    $extraCategoryIds = $extraStmt->fetchAll(PDO::FETCH_COLUMN);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title><?= $project ? 'Editar proyecto' : 'Nuevo proyecto' ?> — Panel</title>
  <link rel="stylesheet" href="/assets/css/tokens.css">
  <link rel="stylesheet" href="assets/css/admin.css">
  <!-- Quill: editor de texto enriquecido, gratuito y ligero (decisión técnica, paso 5) -->
  <link href="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.bubble.css" rel="stylesheet">
  <!-- Cropper.js: recorte de imagen, gratuito y ligero (~30kb) -->
  <link href="https://cdn.jsdelivr.net/npm/cropperjs@1/dist/cropper.min.css" rel="stylesheet">
</head>
<body class="admin-page">
  <header class="admin-header">
    <a href="dashboard.php" class="admin-header__back">&larr; Volver</a>
    <div style="display:flex; align-items:center; gap:16px;">
      <?php if ($project && $project['status'] === 'published'): ?>
        <a href="/proyecto/<?= urlencode($project['slug']) ?>" target="_blank" rel="noopener" class="admin-header__back">Ver en la web ↗</a>
      <?php endif; ?>
      <span id="save-status" class="save-status" aria-live="polite">
        <?= $project ? 'Última edición: ' . $project['updated_at'] : '' ?>
      </span>
    </div>
  </header>

  <main class="admin-form" data-project-id="<?= $project['id'] ?? '' ?>">
    <form id="project-form">

      <!-- BLOQUE: imagen principal -->
      <section class="admin-form__block">
        <h2>Imagen principal</h2>
        <!-- fix (usability-agent 🔴 crítico): recorte con controles ampliados en móvil, ver image-cropper.js -->
        <div id="image-cropper" data-cropper></div>
        <input type="hidden" name="main_image" id="main_image" value="<?= htmlspecialchars($project['main_image'] ?? '') ?>">
        <label for="main_image_alt">Alt de la imagen principal</label>
        <input type="text" id="main_image_alt" name="main_image_alt" value="<?= htmlspecialchars($project['main_image_alt'] ?? '') ?>">
        <p class="admin-form__hint" id="alt-keywords-hint" data-keywords-hint></p>
      </section>

      <!-- BLOQUE: contenido ES/EN en dos columnas -->
      <section class="admin-form__block">
        <h2>Contenido</h2>
        <div class="admin-form__bilingual">
          <div class="admin-form__lang-col">
            <span class="admin-form__lang-badge">Español <span class="admin-form__required">obligatorio</span></span>

            <label for="title_es">Título</label>
            <input type="text" id="title_es" name="title_es" required value="<?= htmlspecialchars($project['title_es'] ?? '') ?>">

            <label for="editor_es">Texto</label>
            <div id="editor_es" class="admin-richtext" data-richtext data-lang="es"><?= $project['content_es'] ?? '' ?></div>
            <input type="hidden" name="content_es" id="content_es">

            <!-- fix (usability-agent 🔴): selección de extracto por párrafo + botón, no arrastre libre en móvil -->
            <p class="admin-form__hint">Selecciona un fragmento del texto y pulsa "Usar como resumen".</p>
            <button type="button" id="excerpt-btn-es" class="admin-btn admin-btn--secondary">Usar como resumen</button>
            <input type="hidden" name="excerpt_es" id="excerpt_es" value="<?= htmlspecialchars($project['excerpt_es'] ?? '') ?>">
          </div>

          <div class="admin-form__lang-col">
            <span class="admin-form__lang-badge">English <span class="admin-form__optional">opcional — si lo dejas vacío, no se mostrará en inglés</span></span>

            <label for="title_en">Title</label>
            <input type="text" id="title_en" name="title_en" value="<?= htmlspecialchars($project['title_en'] ?? '') ?>">

            <label for="slug_en">URL slug (English)</label>
            <input type="text" id="slug_en" name="slug_en" value="<?= htmlspecialchars($project['slug_en'] ?? '') ?>" placeholder="auto-generated from title if left blank">
            <p class="admin-form__hint">/project/<?= htmlspecialchars($project['slug_en'] ?? '...') ?></p>

            <label for="editor_en">Text</label>
            <div id="editor_en" class="admin-richtext" data-richtext data-lang="en"><?= $project['content_en'] ?? '' ?></div>
            <input type="hidden" name="content_en" id="content_en">

            <button type="button" id="excerpt-btn-en" class="admin-btn admin-btn--secondary">Use as excerpt</button>
            <input type="hidden" name="excerpt_en" id="excerpt_en" value="<?= htmlspecialchars($project['excerpt_en'] ?? '') ?>">
          </div>
        </div>
      </section>

      <!-- BLOQUE: organización -->
      <section class="admin-form__block">
        <h2>Organización</h2>
        <label for="category_id">Categoría</label>
        <select id="category_id" name="category_id" required>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" data-keywords="<?= htmlspecialchars($cat['seo_keywords_es'] ?? '') ?>" <?= ($project['category_id'] ?? null) == $cat['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($cat['title_es']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <a href="categorias.php" class="admin-btn admin-btn--link" target="_blank">Gestionar categorías &rarr;</a>

        <label for="sort_order">Orden</label>
        <input type="number" id="sort_order" name="sort_order" value="<?= htmlspecialchars($project['sort_order'] ?? 0) ?>">

        <?php
          $otherCategories = array_filter($categories, fn($c) => $c['id'] != ($project['category_id'] ?? null));
        ?>
        <?php if ($otherCategories): ?>
          <fieldset class="admin-form__fieldset">
            <legend><?= 'Categorías adicionales (opcional)' ?></legend>
            <p class="admin-form__hint">El proyecto también aparecerá listado en estas categorías, sin cambiar su URL ni su categoría principal.</p>
            <?php foreach ($otherCategories as $cat): ?>
              <label class="admin-form__checkbox">
                <input type="checkbox" name="extra_categories[]" value="<?= $cat['id'] ?>"
                  <?= in_array($cat['id'], $extraCategoryIds) ? 'checked' : '' ?>>
                <?= htmlspecialchars($cat['title_es']) ?>
              </label>
            <?php endforeach; ?>
          </fieldset>
        <?php endif; ?>

        <label class="admin-toggle">
          <input type="checkbox" id="featured" name="featured" <?= !empty($project['featured']) ? 'checked' : '' ?>>
          <span class="admin-toggle__track"><span class="admin-toggle__thumb"></span></span>
          Destacar en la página principal
        </label>
      </section>

      <!-- BLOQUE: galería secundaria -->
      <section class="admin-form__block">
        <h2>Resto de imágenes</h2>
        <div id="gallery-uploader" data-gallery-uploader></div>
      </section>

      <!-- BLOQUE: SEO -->
      <section class="admin-form__block">
        <h2>SEO</h2>
        <p class="admin-form__hint">Los campos de meta descripción van dentro de una etiqueta &lt;meta&gt; sin formato — se mantienen como texto plano a propósito.</p>
        <div class="admin-form__bilingual">
          <div class="admin-form__lang-col">
            <span class="admin-form__lang-badge">Español</span>

            <label for="seo_keywords">Palabras clave</label>
            <input type="text" id="seo_keywords" name="seo_keywords" value="<?= htmlspecialchars($project['seo_keywords'] ?? '') ?>">

            <label for="seo_description_es">Meta descripción</label>
            <textarea id="seo_description_es" name="seo_description_es" maxlength="300" rows="2"><?= htmlspecialchars($project['seo_description_es'] ?? '') ?></textarea>
          </div>

          <div class="admin-form__lang-col">
            <span class="admin-form__lang-badge">English</span>

            <label for="seo_keywords_en">Keywords — opcional</label>
            <input type="text" id="seo_keywords_en" name="seo_keywords_en" value="<?= htmlspecialchars($project['seo_keywords_en'] ?? '') ?>">

            <label for="seo_description_en">Meta description — opcional</label>
            <textarea id="seo_description_en" name="seo_description_en" maxlength="300" rows="2"><?= htmlspecialchars($project['seo_description_en'] ?? '') ?></textarea>
          </div>
        </div>
        <p class="admin-form__hint">Nota: Google ya no usa el campo de palabras clave para posicionar desde hace años — lo mantenemos por compatibilidad con otros motores, pero no le dediques mucho tiempo.</p>
      </section>

      <div class="admin-form__actions">
        <label class="admin-toggle" style="margin-right:auto;">
          <input type="checkbox" id="draft-toggle" <?= ($project['status'] ?? '') === 'draft' ? 'checked' : '' ?>>
          <span class="admin-toggle__track"><span class="admin-toggle__thumb"></span></span>
          Guardar como borrador
        </label>
        <button type="submit" id="save-btn" class="admin-btn admin-btn--primary">Guardar</button>
      </div>
    </form>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/cropperjs@1/dist/cropper.min.js"></script>
  <script type="module" src="assets/js/proyecto-form.js"></script>
</body>
</html>
