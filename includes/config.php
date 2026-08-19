<?php
// Configuración global del proyecto
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

define('APP_NAME', 'Sistema de Gestión de Proyectos Integradores');
$requestHost = strtolower(preg_replace('/:\d+$/', '', trim(
    explode(',', $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost')[0]
)));
$localHosts = ['localhost', '127.0.0.1', '::1', 'frontend_swgpi.test'];
$isLocalRequest = in_array($requestHost, $localHosts, true)
    || str_ends_with($requestHost, '.test');
// Conexion anterior:
// $productionApiUrl = 'https://apiswgpi-production-0e59.up.railway.app/api';
$localApiUrl = 'http://127.0.0.1:8000/api';
// Variable de entorno anterior, desactivada para usar siempre la API local:
// $configuredApiUrl = trim((string) getenv('API_BASE_URL'));
$configuredApiUrl = $localApiUrl;

$configuredApiUrl = rtrim($configuredApiUrl, '/');
define('API_BASE_URL', $configuredApiUrl);
define('API_ORIGIN_URL', preg_replace('#/api$#', '', API_BASE_URL));
define('FRONTEND_URL', getenv('FRONTEND_URL') ?: '');
define('SGPI_AUTH_TOKEN_COOKIE', 'sgpi_auth_token');
define('SGPI_AUTH_USER_COOKIE', 'sgpi_auth_user');
define('SGPI_AUTH_SIGNATURE_COOKIE', 'sgpi_auth_signature');
define('SGPI_AUTH_REMEMBER_COOKIE', 'sgpi_auth_remember');
define('SGPI_COOKIE_SUPPORT_COOKIE', 'sgpi_cookie_support');
define('SGPI_AUTH_COOKIE_TTL', 60 * 60 * 24 * 28);
define('SGPI_AUTH_COOKIE_SECRET', getenv('SGPI_AUTH_COOKIE_SECRET') ?: getenv('APP_KEY') ?: hash('sha256', __DIR__ . '|' . API_BASE_URL));

// Iniciar sesión
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => sgpi_request_is_secure(),
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

function sgpi_auth_signature_v2($token, $encodedUser, $remember) {
    return hash_hmac('sha256', $token . '|' . $encodedUser . '|' . ($remember ? '1' : '0'), SGPI_AUTH_COOKIE_SECRET);
}

function sgpi_request_is_secure() {
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') return true;
    $forwardedProto = strtolower(trim(explode(',', $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')[0] ?? ''));
    return $forwardedProto === 'https';
}

function sgpi_cookie_options($expires = 0) {
    return [
        'expires' => $expires,
        'path' => '/',
        'domain' => '',
        'secure' => sgpi_request_is_secure(),
        'httponly' => true,
        'samesite' => 'Lax'
    ];
}

function persist_auth_session($token, $user, $remember = true) {
    if (!$token || !is_array($user)) return false;

    $encodedUser = sgpi_base64url_encode(json_encode($user, JSON_UNESCAPED_UNICODE));
    $expires = $remember ? time() + SGPI_AUTH_COOKIE_TTL : 0;

    $_SESSION['auth_token'] = $token;
    $_SESSION['user'] = $user;
    $_SESSION['auth_remember'] = (bool) $remember;

    setcookie(SGPI_AUTH_TOKEN_COOKIE, $token, sgpi_cookie_options($expires));
    setcookie(SGPI_AUTH_USER_COOKIE, $encodedUser, sgpi_cookie_options($expires));
    setcookie(SGPI_AUTH_REMEMBER_COOKIE, $remember ? '1' : '0', sgpi_cookie_options($expires));
    setcookie(SGPI_AUTH_SIGNATURE_COOKIE, sgpi_auth_signature_v2($token, $encodedUser, $remember), sgpi_cookie_options($expires));
    setcookie(SGPI_COOKIE_SUPPORT_COOKIE, '1', array_merge(sgpi_cookie_options($expires), ['httponly' => false]));
    $_COOKIE[SGPI_AUTH_TOKEN_COOKIE] = $token;
    $_COOKIE[SGPI_AUTH_USER_COOKIE] = $encodedUser;
    $_COOKIE[SGPI_AUTH_REMEMBER_COOKIE] = $remember ? '1' : '0';
    $_COOKIE[SGPI_AUTH_SIGNATURE_COOKIE] = sgpi_auth_signature_v2($token, $encodedUser, $remember);
    $_COOKIE[SGPI_COOKIE_SUPPORT_COOKIE] = '1';

    return true;
}

function clear_auth_session_cookies() {
    foreach ([SGPI_AUTH_TOKEN_COOKIE, SGPI_AUTH_USER_COOKIE, SGPI_AUTH_REMEMBER_COOKIE, SGPI_AUTH_SIGNATURE_COOKIE, SGPI_COOKIE_SUPPORT_COOKIE] as $cookie) {
        $options = sgpi_cookie_options(time() - 42000);
        if ($cookie === SGPI_COOKIE_SUPPORT_COOKIE) $options['httponly'] = false;
        setcookie($cookie, '', $options);
        unset($_COOKIE[$cookie]);
    }
}

function restore_auth_session_from_cookies() {
    $token = $_COOKIE[SGPI_AUTH_TOKEN_COOKIE] ?? null;
    $encodedUser = $_COOKIE[SGPI_AUTH_USER_COOKIE] ?? null;
    $signature = $_COOKIE[SGPI_AUTH_SIGNATURE_COOKIE] ?? null;
    $remember = ($_COOKIE[SGPI_AUTH_REMEMBER_COOKIE] ?? '1') === '1';

    if (!$token || !$encodedUser || !$signature) return false;
    $validSignature = hash_equals(sgpi_auth_signature_v2($token, $encodedUser, $remember), $signature)
        || hash_equals(sgpi_auth_signature($token, $encodedUser), $signature);
    if (!$validSignature) {
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
    $_SESSION['auth_remember'] = $remember;
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
$auth_remember = !empty($_SESSION['auth_remember']);

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
    return $_SESSION['user']['active_profile_id'] ?? $_SESSION['user']['perfil_id'] ?? null;
}

// Helper para verificar rol específico
function is_admin() {
    return in_array((int) get_user_role(), [1, 5], true) || is_general_admin();
}

function is_general_admin() {
    return is_authenticated() && ($_SESSION['user']['perfil_id'] ?? null) == 4;
}

function is_teacher() {
    return get_user_role() == 2;
}

function is_student() {
    return get_user_role() == 3;
}

function is_career_head() {
    return get_user_role() == 5;
}

function is_career_head_assistant() {
    return get_user_role() == 6;
}

function is_project_coordinator() {
    return get_user_role() == 7;
}

function can_manage_academics() {
    return is_admin() || is_career_head_assistant();
}

function can_manage_projects() {
    return is_admin() || is_career_head_assistant() || is_project_coordinator();
}

function is_management_staff() {
    return can_manage_projects();
}

function can_govern_users() {
    return is_general_admin();
}

function profile_role_label() {
    return match ((int) get_user_role()) {
        1 => 'Administrador',
        2 => 'Docente',
        3 => 'Estudiante',
        4 => 'Administrador general',
        5 => 'Jefe de Carrera',
        6 => 'Asistente de Jefe de Carrera',
        7 => 'Coordinador de Proyectos',
        default => 'Usuario',
    };
}

function is_evaluation_manager() {
    return is_admin() || !empty($_SESSION['user']['is_evaluation_manager']);
}
function dashboard_url() {
    if (is_management_staff()) return '/pages/admin/dashboard.php';
    if (is_teacher()) return '/pages/teacher/dashboard.php';
    if (is_student()) return '/pages/student/dashboard.php';
    return '/index.php';
}

function active_career() {
    return $_SESSION['user']['active_career'] ?? null;
}

function active_career_id() {
    return active_career()['id'] ?? null;
}

function available_careers() {
    return $_SESSION['user']['careers'] ?? [];
}

function profile_photo_url($user = null) {
    $user = $user ?? ($_SESSION['user'] ?? null);
    $path = $user['photo_path'] ?? null;
    return $path ? API_ORIGIN_URL . '/storage/' . ltrim($path, '/') : '/assets/img/ITSSMT/ISC.webp';
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
    if (!is_authenticated() || !is_admin()) {
        header('Location: /index.php');
        exit;
    }
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
