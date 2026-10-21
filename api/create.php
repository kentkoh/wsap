<?php
declare(strict_types=1);
/**
 * POST /api/create.php — create a free link from the homepage generator (no account needed).
 * Fields: cc, phone, message, domain. Returns {ok, link:{code, url, display, wa_url, phone, message}}.
 */
require dirname(__DIR__) . '/lib/bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    json_out(['ok' => false, 'error' => 'POST only'], 405);
}
session_start_safe();
$sent = $_SERVER['HTTP_X_CSRF'] ?? ($_POST['_csrf'] ?? '');
if (!is_string($sent) || !hash_equals($_SESSION['csrf'] ?? '', $sent)) {
    json_out(['ok' => false, 'error' => t('err_generic')], 419);
}
if (!rate_limit('create', 20, 3600)) {
    json_out(['ok' => false, 'error' => t('err_rate')], 429);
}

$phone = normalize_phone((string) ($_POST['cc'] ?? ''), (string) ($_POST['phone'] ?? ''));
$message = (string) ($_POST['message'] ?? '');
$user = current_user();

try {
    $opts = ['domain' => (string) ($_POST['domain'] ?? current_domain())];
    if ($user) {
        if (!can_add_link($user)) {
            json_out(['ok' => false, 'error' => t('err_limit', ['n' => plan_max_links(user_plan($user))])], 403);
        }
        $opts['user_id'] = (int) $user['id'];
    } else {
        $opts['session_key'] = anon_session_key();
    }
    $link = create_link($phone, $message, $opts);
} catch (InvalidArgumentException $e) {
    json_out(['ok' => false, 'error' => $e->getMessage()], 422);
} catch (Throwable $e) {
    error_log('create link: ' . $e->getMessage());
    json_out(['ok' => false, 'error' => t('err_generic')], 500);
}

json_out(['ok' => true, 'link' => [
    'code'    => $link['code'],
    'url'     => short_url($link),
    'display' => short_display($link),
    'wa_url'  => wa_url($link),
    'phone'   => $link['phone'],
    'message' => $link['message'],
]]);
