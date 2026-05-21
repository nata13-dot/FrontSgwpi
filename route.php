<?php

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$requestedFile = __DIR__ . $requestPath;

if (PHP_SAPI === 'cli-server' && is_file($requestedFile)) {
    return false;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

$path = $requestPath;
$legacyPath = legacy_url_for($path);

if ($legacyPath) {
    $target = $_SERVER['DOCUMENT_ROOT'] . $legacyPath;
    if (is_file($target)) {
        $_SERVER['PHP_SELF'] = $legacyPath;
        $_SERVER['SCRIPT_NAME'] = $legacyPath;
        require $target;
        exit;
    }
}

http_response_code(404);
require $_SERVER['DOCUMENT_ROOT'] . '/index.php';
