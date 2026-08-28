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
  <title>Páginas — Panel de administración</title>
  <link rel="stylesheet" href="/assets/css/tokens.css">
  <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-page">
  <header class="admin-header">
    <a href="dashboard.php" class="admin-header__back">&larr; Volver a proyectos</a>
  </header>

  <main class="admin-dashboard">
    <div class="admin-dashboard__toolbar">
      <h1 style="font-size:18px;margin:0;">Páginas del sitio</h1>
      <a href="pagina-form.php" class="admin-btn admin-btn--primary">+ Nueva página</a>
    </div>
    <p class="admin-form__hint" style="margin:-8px 0 16px;">Sobre mí, Privacidad, Cookies y Aviso legal ya vienen creadas por defecto — puedes editarlas, eliminarlas o crear páginas nuevas. Arrastra desde ⠿ para reordenar.</p>

    <table class="admin-table" aria-busy="false">
      <thead>
        <tr>
          <th scope="col"></th>
          <th scope="col">Título (ES)</th>
          <th scope="col">URL</th>
          <th scope="col">Header</th>
          <th scope="col">Footer</th>
          <th scope="col"></th>
        </tr>
      </thead>
      <tbody id="pages-table-body">
        <tr><td colspan="6" class="admin-table__loading">Cargando...</td></tr>
      </tbody>
    </table>
  </main>

  <script type="module" src="assets/js/paginas.js"></script>
</body>
</html>
