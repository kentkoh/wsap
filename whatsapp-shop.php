<?php
declare(strict_types=1);
require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/layout.php';
require __DIR__ . '/lib/marketing.php';
require __DIR__ . '/lib/demo.php';

$price = price('shop');
$demoShop = null;
try {
    $demoShop = db_row('SELECT slug, domain FROM shops WHERE demo = 1 AND active = 1 ORDER BY id LIMIT 1');
} catch (Throwable $e) {
}
$demoHref = $demoShop ? '/' . $demoShop['slug'] : '/shop';

$shopMock = '<div style="padding:40px 0 0;height:100%;overflow:hidden;background:#faf9f5">'
    . '<div style="height:90px;background:linear-gradient(135deg,#b45309,#7c2d12)"></div>'
    . '<div style="padding:0 14px"><div style="width:48px;height:48px;border-radius:14px;background:#fff;margin-top:-24px;box-shadow:0 6px 16px rgba(0,0,0,.2);display:grid;place-items:center;font-weight:800;color:#b45309">G</div>'
    . '<div style="font-weight:800;font-size:16px;margin-top:8px;letter-spacing:-.02em">The Golden Bakery</div><div style="font-size:11px;color:#666">Artisan breads · Open now</div>'
    . '<div style="display:flex;gap:6px;margin:10px 0"><span style="font-size:10px;padding:4px 8px;border-radius:999px;background:#fff;border:1px solid #e4e2da">Breads</span><span style="font-size:10px;padding:4px 8px;border-radius:999px;background:#fff;border:1px solid #e4e2da">Pastries</span><span style="font-size:10px;padding:4px 8px;border-radius:999px;background:#fff;border:1px solid #e4e2da">Drinks</span></div>'
    . '<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">'
    . '<div><div style="aspect-ratio:1;border-radius:12px;background:linear-gradient(135deg,#f5d9a8,#d9a05b);position:relative"><span style="position:absolute;right:6px;bottom:6px;background:#fff;border-radius:999px;width:22px;height:22px;display:grid;place-items:center;font-size:12px">+</span></div><div style="display:flex;justify-content:space-between;font-size:11px;margin-top:4px"><b>Sourdough</b><span>RM12.50</span></div></div>'
    . '<div><div style="aspect-ratio:1;border-radius:12px;background:linear-gradient(135deg,#f8e3c0,#c97b3a);position:relative"><span style="position:absolute;right:6px;bottom:6px;background:#fff;border-radius:999px;padding:0 7px;height:22px;display:grid;place-items:center;font-size:8px;letter-spacing:.1em">OPTIONS</span></div><div style="display:flex;justify-content:space-between;font-size:11px;margin-top:4px"><b>Croissant</b><span>RM5.50</span></div></div>'
    . '</div></div>'
    . '<div style="position:absolute;left:12px;right:12px;bottom:12px;background:#b45309;color:#fff;border-radius:999px;padding:10px 14px;font-size:12px;font-weight:700;display:flex;justify-content:space-between"><span>' . e(t('sh_view_cart')) . ' · 3</span><span>RM24.80</span></div></div>';

page_start(['title' => t('nav_p_shops') . ' — ' . t('ps_title_1'), 'description' => t('ps_lead')]);
echo demo_popup_css();
?>
<section class="hero">
  <div class="wrap hero-product">
    <div>
      <span class="kicker"><?= e(t('ps_kicker')) ?></span>
      <h1><?= e(t('ps_title_1')) ?><br><span class="serif-i"><?= e(t('ps_title_2')) ?></span></h1>
      <p class="lead"><?= e(t('ps_lead')) ?></p>
      <div class="hero-ctas"><a class="btn btn-wa btn-lg" href="<?= e($demoHref) ?>" target="_blank" rel="noopener"><?= e(t('ps_cta_demo')) ?> ↗</a><a class="btn btn-ghost btn-lg" href="/shop"><?= e(t('ps_cta_open')) ?></a></div>
      <p class="hero-trust"><?= e(t('ps_price_note', ['price' => $price])) ?></p>
    </div>
    <div class="flow">
      <?= phone_mock($shopMock, 'sm') ?>
      <span class="arrow">→</span>
      <?= phone_mock(wa_screen(t('ps_msg_sample'), 'Marta Ruiz'), 'sm') ?>
    </div>
  </div>
</section>

<section class="section band-dark">
  <div class="wrap">
    <div class="section-head reveal"><span class="kicker"><?= e(t('ps_how_k')) ?></span><h2><?= e(t('ps_how_t')) ?></h2></div>
    <div class="steps">
      <div class="step reveal"><h3><?= e(t('ps_how_1t')) ?></h3><p><?= e(t('ps_how_1d')) ?></p></div>
      <div class="step reveal"><h3><?= e(t('ps_how_2t')) ?></h3><p><?= e(t('ps_how_2d')) ?></p></div>
      <div class="step reveal"><h3><?= e(t('ps_how_3t')) ?></h3><p><?= e(t('ps_how_3d')) ?></p></div>
    </div>
  </div>
</section>

<section class="section">
  <div class="wrap">
    <div class="section-head reveal"><span class="kicker"><?= e(t('ps_feat_k')) ?></span><h2><?= e(t('ps_feat_t')) ?></h2></div>
    <div class="bento">
      <?php foreach ([['ps_f1t', 'ps_f1d', '🖼️', 'wide'], ['ps_f2t', 'ps_f2d', '🧩', ''], ['ps_f3t', 'ps_f3d', '🛵', 'accent'], ['ps_f4t', 'ps_f4d', '🎟️', ''], ['ps_f5t', 'ps_f5d', '📊', 'wide'], ['ps_f6t', 'ps_f6d', '📦', ''], ['ps_f7t', 'ps_f7d', '🌙', ''], ['ps_f8t', 'ps_f8d', '💸', 'wide']] as [$tk, $dk, $ico, $cls]): ?>
      <div class="cell <?= $cls ?> reveal"><div class="ico"><?= $ico ?></div><h3><?= e(t($tk)) ?></h3><p><?= e(t($dk)) ?></p></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?= product_cta_band(t('ps_pricing_t'), t('ps_pricing_d', ['price' => $price]), '/shop', t('ps_cta_open')) ?>
<?php page_end();
