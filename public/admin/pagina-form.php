<?php
require_once __DIR__ . '/../../src/lib/auth.php';
requireAuth();
require_once __DIR__ . '/../../src/lib/db.php';

$pdo = getDb();
$id = $_GET['id'] ?? null;
$page = null;
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM content_pages WHERE id = ?");
    $stmt->execute([$id]);
    $page = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title><?= $page ? 'Editar página' : 'Nueva página' ?> — Panel</title>
  <link rel="stylesheet" href="/assets/css/tokens.css">
  <link rel="stylesheet" href="assets/css/admin.css">
  <link href="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.bubble.css" rel="stylesheet">
</head>
<body class="admin-page">
  <header class="admin-header">
    <a href="paginas.php" class="admin-header__back">&larr; Volver a páginas</a>
    <div style="display:flex; align-items:center; gap:16px;">
      <?php if ($page): ?>
        <a href="/<?= urlencode($page['slug']) ?>" target="_blank" rel="noopener" class="admin-header__back">Ver en la web ↗</a>
      <?php endif; ?>
      <span id="save-status" class="save-status" aria-live="polite"></span>
    </div>
  </header>

  <main class="admin-form" data-page-id="<?= $page['id'] ?? '' ?>">
    <form id="page-form">

      <section class="admin-form__block">
        <h2>Contenido</h2>
        <div class="admin-form__bilingual">
          <div class="admin-form__lang-col">
            <span class="admin-form__lang-badge">Español <span class="admin-form__required">obligatorio</span></span>

            <label for="page-title-es">Título</label>
            <input type="text" id="page-title-es" name="title_es" required value="<?= htmlspecialchars($page['title_es'] ?? '') ?>">

            <label for="page-slug-es">URL slug</label>
            <input type="text" id="page-slug-es" name="slug" required placeholder="ej: preguntas-frecuentes" value="<?= htmlspecialchars($page['slug'] ?? '') ?>">
            <p class="admin-form__hint">/<span id="slug-es-preview"><?= htmlspecialchars($page['slug'] ?? '...') ?></span></p>

            <label>Contenido</label>
            <div id="page-content-es-editor" class="admin-richtext" data-richtext><?= $page['content_es'] ?? '' ?></div>
            <input type="hidden" name="content_es" id="page-content-es">

            <label for="page-meta-es">Meta descripción — opcional, texto plano</label>
            <textarea id="page-meta-es" name="meta_description_es" rows="2" maxlength="300"><?= htmlspecialchars($page['meta_description_es'] ?? '') ?></textarea>
          </div>

          <div class="admin-form__lang-col">
            <span class="admin-form__lang-badge">English <span class="admin-form__optional">opcional</span></span>

            <label for="page-title-en">Title</label>
            <input type="text" id="page-title-en" name="title_en" value="<?= htmlspecialchars($page['title_en'] ?? '') ?>">

            <label for="page-slug-en">URL slug — se autogenera del título si se deja vacío</label>
            <input type="text" id="page-slug-en" name="slug_en" placeholder="ej: faq" value="<?= htmlspecialchars($page['slug_en'] ?? '') ?>">
            <p class="admin-form__hint">/en/<span id="slug-en-preview"><?= htmlspecialchars($page['slug_en'] ?? '...') ?></span></p>

            <label>Content</label>
            <div id="page-content-en-editor" class="admin-richtext" data-richtext><?= $page['content_en'] ?? '' ?></div>
            <input type="hidden" name="content_en" id="page-content-en">

            <label for="page-meta-en">Meta description — opcional, plain text</label>
            <textarea id="page-meta-en" name="meta_description_en" rows="2" maxlength="300"><?= htmlspecialchars($page['meta_description_en'] ?? '') ?></textarea>
          </div>
        </div>
      </section>

      <section class="admin-form__block">
        <h2>Dónde aparece</h2>
        <div class="admin-toggle-row">
          <label class="admin-toggle">
            <input type="checkbox" id="page-show-header" <?= !empty($page['show_in_header']) ? 'checked' : '' ?>>
            <span class="admin-toggle__track"><span class="admin-toggle__thumb"></span></span>
            Menú de cabecera
          </label>
          <label class="admin-toggle">
            <input type="checkbox" id="page-show-footer" <?= ($page === null || !empty($page['show_in_footer'])) ? 'checked' : '' ?>>
            <span class="admin-toggle__track"><span class="admin-toggle__thumb"></span></span>
            Footer
          </label>
        </div>
        <label for="page-order" style="margin-top:12px;">Orden dentro del menú</label>
        <input type="number" id="page-order" name="sort_order" value="<?= htmlspecialchars($page['sort_order'] ?? 0) ?>">
      </section>

      <section class="admin-form__block">
        <h2>Buscadores</h2>
        <label class="admin-toggle">
          <input type="checkbox" id="page-noindex" <?= !empty($page['noindex']) ? 'checked' : '' ?>>
          <span class="admin-toggle__track"><span class="admin-toggle__thumb"></span></span>
          No mostrar esta página en Google
        </label>
        <p class="admin-toggle__desc">Recomendado para páginas legales (Privacidad, Cookies, Aviso legal) — no aportan valor de búsqueda. Para "Sobre mí" y páginas de contenido real, déjalo apagado.</p>
      </section>

      <div class="admin-form__actions">
        <?php if ($page): ?>
          <button type="button" id="delete-page-btn" class="admin-btn admin-btn--link" style="color:#B71C1C;margin-right:auto;">Eliminar página</button>
        <?php endif; ?>
        <button type="submit" class="admin-btn admin-btn--primary">Guardar</button>
      </div>
    </form>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.js"></script>
  <script type="module" src="assets/js/pagina-form.js"></script>
</body>
</html>
