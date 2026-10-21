<?php
declare(strict_types=1);
require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/layout.php';
require __DIR__ . '/lib/shops.php';
require __DIR__ . '/lib/app_nav.php';

$u = require_login();
$shops = user_shops((int) $u['id']);
$since = now(-30 * 86400);

page_start(['title' => t('nav_tab_shops'), 'noindex' => true, 'body_class' => 'page-app']);
?>
<section class="app wrap">
  <?= app_nav('shops') ?>
  <div class="app-head"><h1><?= e(t('nav_tab_shops')) ?></h1><a class="btn btn-primary" href="/shop">+ <?= e(t('sh_new')) ?></a></div>
  <p class="lead" style="font-size:15px;margin-bottom:18px"><?= e(t('sh_lead')) ?></p>
  <?php if (!$shops): ?>
    <div class="empty"><h2><?= e(t('sh_empty_t')) ?></h2><p class="muted"><?= e(t('sh_empty_d')) ?></p><a class="btn btn-wa btn-lg" href="/shop"><?= e(t('sh_new')) ?> →</a></div>
  <?php else: ?>
  <div class="link-list">
    <?php foreach ($shops as $s): $pub = shop_is_published($s, $u);
      $m = db_row('SELECT COUNT(*) AS n, COALESCE(SUM(total),0) AS v FROM orders WHERE shop_id = ? AND status <> ? AND created_at >= ?', [(int) $s['id'], 'cancelled', $since]);
      $np = (int) db_value('SELECT COUNT(*) FROM products WHERE shop_id = ?', [(int) $s['id']]); ?>
    <article class="link-card">
      <div>
        <div class="code"><a href="<?= e(shop_url($s)) ?>" target="_blank" rel="noopener"><?= e(shop_display($s)) ?></a>
          <?php if ($pub): ?><span class="badge badge-prem"><?= e(t('fm_published')) ?><?= !empty($s['premium_until']) ? ' · ' . e(substr((string) $s['premium_until'], 0, 10)) : '' ?></span><?php else: ?><span class="badge badge-off"><?= e(t('fm_draft')) ?></span><?php endif; ?>
          <?php if (!(int) $s['is_open']): ?><span class="badge badge-off"><?= e(t('sh_closed')) ?></span><?php endif; ?></div>
        <div class="sub"><b><?= e($s['name']) ?></b><span><?= $np ?> <?= e(t('sh_products')) ?></span><span><?= (int) $m['n'] ?> <?= e(t('sh_orders_30')) ?> · <?= e(money((int) $m['v'], $s['currency'])) ?></span></div>
      </div>
      <div class="stats"><b><?= number_format((int) $s['views']) ?></b><span><?= e(t('fm_views')) ?></span></div>
      <div class="actions">
        <button class="btn btn-sm btn-ghost" type="button" data-copy="<?= e(shop_url($s)) ?>" data-copied="<?= e(t('res_copied')) ?>"><?= e(t('res_copy')) ?></button>
        <a class="btn btn-sm btn-primary" href="/store?shop=<?= (int) $s['id'] ?>"><?= e(t('sh_nav_dashboard')) ?> →</a>
        <a class="btn btn-sm btn-ghost" href="/products?shop=<?= (int) $s['id'] ?>"><?= e(t('sh_products')) ?></a>
        <a class="btn btn-sm btn-ghost" href="/orders?shop=<?= (int) $s['id'] ?>"><?= e(t('sh_orders')) ?></a>
        <?php if (!$pub): ?><a class="btn btn-sm btn-ghost" href="/upgrade?shop=<?= (int) $s['id'] ?>">★ <?= e(t('fm_publish')) ?></a><?php else: ?><a class="btn btn-sm btn-ghost" href="/upgrade?shop=<?= (int) $s['id'] ?>"><?= e(t('dash_extend')) ?></a><?php endif; ?>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>
<?php page_end();
