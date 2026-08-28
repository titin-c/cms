<?php
/**
 * fix (Andrea): endpoint mínimo para el "latido" de sesión del admin — ver
 * admin/assets/js/components/session-keepalive.js. Su única función es
 * pasar por requireAuth()/session_start(), que en la mayoría de hostings
 * actualiza la fecha de modificación del archivo de sesión en el servidor y
 * así retrasa su borrado automático mientras el panel sigue abierto.
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../../src/lib/auth.php';
requireAuth();
echo json_encode(['ok' => true]);
