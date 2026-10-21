<?php
declare(strict_types=1);
/** /coupons?shop=ID — coupon codes (stored as JSON on the shop). */
require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/layout.php';
require __DIR__ . '/lib/shops.php';
require __DIR__ . '/lib/shop_nav.php';
require __DIR__ . '/lib/app_nav.php';

$u = require_login();
$shop = require_shop($u);
$sid = (int) $shop['id'];
$err = null;
$coupons = shop_coupons($shop);

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
    $act = (string) ($_POST['act'] ?? '');
    try {
        if ($act === 'delete') {
            $code = strtoupper((string) ($_POST['code'] ?? ''));
            $coupons = array_values(array_filter($coupons, fn($c) => strtoupper((string) $c['code']) !== $code));
            flash('ok', t('sh_cp_deleted'));
        } elseif ($act === 'save') {
            $code = strtoupper(preg_replace('/[^A-Z0-9-]/', '', strtoupper(trim((string) ($_POST['code'] ?? '')))) ?? '');
            if (strlen($code) < 2) {
                throw new InvalidArgumentException(t('sh_cp_err_code'));
            }
            $type = ($_POST['type'] ?? 'percent') === 'fixed' ? 'fixed' : 'percent';
            $val = $type === 'fixed' ? to_sen((string) ($_POST['value'] ?? '0')) : max(0, min(100, (float) ($_POST['value'] ?? 0)));
            if ($val <= 0) {
                throw new InvalidArgumentException(t('sh_cp_err_value'));
            }
            $coupons = array_values(array_filter($coupons, fn($c) => strtoupper((string) $c['code']) !== $code));
            $coupons[] = ['code' => mb_substr($code, 0, 30), 'type' => $type, 'value' => $val];
            flash('ok', t('sh_cp_saved'));
        }
        db_exec('UPDATE shops SET coupons = ?, updated_at = ? WHERE id = ?', [json_encode($coupons, JSON_UNESCAPED_UNICODE), now(), $sid]);
        redirect('/coupons?shop=' . $sid);
    } catch (InvalidArgumentException $e) {
        $err = $e->getMessage();
    } catch (Throwable $e) {
        error_log('coupon save: ' . $e->getMessage());
        $err = t('err_generic');
    }
}
$uses = [];
foreach (db_rows('SELECT coupon, COUNT(*) AS n, COALESCE(SUM(discount),0) AS d FROM orders WHERE shop_id = ? AND coupon <> ? GROUP BY coupon', [$sid, '']) as $r) {
    $uses[strtoupper((string) $r['coupon'])] = $r;
}

page_start(['title' => t('sh_coupons') . ' · ' . $shop['name'], 'noindex' => true, 'body_class' => 'page-app']);
?>
<section class="app wrap">
  <?= app_nav('shops') ?>
  <?= shop_nav($shop, 'coupons') ?>
  <div class="app-head"><h1><?= e(t('sh_coupons')) ?></h1></div>
  <p class="muted" style="margin-bottom:16px"><?= e(t('sh_coupons_hint')) ?></p>
  <?php if ($err): ?><div class="flash flash-error" role="alert" style="margin-bottom:16px"><?= e($err) ?></div><?php endif; ?>
  <div class="qr-page" style="align-items:start">
    <div>
      <?php if (!$coupons): ?><div class="empty"><h2><?= e(t('sh_cp_empty')) ?></h2></div><?php else: ?>
      <div class="link-list">
        <?php foreach ($coupons as $c): $k = strtoupper((string) $c['code']); ?>
        <article class="link-card">
          <div><div class="code"><?= e($k) ?></div><div class="sub"><span><?= $c['type'] === 'fixed' ? e(money((int) $c['value'], $shop['currency'])) . ' ' . e(t('sh_cp_fixed')) : e((string) $c['value']) . '% off' ?></span></div></div>
          <div class="stats"><b><?= (int) ($uses[$k]['n'] ?? 0) ?></b><span><?= e(t('sh_cp_uses')) ?><?= isset($uses[$k]) ? ' · −' . e(money((int) $uses[$k]['d'], $shop['currency'])) : '' ?></span></div>
          <div class="actions"><form method="post" data-confirm="<?= e(t('sh_cp_delete_conf')) ?>"><?= csrf_field() ?><input type="hidden" name="shop" value="<?= $sid ?>"><input type="hidden" name="act" value="delete"><input type="hidden" name="code" value="<?= e($k) ?>"><button class="btn btn-sm btn-danger">✕ <?= e(t('sh_remove')) ?></button></form></div>
        </article>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
    <form method="post" class="card" novalidate>
      <?= csrf_field() ?><input type="hidden" name="act" value="save"><input type="hidden" name="shop" value="<?= $sid ?>">
      <h3 style="margin-bottom:14px"><?= e(t('sh_cp_add')) ?></h3>
      <div class="field"><label for="code"><?= e(t('sh_cp_code')) ?></label><input class="input" id="code" name="code" maxlength="30" placeholder="RAYA10" style="text-transform:uppercase" required></div>
      <div class="field" style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
        <div><label for="type"><?= e(t('sh_cp_type')) ?></label><select class="select" id="type" name="type"><option value="percent">% off</option><option value="fixed"><?= e(t('sh_cp_fixed')) ?> (<?= e($shop['currency']) ?>)</option></select></div>
        <div><label for="value"><?= e(t('sh_cp_value')) ?></label><input class="input" id="value" name="value" inputmode="decimal" placeholder="10" required></div>
      </div>
      <div class="form-actions"><button class="btn btn-primary btn-lg" type="submit"><?= e(t('link_save')) ?></button></div>
    </form>
  </div>
</section>
<?php page_end();
