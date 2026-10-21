<?php
declare(strict_types=1);
/** /shop?id= — shop settings editor. */
require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/layout.php';
require __DIR__ . '/lib/shops.php';
require __DIR__ . '/lib/countries.php';
require __DIR__ . '/lib/ua.php';
require __DIR__ . '/lib/app_nav.php';
require __DIR__ . '/lib/shop_nav.php';

$u = require_login();
$id = (int) ($_GET['id'] ?? ($_POST['id'] ?? 0));
$shop = $id ? db_row('SELECT * FROM shops WHERE id = ? AND user_id = ?', [$id, (int) $u['id']]) : null;
if ($id && !$shop) {
    http_response_code(404);
    exit('Not found');
}
$err = null;
$domains = array_keys(cfg('domains'));
$currencies = ['MYR', 'SGD', 'IDR', 'THB', 'PHP', 'BND', 'USD', 'AUD', 'GBP', 'EUR'];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
    try {
        if (isset($_POST['delete']) && $shop) {
            db_exec('DELETE FROM shops WHERE id = ?', [(int) $shop['id']]);
            flash('ok', t('sh_deleted'));
            redirect('/shops');
        }
        $slug = strtolower(trim((string) ($_POST['slug'] ?? '')));
        assert_custom_code($slug);
        if (slug_taken($slug, ['shops', (int) ($shop['id'] ?? 0)])) {
            throw new InvalidArgumentException(t('err_code_taken'));
        }
        $name = mb_substr(trim((string) ($_POST['name'] ?? '')), 0, 80);
        if ($name === '') {
            throw new InvalidArgumentException(t('sh_err_name'));
        }
        $phone = normalize_phone((string) ($_POST['cc'] ?? ''), (string) ($_POST['phone'] ?? ''));
        if (!valid_phone($phone)) {
            throw new InvalidArgumentException(t('err_phone'));
        }
        $fields = [];
        foreach (SHOP_FIELDS as $f) {
            $v = (string) ($_POST['field_' . $f] ?? 'off');
            $fields[$f] = in_array($v, ['req', 'on', 'off'], true) ? $v : 'off';
        }
        $fields['name'] = 'req';
        $shipping = [];
        foreach ((array) ($_POST['ship_name'] ?? []) as $i => $sn) {
            $sn = mb_substr(trim((string) $sn), 0, 60);
            if ($sn !== '') {
                $shipping[] = ['name' => $sn, 'price' => to_sen((string) ($_POST['ship_price'][$i] ?? '0')), 'note' => mb_substr(trim((string) ($_POST['ship_note'][$i] ?? '')), 0, 120)];
            }
        }
        $coupons = $shop ? shop_coupons($shop) : []; // managed at /coupons
        $logo = $shop['logo'] ?? '';
        $cover = $shop['cover'] ?? '';
        if (!empty($_FILES['logo_file']['name'])) {
            $logo = store_image($_FILES['logo_file'], 'logo') ?: $logo;
        } elseif (safe_url((string) ($_POST['logo'] ?? '')) !== '') {
            $logo = safe_url((string) $_POST['logo']);
        }
        if (!empty($_FILES['cover_file']['name'])) {
            $cover = store_image($_FILES['cover_file'], 'cover') ?: $cover;
        } elseif (safe_url((string) ($_POST['cover'] ?? '')) !== '') {
            $cover = safe_url((string) $_POST['cover']);
        }
        if (isset($_POST['remove_logo'])) {
            $logo = '';
        }
        if (isset($_POST['remove_cover'])) {
            $cover = '';
        }
        $accent = preg_match('/^#[0-9a-f]{6}$/i', (string) ($_POST['accent'] ?? '')) ? strtolower($_POST['accent']) : '#0b3d2e';
        $row = [
            'slug' => $slug, 'domain' => in_array($_POST['domain'] ?? '', $domains, true) ? $_POST['domain'] : current_domain(),
            'name' => $name, 'tagline' => mb_substr(trim((string) ($_POST['tagline'] ?? '')), 0, 160), 'about' => mb_substr(trim((string) ($_POST['about'] ?? '')), 0, 1000),
            'logo' => $logo, 'cover' => $cover, 'accent' => $accent, 'phone' => $phone,
            'currency' => in_array($_POST['currency'] ?? '', $currencies, true) ? $_POST['currency'] : 'MYR',
            'fields' => json_encode($fields), 'shipping' => json_encode($shipping, JSON_UNESCAPED_UNICODE), 'coupons' => json_encode($coupons, JSON_UNESCAPED_UNICODE),
            'payment_note' => mb_substr(trim((string) ($_POST['payment_note'] ?? '')), 0, 300),
            'instagram' => safe_url((string) ($_POST['instagram'] ?? '')), 'facebook' => safe_url((string) ($_POST['facebook'] ?? '')), 'tiktok' => safe_url((string) ($_POST['tiktok'] ?? '')),
            'hours_text' => mb_substr(trim((string) ($_POST['hours_text'] ?? '')), 0, 120),
            'is_open' => isset($_POST['is_open']) ? 1 : 0, 'active' => isset($_POST['active']) ? 1 : 0,
        ];
        if ($shop) {
            $sets = implode(', ', array_map(fn($k) => "$k = ?", array_keys($row)));
            db_exec("UPDATE shops SET $sets, updated_at = ? WHERE id = ?", [...array_values($row), now(), (int) $shop['id']]);
            flash('ok', t('sh_saved'));
            redirect('/store?shop=' . (int) $shop['id']);
        }
        $cols = implode(', ', array_keys($row));
        $marks = implode(', ', array_fill(0, count($row), '?'));
        $newId = db_insert("INSERT INTO shops (user_id, $cols, views, created_at, updated_at) VALUES (?, $marks, 0, ?, ?)", [(int) $u['id'], ...array_values($row), now(), now()]);
        flash('ok', t('sh_saved_first'));
        redirect('/categories?shop=' . $newId . '&new=1');
    } catch (InvalidArgumentException $e) {
        $err = $e->getMessage();
    } catch (Throwable $e) {
        error_log('shop save: ' . $e->getMessage());
        $err = t('err_generic');
    }
}

