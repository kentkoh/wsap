<?php
declare(strict_types=1);
require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/layout.php';
require __DIR__ . '/lib/marketing.php';
require_once __DIR__ . '/lib/home_graphics.php';

$price = price('premium');
$domain = current_domain();
page_start(['title' => t('nav_p_links') . ' — ' . t('pl_title_1') . ' ' . t('pl_title_2'), 'description' => t('pl_lead')]);
?>
<section class="hero">
  <div class="wrap hero-product">
    <div>
      <span class="kicker"><?= e(t('pl_kicker')) ?></span>
      <h1><?= e(t('pl_title_1')) ?><br><span class="serif-i"><?= e(t('pl_title_2')) ?></span></h1>
      <p class="lead"><?= e(t('pl_lead')) ?></p>
      <div class="hero-ctas"><a class="btn btn-wa btn-lg" href="/link"><?= e(t('pl_cta')) ?> →</a><a class="btn btn-ghost btn-lg" href="/#generator"><?= e(t('pl_cta2')) ?></a></div>
      <p class="hero-trust"><?= e(t('pl_price_note', ['price' => $price])) ?></p>
    </div>
    <div class="reveal in">
      <div class="gen-card" style="max-width:460px;margin:0 auto">
        <span class="kicker" style="margin:0 0 10px"><?= e(t('nav_p_links')) ?></span>
        <div class="result-link" style="font-size:26px;margin-bottom:12px"><?= e($domain) ?>/<span style="color:var(--wa-dark)">yourbrand</span></div>
        <div class="stat-grid" style="grid-template-columns:1fr 1fr 1fr;gap:8px;margin:0 0 14px">
          <div class="stat" style="padding:12px"><b style="font-size:22px">1,284</b><span><?= e(t('an_clicks')) ?></span></div>
          <div class="stat" style="padding:12px"><b style="font-size:22px">63%</b><span>Instagram</span></div>
          <div class="stat" style="padding:12px"><b style="font-size:22px">212</b><span><?= e(t('an_qr')) ?></span></div>
        </div>
        <div class="chart-days" style="height:70px"><?php foreach ([3, 5, 4, 7, 6, 9, 8, 12, 10, 14, 11, 16, 13, 18] as $v): ?><div class="bar" style="height:<?= $v / 18 * 100 ?>%"></div><?php endforeach; ?></div>
      </div>
    </div>
  </div>
</section>

<?= hg_flow_explainer('link', current_domain(), '#0b3d2e', '#e6f4ec') ?>

<section class="section">
  <div class="wrap">
    <div class="bento">
      <?php foreach ([['pl_f1t', 'pl_f1d', '✨', 'wide'], ['pl_f2t', 'pl_f2d', '✏️', ''], ['pl_f3t', 'pl_f3d', '📊', 'accent'], ['pl_f4t', 'pl_f4d', '▦', ''], ['pl_f5t', 'pl_f5d', '⚡', 'wide'], ['pl_f6t', 'pl_f6d', '🎯', '']] as [$tk, $dk, $ico, $cls]): ?>
      <div class="cell <?= $cls ?> reveal"><div class="ico"><?= $ico ?></div><h3><?= e(t($tk)) ?></h3><p><?= e(t($dk)) ?></p></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section band">
  <div class="wrap">
    <div class="section-head reveal"><span class="kicker"><?= e(t('pl_smart_k')) ?></span><h2><?= e(t('pl_smart_t')) ?></h2><p class="lead"><?= e(t('pl_smart_lead')) ?></p></div>
    <div class="smart-pic reveal">
      <div class="link-chip"><?= e($domain) ?>/yourbrand</div>
      <div class="rules">
        <?php foreach ([['pl_r1', 'pl_r1d'], ['pl_r2', 'pl_r2d'], ['pl_r3', 'pl_r3d'], ['pl_r4', 'pl_r4d']] as $i => [$a, $b]): ?>
        <div class="rule"><span class="n"><?= $i + 1 ?></span><b><?= e(t($a)) ?></b><span><?= e(t($b)) ?></span></div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<?= product_cta_band(t('pl_pricing_t'), t('pl_pricing_d', ['price' => $price, 'smart' => price('smart')]), '/link', t('pl_cta')) ?>
<?php page_end();
