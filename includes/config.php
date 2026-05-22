<?php
// Configuración global del proyecto
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

define('APP_NAME', 'Sistema de Gestión de Proyectos Integradores');
$configuredApiUrl = getenv('API_BASE_URL') ?: 'https://apiswgpi-production-0e59.up.railway.app/api';
$configuredApiUrl = rtrim($configuredApiUrl, '/');
define('API_BASE_URL', $configuredApiUrl);
define('API_ORIGIN_URL', preg_replace('#/api$#', '', API_BASE_URL));
define('FRONTEND_URL', getenv('FRONTEND_URL') ?: '');
define('SGPI_AUTH_TOKEN_COOKIE', 'sgpi_auth_token');
define('SGPI_AUTH_USER_COOKIE', 'sgpi_auth_user');
define('SGPI_AUTH_SIGNATURE_COOKIE', 'sgpi_auth_signature');
define('SGPI_AUTH_COOKIE_TTL', 60 * 60 * 24 * 30);
define('SGPI_AUTH_COOKIE_SECRET', getenv('SGPI_AUTH_COOKIE_SECRET') ?: getenv('APP_KEY') ?: hash('sha256', __DIR__ . '|' . API_BASE_URL));

// Iniciar sesión
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax'
]);
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function sgpi_base64url_encode($value) {
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
}

function sgpi_base64url_decode($value) {
    $decoded = base64_decode(strtr($value, '-_', '+/'), true);
    return $decoded === false ? null : $decoded;
}

function sgpi_auth_signature($token, $encodedUser) {
    return hash_hmac('sha256', $token . '|' . $encodedUser, SGPI_AUTH_COOKIE_SECRET);
}

function sgpi_cookie_options($expires = 0) {
    return [
        'expires' => $expires,
        'path' => '/',
        'domain' => '',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax'
    ];
}

function persist_auth_session($token, $user, $remember = true) {
    if (!$token || !is_array($user)) return false;

    $encodedUser = sgpi_base64url_encode(json_encode($user, JSON_UNESCAPED_UNICODE));
    $signature = sgpi_auth_signature($token, $encodedUser);
    $expires = $remember ? time() + SGPI_AUTH_COOKIE_TTL : 0;

    $_SESSION['auth_token'] = $token;
    $_SESSION['user'] = $user;

    setcookie(SGPI_AUTH_TOKEN_COOKIE, $token, sgpi_cookie_options($expires));
    setcookie(SGPI_AUTH_USER_COOKIE, $encodedUser, sgpi_cookie_options($expires));
    setcookie(SGPI_AUTH_SIGNATURE_COOKIE, $signature, sgpi_cookie_options($expires));
    $_COOKIE[SGPI_AUTH_TOKEN_COOKIE] = $token;
    $_COOKIE[SGPI_AUTH_USER_COOKIE] = $encodedUser;
    $_COOKIE[SGPI_AUTH_SIGNATURE_COOKIE] = $signature;

    return true;
}

function clear_auth_session_cookies() {
    foreach ([SGPI_AUTH_TOKEN_COOKIE, SGPI_AUTH_USER_COOKIE, SGPI_AUTH_SIGNATURE_COOKIE] as $cookie) {
        setcookie($cookie, '', sgpi_cookie_options(time() - 42000));
        unset($_COOKIE[$cookie]);
    }
}

function restore_auth_session_from_cookies() {
    $token = $_COOKIE[SGPI_AUTH_TOKEN_COOKIE] ?? null;
    $encodedUser = $_COOKIE[SGPI_AUTH_USER_COOKIE] ?? null;
    $signature = $_COOKIE[SGPI_AUTH_SIGNATURE_COOKIE] ?? null;

    if (!$token || !$encodedUser || !$signature) return false;
    if (!hash_equals(sgpi_auth_signature($token, $encodedUser), $signature)) {
        clear_auth_session_cookies();
        return false;
    }

    $decodedUser = sgpi_base64url_decode($encodedUser);
    $user = json_decode($decodedUser ?? '', true);
    if (!is_array($user)) {
        clear_auth_session_cookies();
        return false;
    }

    $_SESSION['auth_token'] = $token;
    $_SESSION['user'] = $user;
    return true;
}

restore_auth_session_from_cookies();

if (!empty($_SESSION['auth_token']) && !empty($_SESSION['user']) && empty($_COOKIE[SGPI_AUTH_TOKEN_COOKIE])) {
    persist_auth_session($_SESSION['auth_token'], $_SESSION['user']);
}

// Token de autenticación
$auth_token = $_SESSION['auth_token'] ?? null;

// Usuario autenticado
$current_user = $_SESSION['user'] ?? null;

$sgpiSessionWriteRequest = str_ends_with($_SERVER['SCRIPT_NAME'] ?? '', '/api/set-session.php')
    || str_ends_with($_SERVER['SCRIPT_NAME'] ?? '', '/pages/logout.php');
if (!$sgpiSessionWriteRequest && session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

// Helper function para verificar autenticación
function is_authenticated() {
    return isset($_SESSION['auth_token']);
}

// Helper para obtener rol
function get_user_role() {
    if (!is_authenticated()) return null;
    return $_SESSION['user']['perfil_id'] ?? null;
}

// Helper para verificar rol específico
function is_admin() {
    return get_user_role() == 1;
}

function is_teacher() {
    return get_user_role() == 2;
}

function is_student() {
    return get_user_role() == 3;
}

function is_evaluation_manager() {
    return is_admin() || !empty($_SESSION['user']['is_evaluation_manager']);
}
function dashboard_url() {
    if (is_admin()) return '/pages/admin/dashboard.php';
    if (is_teacher()) return '/pages/teacher/dashboard.php';
    if (is_student()) return '/pages/student/dashboard.php';
    return '/index.php';
}

function profile_photo_url($user = null) {
    $user = $user ?? ($_SESSION['user'] ?? null);
    $path = $user['photo_path'] ?? null;
    return $path ? API_ORIGIN_URL . '/storage/' . ltrim($path, '/') : '/assets/img/ITSSMT/ISC.png';
}

/**
 * Helper para proteger rutas
 */
function requireAuth($minRole = null) {
    if (!is_authenticated()) {
        header('Location: /index.php');
        exit;
    }

    if ($minRole !== null && get_user_role() > $minRole) {
        header('Location: /index.php');
        exit;
    }
}

/**
 * Alias para admin
 */
function requireAdmin() {
    requireAuth(1);
}

/**
 * Alias para teacher
 */
function requireTeacher() {
    requireAuth(2);
}

/**
 * Alias para guest
 */
function requireGuest() {
    if (is_authenticated()) {
        header('Location: ' . dashboard_url());
        exit;
    }
}
?>
