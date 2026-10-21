<?php
declare(strict_types=1);
require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/layout.php';

$u = require_login();
$id = (int) ($_GET['id'] ?? 0);
$link = db_row('SELECT * FROM links WHERE id = ? AND user_id = ?', [$id, (int) $u['id']]);
if (!$link) {
    http_response_code(404);
    exit('Not found');
}
$premium = is_premium($u);
$url = short_url($link) . '?qr=1';

page_start(['title' => t('qr_title') . ' · ' . short_display($link), 'noindex' => true, 'body_class' => 'page-app']);
?>
<section class="app wrap">
  <p><a href="/dashboard">← <?= e(t('dash_title')) ?></a></p>
  <div class="app-head"><h1><?= e(t('qr_title')) ?> <span class="mono" style="font-size:.6em;color:var(--green-2)"><?= e(short_display($link)) ?></span></h1></div>
  <div class="qr-page">
    <div class="qr-stage"><div id="qr"></div></div>
    <div class="card">
      <p class="muted small"><?= e(t('qr_lead')) ?></p>
      <?php if (!$premium): ?><div class="locked">🔒 <?= e(t('qr_premium')) ?> <a href="/pricing"><b><?= e(t('dash_upgrade')) ?> →</b></a></div><?php endif; ?>
      <fieldset style="border:0;padding:0;margin:0" <?= $premium ? '' : 'disabled' ?>>
        <div class="field"><span class="label"><?= e(t('qr_style')) ?></span>
          <select class="select" id="qr-style"><option value="square">Square</option><option value="rounded">Rounded</option><option value="dots">Dots</option><option value="classy">Classy</option><option value="classy-rounded">Classy rounded</option><option value="extra-rounded">Extra rounded</option></select></div>
        <div class="field"><span class="label"><?= e(t('qr_color')) ?></span>
          <div class="swatches" id="qr-colors"><?php foreach (['#10201a', '#0b3d2e', '#1db954', '#1d4ed8', '#9333ea', '#b4232c', '#c2410c'] as $i => $c): ?><span class="swatch<?= $i === 0 ? ' on' : '' ?>" data-c="<?= $c ?>" style="background:<?= $c ?>"></span><?php endforeach; ?>
          <input type="color" id="qr-custom" value="#10201a" style="width:30px;height:30px;border:0;padding:0;background:none"></div></div>
        <div class="field"><span class="label"><?= e(t('qr_bg')) ?></span>
          <div class="swatches" id="qr-bgs"><?php foreach (['#ffffff', '#f7f6f1', '#ecfccb', '#e6f4ec', '#fff6d6'] as $i => $c): ?><span class="swatch<?= $i === 0 ? ' on' : '' ?>" data-c="<?= $c ?>" style="background:<?= $c ?>"></span><?php endforeach; ?></div></div>
        <div class="field"><label for="qr-logo"><?= e(t('qr_logo')) ?></label><input class="input" type="file" id="qr-logo" accept="image/png,image/jpeg,image/svg+xml"></div>
      </fieldset>
      <div class="form-actions">
        <button class="btn btn-primary" id="dl-png" type="button">⤓ <?= e(t('qr_png')) ?></button>
        <button class="btn btn-ghost" id="dl-svg" type="button">⤓ <?= e(t('qr_svg')) ?></button>
      </div>
    </div>
  </div>
</section>
<script>
(function(){
  var data = <?= json_encode($url) ?>, code = <?= json_encode($link['code']) ?>, premium = <?= $premium ? 'true' : 'false' ?>;
  var opts = { size: 1000, style: 'square', color: '#10201a', bg: '#ffffff', logo: null }, qr = null;
  var el = document.getElementById('qr');
  function render(){ window.wsapQr(el, data, opts).then(function(q){ qr = q; el.querySelector('canvas').style.width = '300px'; el.querySelector('canvas').style.height = '300px'; }); }
  render();
  if (premium) {
    document.getElementById('qr-style').addEventListener('change', function(){ opts.style = this.value; render(); });
    function swatches(id, key){ document.getElementById(id).addEventListener('click', function(e){ var s = e.target.closest('.swatch'); if(!s) return; this.querySelectorAll('.swatch').forEach(function(x){x.classList.remove('on')}); s.classList.add('on'); opts[key] = s.dataset.c; render(); }); }
    swatches('qr-colors', 'color'); swatches('qr-bgs', 'bg');
    document.getElementById('qr-custom').addEventListener('input', function(){ opts.color = this.value; render(); });
    document.getElementById('qr-logo').addEventListener('change', function(){ var f = this.files[0]; if(!f){ opts.logo = null; render(); return; } var r = new FileReader(); r.onload = function(){ opts.logo = r.result; render(); }; r.readAsDataURL(f); });
  }
  document.getElementById('dl-png').addEventListener('click', function(){ if (qr) qr.download({ name: 'wsap-' + code, extension: 'png' }); });
  document.getElementById('dl-svg').addEventListener('click', function(){ if (qr) qr.download({ name: 'wsap-' + code, extension: 'svg' }); });
})();
</script>
<?php page_end();
