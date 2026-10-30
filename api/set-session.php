<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/session-helpers.php';

$data = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    sgpi_session_response(400, ['error' => 'JSON invalido']);
}

if (!is_array($data) || empty($data['auth_token']) || !is_string($data['auth_token'])) {
    sgpi_session_response(400, ['error' => 'Datos invalidos']);
}

$verified = sgpi_fetch_authenticated_user($data['auth_token']);
if ($verified['status'] === 401 || $verified['status'] === 403) {
    sgpi_session_response($verified['status'], ['error' => 'La sesión no es válida.']);
}
if ($verified['status'] !== 200 || !$verified['user']) {
    sgpi_session_response(503, ['error' => 'No fue posible verificar la sesión con el servidor.']);
}

$remember = array_key_exists('remember', $data) ? !empty($data['remember']) : true;
persist_auth_session($data['auth_token'], $verified['user'], $remember);

echo json_encode([
    'success' => true,
    'remember' => $remember,
    'cookies_enabled' => isset($_COOKIE[SGPI_COOKIE_SUPPORT_COOKIE]),
    'user' => $verified['user'],
    'dashboard_url' => dashboard_url(),
], JSON_UNESCAPED_UNICODE);