$v = fn(string $k, string $def = '') => e((string) ($_POST[$k] ?? ($shop[$k] ?? $def)));
$fields = $shop ? shop_fields($shop) : shop_default_fields();
$shipping = $shop ? shop_shipping($shop) : [['name' => t('sh_ship_default'), 'price' => 0, 'note' => '']];
$coupons = $shop ? shop_coupons($shop) : [];
[$dial, $national] = $shop ? split_phone($shop['phone']) : ['', ''];
$selIso = 'MY';
foreach (country_codes() as $iso => [$d]) {
    if ($d === $dial) {
        $selIso = $iso;
    }
}

page_start(['title' => $shop ? t('sh_settings') : t('sh_new'), 'noindex' => true, 'body_class' => 'page-app']);
?>
<section class="app wrap">
  <?= app_nav('shops') ?>
  <?php if ($shop): ?><?= shop_nav($shop, 'settings') ?><?php else: ?><p><a href="/shops">← <?= e(t('nav_tab_shops')) ?></a></p><?php endif; ?>
  <div class="app-head"><h1><?= e($shop ? t('sh_settings') : t('sh_new')) ?></h1></div>
  <?php if ($err): ?><div class="flash flash-error" role="alert" style="margin-bottom:16px"><?= error_html($err) ?></div><?php endif; ?>

  <form method="post" enctype="multipart/form-data" novalidate>
    <?= csrf_field() ?><?php if ($shop): ?><input type="hidden" name="id" value="<?= (int) $shop['id'] ?>"><?php endif; ?>
    <div class="qr-page" style="align-items:start">
      <div class="card">
        <h3 style="margin-bottom:14px"><?= e(t('sh_basics')) ?></h3>
        <div class="field"><label for="name"><?= e(t('sh_f_shopname')) ?></label><input class="input" id="name" name="name" maxlength="80" value="<?= $v('name') ?>" required></div>
        <div class="field"><label for="slug"><?= e(t('fm_f_slug')) ?></label><div class="inline-prefix"><span><?= e(current_domain()) ?>/</span><input id="handle" name="slug" maxlength="30" value="<?= $v('slug') ?>" placeholder="my-shop" pattern="[a-z0-9-]{3,30}" required></div></div>
        <?php if (count($domains) > 1): ?><div class="field"><span class="label"><?= e(t('gen_domain')) ?></span><div class="seg"><?php $cur = (string) ($_POST['domain'] ?? ($shop['domain'] ?? current_domain())); foreach ($domains as $d): ?><input type="radio" name="domain" id="dom-<?= e($d) ?>" value="<?= e($d) ?>" <?= $d === $cur ? 'checked' : '' ?>><label for="dom-<?= e($d) ?>"><?= e($d) ?></label><?php endforeach; ?></div></div><?php endif; ?>
        <div class="field"><label for="tagline"><?= e(t('sh_f_tagline')) ?></label><input class="input" id="tagline" name="tagline" maxlength="160" value="<?= $v('tagline') ?>"></div>
        <div class="field"><label for="about"><?= e(t('sh_f_about')) ?> <small><?= e(t('gen_optional')) ?></small></label><textarea class="input" id="about" name="about" maxlength="1000" style="min-height:70px"><?= $v('about') ?></textarea></div>
        <div class="field"><label for="phone"><?= e(t('sh_f_whatsapp')) ?></label><div class="phone-row"><select class="select" name="cc"><?= country_options(isset($_POST['cc']) ? $selIso : ($shop ? $selIso : default_country())) ?></select><input class="input" id="phone" name="phone" type="tel" inputmode="numeric" value="<?= e((string) ($_POST['phone'] ?? $national)) ?>" required data-phone></div></div>
        <div class="field"><label for="currency"><?= e(t('sh_f_currency')) ?></label><select class="select" id="currency" name="currency" style="width:160px"><?php foreach ($currencies as $c): ?><option <?= ($_POST['currency'] ?? ($shop['currency'] ?? 'MYR')) === $c ? 'selected' : '' ?>><?= $c ?></option><?php endforeach; ?></select></div>
        <div class="field"><label for="logo_file"><?= e(t('sh_f_logo')) ?></label><input class="input" type="file" id="logo_file" name="logo_file" accept="image/*"><?php if (!empty($shop['logo'])): ?><p class="hint"><img src="<?= e($shop['logo']) ?>" alt="" style="height:48px;display:inline-block;vertical-align:middle;border-radius:8px"> <label><input type="checkbox" name="remove_logo"> <?= e(t('sh_remove')) ?></label></p><?php endif; ?></div>
        <div class="field"><label for="cover_file"><?= e(t('sh_f_cover')) ?></label><input class="input" type="file" id="cover_file" name="cover_file" accept="image/*"><?php if (!empty($shop['cover'])): ?><p class="hint"><img src="<?= e($shop['cover']) ?>" alt="" style="height:48px;display:inline-block;vertical-align:middle;border-radius:8px"> <label><input type="checkbox" name="remove_cover"> <?= e(t('sh_remove')) ?></label></p><?php endif; ?></div>
        <div class="field"><label for="accent"><?= e(t('sh_f_accent')) ?></label><input type="color" id="accent" name="accent" value="<?= $v('accent', '#0b3d2e') ?>" style="width:60px;height:36px;border:1px solid var(--line-2);border-radius:8px;padding:2px;background:#fff"></div>
        <div class="field"><label for="hours_text"><?= e(t('sh_f_hours')) ?> <small><?= e(t('gen_optional')) ?></small></label><input class="input" id="hours_text" name="hours_text" maxlength="120" value="<?= $v('hours_text') ?>" placeholder="<?= e(t('sh_f_hours_ph')) ?>"></div>
        <div class="field"><span class="label"><?= e(t('sh_f_social')) ?> <small><?= e(t('gen_optional')) ?></small></span>
          <input class="input" name="instagram" type="url" value="<?= $v('instagram') ?>" placeholder="https://instagram.com/…" style="margin-bottom:6px"><input class="input" name="facebook" type="url" value="<?= $v('facebook') ?>" placeholder="https://facebook.com/…" style="margin-bottom:6px"><input class="input" name="tiktok" type="url" value="<?= $v('tiktok') ?>" placeholder="https://tiktok.com/@…"></div>
        <div class="field"><label class="switch"><input type="checkbox" name="is_open" <?= ($_POST['is_open'] ?? ($shop['is_open'] ?? 1)) ? 'checked' : '' ?>> <?= e(t('sh_f_open')) ?></label><p class="hint"><?= e(t('sh_f_open_hint')) ?></p></div>
        <div class="field"><label class="switch"><input type="checkbox" name="active" <?= ($_POST['active'] ?? ($shop['active'] ?? 1)) ? 'checked' : '' ?>> <?= e(t('link_active')) ?></label></div>
      </div>

      <div>
        <div class="card">
          <h3 style="margin-bottom:6px"><?= e(t('sh_checkout')) ?></h3>
          <p class="muted small"><?= e(t('sh_checkout_hint')) ?></p>
          <?php foreach (SHOP_FIELDS as $f): ?>
          <div class="field" style="display:flex;align-items:center;gap:12px;margin-bottom:10px"><span class="label" style="margin:0;min-width:110px"><?= e(t('sh_f_' . $f)) ?></span>
            <?php if ($f === 'name'): ?><span class="badge"><?= e(t('sh_req')) ?></span><?php else: ?>
            <div class="seg"><?php foreach (['req' => t('sh_req'), 'on' => t('sh_on'), 'off' => t('sh_off')] as $k => $lab): ?><input type="radio" name="field_<?= $f ?>" id="f-<?= $f ?>-<?= $k ?>" value="<?= $k ?>" <?= ($_POST['field_' . $f] ?? $fields[$f]) === $k ? 'checked' : '' ?>><label for="f-<?= $f ?>-<?= $k ?>" style="padding:6px 10px;font-size:13px"><?= e($lab) ?></label><?php endforeach; ?></div>
            <?php endif; ?></div>
          <?php endforeach; ?>
          <div class="field" style="margin-top:14px"><label for="payment_note"><?= e(t('sh_f_payment')) ?></label><textarea class="input" id="payment_note" name="payment_note" maxlength="300" style="min-height:60px" placeholder="<?= e(t('sh_f_payment_ph')) ?>"><?= $v('payment_note') ?></textarea></div>
        </div>

        <div class="card">
          <h3 style="margin-bottom:6px"><?= e(t('sh_shipping')) ?></h3>
          <p class="muted small"><?= e(t('sh_shipping_hint')) ?></p>
          <div id="ship-rows">
            <?php foreach (array_pad($shipping, 3, ['name' => '', 'price' => 0, 'note' => '']) as $i => $m): ?>
            <div style="display:grid;grid-template-columns:1.2fr .6fr 1fr;gap:6px;margin-bottom:6px"><input class="input" name="ship_name[]" value="<?= e((string) $m['name']) ?>" placeholder="<?= e(t('sh_ship_name_ph')) ?>" maxlength="60"><input class="input" name="ship_price[]" value="<?= $m['name'] !== '' ? e(number_format(((int) $m['price']) / 100, 2, '.', '')) : '' ?>" placeholder="0.00" inputmode="decimal"><input class="input" name="ship_note[]" value="<?= e((string) $m['note']) ?>" placeholder="<?= e(t('sh_ship_note_ph')) ?>" maxlength="120"></div>
            <?php endforeach; ?>
          </div>
        </div>

      </div>
    </div>
    <div class="form-actions" style="margin-top:18px">
      <button class="btn btn-primary btn-lg" type="submit"><?= e(t('link_save')) ?></button><a class="btn btn-ghost" href="/shops">←</a>
      <?php if ($shop): ?><a class="btn btn-ghost" href="/coupons?shop=<?= (int) $shop['id'] ?>"><?= e(t('sh_coupons')) ?> →</a><button class="btn btn-sm btn-danger right" type="submit" name="delete" value="1" onclick="return confirm(<?= e(json_encode(t('sh_delete_conf'))) ?>)"><?= e(t('sh_delete')) ?></button><?php endif; ?>
    </div>
  </form>
</section>
<?php page_end();
