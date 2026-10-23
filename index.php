<?php
declare(strict_types=1);
require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/layout.php';
require __DIR__ . '/lib/countries.php';
require __DIR__ . '/lib/ua.php';
require __DIR__ . '/lib/home_graphics.php';
require __DIR__ . '/lib/auth_ui.php';
require __DIR__ . '/lib/marketing.php';
require __DIR__ . '/lib/demo.php';
require __DIR__ . '/lib/generator.php';
require __DIR__ . '/lib/seo.php';

$domain = current_domain();
$domains = cfg('domains');
$premPrice = price('premium');
$waIcon = wa_icon_svg();

page_start(['body_class' => 'page-home']);
echo demo_popup_css();
?>
<section class="hero">
  <div class="wrap hero-grid">
    <div>
      <span class="kicker"><?= e(t('hero_kicker')) ?></span>
      <h1><?= e(t('hero_title_1')) ?><br><span class="serif-i"><?= e(t('hero_title_2')) ?></span></h1>
      <p class="lead"><?= e(t('hero_lead', ['domain' => $domain])) ?></p>
      <div class="hero-ctas">
        <a class="btn btn-wa btn-lg" href="#generator"><?= $waIcon ?> <?= e(t('hero_cta_free')) ?></a>
        <a class="btn btn-ghost btn-lg" href="/pricing"><?= e(t('hero_cta_prem')) ?></a>
      </div>
      <p class="hero-trust"><?= e(t('hero_trust')) ?></p>
      <div class="hero-domains"><?php foreach ($domains as $d => $dc): ?><span><?= e($d) ?> · <?= e($dc['label']) ?></span><?php endforeach; ?></div>
    </div>

    <?= generator_card() ?>
  </div>
</section>

<?php
$tabs = [
    'links' => ['hw_tab_links', [['hw_l1t', 'hw_l1d', 'free'], ['hw_l2t', 'hw_l2d', 'free'], ['hw_l3t', 'hw_l3d', 'free'], ['sep'], ['hw_l4t', 'hw_l4d', 'premium'], ['hw_l5t', 'hw_l5d', 'premium']], 'chat'],
    'smart' => ['hw_tab_smart', [['hw_s1t', 'hw_s1d', 'premium'], ['hw_s2t', 'hw_s2d', 'premium'], ['hw_s3t', 'hw_s3d', 'premium'], ['hw_s4t', 'hw_s4d', 'premium']], 'route'],
    'forms' => ['hw_tab_forms', [['hw_f1t', 'hw_f1d', ''], ['hw_f2t', 'hw_f2d', ''], ['hw_f3t', 'hw_f3d', ''], ['hw_f4t', 'hw_f4d', '']], 'form'],
    'shops' => ['hw_tab_shops', [['hw_p1t', 'hw_p1d', ''], ['hw_p2t', 'hw_p2d', ''], ['hw_p3t', 'hw_p3d', ''], ['hw_p4t', 'hw_p4d', '']], 'shop'],
];
$tiles = [
    ['bio', '📱', '#e6f4ec', 'wh_1t', 'wh_1d'], ['story', '▶', '#ede9fe', 'wh_2t', 'wh_2d'], ['fb', 'f', '#e0ecff', 'wh_3t', 'wh_3d'],
    ['gbp', '🌐', '#e6f4ec', 'wh_4t', 'wh_4d'], ['ads', '📣', '#fef3c7', 'wh_5t', 'wh_5d'], ['qr', '▦', '#ede9fe', 'wh_6t', 'wh_6d'],
    ['sms', '💬', '#e0ecff', 'wh_7t', 'wh_7d'], ['email', '✉️', '#e6f4ec', 'wh_8t', 'wh_8d'], ['web', '🖥️', '#e6f4ec', 'wh_9t', 'wh_9d'],
];
$products = [
    ['link', 'pr2_1k', 'pr2_1t', 'pr2_1d', '/premium-links', '#0b3d2e', '#e6f4ec'],
    ['smart', 'pr2_2k', 'pr2_2t', 'pr2_2d', '/smart-links', '#1d4ed8', '#e0ecff'],
    ['form', 'pr2_3k', 'pr2_3t', 'pr2_3d', '/whatsapp-forms', '#b45309', '#fef3c7'],
    ['shop', 'pr2_4k', 'pr2_4t', 'pr2_4d', '/whatsapp-shop', '#7c3aed', '#ede9fe'],
];
?>
<section class="section band" id="how">
  <div class="wrap">
    <div class="section-head reveal"><span class="kicker"><?= e(t('hw_k')) ?></span><h2><?= e(t('hw_t1')) ?> <span class="serif-i"><?= e(t('hw_t2')) ?></span> <?= e(t('hw_t3')) ?></h2></div>
    <div class="hw">
      <div>
        <span class="kicker" style="margin-bottom:8px"><?= e(t('hw_pick')) ?></span>
        <div class="hw-tabs"><?php foreach ($tabs as $k => [$label]): ?><button type="button" class="<?= $k === 'links' ? 'on' : '' ?>" data-hw="<?= $k ?>"><?= e(t($label)) ?></button><?php endforeach; ?></div>
        <?php foreach ($tabs as $k => [$label, $steps]): $n = 0; ?>
        <div class="hw-list <?= $k === 'links' ? 'on' : '' ?>" data-hw-list="<?= $k ?>">
          <?php foreach ($steps as $st): if ($st[0] === 'sep'): ?><div class="hw-sep">— <?= e(t('hw_with_premium')) ?> —</div><?php continue; endif; $n++; ?>
          <div class="hw-step <?= $n === 1 ? 'on' : '' ?>" data-step="<?= $n ?>"><span class="n"><?= $n ?></span><div><b><?= e(t($st[0])) ?></b><p><?= e(t($st[1])) ?></p></div><?php if ($st[2] !== ''): ?><span class="tag <?= $st[2] === 'premium' ? 'p' : '' ?>"><?= e(t($st[2] === 'premium' ? 'hw_premium' : 'hw_free')) ?></span><?php else: ?><span></span><?php endif; ?></div>
          <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="hw-phone-wrap reveal">
        <div class="phone" id="hw-phone"><div class="phone-notch"></div><div class="phone-screen">
          <?= hg_scene_profile($domain) ?>
          <?= hg_scene_chat(t('hw_chat_msg'), t('hw_chat_reply')) ?>
          <?= hg_scene_route($domain) ?>
          <?= hg_scene_form() ?>
          <?= hg_scene_shop() ?>
          <div class="scene" data-scene="formmsg"><?= substr(hg_scene_chat(t('hw_form_msg')), strlen('<div class="scene" data-scene="chat">'), -6) ?></div>
          <div class="scene" data-scene="shopmsg"><?= substr(hg_scene_chat(t('hw_shop_msg')), strlen('<div class="scene" data-scene="chat">'), -6) ?></div>
          <?= hg_cursor() ?>
        </div></div>
        <p class="hw-caption"><?= e(t('hw_try')) ?></p>
      </div>
    </div>
  </div>
