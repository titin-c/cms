<?php
/**
 * fix: /admin/ (la carpeta a secas, sin archivo) daba 403 Forbidden — nunca
 * había un index.php aquí. dashboard.php ya gestiona la redirección al login
 * si no hay sesión iniciada (via requireAuth()), así que basta con apuntar ahí.
 */
header('Location: dashboard.php');
exit;
