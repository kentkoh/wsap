<?php
declare(strict_types=1);
/**
 * /upgrade?id=LINK   — buy Premium for one link (per year), optional custom handle
 * /upgrade?form=ID   — publish a form (per year)
 * /upgrade           — picker
 */
require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/layout.php';
require __DIR__ . '/lib/billplz.php';
require __DIR__ . '/lib/forms.php';
require __DIR__ . '/lib/shops.php';

$u = require_login();
$id = (int) ($_GET['id'] ?? ($_POST['id'] ?? 0));
$formId = (int) ($_GET['form'] ?? ($_POST['form'] ?? 0));
$link = $id ? db_row('SELECT * FROM links WHERE id = ? AND user_id = ?', [$id, (int) $u['id']]) : null;
$form = $formId ? db_row('SELECT * FROM forms WHERE id = ? AND user_id = ?', [$formId, (int) $u['id']]) : null;
$shopId = (int) ($_GET['shop'] ?? ($_POST['shop'] ?? 0));
$shop = $shopId ? db_row('SELECT * FROM shops WHERE id = ? AND user_id = ?', [$shopId, (int) $u['id']]) : null;
$err = null;
$wa = (string) cfg('support_whatsapp');
$cur = domain_cfg()['currency'];
$plan = $shop ? 'shop' : ($form ? 'form' : 'premium');
$years = max(1, min(3, (int) ($_POST['years'] ?? 1)));

if (($link || $form || $shop) && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
    try {
        if (!billplz_enabled()) {
            throw new RuntimeException(t('up_offline'));
        }
        $url = $shop ? start_shop_checkout($u, $shop, $years)
            : ($form ? start_form_checkout($u, $form, $years)
            : start_premium_checkout($u, $link, (string) ($_POST['handle'] ?? ''), $years));
        redirect($url);
    } catch (InvalidArgumentException | RuntimeException $e) {
        $err = $e->getMessage();
    } catch (Throwable $e) {
        error_log('upgrade: ' . $e->getMessage());
        $err = t('err_generic') . (cfg('env') === 'development' ? ' (' . $e->getMessage() . ')' : '');
    }
}

$priceSen = premium_price_sen($plan);
$rm = fn(int $y) => 'RM' . rtrim(rtrim(number_format($priceSen * $y / 100, 2), '0'), '.');
$waText = 'Hi WSAP, I want to upgrade ' . ($shop ? shop_display($shop) : ($form ? $form['domain'] . '/f/' . $form['slug'] : ($link ? short_display($link) : 'a link'))) . '. Account: ' . $u['email'];

