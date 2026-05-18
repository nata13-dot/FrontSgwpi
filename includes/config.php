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

// Iniciar sesión
session_start();

// Token de autenticación
$auth_token = $_SESSION['auth_token'] ?? null;

// Usuario autenticado
$current_user = $_SESSION['user'] ?? null;

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
