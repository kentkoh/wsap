<?php
declare(strict_types=1);
/** /orders?shop=ID — orders list, status changes, 30-day metrics, CSV. */
require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/layout.php';
require __DIR__ . '/lib/shops.php';
require __DIR__ . '/lib/app_nav.php';

$u = require_login();
$sid = (int) ($_GET['shop'] ?? ($_POST['shop'] ?? 0));
$shop = db_row('SELECT * FROM shops WHERE id = ? AND user_id = ?', [$sid, (int) $u['id']]);
if (!$shop) {
    http_response_code(404);
    exit('Not found');
}
$cur = $shop['currency'];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
    $st = (string) ($_POST['status'] ?? '');
    if (in_array($st, ORDER_STATUSES, true)) {
        db_exec('UPDATE orders SET status = ? WHERE id = ? AND shop_id = ?', [$st, (int) $_POST['oid'], $sid]);
    }
    redirect('/orders?shop=' . $sid . (isset($_POST['q']) ? '&q=' . urlencode((string) $_POST['q']) : ''));
}

$q = trim((string) ($_GET['q'] ?? ''));
$rows = db_rows('SELECT * FROM orders WHERE shop_id = ?' . ($q !== '' ? ' AND (number LIKE ? OR customer LIKE ? OR items LIKE ?)' : '') . ' ORDER BY id DESC LIMIT 300', $q !== '' ? [$sid, "%$q%", "%$q%", "%$q%"] : [$sid]);

if (isset($_GET['csv'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="wsap-orders-' . $shop['slug'] . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['number', 'time_utc', 'status', 'name', 'phone', 'address', 'city', 'notes', 'items', 'subtotal', 'shipping', 'discount', 'total']);
    foreach ($rows as $r) {
        $c = json_decode((string) $r['customer'], true) ?: [];
        $items = implode(' | ', array_map(fn($i) => $i['qty'] . 'x ' . $i['name'] . ($i['options'] ? " ({$i['options']})" : ''), json_decode((string) $r['items'], true) ?: []));
        fputcsv($out, [$r['number'], $r['created_at'], $r['status'], $c['name'] ?? '', $c['phone'] ?? '', $c['address'] ?? '', $c['city'] ?? '', $c['notes'] ?? '', $items, $r['subtotal'] / 100, $r['shipping'] / 100, $r['discount'] / 100, $r['total'] / 100]);
    }
    exit;
}

$since = now(-30 * 86400);
$m = db_row('SELECT COUNT(*) AS n, COALESCE(SUM(total),0) AS v FROM orders WHERE shop_id = ? AND status <> ? AND created_at >= ?', [$sid, 'cancelled', $since]);
$customers = (int) db_value('SELECT COUNT(DISTINCT customer) FROM orders WHERE shop_id = ? AND created_at >= ?', [$sid, $since]);
$aov = (int) $m['n'] > 0 ? (int) round((int) $m['v'] / (int) $m['n']) : 0;

page_start(['title' => t('sh_orders') . ' · ' . $shop['name'], 'noindex' => true, 'body_class' => 'page-app']);
?>
<section class="app wrap">
  <?= app_nav('shops') ?>
  <p><a href="/shops">← <?= e(t('nav_tab_shops')) ?></a> · <a href="/products?shop=<?= $sid ?>"><?= e(t('sh_products')) ?></a> · <a href="/shop?id=<?= $sid ?>"><?= e(t('sh_settings')) ?></a></p>
  <div class="app-head"><h1><?= e($shop['name']) ?> <span class="muted" style="font-size:.6em">· <?= e(t('sh_orders')) ?></span></h1><a class="btn btn-sm btn-ghost" href="?shop=<?= $sid ?>&csv=1">⤓ <?= e(t('an_csv')) ?></a></div>
  <div class="stat-grid">
    <div class="stat"><b><?= e(money((int) $m['v'], $cur)) ?></b><span><?= e(t('sh_m_value')) ?></span></div>
    <div class="stat"><b><?= (int) $m['n'] ?></b><span><?= e(t('sh_m_orders')) ?></span></div>
    <div class="stat"><b><?= e(money($aov, $cur)) ?></b><span><?= e(t('sh_m_aov')) ?></span></div>
    <div class="stat"><b><?= $customers ?></b><span><?= e(t('sh_m_customers')) ?></span></div>
  </div>
  <form method="get" style="display:flex;gap:8px;margin-bottom:14px"><input type="hidden" name="shop" value="<?= $sid ?>"><input class="input" name="q" value="<?= e($q) ?>" placeholder="<?= e(t('sh_search')) ?>" style="max-width:320px"><button class="btn btn-ghost"><?= e(t('fm_search_btn')) ?></button></form>
  <?php if (!$rows): ?><div class="empty"><h2><?= e(t('sh_no_orders')) ?></h2></div><?php else: ?>
  <div class="link-list">
    <?php foreach ($rows as $r): $c = json_decode((string) $r['customer'], true) ?: []; $items = json_decode((string) $r['items'], true) ?: []; ?>
    <article class="link-card">
      <div>
        <div class="code" style="font-size:16px">#<?= e($r['number']) ?> <span class="badge <?= $r['status'] === 'completed' ? 'badge-prem' : ($r['status'] === 'cancelled' ? 'badge-off' : '') ?>"><?= e(t('sh_st_' . $r['status'])) ?></span></div>
        <div class="sub"><b><?= e((string) ($c['name'] ?? '')) ?></b><?php if (!empty($c['phone'])): ?><a href="https://wa.me/<?= e(preg_replace('/\D/', '', (string) $c['phone'])) ?>" target="_blank" rel="noopener">+<?= e(preg_replace('/\D/', '', (string) $c['phone'])) ?></a><?php endif; ?><span><time data-utc="<?= e($r['created_at']) ?>"><?= e(substr($r['created_at'], 0, 16)) ?></time></span></div>
        <div class="sub" style="margin-top:6px"><?php foreach ($items as $it): ?><span><?= (int) $it['qty'] ?>× <?= e($it['name']) ?><?= $it['options'] ? ' (' . e($it['options']) . ')' : '' ?></span><?php endforeach; ?></div>
        <?php if (!empty($c['address']) || !empty($c['notes'])): ?><div class="sub small" style="margin-top:4px"><?= e(trim(($c['address'] ?? '') . ' ' . ($c['city'] ?? ''))) ?><?= !empty($c['notes']) ? ' · “' . e((string) $c['notes']) . '”' : '' ?></div><?php endif; ?>
      </div>
      <div class="stats"><b><?= e(money((int) $r['total'], $cur)) ?></b><span><?= $r['shipping_name'] !== '' ? e($r['shipping_name']) : '' ?><?= $r['discount'] > 0 ? ' · −' . e(money((int) $r['discount'], $cur)) : '' ?></span></div>
      <form method="post" class="actions"><?= csrf_field() ?><input type="hidden" name="shop" value="<?= $sid ?>"><input type="hidden" name="oid" value="<?= (int) $r['id'] ?>"><input type="hidden" name="q" value="<?= e($q) ?>">
        <?php foreach (ORDER_STATUSES as $st): if ($st === $r['status']) { continue; } ?><button class="btn btn-sm btn-ghost" name="status" value="<?= $st ?>">→ <?= e(t('sh_st_' . $st)) ?></button><?php endforeach; ?>
      </form>
    </article>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>
<?php page_end();
