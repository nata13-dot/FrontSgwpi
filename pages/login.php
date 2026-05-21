<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

if (is_authenticated()) {
    header('Location: ' . dashboard_url());
    exit;
}

header('Location: /index.php#login');
exit;
?>