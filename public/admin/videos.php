<?php
require_once __DIR__ . '/../../src/lib/auth.php';
requireAuth();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Vídeos — Panel de administración</title>
  <link rel="stylesheet" href="/assets/css/tokens.css">
  <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-page">
  <header class="admin-header">
    <a href="dashboard.php" class="admin-header__back">&larr; Volver a proyectos</a>
  </header>

  <main class="admin-dashboard">
    <div class="admin-dashboard__toolbar">
      <h1 style="font-size:18px;margin:0;">Vídeos</h1>
      <input type="search" id="dashboard-search" placeholder="Buscar vídeo..." aria-label="Buscar vídeo">
      <a href="video-form.php" class="admin-btn admin-btn--primary">+ Nuevo vídeo</a>
    </div>

    <p class="admin-form__hint" style="margin:-8px 0 16px;">Arrastra desde el icono ⠿ para reordenar (solo sin filtro de búsqueda activo).</p>

    <table class="admin-table" aria-busy="false">
      <thead>
        <tr>
          <th scope="col"></th>
          <th scope="col"></th>
          <th scope="col">Título</th>
          <th scope="col">Proveedor</th>
          <th scope="col">Visualización</th>
          <th scope="col">Estado</th>
          <th scope="col">Destacado</th>
          <th scope="col"></th>
        </tr>
      </thead>
      <tbody id="videos-table-body">
        <tr><td colspan="8" class="admin-table__loading">Cargando...</td></tr>
      </tbody>
    </table>

    <!-- fix (Andrea): ajustes de la propia página /videos, todo opcional —
         si no rellenas H1/descripción, no aparecen en la web -->
    <section class="admin-form__block" style="margin-top:32px;">
      <h2>Ajustes de la página de vídeos</h2>
      <span id="videos-settings-status" class="save-status" aria-live="polite"></span>

      <label class="admin-toggle">
        <input type="checkbox" id="videos-show-header">
        <span class="admin-toggle__track"><span class="admin-toggle__thumb"></span></span>
        Menú de cabecera
      </label>
      <label class="admin-toggle">
        <input type="checkbox" id="videos-show-footer">
        <span class="admin-toggle__track"><span class="admin-toggle__thumb"></span></span>
        Footer
      </label>

      <div class="admin-form__bilingual" style="margin-top:12px;">
        <div class="admin-form__lang-col">
          <span class="admin-form__lang-badge">Español</span>

          <label for="videos-slug-es">URL slug</label>
          <input type="text" id="videos-slug-es" placeholder="videos">
          <p class="admin-form__hint">/<span id="videos-slug-es-preview">videos</span></p>

          <label for="videos-h1-es">Título (H1) — opcional</label>
          <input type="text" id="videos-h1-es" placeholder="ej: Vídeos">

          <label for="videos-desc-es">Descripción — opcional</label>
          <textarea id="videos-desc-es" rows="2"></textarea>

          <label for="videos-meta-title-es">Meta título — opcional</label>
          <input type="text" id="videos-meta-title-es" placeholder="Se genera automático si lo dejas vacío">

          <label for="videos-meta-es">Meta descripción — opcional</label>
          <textarea id="videos-meta-es" rows="2" maxlength="300"></textarea>
        </div>

        <div class="admin-form__lang-col">
          <span class="admin-form__lang-badge">English</span>

          <label for="videos-slug-en">URL slug — opcional</label>
          <input type="text" id="videos-slug-en" placeholder="videos">
          <p class="admin-form__hint">/en/<span id="videos-slug-en-preview">videos</span></p>

          <label for="videos-h1-en">Title — optional</label>
          <input type="text" id="videos-h1-en" placeholder="ej: Videos">

          <label for="videos-desc-en">Description — optional</label>
          <textarea id="videos-desc-en" rows="2"></textarea>

          <label for="videos-meta-title-en">Meta title — optional</label>
          <input type="text" id="videos-meta-title-en" placeholder="Auto-generated if left blank">

          <label for="videos-meta-en">Meta description — optional</label>
          <textarea id="videos-meta-en" rows="2" maxlength="300"></textarea>
        </div>
      </div>

      <div class="admin-form__actions">
        <button type="button" id="save-videos-settings-btn" class="admin-btn admin-btn--primary">Guardar ajustes de la página</button>
      </div>
    </section>
  </main>

  <script type="module" src="assets/js/videos-dashboard.js"></script>
</body>
</html>
