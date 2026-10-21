<?php
declare(strict_types=1);
/** /customers?shop=ID — customers derived from orders, with order history. */
require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/layout.php';
require __DIR__ . '/lib/shops.php';
require __DIR__ . '/lib/shop_nav.php';
require __DIR__ . '/lib/app_nav.php';

$u = require_login();
$shop = require_shop($u);
$sid = (int) $shop['id'];
$cur = $shop['currency'];
$q = trim((string) ($_GET['q'] ?? ''));
$customers = shop_customers($sid, $q);
$view = (string) ($_GET['c'] ?? '');

if (isset($_GET['csv'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="wsap-customers-' . $shop['slug'] . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['name', 'phone', 'orders', 'spent', 'last_order_utc', 'city', 'address']);
    foreach ($customers as $c) {
        fputcsv($out, [$c['name'], $c['phone'], $c['orders'], $c['spent'] / 100, $c['last'], $c['city'], $c['address']]);
    }
    exit;
}

page_start(['title' => t('sh_nav_customers') . ' · ' . $shop['name'], 'noindex' => true, 'body_class' => 'page-app']);
?>
<section class="app wrap">
  <?= app_nav('shops') ?>
  <?= shop_nav($shop, 'customers') ?>
  <div class="app-head"><h1><?= e(t('sh_nav_customers')) ?> <span class="muted" style="font-size:.6em">· <?= count($customers) ?></span></h1><a class="btn btn-sm btn-ghost" href="?shop=<?= $sid ?>&csv=1">⤓ <?= e(t('an_csv')) ?></a></div>
  <form method="get" style="display:flex;gap:8px;margin-bottom:14px"><input type="hidden" name="shop" value="<?= $sid ?>"><input class="input" name="q" value="<?= e($q) ?>" placeholder="<?= e(t('sh_cu_search')) ?>" style="max-width:320px"><button class="btn btn-ghost"><?= e(t('fm_search_btn')) ?></button></form>
  <?php if (!$customers): ?><div class="empty"><h2><?= e(t('sh_cu_empty')) ?></h2></div><?php else: ?>
  <div class="table-wrap"><table class="table">
    <tr><th><?= e(t('sh_f_name')) ?></th><th><?= e(t('sh_f_phone')) ?></th><th><?= e(t('sh_orders')) ?></th><th><?= e(t('sh_cu_spent')) ?></th><th><?= e(t('sh_cu_last')) ?></th><th><?= e(t('sh_f_city')) ?></th><th></th></tr>
    <?php foreach ($customers as $key => $c): ?>
    <tr><td><b><?= e($c['name']) ?></b></td><td class="mono"><?= $c['phone'] !== '' ? '+' . e($c['phone']) : '—' ?></td><td><a href="/orders?shop=<?= $sid ?>&q=<?= urlencode($c['phone'] !== '' ? $c['phone'] : $c['name']) ?>"><?= $c['orders'] ?></a></td>
      <td><b><?= e(money($c['spent'], $cur)) ?></b></td><td class="small"><time data-utc="<?= e($c['last']) ?>"><?= e(substr($c['last'], 0, 10)) ?></time></td><td class="small"><?= e($c['city']) ?></td>
      <td><?php if ($c['phone'] !== ''): ?><a class="btn btn-sm btn-ghost" href="https://wa.me/<?= e($c['phone']) ?>" target="_blank" rel="noopener">💬 Chat</a><?php endif; ?></td></tr>
    <?php endforeach; ?>
  </table></div>
  <?php endif; ?>
</section>
<?php page_end();
