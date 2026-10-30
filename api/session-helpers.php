<?php
// Funciones compartidas por los puentes de sesión (set-session.php y view-as.php).

function sgpi_session_response($status, array $payload) {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Consulta /auth/me con el token recibido. La sesión PHP sólo se guarda con
 * el usuario que devuelve la API, nunca con el enviado por el navegador.
 */
function sgpi_fetch_authenticated_user($token) {
    $url = API_BASE_URL . '/auth/me';
    $headers = [
        'Accept: application/json',
        'Authorization: Bearer ' . $token,
    ];

    if (function_exists('curl_init')) {
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 10,
        ]);
        $body = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);
    } else {
        $context = stream_context_create(['http' => [
            'method' => 'GET',
            'header' => implode("\r\n", $headers),
            'timeout' => 10,
            'ignore_errors' => true,
        ]]);
        $body = @file_get_contents($url, false, $context);
        $status = 0;
        foreach ($http_response_header ?? [] as $line) {
            if (preg_match('#^HTTP/\S+\s+(\d{3})#', $line, $match)) $status = (int) $match[1];
        }
    }

    if ($body === false || $status === 0) return ['status' => 503, 'user' => null];
    $decoded = json_decode((string) $body, true);
    $user = is_array($decoded) && is_array($decoded['user'] ?? null) ? $decoded['user'] : null;
    return ['status' => $status, 'user' => $user];
}

/** POST a la API con el token indicado; se usa para invalidar tokens al salir de "ver como". */
function sgpi_api_post($endpoint, $token) {
    $url = API_BASE_URL . $endpoint;
    $headers = ['Accept: application/json', 'Authorization: Bearer ' . $token];
    if (function_exists('curl_init')) {
        $curl = curl_init($url);
        curl_setopt_array($curl, [CURLOPT_POST => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_HTTPHEADER => $headers, CURLOPT_CONNECTTIMEOUT => 5, CURLOPT_TIMEOUT => 10]);
        curl_exec($curl);
        curl_close($curl);
        return;
    }
    @file_get_contents($url, false, stream_context_create(['http' => ['method' => 'POST', 'header' => implode("\r\n", $headers), 'timeout' => 10, 'ignore_errors' => true]]));
}

/** Petición JSON a la API con el token indicado. @return array{status: int, body: array} */
function sgpi_api_json($method, $endpoint, $token, ?array $body = null) {
    $url = API_BASE_URL . $endpoint;
    $headers = ['Accept: application/json', 'Content-Type: application/json', 'Authorization: Bearer ' . $token];
    $payload = $body === null ? null : json_encode($body, JSON_UNESCAPED_UNICODE);
    if (function_exists('curl_init')) {
        $curl = curl_init($url);
        curl_setopt_array($curl, [CURLOPT_CUSTOMREQUEST => $method, CURLOPT_RETURNTRANSFER => true, CURLOPT_HTTPHEADER => $headers, CURLOPT_CONNECTTIMEOUT => 5, CURLOPT_TIMEOUT => 15]);
        if ($payload !== null) curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        $raw = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);
    } else {
        $raw = @file_get_contents($url, false, stream_context_create(['http' => ['method' => $method, 'header' => implode("\r\n", $headers), 'content' => $payload ?? '', 'timeout' => 15, 'ignore_errors' => true]]));
        $status = 0;
        foreach ($http_response_header ?? [] as $line) {
            if (preg_match('#^HTTP/\S+\s+(\d{3})#', $line, $match)) $status = (int) $match[1];
        }
    }
    $decoded = json_decode((string) $raw, true);
    return ['status' => $raw === false ? 503 : $status, 'body' => is_array($decoded) ? $decoded : []];
}
