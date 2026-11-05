<?php
// Router para el servidor integrado (composer start). El servidor integrado no
// lee .htaccess, así que aquí se replican las mismas restricciones.
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');

// Mismas direcciones limpias que deploy/nginx-clean-urls.conf.
$cleanFile = array_search(rtrim($path, '/'), require __DIR__.'/includes/clean-urls.php', true);
if ($cleanFile !== false) {
    $_SERVER['SCRIPT_NAME'] = $_SERVER['PHP_SELF'] = $cleanFile;
    $_SERVER['SCRIPT_FILENAME'] = __DIR__.$cleanFile;
    chdir(dirname(__DIR__.$cleanFile));
    require __DIR__.$cleanFile;
    return;
}

$blocked = [
    '#(^|/)\.#',                                  // .git, .htaccess, .vscode, .env
    '#^/(includes|tests|docs|vendor)(/|$)#',      // código interno, pruebas e informes
    '#^/(test-api|router)\.php$#',
    '#\.(sql|md|json|lock|log|key|crt|p12)$#i',
];
foreach ($blocked as $pattern) {
    if (preg_match($pattern, $path)) {
        http_response_code(404);
        exit;
    }
}

$file = __DIR__ . $path;
if ($path !== '/' && is_file($file)) {
    $types = [
        'css' => 'text/css; charset=utf-8',
        'js' => 'text/javascript; charset=utf-8',
        'webp' => 'image/webp',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'woff2' => 'font/woff2',
    ];
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if (!isset($types[$ext])) return false;

    // El servidor integrado no envía cabeceras de caché para estáticos; los
    // recursos con ?v= cambian de URL al modificarse y pueden cachearse un año.
    $etag = '"' . dechex(filemtime($file)) . '-' . dechex(filesize($file)) . '"';
    header('Content-Type: ' . $types[$ext]);
    header('Cache-Control: public, max-age=' . (isset($_GET['v']) ? '31536000, immutable' : '3600'));
    header('ETag: ' . $etag);
    if (trim($_SERVER['HTTP_IF_NONE_MATCH'] ?? '') === $etag) {
        http_response_code(304);
        exit;
    }
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit;
}

if (is_dir($file) && is_file(rtrim($file, '/') . '/index.php')) {
    return false;
}

if ($path === '/') {
    return false;
}

http_response_code(404);
require __DIR__ . '/index.php';
