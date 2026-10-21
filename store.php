<?php
declare(strict_types=1);
/** /store?shop=ID — shop dashboard: 30-day metrics, recent orders, quick actions, share. */
require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/layout.php';
require __DIR__ . '/lib/shops.php';
require __DIR__ . '/lib/shop_nav.php';
require __DIR__ . '/lib/app_nav.php';

$u = require_login();
$shop = require_shop($u);
$sid = (int) $shop['id'];
$cur = $shop['currency'];
$m = shop_metrics($sid, 30);
$recent = db_rows('SELECT * FROM orders WHERE shop_id = ? ORDER BY id DESC LIMIT 6', [$sid]);
$nProducts = (int) db_value('SELECT COUNT(*) FROM products WHERE shop_id = ?', [$sid]);
$pub = shop_is_published($shop, $u);
$trend = fn(int $p) => '<span class="trend ' . ($p >= 0 ? 'up' : 'down') . '">' . ($p >= 0 ? '↗ +' : '↘ ') . $p . '%</span>';

page_start(['title' => $shop['name'], 'noindex' => true, 'body_class' => 'page-app']);
?>
<section class="app wrap">
  <?= app_nav('shops') ?>
  <?= shop_nav($shop, 'dashboard') ?>
  <?php if (!$pub): ?><div class="locked" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">🔒 <?= e(t('sh_dash_draft')) ?> <a class="btn btn-sm btn-primary" href="/upgrade?shop=<?= $sid ?>" style="margin-left:auto">★ <?= e(t('fm_publish')) ?></a></div><?php endif; ?>
  <div class="app-head"><div><h1><?= e(t('sh_dash_welcome', ['name' => $u['name'] !== '' ? explode(' ', (string) $u['name'])[0] : $shop['name']])) ?> 👋</h1><p class="muted" style="margin:4px 0 0"><?= e(t('sh_dash_sub')) ?></p></div>
    <a class="btn btn-primary" href="/products?shop=<?= $sid ?>&new=1">+ <?= e(t('sh_p_add')) ?></a></div>

  <div class="stat-grid">
    <div class="stat"><div class="stat-top"><span class="stat-ico">💰</span><?= $trend($m['value_pct']) ?></div><span><?= e(t('sh_m_value')) ?></span><b><?= e(money($m['value'], $cur)) ?></b></div>
    <div class="stat"><div class="stat-top"><span class="stat-ico">🛍️</span><?= $trend($m['orders_pct']) ?></div><span><?= e(t('sh_m_orders')) ?></span><b><?= $m['orders'] ?></b></div>
    <div class="stat"><div class="stat-top"><span class="stat-ico">🧾</span><?= $trend($m['aov_pct']) ?></div><span><?= e(t('sh_m_aov')) ?></span><b><?= e(money($m['aov'], $cur)) ?></b></div>
    <div class="stat"><div class="stat-top"><span class="stat-ico">👥</span><?= $trend($m['customers_pct']) ?></div><span><?= e(t('sh_m_customers')) ?></span><b><?= $m['customers'] ?></b></div>
  </div>

  <div class="dash-grid">
    <div class="card">
      <div style="display:flex;align-items:center;margin-bottom:12px"><h3 style="font-size:18px"><?= e(t('sh_dash_recent')) ?></h3><a href="/orders?shop=<?= $sid ?>" style="margin-left:auto;font-weight:700"><?= e(t('sh_dash_viewall')) ?> →</a></div>
      <?php if (!$recent): ?><p class="muted"><?= e(t('sh_no_orders')) ?></p><?php else: ?>
      <div class="table-wrap"><table class="table table-plain">
        <tr><th><?= e(t('dash_created')) ?></th><th><?= e(t('sh_f_name')) ?></th><th><?= e(t('sh_msg_total')) ?></th><th>Status</th><th></th></tr>
        <?php foreach ($recent as $r): $c = json_decode((string) $r['customer'], true) ?: []; $ph = preg_replace('/\D/', '', (string) ($c['phone'] ?? '')); ?>
        <tr><td class="small"><time data-utc="<?= e($r['created_at']) ?>"><?= e(substr($r['created_at'], 0, 10)) ?></time><br><span class="muted">#<?= e($r['number']) ?></span></td>
          <td><?= e((string) ($c['name'] ?? '')) ?></td><td><b><?= e(money((int) $r['total'], $cur)) ?></b></td>
          <td><span class="badge st-<?= e($r['status']) ?>"><?= e(t('sh_st_' . $r['status'])) ?></span></td>
          <td><?php if ($ph): ?><a class="btn btn-sm btn-ghost" href="https://wa.me/<?= e($ph) ?>?text=<?= rawurlencode(t('sh_chat_text', ['n' => $r['number'], 'shop' => $shop['name']])) ?>" target="_blank" rel="noopener">💬 Chat</a><?php endif; ?></td></tr>
        <?php endforeach; ?>
      </table></div>
      <?php endif; ?>
      <?php if ($m['pending'] > 0): ?><p class="small" style="margin:12px 0 0"><a href="/orders?shop=<?= $sid ?>&status=pending"><b><?= $m['pending'] ?> <?= e(t('sh_dash_pending')) ?> →</b></a></p><?php endif; ?>
    </div>

    <div>
      <div class="card">
        <h3 style="font-size:18px;margin-bottom:12px"><?= e(t('sh_dash_quick')) ?></h3>
        <a class="quick" href="/products?shop=<?= $sid ?>&new=1"><span class="q-ico">＋</span><span><b><?= e(t('sh_p_add')) ?></b><small><?= e(t('sh_dash_q1')) ?></small></span><span>→</span></a>
        <a class="quick" href="/coupons?shop=<?= $sid ?>"><span class="q-ico">🎁</span><span><b><?= e(t('sh_dash_q2t')) ?></b><small><?= e(t('sh_dash_q2')) ?></small></span><span>→</span></a>
        <a class="quick" href="/categories?shop=<?= $sid ?>"><span class="q-ico">▦</span><span><b><?= e(t('sh_nav_categories')) ?></b><small><?= e(t('sh_dash_q3')) ?></small></span><span>→</span></a>
      </div>
      <div class="card">
        <h3 style="font-size:18px;margin-bottom:6px"><?= e(t('sh_dash_share')) ?></h3>
        <p class="muted small"><?= e(t('sh_dash_share_d')) ?></p>
        <div class="result-link" style="font-size:16px;margin-bottom:10px"><?= e(shop_display($shop)) ?></div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
          <button class="btn btn-sm btn-primary" type="button" data-copy="<?= e(shop_url($shop)) ?>" data-copied="<?= e(t('res_copied')) ?>"><?= e(t('res_copy')) ?></button>
          <a class="btn btn-sm btn-ghost" href="https://wa.me/?text=<?= rawurlencode($shop['name'] . ' · ' . shop_url($shop)) ?>" target="_blank" rel="noopener">WhatsApp</a>
          <button class="btn btn-sm btn-ghost" type="button" id="qr-btn"><?= e(t('dash_qr')) ?></button>
        </div>
        <div id="shop-qr" style="margin-top:12px;max-width:180px"></div>
      </div>
    </div>
  </div>
</section>
<script>document.getElementById('qr-btn').addEventListener('click',function(){var el=document.getElementById('shop-qr');window.wsapQr(el,<?= json_encode(shop_url($shop) . '?qr=1') ?>,{size:600}).then(function(q){el.querySelector('canvas').style.width='180px';el.querySelector('canvas').style.height='180px';var a=document.createElement('a');a.href='#';a.textContent='⤓ PNG';a.className='small mono';a.style.display='block';a.onclick=function(e){e.preventDefault();q.download({name:'wsap-shop-<?= e($shop['slug']) ?>',extension:'png'});};el.appendChild(a);});});</script>
<?php page_end();
