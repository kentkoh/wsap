<?php
declare(strict_types=1);
/** One-click opt-out from the monthly report (link in the email). The dashboard has the same toggle. */
require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/layout.php';

$uid = consume_token((string) ($_GET['t'] ?? ''), 'unsub');
if ($uid) {
    db_exec('UPDATE users SET notify_report = 0 WHERE id = ?', [$uid]);
}
page_start(['title' => t('unsub_title'), 'noindex' => true, 'body_class' => 'page-auth']);
?>
<section class="wrap" style="max-width:520px;padding:80px 16px;text-align:center">
  <h1 style="font-size:28px;margin-bottom:12px"><?= e($uid ? t('unsub_done_t') : t('unsub_bad_t')) ?></h1>
  <p class="muted"><?= e($uid ? t('unsub_done') : t('unsub_bad')) ?></p>
  <p style="margin-top:24px"><a class="btn btn-primary" href="/dashboard"><?= e(t('nav_dashboard')) ?> →</a></p>
</section>
<?php page_end();
