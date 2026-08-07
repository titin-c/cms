<?php
require_once __DIR__ . '/../src/lib/auth.php';
requireAuth();
require_once __DIR__ . '/../src/lib/db.php';

$pdo = getDb();
$id = $_GET['id'] ?? null;
$video = null;
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM videos WHERE id = ?");
    $stmt->execute([$id]);
    $video = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title><?= $video ? 'Editar vídeo' : 'Nuevo vídeo' ?> — Panel</title>
  <link rel="stylesheet" href="/assets/css/tokens.css">
  <link rel="stylesheet" href="assets/css/admin.css">
  <link href="https://cdn.jsdelivr.net/npm/cropperjs@1/dist/cropper.min.css" rel="stylesheet">
</head>
<body class="admin-page">
  <header class="admin-header">
    <a href="videos.php" class="admin-header__back">&larr; Volver a vídeos</a>
    <div style="display:flex; align-items:center; gap:16px;">
      <?php if ($video && $video['status'] === 'published'): ?>
        <a href="/videos" target="_blank" rel="noopener" class="admin-header__back">Ver en la web ↗</a>
      <?php endif; ?>
      <span id="save-status" class="save-status" aria-live="polite"></span>
    </div>
  </header>

  <main class="admin-form" data-video-id="<?= $video['id'] ?? '' ?>">
    <form id="video-form">

      <section class="admin-form__block">
        <h2>Miniatura (thumbnail)</h2>
        <div id="image-cropper" data-cropper data-aspect-ratio="16/9"></div>
        <input type="hidden" name="thumbnail" id="main_image" value="<?= htmlspecialchars($video['thumbnail'] ?? '') ?>">
        <label for="thumbnail_alt">Alt de la miniatura</label>
        <input type="text" id="thumbnail_alt" name="thumbnail_alt" value="<?= htmlspecialchars($video['thumbnail_alt'] ?? '') ?>">
      </section>

      <section class="admin-form__block">
        <h2>Contenido</h2>
        <div class="admin-form__bilingual">
          <div class="admin-form__lang-col">
            <span class="admin-form__lang-badge">Español <span class="admin-form__required">obligatorio</span></span>
            <label for="title_es">Título</label>
            <input type="text" id="title_es" name="title_es" required value="<?= htmlspecialchars($video['title_es'] ?? '') ?>">
            <label for="subtitle_es">Subtítulo</label>
            <input type="text" id="subtitle_es" name="subtitle_es" value="<?= htmlspecialchars($video['subtitle_es'] ?? '') ?>">
          </div>
          <div class="admin-form__lang-col">
            <span class="admin-form__lang-badge">English <span class="admin-form__optional">opcional</span></span>
            <label for="title_en">Title</label>
            <input type="text" id="title_en" name="title_en" value="<?= htmlspecialchars($video['title_en'] ?? '') ?>">
            <label for="subtitle_en">Subtitle</label>
            <input type="text" id="subtitle_en" name="subtitle_en" value="<?= htmlspecialchars($video['subtitle_en'] ?? '') ?>">
          </div>
        </div>
      </section>

      <section class="admin-form__block">
        <h2>Vídeo</h2>
        <label for="video_url">URL del vídeo (YouTube, Vimeo, u otro)</label>
        <input type="url" id="video_url" name="video_url" required placeholder="https://www.youtube.com/watch?v=..." value="<?= htmlspecialchars($video['video_url'] ?? '') ?>">

        <label for="video_provider">Proveedor</label>
        <select id="video_provider" name="video_provider">
          <option value="youtube" <?= ($video['video_provider'] ?? 'youtube') === 'youtube' ? 'selected' : '' ?>>YouTube</option>
          <option value="vimeo" <?= ($video['video_provider'] ?? '') === 'vimeo' ? 'selected' : '' ?>>Vimeo</option>
          <option value="other" <?= ($video['video_provider'] ?? '') === 'other' ? 'selected' : '' ?>>Otro</option>
        </select>

        <label for="display_mode">Cómo se ve al hacer clic</label>
        <select id="display_mode" name="display_mode">
          <option value="lightbox" <?= ($video['display_mode'] ?? 'lightbox') === 'lightbox' ? 'selected' : '' ?>>Se reproduce aquí mismo (lightbox)</option>
          <option value="external" <?= ($video['display_mode'] ?? '') === 'external' ? 'selected' : '' ?>>Abre la página del vídeo en otra pestaña</option>
        </select>
        <p class="admin-form__hint" id="provider-other-hint" hidden>Con proveedor "Otro" no podemos garantizar que el sitio permita embeberse — te recomendamos usar "Abre en otra pestaña".</p>
      </section>

      <section class="admin-form__block">
        <h2>Organización</h2>
        <label for="sort_order">Orden</label>
        <input type="number" id="sort_order" name="sort_order" value="<?= htmlspecialchars($video['sort_order'] ?? 0) ?>">
        <label class="admin-toggle">
          <input type="checkbox" id="featured" name="featured" <?= !empty($video['featured']) ? 'checked' : '' ?>>
          <span class="admin-toggle__track"><span class="admin-toggle__thumb"></span></span>
          Destacar
        </label>
      </section>

      <div class="admin-form__actions">
        <button type="button" id="save-draft-btn" class="admin-btn admin-btn--secondary">Guardar borrador</button>
        <button type="submit" id="publish-btn" class="admin-btn admin-btn--primary">Publicar</button>
      </div>
    </form>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/cropperjs@1/dist/cropper.min.js"></script>
  <script type="module" src="assets/js/video-form.js"></script>
</body>
</html>
