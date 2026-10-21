<?php
declare(strict_types=1);
require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/layout.php';
require __DIR__ . '/lib/auth_ui.php';
require __DIR__ . '/lib/mail.php';

$sent = false;
$err = null;
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
    if (!rate_limit('forgot', 5, 900)) {
        $err = t('auth_too_many');
    } else {
        try {
            $u = find_user_by_email((string) ($_POST['email'] ?? ''));
            if ($u) {
                send_reset_email($u);
            }
        } catch (Throwable $e) {
            error_log('forgot: ' . $e->getMessage());
        }
        $sent = true; // always the same answer, to avoid leaking which emails exist
    }
}

page_start(['title' => t('auth_forgot_title'), 'noindex' => true, 'body_class' => 'page-auth']);
auth_card_start(t('auth_forgot_title'), t('auth_forgot_lead'));
form_error($err);
if ($sent) {
    echo '<div class="flash" role="status" style="margin:0">' . e(t('auth_forgot_sent')) . '</div>';
} else {
    ?>
<form method="post" novalidate>
  <?= csrf_field() ?>
  <div class="field"><label for="email"><?= e(t('auth_email')) ?></label><input class="input" id="email" name="email" type="email" autocomplete="email" required autofocus></div>
  <button class="btn btn-primary btn-lg btn-block" type="submit"><?= e(t('auth_forgot_btn')) ?></button>
</form>
<?php
}
echo '<p class="auth-foot"><a href="/login">← ' . e(t('auth_login_btn')) . '</a></p>';
auth_card_end();
page_end();