</section>

<section class="section band" id="members">
  <div class="wrap">
    <div class="section-head reveal"><span class="kicker"><?= e(t('mem_k')) ?></span><h2><?= e(t('mem_t1')) ?> <span class="serif-i"><?= e(t('mem_t2')) ?></span></h2><p class="lead"><?= e(t('mem_lead')) ?></p></div>
    <?= member_cards() ?>
    <p class="reveal" style="margin-top:20px"><a class="btn btn-ghost" href="/members"><?= e(t('mem_more')) ?> →</a></p>
  </div>
</section>

<section class="section" id="where">
  <div class="wrap">
    <div class="section-head reveal"><span class="kicker"><?= e(t('wh_k')) ?></span><h2><?= e(t('wh_t1')) ?> <span class="serif-i"><?= e(t('wh_t2')) ?></span></h2><p class="lead"><?= e(t('wh_lead')) ?></p></div>
    <div class="wh-grid">
      <?php foreach ($tiles as [$kind, $ico, $bg, $tk, $dk]): ?>
      <div class="wh reveal"><div class="art"><?= hg_tile_art($kind, $domain) ?></div><div class="meta"><span class="ico" style="background:<?= $bg ?>"><?= $ico ?></span><div><h3><?= e(t($tk)) ?></h3><p><?= e(t($dk)) ?></p></div></div></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section band-dark" id="products">
  <div class="wrap">
    <div class="section-head reveal"><span class="kicker"><?= e(t('pr2_k')) ?></span><h2><?= e(t('pr2_t1')) ?><br><span class="serif-i"><?= e(t('pr2_t2')) ?></span></h2><p class="lead"><?= e(t('pr2_lead')) ?></p></div>
    <div class="pr2-grid">
      <?php foreach ($products as [$kind, $k, $tt, $d, $href, $color, $bg]): ?>
      <a class="pr2 reveal" href="<?= e($href) ?>"><div class="art" style="background:<?= $bg ?>"><?= hg_flow($kind, $domain, $color) ?></div><span class="k" style="color:<?= $color ?>"><?= e(t($k)) ?></span><h3 style="color:var(--ink)"><?= e(t($tt)) ?></h3><p><?= e(t($d)) ?></p><span class="learn"><?= e(t('pr2_learn')) ?> →</span></a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<section class="section" id="premium">
  <div class="wrap">
    <div class="section-head reveal">
      <span class="kicker"><?= e(t('cmp_kicker')) ?></span>
      <h2><?= e(t('cmp_title')) ?></h2>
      <p class="lead"><?= e(t('cmp_lead', ['price' => $premPrice])) ?></p>
    </div>
    <?= member_compare_table($domain) ?>
    <p style="margin-top:22px;display:flex;gap:10px;flex-wrap:wrap"><?php if (!current_user()): ?><a class="btn btn-wa btn-lg" href="/signup"><?= e(t('mem_cta')) ?> →</a><?php endif; ?><a class="btn btn-primary btn-lg" href="/pricing"><?= e(t('cmp_cta')) ?> →</a></p>
  </div>
