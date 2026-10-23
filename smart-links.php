<?php
declare(strict_types=1);
require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/layout.php';
require __DIR__ . '/lib/seo.php';
require __DIR__ . '/lib/marketing.php';
require_once __DIR__ . '/lib/home_graphics.php';

$price = price('smart');            // monthly, admin-editable
$plink = price('premium');
$domain = current_domain();
$u = current_user();
$cta = $u ? '/upgrade' : '/signup';
$maxDest = smart_max_destinations();
$split = fn(string $k) => explode('|', t($k, ['n' => $maxDest > 0 ? $maxDest : '∞']));

page_start(['title' => t('seo_sl_title'), 'description' => t('sl_lead'), 'jsonld' => [seo_breadcrumbs([[t('nav_home'), '/'], [t('nav_p_smart'), '/smart-links']]), seo_app(cfg('site_name') . ' ' . t('nav_p_smart'), t('sl_lead'), '/smart-links', seo_price_amount('smart'), currency())]]);
?>
<section class="hero">
  <div class="wrap hero-product">
    <div>
      <span class="kicker"><?= e(t('sl_kicker')) ?></span>
      <h1><?= e(t('sl_h1a')) ?><br><span class="serif-i"><?= e(t('sl_h1b')) ?></span></h1>
      <p class="lead"><?= e(t('sl_lead')) ?></p>
      <div class="price-hero"><span class="only"><?= e(t('sl_only')) ?></span><b><?= e($price) ?></b><span>/ <?= e(t('sl_per_month')) ?></span></div>
      <p class="hero-trust"><?= e(t('sl_billed')) ?></p>
      <div class="hero-ctas"><a class="btn btn-wa btn-lg" href="<?= e($cta) ?>">⚡ <?= e(t('sl_cta')) ?></a><a class="btn btn-ghost btn-lg" href="#how"><?= e(t('sl_cta2')) ?></a></div>
    </div>
    <div class="reveal in">
      <div class="sl-stage">
        <div class="chip"><span class="tapper"><svg viewBox="0 0 24 24" width="22" height="22"><path d="M4 2l16 10-7 1.5L16 21l-3 1-3-7.5L4 18z" fill="#fff" stroke="#111" stroke-width="1.5" stroke-linejoin="round"/></svg></span><?= e($domain) ?>/yourteam</div>
        <div class="ball"></div>
        <div class="rules">
          <div class="rule"><span>1 · <?= e(t('sl_r_hours')) ?></span><span>→ <?= e(t('sl_to_counter')) ?></span></div>
          <div class="rule"><span>2 · <?= e(t('sl_r_country')) ?></span><span>→ SG</span></div>
          <div class="rule"><span>3 · <?= e(t('sl_r_source')) ?></span><span>→ Aina</span></div>
          <div class="rule"><span>4 · <?= e(t('sl_r_split')) ?></span><span>→ Ben · Dina</span></div>
        </div>
        <div class="people"><div class="person"><i>🧑‍💼</i><?= e(t('sl_to_counter')) ?></div><div class="person"><i>🏢</i>SG</div><div class="person hit"><i>👩</i>Aina</div><div class="person"><i>🧑</i>Ben</div><div class="person"><i>👩‍🦱</i>Dina</div></div>
        <div class="wa-pop"><b>Aina</b> · <?= e(t('sl_tap_from')) ?> Instagram<br>"<?= e(t('hw_smart_msg')) ?>"</div>
      </div>
    </div>
  </div>
</section>

<?= hg_flow_explainer('smart', current_domain(), '#1d4ed8', '#e0ecff') ?>

