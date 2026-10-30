<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once __DIR__ . '/session-helpers.php';

header('Content-Type: application/json; charset=utf-8');

/**
 * Simulación de niveles del administrador general (sin acceso a la cuenta de ninguna persona).
 * - start: abre una sesión de sólo lectura del propio administrador con los permisos del nivel elegido.
 *   Funciona desde su sesión o desde otra simulación: el bridge usa la sesión original guardada.
 * - exit: invalida el token de sólo lectura y restaura la sesión del administrador.
 * La sesión original viaja sólo en una cookie HttpOnly firmada; JavaScript no puede leerla.
 */
$data = json_decode(file_get_contents('php://input'), true);
$action = is_array($data) ? ($data['action'] ?? '') : '';

/** Token del administrador general: el de la sesión o, dentro de una vista, el guardado al entrar. */
function sgpi_general_admin_token() {
    if (view_as_session()) return read_view_as_origin()['token'] ?? null;
    return is_general_admin() ? ($_SESSION['auth_token'] ?? null) : null;
}

function sgpi_view_as_career_id(array $data) {
    return (int) ($data['carrera_id'] ?? active_career_id() ?? 0);
}

if ($action === 'start') {
    $adminToken = sgpi_general_admin_token() ?: sgpi_session_response(403, ['error' => 'Sólo el administrador general puede simular otro nivel.']);
    $admin = sgpi_fetch_authenticated_user($adminToken);
    if ($admin['status'] !== 200 || empty($admin['user']['is_general_admin'])) {
        sgpi_session_response(401, ['error' => 'La sesión del administrador expiró. Inicia sesión de nuevo.', 'redirect' => '/index.php']);
    }

    // El token lo emite la API para el propio administrador: simula el nivel, nunca entra a la cuenta de otra persona.
    $profileId = (int) ($data['perfil_id'] ?? 0);
    $issued = sgpi_api_json('POST', '/admin/view-level', $adminToken, ['carrera_id' => sgpi_view_as_career_id($data), 'perfil_id' => $profileId]);
    if ($issued['status'] !== 200) {
        sgpi_session_response($issued['status'] ?: 503, ['error' => $issued['body']['error'] ?? $issued['body']['message'] ?? 'No se pudo abrir la simulación.']);
    }
    $token = (string) ($issued['body']['access_token'] ?? '');
    $verified = $token !== '' ? sgpi_fetch_authenticated_user($token) : ['status' => 400, 'user' => null];
    $viewAs = $verified['user']['view_as'] ?? null;
    // Debe ser una simulación de sólo lectura, del mismo administrador y del nivel pedido.
    if ($verified['status'] !== 200 || !is_array($viewAs) || (string) ($viewAs['by_id'] ?? '') !== (string) ($admin['user']['id'] ?? '')
        || (string) ($verified['user']['id'] ?? '') !== (string) ($admin['user']['id'] ?? '') || (int) ($viewAs['simulated_profile'] ?? 0) !== $profileId) {
        sgpi_session_response(403, ['error' => 'No fue posible verificar la simulación.']);
    }

    if (view_as_session()) {
        // Brinco entre niveles: se invalida la simulación anterior y se conserva la sesión original.
        sgpi_api_post('/auth/logout', $_SESSION['auth_token']);
    } else {
        // Basta el token: al volver, el usuario se obtiene otra vez de /auth/me (y la cookie no rebasa 4 KB).
        save_view_as_origin([
            'token' => $_SESSION['auth_token'],
            'remember' => (bool) ($_SESSION['auth_remember'] ?? false),
        ]);
    }
    persist_auth_session($token, $verified['user'], false);
    echo json_encode(['success' => true, 'dashboard_url' => dashboard_url()], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'exit') {
    $origin = read_view_as_origin();
    $viewToken = view_as_session() ? ($_SESSION['auth_token'] ?? null) : null;
    clear_view_as_origin();
    if ($viewToken) sgpi_api_post('/auth/logout', $viewToken);
    if (!is_array($origin) || empty($origin['token'])) {
        clear_auth_session_cookies();
        session_destroy();
        sgpi_session_response(401, ['error' => 'La sesión del administrador ya no está disponible. Inicia sesión de nuevo.', 'redirect' => '/index.php']);
    }
    $verified = sgpi_fetch_authenticated_user($origin['token']);
    if ($verified['status'] !== 200 || !$verified['user']) {
        clear_auth_session_cookies();
        session_destroy();
        sgpi_session_response(401, ['error' => 'La sesión del administrador expiró. Inicia sesión de nuevo.', 'redirect' => '/index.php']);
    }
    persist_auth_session($origin['token'], $verified['user'], $origin['remember']);
    $target = is_string($data['return_to'] ?? null) && str_starts_with($data['return_to'], '/pages/') ? $data['return_to'] : '/pages/admin/general.php';
    echo json_encode(['success' => true, 'dashboard_url' => $target], JSON_UNESCAPED_UNICODE);
    exit;
}

sgpi_session_response(400, ['error' => 'Acción no válida.']);