</section>

<section class="section band" id="faq">
  <div class="wrap">
    <div class="section-head reveal"><h2><?= e(t('faq_title')) ?></h2></div>
    <div class="faq reveal">
      <?php for ($i = 1; $i <= 8; $i++): ?>
      <details<?= $i === 1 ? ' open' : '' ?>><summary><?= e(t("faq_{$i}_q")) ?></summary><div class="a"><?= e(t("faq_{$i}_a")) ?></div></details>
      <?php endfor; ?>
    </div>
  </div>
</section>

<section class="section" style="padding-bottom:0">
  <div class="wrap">
    <div class="cta-band reveal">
      <h2><?= e(t('cta_title')) ?></h2>
      <p class="lead"><?= e(t('cta_lead')) ?></p>
      <a class="btn btn-wa btn-lg" href="#generator"><?= $waIcon ?> <?= e(t('hero_cta_free')) ?></a>
    </div>
  </div>
</section>

<script type="application/ld+json">
<?= json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        ['@type' => 'WebApplication', 'name' => 'WSAP', 'url' => base_url(), 'applicationCategory' => 'BusinessApplication', 'operatingSystem' => 'Web',
         'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => currency()],
         'description' => t('hero_lead', ['domain' => $domain])],
        ['@type' => 'FAQPage', 'mainEntity' => array_map(fn($i) => ['@type' => 'Question', 'name' => t("faq_{$i}_q"), 'acceptedAnswer' => ['@type' => 'Answer', 'text' => t("faq_{$i}_a")]], range(1, 8))],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
</script>
<script>
(function(){
  var phone = document.getElementById('hw-phone'); if (!phone) return;
  var scenes = {}; phone.querySelectorAll('.scene').forEach(function(s){ scenes[s.dataset.scene] = s; });
  var cursor = phone.querySelector('[data-cursor]'), link = phone.querySelector('[data-link]');
  var flows = { links: ['profile', 'chat'], smart: ['profile', 'route'], forms: ['form', 'formmsg'], shops: ['shop', 'shopmsg'] };
  var current = 'links', timer = null, token = 0;
  function show(name){ Object.keys(scenes).forEach(function(k){ scenes[k].classList.toggle('on', k === name); }); }
  function setStep(n){ var list = document.querySelector('.hw-list[data-hw-list="' + current + '"]'); if (!list) return; list.querySelectorAll('.hw-step').forEach(function(s){ s.classList.toggle('on', +s.dataset.step === n); }); }
  function wait(ms, t){ return new Promise(function(r){ timer = setTimeout(function(){ if (t === token) r(); }, ms); }); }
  async function loop(){
    var t = ++token; var f = flows[current];
    while (t === token) {
      show(f[0]); setStep(1); cursor.classList.remove('go'); link && link.classList.remove('pressed');
      await wait(900, t); if (t !== token) return;
      if (f[0] === 'profile' && link) { var r = link.getBoundingClientRect(), p = phone.getBoundingClientRect(); cursor.style.left = (r.left - p.left + r.width * .6) + 'px'; cursor.style.top = (r.top - p.top + r.height * .6) + 'px'; cursor.classList.add('go'); await wait(1700, t); if (t !== token) return; link.classList.add('pressed'); await wait(300, t); if (t !== token) return; }
      else { await wait(1400, t); if (t !== token) return; }
      show(f[1]); setStep(2); await wait(1800, t); if (t !== token) return; setStep(3); await wait(2200, t); if (t !== token) return;
      var steps = document.querySelectorAll('.hw-list[data-hw-list="' + current + '"] .hw-step').length; if (steps > 3) { setStep(4); await wait(1800, t); if (t !== token) return; }
    }
  }
  document.querySelectorAll('[data-hw]').forEach(function(b){ b.addEventListener('click', function(){ document.querySelectorAll('[data-hw]').forEach(function(x){ x.classList.remove('on'); }); b.classList.add('on'); document.querySelectorAll('.hw-list').forEach(function(l){ l.classList.toggle('on', l.dataset.hwList === b.dataset.hw); }); current = b.dataset.hw; clearTimeout(timer); loop(); }); });
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) { show('chat'); return; }
  var started = false; var io = new IntersectionObserver(function(es){ es.forEach(function(e){ if (e.isIntersecting && !started) { started = true; loop(); } }); }, { threshold: .2 }); io.observe(phone);
})();
</script>
<?php page_end();
