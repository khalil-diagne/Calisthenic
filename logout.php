<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

if (!is_logged_in()) {
    header('Location: index.php');
    exit;
}

logout_user();
header('Location: index.php?logged_out=1');
exit;

?>
