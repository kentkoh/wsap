<?php
declare(strict_types=1);
require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/mail.php';

// POST = resend; GET ?t= = confirm
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
    $u = require_login();
    if (empty($u['verified_at']) && rate_limit('verify_resend', 3, 900)) {
        send_verification_email($u);
        flash('ok', t('auth_verify_sent'));
    }
    redirect('/dashboard');
}

$uid = consume_token((string) ($_GET['t'] ?? ''), 'verify');
if ($uid) {
    db_exec('UPDATE users SET verified_at = ? WHERE id = ?', [now(), $uid]);
    flash('ok', t('auth_verify_done'));
    $u = current_user();
    redirect($u ? '/dashboard' : '/login');
}
flash('error', t('auth_verify_bad'));
redirect('/dashboard');
