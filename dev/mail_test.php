<?php
declare(strict_types=1);
/**
 * Send a test email through the configured Brevo key (CLI only).
 *   php dev/mail_test.php you@example.com
 */
// Refuse real web requests; allow cli and cPanel's php-cgi command line.
if (!empty($_SERVER['REQUEST_METHOD']) || !empty($_SERVER['HTTP_HOST'])) {
    http_response_code(404);
    exit;
}
$argv = $argv ?? ($_SERVER['argv'] ?? []);
echo 'PHP ' . PHP_VERSION . ' (' . PHP_SAPI . ")\n";
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
set_exception_handler(function (Throwable $e): void {
    echo 'ERROR: ' . get_class($e) . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() . "\n";
    exit(1);
});
register_shutdown_function(function (): void {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        echo 'FATAL: ' . $e['message'] . ' in ' . $e['file'] . ':' . $e['line'] . "\n";
    }
});
require __DIR__ . '/../lib/bootstrap.php';
require __DIR__ . '/../lib/mail.php';
echo "Libraries loaded.\n";

$to = (string) ($argv[1] ?? '');
if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
    exit("usage: php dev/mail_test.php you@example.com\n");
}
$key = (string) cfg('brevo.api_key');
echo 'Brevo key: ' . ($key === '' ? "MISSING (set brevo.api_key in config.local.php)\n" : substr($key, 0, 12) . "…\n");
echo 'From: ' . cfg('brevo.from_name') . ' <' . cfg('brevo.from_email') . ">\n";
$ok = send_mail($to, 'Test', cfg('site_name') . ' test email', mail_template('It works', '<p>Brevo is connected to ' . e((string) cfg('site_name')) . '. Sent ' . gmdate('Y-m-d H:i') . ' UTC.</p>', base_url(), 'Open ' . cfg('site_name')));
echo $ok ? "Sent to $to. Check the inbox (and spam).\n" : "FAILED. See error_log in this folder for the Brevo response.\n";
