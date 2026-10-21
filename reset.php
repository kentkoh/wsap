<?php
declare(strict_types=1);
require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/layout.php';
require __DIR__ . '/lib/auth_ui.php';

$token = (string) ($_REQUEST['t'] ?? '');
$err = null;

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
    $pw = (string) ($_POST['password'] ?? '');
    if (strlen($pw) < 8) {
        $err = t('err_password_short');
    } else {
        $uid = consume_token($token, 'reset');
        if (!$uid) {
            $err = t('auth_reset_bad');
        } else {
            db_exec('UPDATE users SET password_hash = ?, verified_at = COALESCE(verified_at, ?) WHERE id = ?', [password_hash($pw, PASSWORD_DEFAULT), now(), $uid]);
            flash('ok', t('auth_reset_done'));
            redirect('/login');
        }
    }
}

page_start(['title' => t('auth_reset_title'), 'noindex' => true, 'body_class' => 'page-auth']);
auth_card_start(t('auth_reset_title'), '');
form_error($err);
?>
<form method="post" novalidate>
  <?= csrf_field() ?>
  <input type="hidden" name="t" value="<?= e($token) ?>">
  <div class="field"><label for="password"><?= e(t('auth_password')) ?></label><input class="input" id="password" name="password" type="password" autocomplete="new-password" minlength="8" placeholder="<?= e(t('auth_password_min')) ?>" required autofocus></div>
  <button class="btn btn-primary btn-lg btn-block" type="submit"><?= e(t('auth_reset_btn')) ?></button>
</form>
<?php
auth_card_end();
page_end();
