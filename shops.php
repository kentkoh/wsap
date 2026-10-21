<?php
declare(strict_types=1);
require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/layout.php';
require __DIR__ . '/lib/app_nav.php';

$u = require_login();
page_start(['title' => t('nav_tab_shops'), 'noindex' => true, 'body_class' => 'page-app']);
?>
<section class="app wrap">
  <?= app_nav('shops') ?>
  <div class="app-head"><h1><?= e(t('nav_tab_shops')) ?></h1></div>
  <div class="empty"><h2><?= e(t('pr_soon')) ?></h2><p class="muted"><?= e(t('sh_soon')) ?></p></div>
</section>
<?php page_end();
