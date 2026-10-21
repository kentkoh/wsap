<?php
declare(strict_types=1);
require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/layout.php';

$wa = (string) cfg('support_whatsapp');
$cur = domain_cfg()['currency'];
$u = current_user();
require __DIR__ . '/lib/billplz.php';
$upgradeHref = billplz_enabled()
    ? '/upgrade'
    : ($wa !== '' ? 'https://wa.me/' . $wa . '?text=' . rawurlencode('Hi WSAP, I want to upgrade a link to Premium.' . ($u ? ' My account: ' . $u['email'] : '')) : '/signup');
$waitlistHref = $wa !== '' ? 'https://wa.me/' . $wa . '?text=' . rawurlencode('Hi WSAP, please add me to the Business plan waitlist.') : '/signup';

page_start(['title' => t('nav_pricing'), 'description' => t('pr_lead')]);
?>
<section class="section" style="padding-top:clamp(40px,6vw,72px)">
  <div class="wrap">
    <div class="section-head center" style="margin-inline:auto">
      <span class="kicker"><?= e(t('pr_kicker')) ?></span>
      <h2><?= e(t('pr_title')) ?></h2>
      <p class="lead"><?= e(t('pr_lead')) ?></p>
    </div>
    <div class="plans">
      <div class="plan">
        <h3><?= e(plan_name('free')) ?></h3>
        <p class="desc"><?= e(t('pr_free_d')) ?></p>
        <div class="amount"><?= e(price('free', $cur)) ?><small><?= e(t('pr_forever')) ?></small></div>
        <ul><li><?= e(t('pr_free_1')) ?></li><li><?= e(t('pr_free_2')) ?></li><li><?= e(t('pr_free_3')) ?></li><li><?= e(t('cmp_2')) ?></li></ul>
        <a class="btn btn-ghost btn-lg" href="/#generator"><?= e(t('pr_cta_free')) ?></a>
      </div>
      <div class="plan featured">
        <span class="tag">★ <?= e(t('nav_premium')) ?></span>
        <h3><?= e(plan_name('premium')) ?></h3>
        <p class="desc"><?= e(t('pr_prem_d')) ?></p>
        <div class="amount"><?= e(price('premium', $cur)) ?><small><?= e(t('pr_per_year')) ?></small></div>
        <ul><li><?= e(t('pr_prem_1')) ?></li><li><?= e(t('pr_prem_2')) ?></li><li><?= e(t('pr_prem_3')) ?></li><li><?= e(t('pr_prem_4')) ?></li><li><?= e(t('pr_prem_5')) ?></li><li><?= e(t('cmp_10')) ?></li></ul>
        <a class="btn btn-wa btn-lg" href="<?= e($upgradeHref) ?>" rel="noopener"><?= e(t('pr_cta_prem')) ?> →</a>
      </div>
      <div class="plan">
        <h3><?= e(t('nav_tab_forms')) ?></h3>
        <p class="desc"><?= e(t('pr_form_d')) ?></p>
        <div class="amount"><?= e(price('form', $cur)) ?><small><?= e(t('pr_per_form')) ?></small></div>
        <ul><li><?= e(t('fm_feat_1')) ?></li><li><?= e(t('fm_feat_2')) ?></li><li><?= e(t('fm_feat_3')) ?></li><li><?= e(t('fm_feat_4')) ?></li></ul>
        <a class="btn btn-ghost btn-lg" href="/form"><?= e(t('pr_cta_form')) ?></a>
      </div>
    </div>
    <p class="pay-note"><?= e(t('pr_pay_note')) ?></p>
  </div>
</section>
<section class="section band" style="padding-top:0;padding-bottom:0"></section>
<?php page_end();
