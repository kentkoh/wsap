<?php
declare(strict_types=1);
require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/layout.php';
require __DIR__ . '/lib/auth_ui.php';

if (current_user()) {
    redirect('/dashboard');
}
$err = null;
$email = (string) ($_POST['email'] ?? '');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
    if (!rate_limit('login', 15, 900)) {
        $err = t('auth_too_many');
    } else {
        try {
            $u = find_user_by_email($email);
            if ($u && empty($u['password_hash']) && !empty($u['google_sub'])) {
                $err = t('auth_google_only');
            } else {
                $u = authenticate($email, (string) ($_POST['password'] ?? ''));
                if ($u) {
                    login_user($u);
                    redirect(after_login_url());
                }
                $err = t('auth_bad_login');
            }
        } catch (Throwable $e) {
            error_log('login: ' . $e->getMessage());
            $err = t('err_generic');
        }
    }
}

page_start(['title' => t('auth_login_title'), 'noindex' => true, 'body_class' => 'page-auth']);
auth_card_start(t('auth_login_title'), t('auth_login_lead'));
form_error($err);
if (isset($_GET['claim']) && !empty($_SESSION['anon_key'])) {
    echo '<div class="flash flash-ok" style="margin-bottom:14px">' . e(t('auth_claim_note')) . '</div>';
}
echo google_button();
?>
<form method="post" novalidate>
  <?= csrf_field() ?>
  <div class="field"><label for="email"><?= e(t('auth_email')) ?></label><input class="input" id="email" name="email" type="email" value="<?= e($email) ?>" autocomplete="email" required autofocus></div>
  <div class="field"><label for="password"><?= e(t('auth_password')) ?></label><input class="input" id="password" name="password" type="password" autocomplete="current-password" required>
    <p class="hint"><a href="/forgot"><?= e(t('auth_forgot')) ?></a></p></div>
  <button class="btn btn-primary btn-lg btn-block" type="submit"><?= e(t('auth_login_btn')) ?> →</button>
</form>
<p class="auth-foot"><?= e(t('auth_none')) ?> <a href="/signup"><b><?= e(t('nav_signup')) ?></b></a></p>
<?php
auth_card_end();
page_end();