<section class="section band" id="why">
  <div class="wrap">
    <div class="section-head reveal"><span class="kicker"><?= e(t('sl_why_k')) ?></span><h2><?= e(t('sl_why_t')) ?></h2><p class="lead"><?= e(t('sl_why_lead')) ?></p></div>
    <div class="pain reveal" style="margin-bottom:22px">
      <div class="pain-col bad"><h3>✕ <?= e(t('pf_vs_before')) ?></h3>
        <div class="pile">
          <div class="msg"><span>"Hi, ada stok?"</span><small>21:04 · waiting</small></div>
          <div class="msg"><span>"How much for 2?"</span><small>21:10 · waiting</small></div>
          <div class="msg"><span>"Boleh COD?"</span><small>21:17 · waiting</small></div>
          <div class="msg"><span>"Hello? still there?"</span><small>21:40 · waiting</small></div>
          <div class="msg"><span>"ok nevermind"</span><small>22:05 · lost</small></div>
        </div>
        <span class="badge-wait">1 phone · 5 waiting · reply tomorrow</span>
      </div>
      <div class="pain-col good"><h3>✓ <?= e(t('pr2_2t')) ?></h3>
        <div class="router">
          <div class="in"><?= e($domain) ?>/yourteam</div>
          <div class="rules"><div class="rule"><?= e(t('sl_r_hours')) ?></div><div class="rule on"><?= e(t('sl_r_source')) ?></div><div class="rule"><?= e(t('sl_r_country')) ?></div><div class="rule"><?= e(t('sl_r_split')) ?></div></div>
          <div class="people"><div class="person"><i>🧑‍💼</i><?= e(t('sl_to_counter')) ?><span class="cnt">12</span></div><div class="person on"><i>👩</i>Aina<span class="cnt">31</span></div><div class="person"><i>🧑</i>Ben<span class="cnt">9</span></div><div class="person"><i>👩‍🦱</i>Dina<span class="cnt">8</span></div></div>
        </div>
        <span class="badge-fast">4 phones · 0 waiting · reply in minutes</span>
      </div>
    </div>
    <div class="why-grid">
      <?php foreach ([['sl_w1t', 'sl_w1d', '🔁'], ['sl_w2t', 'sl_w2d', '⚡'], ['sl_w3t', 'sl_w3d', '⚖️'], ['sl_w4t', 'sl_w4d', '🌏'], ['sl_w5t', 'sl_w5d', '📊'], ['sl_w6t', 'sl_w6d', '🔗']] as [$tk, $dk, $ico]): ?>
      <div class="why reveal"><div class="ico"><?= $ico ?></div><h3><?= e(t($tk)) ?></h3><p><?= e(t($dk)) ?></p></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" id="how">
  <div class="wrap">
    <div class="section-head reveal"><span class="kicker"><?= e(t('sl_how_k')) ?></span><h2><?= e(t('sl_how_t')) ?></h2><p class="lead"><?= e(t('sl_how_lead')) ?></p></div>
    <div class="sl-how reveal">
      <div class="sl-steps" id="sl-steps">
        <?php foreach ([['sl_h1t', 'sl_h1d'], ['sl_h2t', 'sl_h2d'], ['sl_h3t', 'sl_h3d'], ['sl_h4t', 'sl_h4d']] as $i => [$tk, $dk]): ?>
        <div class="sl-step <?= $i === 0 ? 'on' : '' ?>" data-i="<?= $i ?>"><span class="n"><?= $i + 1 ?></span><div><b><?= e(t($tk)) ?></b><p><?= e(t($dk)) ?></p></div></div>
        <?php endforeach; ?>
      </div>
      <div class="sl-stage" id="sl-stage2">
        <div class="chip"><span class="tapper"><svg viewBox="0 0 24 24" width="22" height="22"><path d="M4 2l16 10-7 1.5L16 21l-3 1-3-7.5L4 18z" fill="#fff" stroke="#111" stroke-width="1.5" stroke-linejoin="round"/></svg></span><?= e($domain) ?>/yourteam</div>
        <div class="ball"></div>
        <div class="rules">
          <div class="rule"><span>1 · <?= e(t('sl_r_hours')) ?></span><span>→ <?= e(t('sl_to_counter')) ?></span></div>
          <div class="rule"><span>2 · <?= e(t('sl_r_country')) ?></span><span>→ SG</span></div>
          <div class="rule"><span>3 · <?= e(t('sl_r_source')) ?></span><span>→ Aina</span></div>
          <div class="rule"><span>4 · <?= e(t('sl_r_split')) ?></span><span>→ Ben · Dina</span></div>
        </div>
        <div class="people"><div class="person"><i>🧑‍💼</i><?= e(t('sl_to_counter')) ?></div><div class="person"><i>🏢</i>SG</div><div class="person hit"><i>👩</i>Aina</div><div class="person"><i>🧑</i>Ben</div><div class="person"><i>👩‍🦱</i>Dina</div></div>
        <div class="wa-pop"><b>Aina</b> · <?= e(t('sl_tap_from')) ?> Instagram<br>"<?= e(t('hw_smart_msg')) ?>"</div>
      </div>
    </div>
  </div>
</section>

