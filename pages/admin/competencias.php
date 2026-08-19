<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

if (!is_authenticated() || !can_manage_academics()) {
    header('Location: /index.php');
    exit;
}

header('Location: /pages/admin/asignaturas.php#competencias');
exit;
