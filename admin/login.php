<?php
require_once __DIR__ . '/../src/lib/auth.php';
startSecureSession();
if (!empty($_SESSION['admin_id'])) {
    header('Location: /admin/dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Acceso — Panel de administración</title>
  <link rel="stylesheet" href="/assets/css/tokens.css">
  <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-auth-page">
  <main class="admin-auth">
    <h1>Panel de administración</h1>
    <form id="login-form" novalidate>
      <label for="email">Email</label>
      <input type="email" id="email" name="email" required autocomplete="username">

      <label for="password">Contraseña</label>
      <input type="password" id="password" name="password" required autocomplete="current-password">

      <span id="login-error" role="alert" class="field-error"></span>

      <button type="submit" id="login-submit">Entrar</button>
    </form>
  </main>
  <script type="module" src="assets/js/login.js"></script>
</body>
</html>
