<?php
declare(strict_types=1);
/** /members — why create a free account: guest vs member vs Premium. */
require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/layout.php';
require __DIR__ . '/lib/seo.php';
require __DIR__ . '/lib/marketing.php';
require __DIR__ . '/lib/auth_ui.php';

$domain = current_domain();
$user = current_user();
page_start(['title' => t('mp_title'), 'description' => t('mp_lead'), 'jsonld' => [seo_breadcrumbs([[t('nav_home'), '/'], [t('mp_title'), '/members']])]]);
?>
<section class="hero">
  <div class="wrap hero-product">
    <div>
      <span class="kicker"><?= e(t('mp_kicker')) ?></span>
      <h1><?= e(t('mp_h1a')) ?><br><span class="serif-i"><?= e(t('mp_h1b')) ?></span></h1>
      <p class="lead"><?= e(t('mp_lead')) ?></p>
      <?php if (!$user): ?>
      <div class="hero-ctas"><a class="btn btn-wa btn-lg" href="/signup"><?= e(t('mem_cta')) ?> →</a><a class="btn btn-ghost btn-lg" href="/login"><?= e(t('nav_login')) ?></a></div>
      <p class="hero-trust"><?= e(t('mp_cta_sub')) ?></p>
      <?php else: ?>
      <div class="hero-ctas"><a class="btn btn-wa btn-lg" href="/dashboard"><?= e(t('nav_dashboard')) ?> →</a></div>
      <?php endif; ?>
    </div>
    <div class="reveal in">
      <div class="gen-card mp-dash" style="max-width:460px;margin:0 auto">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px"><span class="kicker" style="margin:0"><?= e(t('dash_title')) ?></span><span class="badge"><?= e(t('cmp_member')) ?></span></div>
        <?php foreach ([['yourbrand', 'Instagram bio', 1284, true], ['x7k2pq', 'Flyer QR', 318, false], ['m9d4ta', 'Facebook ad', 97, false]] as $i => [$code, $title, $n, $prem]): ?>
        <div class="mp-row" style="animation-delay:<?= $i * .15 ?>s"><div><b class="mono"><?= e($domain) ?>/<?= e($code) ?></b><?= $prem ? ' <span class="badge badge-prem">Premium</span>' : '' ?><br><small class="muted"><?= e($title) ?></small></div><div class="n"><b><?= number_format($n) ?></b><small><?= e(t('dash_clicks')) ?></small></div><div class="acts"><span>✎</span><span>▦</span><span>⏸</span></div></div>
        <?php endforeach; ?>
        <div class="mp-mail">📬 <?= e(t('mp_mail_mock')) ?></div>
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="wrap">
    <div class="section-head reveal"><span class="kicker"><?= e(t('mem_k')) ?></span><h2><?= e(t('mem_t')) ?></h2><p class="lead"><?= e(t('mem_lead')) ?></p></div>
    <?= member_cards() ?>
  </div>
</section>

<section class="section band" id="benefits">
  <div class="wrap">
    <div class="section-head reveal"><span class="kicker"><?= e(t('mp_b_k')) ?></span><h2><?= e(t('mp_b_t')) ?></h2></div>
    <div class="bento">
      <?php foreach ([['mp_b1t', 'mp_b1d', '▦', 'wide'], ['mp_b2t', 'mp_b2d', '📈', ''], ['mp_b3t', 'mp_b3d', '✎', 'accent'], ['mp_b4t', 'mp_b4d', '⏸', ''], ['mp_b5t', 'mp_b5d', '📬', 'wide'], ['mp_b6t', 'mp_b6d', '🧪', '']] as [$tk, $dk, $ico, $cls]): ?>
      <div class="cell <?= $cls ?> reveal"><div class="ico"><?= $ico ?></div><h3><?= e(t($tk)) ?></h3><p><?= e(t($dk)) ?></p></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" id="compare">
  <div class="wrap">
    <div class="section-head reveal"><span class="kicker"><?= e(t('cmp_kicker')) ?></span><h2><?= e(t('cmp_title')) ?></h2><p class="lead"><?= e(t('mp_cmp_lead', ['price' => price('premium')])) ?></p></div>
    <?= member_compare_table($domain) ?>
  </div>
</section>

<?php if (!$user): ?>
<section class="section band" style="padding-bottom:0">
  <div class="wrap">
    <div class="cta-band reveal" style="margin-bottom:0">
      <h2><?= e(t('mp_cta_t')) ?></h2><p class="lead"><?= e(t('mp_cta_d')) ?></p>
      <div class="mp-cta-btns"><?= google_button() ?><a class="btn btn-wa btn-lg" href="/signup"><?= e(t('res_save_email')) ?> →</a></div>
      <p class="hero-trust"><?= e(t('mp_cta_sub')) ?> · <a href="/login"><?= e(t('res_save_login')) ?></a></p>
    </div>
  </div>
</section>
<?php endif; ?>
<?php page_end();
