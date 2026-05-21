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

$routeAliases = [
    '/index.php' => '/',
    '/pages/logout.php' => '/salir',
    '/pages/profile.php' => '/perfil',
    '/pages/repositorio.php' => '/repositorio',
    '/pages/repositorio-detail.php' => '/repositorio/documento',
    '/pages/evaluation-documents.php' => '/documentos-evaluacion',
    '/pages/admin/dashboard.php' => '/admin',
    '/pages/admin/users.php' => '/admin/usuarios',
    '/pages/admin/advisors.php' => '/admin/asesores',
    '/pages/admin/projects.php' => '/admin/proyectos',
    '/pages/admin/project-create.php' => '/admin/proyectos/nuevo',
    '/pages/admin/project-edit.php' => '/admin/proyectos/editar',
    '/pages/admin/proposal-config.php' => '/admin/propuestas',
    '/pages/admin/deliverables.php' => '/admin/entregables',
    '/pages/admin/evaluations.php' => '/admin/evaluaciones',
    '/pages/admin/asignaturas.php' => '/admin/asignaturas',
    '/pages/admin/competencias.php' => '/admin/competencias',
    '/pages/admin/document-tags.php' => '/admin/etiquetas',
    '/pages/admin/notices.php' => '/admin/avisos',
    '/pages/admin/settings.php' => '/admin/ajustes',
    '/pages/admin/user-create.php' => '/admin/usuarios/nuevo',
    '/pages/admin/user-edit.php' => '/admin/usuarios/editar',
    '/pages/teacher/dashboard.php' => '/docente',
    '/pages/teacher/my-projects.php' => '/docente/proyectos',
    '/pages/teacher/proposal-review.php' => '/docente/propuestas',
    '/pages/teacher/my-deliverables.php' => '/docente/entregables',
    '/pages/student/dashboard.php' => '/estudiante',
    '/pages/student/proposal-register.php' => '/estudiante/proyecto',
    '/pages/student/my-deliverables.php' => '/estudiante/entregables',
];

function route_aliases(): array {
    global $routeAliases;
    return $routeAliases;
}

function clean_url(string $path): string {
    $aliases = route_aliases();
    $parts = parse_url($path);
    $pathname = $parts['path'] ?? $path;
    $clean = $aliases[$pathname] ?? $path;

    if (isset($parts['query']) && $parts['query'] !== '') {
        $clean .= '?' . $parts['query'];
    }

    if (isset($parts['fragment']) && $parts['fragment'] !== '') {
        $clean .= '#' . $parts['fragment'];
    }

    return $clean;
}

function legacy_url_for(string $cleanPath): ?string {
    $aliases = array_flip(route_aliases());
    return $aliases[$cleanPath] ?? null;
}

function rewrite_legacy_urls(string $content): string {
    foreach (route_aliases() as $legacy => $clean) {
        $content = str_replace($legacy, $clean, $content);
    }

    return $content;
}

function redirect_legacy_request(): void {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $path = parse_url($uri, PHP_URL_PATH) ?: '';
    $aliases = route_aliases();

    if (!isset($aliases[$path])) {
        return;
    }

    $target = $aliases[$path];
    $query = parse_url($uri, PHP_URL_QUERY);
    if ($query) {
        $target .= '?' . $query;
    }

    header('Location: ' . $target, true, 302);
    exit;
}

function redirect_to(string $path, int $status = 302): void {
    header('Location: ' . clean_url($path), true, $status);
    exit;
}

redirect_legacy_request();

if (!defined('SGPI_URL_REWRITE_BUFFER')) {
    define('SGPI_URL_REWRITE_BUFFER', true);
    ob_start('rewrite_legacy_urls');
}

function configure_frontend_session(): void {
    $sessionDir = $_SERVER['DOCUMENT_ROOT'] . '/storage/sessions';

    if (!is_dir($sessionDir)) {
        @mkdir($sessionDir, 0775, true);
    }

    if (is_dir($sessionDir) && is_writable($sessionDir)) {
        session_save_path($sessionDir);
    }

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    session_name('SGPI_FRONTEND_SESSION');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    configure_frontend_session();
    session_start();
}

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
    if (is_admin()) return '/admin';
    if (is_teacher()) return '/docente';
    if (is_student()) return '/estudiante';
    return '/';
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
        redirect_to('/');
    }

    if ($minRole !== null && get_user_role() > $minRole) {
        redirect_to('/');
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
        redirect_to(dashboard_url());
    }
}
?>
