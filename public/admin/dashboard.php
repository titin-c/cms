<?php
require_once __DIR__ . '/../../src/lib/auth.php';
requireAuth();
require_once __DIR__ . '/../../src/lib/db.php';
require_once __DIR__ . '/../../src/lib/theme.php';

$pdo = getDb();
$themeSettings = getSiteSettings($pdo);
$projectsEnabled = ($themeSettings['module_projects_enabled'] ?? '1') === '1';
$videosEnabled = ($themeSettings['module_videos_enabled'] ?? '1') === '1';
$pagesEnabled = ($themeSettings['module_pages_enabled'] ?? '1') === '1';
$projectCount = $projectsEnabled ? (int) $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn() : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Proyectos — Panel de administración</title>
  <link rel="stylesheet" href="/assets/css/tokens.css">
  <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-page">
  <header class="admin-header">
    <span class="admin-header__title"><?= htmlspecialchars($themeSettings['site_name'] ?? 'Mi Sitio') ?> — Panel</span>
    <div style="display:flex; gap:16px; align-items:center;">
      <?php if ($projectsEnabled): ?>
        <a href="categorias.php" class="admin-header__back">Categorías</a>
      <?php endif; ?>
      <?php if ($videosEnabled): ?>
        <a href="videos.php" class="admin-header__back">Vídeos</a>
      <?php endif; ?>
      <?php if ($pagesEnabled): ?>
        <a href="paginas.php" class="admin-header__back">Páginas</a>
      <?php endif; ?>
      <a href="ajustes.php" class="admin-header__back">Ajustes</a>
      <a href="/admin/logout.php" class="admin-header__logout">Cerrar sesión</a>
    </div>
  </header>

  <main class="admin-dashboard">
    <?php if (!$projectsEnabled): ?>
      <!-- fix (CMS multi-cliente): módulo desactivado desde Ajustes para este cliente -->
      <div class="admin-empty-state">
        <h1>Módulo desactivado</h1>
        <p>Los proyectos y categorías están desactivados para este sitio.</p>
        <?php if ($videosEnabled): ?>
          <a href="videos.php" class="admin-btn admin-btn--primary">Ir a Vídeos</a>
        <?php else: ?>
          <a href="ajustes.php" class="admin-btn admin-btn--primary">Ir a Ajustes</a>
        <?php endif; ?>
      </div>
    <?php elseif ($projectCount === 0): ?>
      <!-- fix (ux-agent): estado vacío de primera vez, con CTA claro -->
      <div class="admin-empty-state">
        <h1>Empieza aquí</h1>
        <p>Aún no has publicado ningún proyecto.</p>
        <a href="proyecto-form.php" class="admin-btn admin-btn--primary">Crear tu primer proyecto</a>
      </div>
    <?php else: ?>
      <div class="admin-dashboard__toolbar">
        <input type="search" id="dashboard-search" placeholder="Buscar proyecto..." aria-label="Buscar proyecto">
        <a href="proyecto-form.php" class="admin-btn admin-btn--primary">+ Nuevo proyecto</a>
      </div>
      <p class="admin-form__hint" style="margin:-8px 0 16px;">Arrastra desde el icono ⠿ para reordenar (solo sin filtro de búsqueda activo).</p>

      <table class="admin-table" aria-busy="false">
        <thead>
          <tr>
            <th scope="col"></th>
            <th scope="col"></th>
            <th scope="col">Título</th>
            <th scope="col">Categoría</th>
            <th scope="col">Estado</th>
            <th scope="col">Destacado</th>
            <th scope="col"></th>
          </tr>
        </thead>
        <tbody id="dashboard-table-body">
          <!-- fix (component-library-base): skeleton mientras carga vía fetch -->
          <tr><td colspan="7" class="admin-table__loading">Cargando...</td></tr>
        </tbody>
      </table>
    <?php endif; ?>
  </main>

  <script type="module" src="assets/js/dashboard.js"></script>
</body>
</html>
