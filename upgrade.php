<?php
declare(strict_types=1);
/**
 * /upgrade?id=LINK — buy Premium for one link (per year). Optional custom handle for random-code links.
 */
require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/layout.php';
require __DIR__ . '/lib/billplz.php';

$u = require_login();
$id = (int) ($_GET['id'] ?? ($_POST['id'] ?? 0));
$links = user_links((int) $u['id']);
$link = null;
foreach ($links as $l) {
    if ((int) $l['id'] === $id) {
        $link = $l;
    }
}
$err = null;
$wa = (string) cfg('support_whatsapp');
$cur = domain_cfg()['currency'];

if ($link && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
    try {
        if (!billplz_enabled()) {
            throw new RuntimeException(t('up_offline'));
        }
        $url = start_premium_checkout($u, $link, (string) ($_POST['handle'] ?? ''), (int) ($_POST['years'] ?? 1));
        redirect($url);
    } catch (InvalidArgumentException | RuntimeException $e) {
        $err = $e->getMessage();
    } catch (Throwable $e) {
        error_log('upgrade: ' . $e->getMessage());
        $err = t('err_generic') . (cfg('env') === 'development' ? ' (' . $e->getMessage() . ')' : '');
    }
}

page_start(['title' => t('up_title'), 'noindex' => true, 'body_class' => 'page-app']);
?>
<section class="app wrap form-narrow">
  <p><a href="/dashboard">← <?= e(t('dash_title')) ?></a></p>
  <div class="app-head"><h1><?= e(t('up_title')) ?></h1></div>
  <?php if ($err): ?><div class="flash flash-error" role="alert" style="margin-bottom:16px"><?= e($err) ?></div><?php endif; ?>

  <?php if (!$link): ?>
    <div class="card">
      <p><?= e(t('up_pick')) ?></p>
      <?php if (!$links): ?><a class="btn btn-primary" href="/link"><?= e(t('dash_new')) ?></a><?php else: ?>
      <div class="link-list">
        <?php foreach ($links as $l): $prem = link_is_premium($l, $u); ?>
        <div class="link-card"><div><div class="code"><?= e(short_display($l)) ?> <?= $prem ? '<span class="badge badge-prem">' . e(t('dash_prem_badge')) . '</span>' : '' ?></div><div class="sub"><?= e($l['title'] ?: '+' . $l['phone']) ?></div></div>
          <a class="btn btn-sm <?= $prem ? 'btn-ghost' : 'btn-primary' ?>" href="/upgrade?id=<?= (int) $l['id'] ?>"><?= e($prem ? t('up_extend') : t('dash_upgrade')) ?></a></div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  <?php else: $prem = link_is_premium($link, $u); ?>
    <form method="post" class="card" novalidate>
      <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $link['id'] ?>">
      <div class="field"><span class="label">Link</span><div class="result-link" style="font-size:20px"><?= e(short_display($link)) ?></div>
        <?php if ($prem && !empty($link['premium_until'])): ?><p class="hint"><?= e(t('up_current_until', ['date' => substr((string) $link['premium_until'], 0, 10)])) ?></p><?php endif; ?></div>

      <?php if (!(int) $link['is_custom']): ?>
      <div class="field">
        <label for="handle"><?= e(t('up_handle')) ?> <small><?= e(t('gen_optional')) ?></small></label>
        <div class="inline-prefix"><span><?= e($link['domain']) ?>/</span><input id="handle" name="handle" maxlength="30" value="<?= e((string) ($_POST['handle'] ?? ($_GET['handle'] ?? ''))) ?>" placeholder="<?= e(t('up_handle_ph')) ?>" pattern="[a-z0-9-]{3,30}"></div>
        <p class="hint"><?= e(t('up_handle_hint')) ?></p>
      </div>
      <?php endif; ?>

      <div class="field"><span class="label"><?= e(t('up_years')) ?></span>
        <div class="seg"><?php foreach ([1, 2, 3] as $y): ?><input type="radio" name="years" id="y<?= $y ?>" value="<?= $y ?>" <?= $y === 1 ? 'checked' : '' ?>><label for="y<?= $y ?>"><?= $y ?> <?= e($y === 1 ? t('up_year') : t('up_years_pl')) ?> · RM<?= (int) cfg('plans.premium.myr') * $y ?></label><?php endforeach; ?></div>
        <?php if ($cur !== 'MYR'): ?><p class="hint"><?= e(t('up_charged_myr', ['usd' => price('premium', 'USD')])) ?></p><?php endif; ?>
      </div>

      <ul style="list-style:none;padding:0;margin:0 0 20px;display:grid;gap:8px;font-size:14.5px">
        <?php foreach (['pr_prem_1', 'pr_prem_2', 'pr_prem_3', 'pr_prem_4', 'cmp_10'] as $k): ?><li>✓ <?= e(t($k)) ?></li><?php endforeach; ?>
      </ul>

      <?php if (billplz_enabled()): ?>
        <button class="btn btn-wa btn-lg btn-block" type="submit"><?= e(t('up_pay')) ?> →</button>
        <p class="hint center" style="margin-top:10px"><?= e(t('up_methods')) ?></p>
      <?php else: ?>
        <div class="locked"><?= e(t('up_offline')) ?></div>
        <?php if ($wa !== ''): ?><a class="btn btn-wa btn-lg btn-block" href="https://wa.me/<?= e($wa) ?>?text=<?= rawurlencode('Hi WSAP, I want to upgrade ' . short_display($link) . ' to Premium. Account: ' . $u['email']) ?>" rel="noopener"><?= e(t('footer_contact')) ?></a><?php endif; ?>
      <?php endif; ?>
    </form>
  <?php endif; ?>
</section>
<?php page_end();
