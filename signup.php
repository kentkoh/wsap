<?php
declare(strict_types=1);
require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/layout.php';
require __DIR__ . '/lib/auth_ui.php';
require __DIR__ . '/lib/mail.php';

if (current_user()) {
    redirect('/dashboard');
}
$err = null;
$name = (string) ($_POST['name'] ?? '');
$email = (string) ($_POST['email'] ?? '');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
    if (!rate_limit('signup', 10, 3600)) {
        $err = t('auth_too_many');
    } else {
        try {
            $user = register_user($email, (string) ($_POST['password'] ?? ''), $name);
            login_user($user);
            try {
                send_verification_email($user); // a mail outage must never block signup
            } catch (Throwable $e) {
                error_log('verification mail: ' . $e->getMessage());
            }
            flash('ok', t('auth_welcome'));
            redirect(after_login_url());
        } catch (InvalidArgumentException $e) {
            $err = $e->getMessage();
        } catch (Throwable $e) {
            error_log('signup: ' . $e->getMessage());
            $err = t('err_generic');
        }
    }
}

page_start(['title' => t('auth_signup_title'), 'noindex' => true, 'body_class' => 'page-auth']);
auth_card_start(t('auth_signup_title'), t('auth_signup_lead'));
form_error($err);
echo google_button();
?>
<form method="post" novalidate>
  <?= csrf_field() ?>
  <div class="field"><label for="name"><?= e(t('auth_name')) ?></label><input class="input" id="name" name="name" value="<?= e($name) ?>" autocomplete="name" maxlength="80"></div>
  <div class="field"><label for="email"><?= e(t('auth_email')) ?></label><input class="input" id="email" name="email" type="email" value="<?= e($email) ?>" autocomplete="email" required></div>
  <div class="field"><label for="password"><?= e(t('auth_password')) ?></label><input class="input" id="password" name="password" type="password" autocomplete="new-password" minlength="8" placeholder="<?= e(t('auth_password_min')) ?>" required></div>
  <button class="btn btn-primary btn-lg btn-block" type="submit"><?= e(t('auth_signup_btn')) ?> →</button>
</form>
<p class="auth-terms"><?= e(t('auth_terms')) ?></p>
<p class="auth-foot"><?= e(t('auth_have')) ?> <a href="/login"><b><?= e(t('auth_login_btn')) ?></b></a></p>
<?php
auth_card_end();
page_end();