page_start(['title' => ($form || $shop) ? t('fm_publish') : t('up_title'), 'noindex' => true, 'body_class' => 'page-app']);
?>
<section class="app wrap form-narrow">
  <p><a href="<?= $shop ? '/shops' : ($form ? '/forms' : '/dashboard') ?>">← <?= e($shop ? t('nav_tab_shops') : ($form ? t('fm_title') : t('dash_title'))) ?></a></p>
  <div class="app-head"><h1><?= e(($form || $shop) ? t('fm_publish') : t('up_title')) ?></h1></div>
  <?php if ($err): ?><div class="flash flash-error" role="alert" style="margin-bottom:16px"><?= e($err) ?></div><?php endif; ?>

  <?php if (!$link && !$form && !$shop): $links = user_links((int) $u['id']); $forms = user_forms((int) $u['id']); $shopsAll = user_shops((int) $u['id']); ?>
    <div class="card">
      <p><?= e(t('up_pick')) ?></p>
      <?php if (!$links && !$forms): ?><a class="btn btn-primary" href="/link"><?= e(t('dash_new')) ?></a><?php endif; ?>
      <div class="link-list">
        <?php foreach ($links as $l): $prem = link_is_premium($l, $u); ?>
        <div class="link-card"><div><div class="code"><?= e(short_display($l)) ?> <?= $prem ? '<span class="badge badge-prem">' . e(t('dash_prem_badge')) . '</span>' : '' ?></div><div class="sub"><?= e($l['title'] ?: '+' . $l['phone']) ?></div></div>
          <a class="btn btn-sm <?= $prem ? 'btn-ghost' : 'btn-primary' ?>" href="/upgrade?id=<?= (int) $l['id'] ?>"><?= e($prem ? t('up_extend') : t('dash_upgrade')) ?></a></div>
        <?php endforeach; ?>
        <?php foreach ($forms as $f): $pub = form_is_published($f, $u); ?>
        <div class="link-card"><div><div class="code"><?= e(form_display($f)) ?> <?= $pub ? '<span class="badge badge-prem">' . e(t('fm_published')) . '</span>' : '<span class="badge badge-off">' . e(t('fm_draft')) . '</span>' ?></div><div class="sub"><?= e($f['title']) ?></div></div>
          <a class="btn btn-sm <?= $pub ? 'btn-ghost' : 'btn-primary' ?>" href="/upgrade?form=<?= (int) $f['id'] ?>"><?= e($pub ? t('up_extend') : t('fm_publish')) ?></a></div>
        <?php endforeach; ?>
        <?php foreach ($shopsAll as $sh): $pub = shop_is_published($sh, $u); ?>
        <div class="link-card"><div><div class="code"><?= e(shop_display($sh)) ?> <?= $pub ? '<span class="badge badge-prem">' . e(t('fm_published')) . '</span>' : '<span class="badge badge-off">' . e(t('fm_draft')) . '</span>' ?></div><div class="sub"><?= e($sh['name']) ?></div></div>
          <a class="btn btn-sm <?= $pub ? 'btn-ghost' : 'btn-primary' ?>" href="/upgrade?shop=<?= (int) $sh['id'] ?>"><?= e($pub ? t('up_extend') : t('fm_publish')) ?></a></div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php else:
    $item = $shop ?: ($form ?: $link);
    $display = $shop ? shop_display($shop) : ($form ? form_display($form) : short_display($link));
    $prem = $shop ? shop_is_published($shop, $u) : ($form ? form_is_published($form, $u) : link_is_premium($link, $u));
    $features = $shop ? ['sh_feat_1', 'sh_feat_2', 'sh_feat_3', 'sh_feat_4'] : ($form ? ['fm_feat_1', 'fm_feat_2', 'fm_feat_3', 'fm_feat_4'] : ['pr_prem_1', 'pr_prem_2', 'pr_prem_3', 'pr_prem_4', 'pr_prem_5', 'cmp_10']);
  ?>
    <form method="post" class="card" novalidate>
      <?= csrf_field() ?><?php if ($shop): ?><input type="hidden" name="shop" value="<?= (int) $shop['id'] ?>"><?php elseif ($form): ?><input type="hidden" name="form" value="<?= (int) $form['id'] ?>"><?php else: ?><input type="hidden" name="id" value="<?= (int) $link['id'] ?>"><?php endif; ?>
      <div class="field"><span class="label"><?= $shop ? 'Shop' : ($form ? 'Form' : 'Link') ?></span><div class="result-link" style="font-size:20px"><?= e($display) ?></div>
        <?php if ($prem && !empty($item['premium_until'])): ?><p class="hint"><?= e(t('up_current_until', ['date' => substr((string) $item['premium_until'], 0, 10)])) ?></p><?php endif; ?></div>

      <?php if ($link && !(int) $link['is_custom']): ?>
      <div class="field">
        <label for="handle"><?= e(t('up_handle')) ?> <small><?= e(t('gen_optional')) ?></small></label>
        <div class="inline-prefix"><span><?= e($link['domain']) ?>/</span><input id="handle" name="handle" maxlength="30" value="<?= e((string) ($_POST['handle'] ?? ($_GET['handle'] ?? ''))) ?>" placeholder="<?= e(t('up_handle_ph')) ?>" pattern="[a-z0-9-]{3,30}"></div>
        <p class="hint"><?= e(t('up_handle_hint')) ?></p>
      </div>
      <?php endif; ?>

      <div class="field"><span class="label"><?= e(t('up_years')) ?></span>
        <div class="seg"><?php foreach ([1, 2, 3] as $y): ?><input type="radio" name="years" id="y<?= $y ?>" value="<?= $y ?>" <?= $y === $years ? 'checked' : '' ?>><label for="y<?= $y ?>"><?= $y ?> <?= e($y === 1 ? t('up_year') : t('up_years_pl')) ?> · <?= $rm($y) ?></label><?php endforeach; ?></div>
        <?php if ($cur !== 'MYR'): ?><p class="hint"><?= e(t('up_charged_myr', ['usd' => price($plan, 'USD')])) ?></p><?php endif; ?>
      </div>

      <ul style="list-style:none;padding:0;margin:0 0 20px;display:grid;gap:8px;font-size:14.5px">
        <?php foreach ($features as $k): ?><li>✓ <?= e(t($k)) ?></li><?php endforeach; ?>
      </ul>

      <?php if (billplz_enabled()): ?>
        <button class="btn btn-wa btn-lg btn-block" type="submit"><?= e(t('up_pay')) ?> →</button>
        <p class="hint center" style="margin-top:10px"><?= e(t('up_methods')) ?></p>
      <?php else: ?>
        <div class="locked"><?= e(t('up_offline')) ?></div>
        <?php if ($wa !== ''): ?><a class="btn btn-wa btn-lg btn-block" href="https://wa.me/<?= e($wa) ?>?text=<?= rawurlencode($waText) ?>" rel="noopener"><?= e(t('footer_contact')) ?></a><?php endif; ?>
      <?php endif; ?>
    </form>
  <?php endif; ?>
</section>
<?php page_end();
