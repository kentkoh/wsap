<?php
declare(strict_types=1);
require __DIR__ . '/lib/bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
}
logout_user();
session_start_safe();
flash('ok', t('auth_logged_out'));
redirect('/');
