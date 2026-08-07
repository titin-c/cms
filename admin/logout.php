<?php
require_once __DIR__ . '/../src/lib/auth.php';
logout();
header('Location: /admin/login.php');
exit;
