<?php
declare(strict_types=1);
/**
 * Send a test email through the configured Brevo key (CLI only).
 *   php dev/mail_test.php you@example.com
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require __DIR__ . '/../lib/bootstrap.php';
require __DIR__ . '/../lib/mail.php';

$to = (string) ($argv[1] ?? '');
if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
    exit("usage: php dev/mail_test.php you@example.com\n");
}
$key = (string) cfg('brevo.api_key');
echo 'Brevo key: ' . ($key === '' ? "MISSING (set brevo.api_key in config.local.php)\n" : substr($key, 0, 12) . "…\n");
echo 'From: ' . cfg('brevo.from_name') . ' <' . cfg('brevo.from_email') . ">\n";
$ok = send_mail($to, 'Test', cfg('site_name') . ' test email', mail_template('It works', '<p>Brevo is connected to ' . e((string) cfg('site_name')) . '. Sent ' . gmdate('Y-m-d H:i') . ' UTC.</p>', base_url(), 'Open ' . cfg('site_name')));
echo $ok ? "Sent to $to. Check the inbox (and spam).\n" : "FAILED. See error_log in this folder for the Brevo response.\n";
