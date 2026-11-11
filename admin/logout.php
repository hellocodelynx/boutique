<?php
define('VELORA_ACCESS', true);
require_once __DIR__ . '/includes/admin_auth.php';

logoutAdmin();
header('Location: login.php');
exit;

