<?php
declare(strict_types=1);
/** Billplz callback_url (server-to-server POST). Verifies X-Signature, then re-checks the bill via API. */
require dirname(__DIR__) . '/lib/bootstrap.php';
require dirname(__DIR__) . '/lib/billplz.php';

header('Content-Type: text/plain');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || empty($_POST['id'])) {
    http_response_code(400);
    exit('bad request');
}
$data = $_POST;
$sig = (string) ($data['x_signature'] ?? '');
unset($data['x_signature']);
$data = array_map(fn($v) => is_array($v) ? '' : (string) $v, $data);

if (!billplz_verify_signature($data, $sig)) {
    error_log('billplz callback: bad signature ' . json_encode($data));
    http_response_code(403);
    exit('bad signature');
}
try {
    sync_bill((string) $data['id']);
} catch (Throwable $e) {
    error_log('billplz callback: ' . $e->getMessage());
    http_response_code(500);
    exit('error');
}
echo 'ok';
