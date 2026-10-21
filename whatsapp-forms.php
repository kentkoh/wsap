<?php
declare(strict_types=1);
require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/layout.php';
require __DIR__ . '/lib/marketing.php';
require __DIR__ . '/lib/demo.php';

$price = price('form');
$demos = [
    ['demo-homestay', '🏡', 'pf_i1_t', 'pf_i1_d', '#e6f4ec'],
    ['demo-clinic', '🩺', 'pf_i2_t', 'pf_i2_d', '#e0ecff'],
    ['demo-pickleball', '🏓', 'pf_i3_t', 'pf_i3_d', '#ffedd5'],
    ['demo-workshop', '🔧', 'pf_i4_t', 'pf_i4_d', '#fef3c7'],
    ['demo-tuition', '📚', 'pf_i5_t', 'pf_i5_d', '#ede9fe'],
    ['demo-catering', '🍛', 'pf_i6_t', 'pf_i6_d', '#fee2e2'],
];
page_start(['title' => t('nav_p_forms') . ' — ' . t('pf_title_1') . ' ' . t('pf_title_2'), 'description' => t('pf_lead')]);
echo demo_popup_css();
?>
<section class="hero">
  <div class="wrap hero-product">
    <div>
      <span class="kicker"><?= e(t('pf_kicker')) ?></span>
      <h1><?= e(t('pf_title_1')) ?><br><span class="serif-i"><?= e(t('pf_title_2')) ?></span></h1>
      <p class="lead"><?= e(t('pf_lead')) ?></p>
      <div class="hero-ctas"><a class="btn btn-wa btn-lg" href="/form"><?= e(t('pf_cta_build')) ?> →</a><a class="btn btn-ghost btn-lg" href="#samples"><?= e(t('pf_cta_demo')) ?></a></div>
      <p class="hero-trust"><?= e(t('pf_price_note', ['price' => $price])) ?></p>
    </div>
    <div class="flow">
      <?= phone_mock(form_step_mock('Klinik Dr. Aina', t('pf_mock_q'), t('pf_mock_step')), 'sm') ?>
      <span class="arrow">→</span>
      <?= phone_mock(wa_screen(t('pf_mock_msg'), 'Aisyah Rahman'), 'sm') ?>
    </div>
  </div>
</section>

<section class="section band-dark">
  <div class="wrap">
    <div class="section-head reveal"><span class="kicker"><?= e(t('pf_how_k')) ?></span><h2><?= e(t('pf_how_t')) ?></h2></div>
    <div class="steps">
      <div class="step reveal"><h3><?= e(t('pf_how_1t')) ?></h3><p><?= e(t('pf_how_1d')) ?></p></div>
      <div class="step reveal"><h3><?= e(t('pf_how_2t')) ?></h3><p><?= e(t('pf_how_2d')) ?></p></div>
      <div class="step reveal"><h3><?= e(t('pf_how_3t')) ?></h3><p><?= e(t('pf_how_3d')) ?></p></div>
    </div>
  </div>
</section>

<section class="section" id="samples">
  <div class="wrap">
    <div class="section-head reveal"><span class="kicker"><?= e(t('pf_ind_k')) ?></span><h2><?= e(t('pf_ind_t')) ?></h2><p class="lead"><?= e(t('pf_ind_lead')) ?></p></div>
    <div class="ind-grid">
      <?php foreach ($demos as [$slug, $ico, $tk, $dk, $bg]): ?>
      <div class="ind reveal"><div class="ico" style="background:<?= $bg ?>"><?= $ico ?></div><h3><?= e(t($tk)) ?></h3><p><?= e(t($dk)) ?></p><a class="btn btn-sm btn-primary" href="/<?= $slug ?>" target="_blank" rel="noopener"><?= e(t('pf_try')) ?> ↗</a></div>
      <?php endforeach; ?>
    </div>
    <p class="muted" style="margin-top:18px;font-size:14.5px"><?= e(t('pf_more_ind')) ?></p>
  </div>
</section>

<section class="section band">
  <div class="wrap">
    <div class="section-head reveal"><span class="kicker"><?= e(t('pf_vs_k')) ?></span><h2><?= e(t('pf_vs_t')) ?></h2></div>
    <div class="chat-compare reveal">
      <div class="chat-col bad"><h3>✕ <?= e(t('pf_vs_before')) ?></h3>
        <div class="cb"><?= e(t('pf_vs_b1')) ?></div><div class="cb"><?= e(t('pf_vs_b2')) ?></div><div class="cb me"><?= e(t('pf_vs_b3')) ?></div><div class="cb"><?= e(t('pf_vs_b4')) ?></div><div class="cb me"><?= e(t('pf_vs_b5')) ?></div><div class="cb gone"><?= e(t('pf_vs_b6')) ?></div></div>
      <div class="chat-col good"><h3>✓ <?= e(t('pf_vs_after')) ?></h3>
        <div class="cb" style="white-space:pre-line"><?= preg_replace('/\*([^*\n]+)\*/', '<b>$1</b>', e("Hai Seri Damai! Saya nak tempah homestay:\n*Nama penuh*\nFarah Hanis\n*Tarikh check-in dan check-out*\n2026-12-20 → 2026-12-22\n*Bilangan tetamu*\n6\n*Unit*\nVilla 3 bilik (RM450/malam)\n*Tambahan*\nBBQ set (RM80), Sarapan (RM15/orang)")) ?></div>
        <div class="cb me">Hai Farah! Villa kosong 20–22 Dis. Total RM1,160. Nak confirm? 😊</div></div>
    </div>
  </div>
</section>

<section class="section">
  <div class="wrap">
    <div class="section-head reveal"><span class="kicker"><?= e(t('pf_feat_k')) ?></span><h2><?= e(t('pf_feat_t')) ?></h2></div>
    <div class="bento">
      <?php foreach ([['pf_f1t', 'pf_f1d', '🧩', 'wide'], ['pf_f2t', 'pf_f2d', '📱', ''], ['pf_f3t', 'pf_f3d', '🎨', 'accent'], ['pf_f4t', 'pf_f4d', '📥', ''], ['pf_f5t', 'pf_f5d', '📈', 'wide'], ['pf_f6t', 'pf_f6d', '🔗', '']] as [$tk, $dk, $ico, $cls]): ?>
      <div class="cell <?= $cls ?> reveal"><div class="ico"><?= $ico ?></div><h3><?= e(t($tk)) ?></h3><p><?= e(t($dk)) ?></p></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?= product_cta_band(t('pf_pricing_t'), t('pf_pricing_d', ['price' => $price]), '/form', t('pf_pricing_cta')) ?>
<?php page_end();
