<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

// Recibir token y usuario desde el login
$data = json_decode(file_get_contents('php://input'), true);

if ($data && isset($data['auth_token']) && isset($data['user'])) {
    $_SESSION['auth_token'] = $data['auth_token'];
    $_SESSION['user'] = $data['user'];
    
    echo json_encode(['success' => true]);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Datos inválidos']);
}
?>