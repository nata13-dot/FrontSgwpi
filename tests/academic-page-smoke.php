<?php
// Render locally with a synthetic session; no API requests or user credentials.
$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__);
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/pages/academic-workflows.php';
$_SERVER['SCRIPT_NAME'] = $_SERVER['REQUEST_URI'];
$_SERVER['HTTP_HOST'] = 'localhost';
require $_SERVER['DOCUMENT_ROOT'].'/includes/config.php';
$profile = (int) ($argv[1] ?? 3);
$auth_token = 'synthetic-smoke-token';
$current_user = ['id' => 'smoke', 'nombres' => 'Prueba', 'perfil_id' => $profile, 'active_profile_id' => $profile];
$_SESSION['auth_token'] = $auth_token;
$_SESSION['user'] = $current_user;
ob_start();
require $_SERVER['DOCUMENT_ROOT'].'/pages/academic-workflows.php';
$html = ob_get_clean();
if (!str_contains($html, 'id="academicConsole"') || !str_contains($html, 'mountAcademicConsole')) {
    fwrite(STDERR, 'Academic page did not render'.PHP_EOL); exit(1);
}
echo "PHP academic page profile {$profile}: OK".PHP_EOL;