<section class="section band-dark" id="modes">
  <div class="wrap">
    <div class="section-head reveal"><span class="kicker"><?= e(t('sl_modes_k')) ?></span><h2><?= e(t('sl_modes_t')) ?></h2><p class="lead"><?= e(t('sl_modes_lead')) ?></p></div>
    <div class="modes">
      <div class="mode reveal"><div class="viz"><div class="clock"><i></i><i></i><i class="on"></i><i class="on"></i><i class="on"></i><i class="on"></i><i class="on"></i><i></i><i></i></div></div><h3>🕘 <?= e(t('sl_m1t')) ?></h3><p><?= e(t('sl_m1d')) ?></p></div>
      <div class="mode reveal"><div class="viz"><div class="flags"><span>🇲🇾</span><span>🇸🇬</span><span>🇮🇩</span></div></div><h3>🌏 <?= e(t('sl_m2t')) ?></h3><p><?= e(t('sl_m2d')) ?></p></div>
      <div class="mode reveal"><div class="viz"><div class="srcs"><span>Instagram</span><span>TikTok</span><span>Facebook</span><span>Google</span><span>QR</span></div></div><h3>📣 <?= e(t('sl_m3t')) ?></h3><p><?= e(t('sl_m3d')) ?></p></div>
      <div class="mode reveal"><div class="viz"><div class="split"><i style="height:100%"></i><i></i><i></i></div></div><h3>⚖️ <?= e(t('sl_m4t')) ?></h3><p><?= e(t('sl_m4d')) ?></p></div>
    </div>
  </div>
</section>

<section class="section">
  <div class="wrap">
    <div class="section-head reveal"><span class="kicker"><?= e(t('sl_uc_k')) ?></span><h2><?= e(t('sl_uc_t')) ?></h2></div>
    <div class="uc-grid">
      <?php foreach ([['sl_u1t', 'sl_u1d', 'sl_m4t'], ['sl_u2t', 'sl_u2d', 'sl_m1t'], ['sl_u3t', 'sl_u3d', 'sl_m1t'], ['sl_u4t', 'sl_u4d', 'sl_m4t'], ['sl_u5t', 'sl_u5d', 'sl_m2t'], ['sl_u6t', 'sl_u6d', 'sl_m3t']] as [$tk, $dk, $mk]): ?>
      <div class="uc reveal"><span class="mode-tag"><?= e(t($mk)) ?></span><h3><?= e(t($tk)) ?></h3><p><?= e(t($dk)) ?></p></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section band" id="pricing">
  <div class="wrap">
    <div class="section-head reveal"><span class="kicker"><?= e(t('sl_price_k')) ?></span><h2><?= e(t('sl_price_t', ['price' => $price])) ?></h2><p class="lead"><?= e(t('sl_price_lead', ['n' => $maxDest > 0 ? $maxDest : '∞'])) ?></p></div>
    <div class="price-box reveal">
      <div class="card"><h3 style="font-size:20px"><?= e(t('ps2_inc_t')) ?></h3><ul class="inc-list"><?php foreach ($split('sl_inc') as $b): ?><li><?= e($b) ?></li><?php endforeach; ?></ul></div>
      <div class="price-big"><small style="margin:0 0 6px"><?= e(t('sl_only')) ?></small><b><?= e($price) ?></b><small><?= e(t('sl_per_month')) ?> · <?= e(t('up_year')) ?>ly</small><a class="btn btn-wa btn-lg" href="<?= e($cta) ?>">⚡ <?= e(t('sl_price_cta')) ?> →</a><p class="small" style="opacity:.75;margin:14px 0 0"><?= e(t('sl_price_note', ['plink' => $plink])) ?></p></div>
    </div>
  </div>
</section>

<section class="section">
  <div class="wrap">
    <div class="section-head reveal"><h2><?= e(t('sl_faq_t')) ?></h2></div>
    <div class="faq reveal"><?php for ($i = 1; $i <= 6; $i++): ?><details<?= $i === 1 ? ' open' : '' ?>><summary><?= e(t("sl_q$i")) ?></summary><div class="a"><?= e(t("sl_a$i", ['price' => $price])) ?></div></details><?php endfor; ?></div>
  </div>
</section>

<?= product_cta_band(t('sl_final_t'), t('sl_final_d'), $cta, t('sl_price_cta')) ?>
<script>
(function(){ var steps=document.querySelectorAll('#sl-steps .sl-step'); if(!steps.length) return; var seq=[[0,0],[1,2400],[2,5300],[3,6200]]; function run(){ seq.forEach(function(s){ setTimeout(function(){ steps.forEach(function(x){ x.classList.toggle('on', +x.dataset.i===s[0]); }); }, s[1]); }); } run(); setInterval(run, 7000); })();
</script>
<?php page_end();
