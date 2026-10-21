<?php
declare(strict_types=1);

/**
 * Minimal HTTP client: uses curl when the extension exists, otherwise PHP streams
 * (allow_url_fopen). Returns ['status' => int, 'body' => string, 'error' => string].
 *
 * $opts: method (GET|POST), headers (list of "Name: value"), body (string),
 *        basic_auth ("user:pass"), timeout (seconds)
 */
function http_request(string $url, array $opts = []): array
{
    $method = strtoupper($opts['method'] ?? 'GET');
    $headers = $opts['headers'] ?? [];
    $body = (string) ($opts['body'] ?? '');
    $timeout = (int) ($opts['timeout'] ?? 15);
    if (!empty($opts['basic_auth'])) {
        $headers[] = 'Authorization: Basic ' . base64_encode($opts['basic_auth']);
    }

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        $c = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_FOLLOWLOCATION => false,
        ];
        if ($method === 'POST') {
            $c[CURLOPT_POST] = true;
            $c[CURLOPT_POSTFIELDS] = $body;
        }
        curl_setopt_array($ch, $c);
        $out = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $err = curl_error($ch);
        curl_close($ch);
        return ['status' => $status, 'body' => $out === false ? '' : (string) $out, 'error' => $err];
    }

    $ctx = stream_context_create(['http' => [
        'method'        => $method,
        'header'        => implode("\r\n", $headers),
        'content'       => $body,
        'timeout'       => $timeout,
        'ignore_errors' => true,
        'follow_location' => 0,
    ], 'ssl' => ['verify_peer' => true, 'verify_peer_name' => true]]);
    $out = @file_get_contents($url, false, $ctx);
    $status = 0;
    foreach ($http_response_header ?? [] as $h) {
        if (preg_match('#^HTTP/\S+\s+(\d{3})#', $h, $m)) {
            $status = (int) $m[1];
        }
    }
    $err = $out === false ? (error_get_last()['message'] ?? 'request failed') : '';
    return ['status' => $status, 'body' => $out === false ? '' : $out, 'error' => $err];
}

function http_post_form(string $url, array $fields, array $opts = []): array
{
    $opts['method'] = 'POST';
    $opts['body'] = http_build_query($fields);
    $opts['headers'] = array_merge($opts['headers'] ?? [], ['Content-Type: application/x-www-form-urlencoded']);
    return http_request($url, $opts);
}

function http_post_json(string $url, array $data, array $opts = []): array
{
    $opts['method'] = 'POST';
    $opts['body'] = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $opts['headers'] = array_merge($opts['headers'] ?? [], ['Content-Type: application/json', 'Accept: application/json']);
    return http_request($url, $opts);
}
