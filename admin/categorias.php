<?php
require_once __DIR__ . '/../src/lib/auth.php';
requireAuth();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Categorías — Panel de administración</title>
  <link rel="stylesheet" href="/assets/css/tokens.css">
  <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-page">
  <header class="admin-header">
    <a href="dashboard.php" class="admin-header__back">&larr; Volver a proyectos</a>
  </header>

  <main class="admin-dashboard">
    <div class="admin-dashboard__toolbar">
      <h1 style="font-size:18px;margin:0;">Categorías</h1>
      <a href="categoria-form.php" class="admin-btn admin-btn--primary">+ Nueva categoría</a>
    </div>
    <p class="admin-form__hint" style="margin:-8px 0 16px;">Arrastra desde el icono ⠿ para reordenar.</p>

    <table class="admin-table" aria-busy="false">
      <thead>
        <tr>
          <th scope="col"></th>
          <th scope="col">Título página (ES)</th>
          <th scope="col">Título home (ES)</th>
          <th scope="col">Título página (EN)</th>
          <th scope="col">Estado</th>
          <th scope="col"></th>
        </tr>
      </thead>
      <tbody id="categories-table-body">
        <tr><td colspan="6" class="admin-table__loading">Cargando...</td></tr>
      </tbody>
    </table>

    <!-- fix (Andrea): ajustes de la página agregada /categorias (listado de
         todas), mismo patrón que /videos — todo opcional -->
    <section class="admin-form__block" style="margin-top:32px;">
      <h2>Ajustes de la página "todas las categorías"</h2>
      <p class="admin-form__hint">Una página que lista todas tus categorías juntas (como un índice). Distinta de cada página de categoría individual.</p>
      <span id="categories-settings-status" class="save-status" aria-live="polite"></span>

      <label class="admin-toggle">
        <input type="checkbox" id="categories-show-header">
        <span class="admin-toggle__track"><span class="admin-toggle__thumb"></span></span>
        Menú de cabecera
      </label>
      <p class="admin-toggle__desc">Aparece como "Categorías", con un submenú desplegable de cada una.</p>

      <label class="admin-toggle">
        <input type="checkbox" id="categories-show-footer">
        <span class="admin-toggle__track"><span class="admin-toggle__thumb"></span></span>
        Footer
      </label>

      <div class="admin-form__bilingual" style="margin-top:12px;">
        <div class="admin-form__lang-col">
          <span class="admin-form__lang-badge">Español</span>

          <label for="categories-slug-es">URL slug</label>
          <input type="text" id="categories-slug-es" placeholder="categorias">
          <p class="admin-form__hint">/<span id="categories-slug-es-preview">categorias</span></p>

          <label for="categories-h1-es">Título (H1) — opcional</label>
          <input type="text" id="categories-h1-es" placeholder="ej: Categorías">

          <label for="categories-desc-es">Descripción — opcional</label>
          <textarea id="categories-desc-es" rows="2"></textarea>

          <label for="categories-meta-title-es">Meta título — opcional</label>
          <input type="text" id="categories-meta-title-es" placeholder="Se genera automático si lo dejas vacío">

          <label for="categories-meta-es">Meta descripción — opcional</label>
          <textarea id="categories-meta-es" rows="2" maxlength="300"></textarea>
        </div>

        <div class="admin-form__lang-col">
          <span class="admin-form__lang-badge">English</span>

          <label for="categories-slug-en">URL slug — opcional</label>
          <input type="text" id="categories-slug-en" placeholder="categories">
          <p class="admin-form__hint">/en/<span id="categories-slug-en-preview">categories</span></p>

          <label for="categories-h1-en">Title — optional</label>
          <input type="text" id="categories-h1-en" placeholder="ej: Categories">

          <label for="categories-desc-en">Description — optional</label>
          <textarea id="categories-desc-en" rows="2"></textarea>

          <label for="categories-meta-title-en">Meta title — optional</label>
          <input type="text" id="categories-meta-title-en" placeholder="Auto-generated if left blank">

          <label for="categories-meta-en">Meta description — optional</label>
          <textarea id="categories-meta-en" rows="2" maxlength="300"></textarea>
        </div>
      </div>

      <div class="admin-form__actions">
        <button type="button" id="save-categories-settings-btn" class="admin-btn admin-btn--primary">Guardar ajustes de la página</button>
      </div>
    </section>
  </main>

  <script type="module" src="assets/js/categorias.js"></script>
</body>
</html>
