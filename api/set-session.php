<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

header('Content-Type: application/json; charset=utf-8');

// Recibir token y usuario desde el login
$data = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['error' => 'JSON invalido']);
    exit;
}

if (is_array($data) && !empty($data['auth_token']) && isset($data['user']) && is_array($data['user'])) {
    $remember = array_key_exists('remember', $data) ? !empty($data['remember']) : true;
    persist_auth_session($data['auth_token'], $data['user'], $remember);

    echo json_encode(['success' => true]);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Datos invalidos']);
}
?>
