<?php
declare(strict_types=1);
/** /products?shop=ID — product list + add/edit form. */
require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/layout.php';
require __DIR__ . '/lib/shops.php';
require __DIR__ . '/lib/app_nav.php';

$u = require_login();
$sid = (int) ($_GET['shop'] ?? ($_POST['shop'] ?? 0));
$shop = db_row('SELECT * FROM shops WHERE id = ? AND user_id = ?', [$sid, (int) $u['id']]);
if (!$shop) {
    http_response_code(404);
    exit('Not found');
}
$err = null;
$editing = null;

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
    $act = (string) ($_POST['act'] ?? '');
    $pid = (int) ($_POST['pid'] ?? 0);
    try {
        if ($act === 'delete') {
            db_exec('DELETE FROM products WHERE id = ? AND shop_id = ?', [$pid, $sid]);
            flash('ok', t('sh_p_deleted'));
            redirect('/products?shop=' . $sid);
        }
        if ($act === 'toggle') {
            db_exec('UPDATE products SET active = 1 - active WHERE id = ? AND shop_id = ?', [$pid, $sid]);
            redirect('/products?shop=' . $sid);
        }
        if ($act === 'move') {
            $ids = array_map(fn($p) => (int) $p['id'], shop_products($sid));
            $i = array_search($pid, $ids, true);
            $j = $i + ((string) ($_POST['dir'] ?? '') === 'up' ? -1 : 1);
            if ($i !== false && isset($ids[$j])) {
                [$ids[$i], $ids[$j]] = [$ids[$j], $ids[$i]];
                foreach ($ids as $pos => $x) {
                    db_exec('UPDATE products SET sort = ? WHERE id = ?', [$pos, $x]);
                }
            }
            redirect('/products?shop=' . $sid);
        }
        if ($act === 'save') {
            $name = mb_substr(trim((string) ($_POST['name'] ?? '')), 0, 100);
            if ($name === '') {
                throw new InvalidArgumentException(t('sh_err_pname'));
            }
            $existing = $pid ? db_row('SELECT * FROM products WHERE id = ? AND shop_id = ?', [$pid, $sid]) : null;
            $image = $existing['image'] ?? '';
            if (!empty($_FILES['image_file']['name'])) {
                $image = store_image($_FILES['image_file'], 'p' . $sid) ?: $image;
            } elseif (safe_url((string) ($_POST['image'] ?? '')) !== '') {
                $image = safe_url((string) $_POST['image']);
            }
            if (isset($_POST['remove_image'])) {
                $image = '';
            }
            $row = [
                'name' => $name, 'description' => mb_substr(trim((string) ($_POST['description'] ?? '')), 0, 1000),
                'price' => to_sen((string) ($_POST['price'] ?? '0')), 'image' => $image,
                'category' => mb_substr(trim((string) ($_POST['category'] ?? '')), 0, 60),
                'variants' => json_encode(parse_variants((string) ($_POST['variants'] ?? '')), JSON_UNESCAPED_UNICODE),
                'active' => isset($_POST['active']) ? 1 : 0,
            ];
            if ($existing) {
                $sets = implode(', ', array_map(fn($k) => "$k = ?", array_keys($row)));
                db_exec("UPDATE products SET $sets WHERE id = ?", [...array_values($row), $pid]);
            } else {
                $sort = (int) db_value('SELECT COALESCE(MAX(sort), -1) + 1 FROM products WHERE shop_id = ?', [$sid]);
                $cols = implode(', ', array_keys($row));
                $marks = implode(', ', array_fill(0, count($row), '?'));
                db_insert("INSERT INTO products (shop_id, $cols, sort, created_at) VALUES (?, $marks, ?, ?)", [$sid, ...array_values($row), $sort, now()]);
            }
            flash('ok', t('sh_p_saved'));
            redirect('/products?shop=' . $sid);
        }
    } catch (InvalidArgumentException $e) {
        $err = $e->getMessage();
        $editing = $_POST + ['id' => $pid];
    } catch (Throwable $e) {
        error_log('product save: ' . $e->getMessage());
        $err = t('err_generic');
    }
}

$products = shop_products($sid);
if (!$editing && isset($_GET['edit'])) {
    foreach ($products as $p) {
        if ((int) $p['id'] === (int) $_GET['edit']) {
            $editing = $p;
            $editing['price'] = number_format(((int) $p['price']) / 100, 2, '.', '');
            $editing['variants'] = variants_to_text(product_variants($p));
        }
    }
}
$showForm = $editing !== null || isset($_GET['new']) || !$products;
$ev = fn(string $k, string $def = '') => e((string) ($editing[$k] ?? $def));
$cats = array_values(array_unique(array_filter(array_map(fn($p) => $p['category'], $products))));

page_start(['title' => t('sh_products') . ' · ' . $shop['name'], 'noindex' => true, 'body_class' => 'page-app']);
?>
<section class="app wrap">
  <?= app_nav('shops') ?>
  <p><a href="/shops">← <?= e(t('nav_tab_shops')) ?></a> · <a href="/shop?id=<?= $sid ?>"><?= e(t('sh_settings')) ?></a> · <a href="/orders?shop=<?= $sid ?>"><?= e(t('sh_orders')) ?></a> · <a href="<?= e(shop_url($shop)) ?>?preview=1" target="_blank" rel="noopener"><?= e(t('fm_preview')) ?> ↗</a></p>
  <div class="app-head"><h1><?= e($shop['name']) ?> <span class="muted" style="font-size:.6em">· <?= e(t('sh_products')) ?></span></h1><a class="btn btn-primary" href="/products?shop=<?= $sid ?>&new=1">+ <?= e(t('sh_p_add')) ?></a></div>
  <?php if ($err): ?><div class="flash flash-error" role="alert" style="margin-bottom:16px"><?= e($err) ?></div><?php endif; ?>

  <div class="qr-page" style="align-items:start">
    <div>
      <?php if (!$products): ?><div class="empty"><h2><?= e(t('sh_p_empty')) ?></h2><p class="muted"><?= e(t('sh_p_empty_d')) ?></p></div><?php else: ?>
      <div class="link-list">
        <?php foreach ($products as $i => $p): ?>
        <article class="link-card" style="grid-template-columns:auto 1fr auto">
          <div style="width:56px;height:56px;border-radius:10px;background:var(--paper-2);overflow:hidden"><?php if ($p['image'] !== ''): ?><img src="<?= e($p['image']) ?>" alt="" style="width:100%;height:100%;object-fit:cover"><?php endif; ?></div>
          <div><div class="code" style="font-family:var(--sans);font-size:16px"><?= e($p['name']) ?> <?= (int) $p['active'] ? '' : '<span class="badge badge-off">' . e(t('sh_hidden')) . '</span>' ?></div>
            <div class="sub"><b><?= e(money((int) $p['price'], $shop['currency'])) ?></b><?php if ($p['category'] !== ''): ?><span><?= e($p['category']) ?></span><?php endif; ?><?php if (product_variants($p)): ?><span><?= count(product_variants($p)) ?> <?= e(t('sh_variants')) ?></span><?php endif; ?></div></div>
          <div class="actions" style="grid-column:auto;justify-content:flex-end">
            <a class="btn btn-sm btn-ghost" href="/products?shop=<?= $sid ?>&edit=<?= (int) $p['id'] ?>"><?= e(t('dash_edit')) ?></a>
            <form method="post" style="display:inline"><?= csrf_field() ?><input type="hidden" name="shop" value="<?= $sid ?>"><input type="hidden" name="pid" value="<?= (int) $p['id'] ?>"><button class="btn btn-sm btn-ghost" name="act" value="toggle"><?= (int) $p['active'] ? e(t('sh_hide')) : e(t('sh_show')) ?></button> <button class="btn btn-sm btn-ghost" name="act" value="move" onclick="this.form.dir.value='up'" <?= $i === 0 ? 'disabled' : '' ?>>↑</button><button class="btn btn-sm btn-ghost" name="act" value="move" onclick="this.form.dir.value='down'" <?= $i === count($products) - 1 ? 'disabled' : '' ?>>↓</button><input type="hidden" name="dir" value="down"></form>
            <form method="post" style="display:inline" data-confirm="<?= e(t('sh_p_delete_conf')) ?>"><?= csrf_field() ?><input type="hidden" name="shop" value="<?= $sid ?>"><input type="hidden" name="pid" value="<?= (int) $p['id'] ?>"><button class="btn btn-sm btn-danger" name="act" value="delete">✕</button></form>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <?php if ($showForm): ?>
    <form method="post" class="card" enctype="multipart/form-data" novalidate>
      <?= csrf_field() ?><input type="hidden" name="act" value="save"><input type="hidden" name="shop" value="<?= $sid ?>"><input type="hidden" name="pid" value="<?= (int) ($editing['id'] ?? 0) ?>">
      <h3 style="margin-bottom:14px"><?= e(!empty($editing['id']) ? t('sh_p_edit') : t('sh_p_add')) ?></h3>
      <div class="field"><label for="pname"><?= e(t('sh_p_name')) ?></label><input class="input" id="pname" name="name" maxlength="100" value="<?= $ev('name') ?>" required></div>
      <div class="field" style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
        <div><label for="price"><?= e(t('sh_p_price')) ?> (<?= e($shop['currency']) ?>)</label><input class="input" id="price" name="price" inputmode="decimal" value="<?= $ev('price') ?>" placeholder="0.00"></div>
        <div><label for="category"><?= e(t('sh_p_category')) ?></label><input class="input" id="category" name="category" list="cats" maxlength="60" value="<?= $ev('category') ?>" placeholder="<?= e(t('sh_p_category_ph')) ?>"><datalist id="cats"><?php foreach ($cats as $c): ?><option value="<?= e($c) ?>"><?php endforeach; ?></datalist></div>
      </div>
      <div class="field"><label for="description"><?= e(t('sh_p_desc')) ?> <small><?= e(t('gen_optional')) ?></small></label><textarea class="input" id="description" name="description" maxlength="1000" style="min-height:70px"><?= $ev('description') ?></textarea></div>
      <div class="field"><label for="image_file"><?= e(t('sh_p_image')) ?></label><input class="input" type="file" id="image_file" name="image_file" accept="image/*"><p class="hint"><?= e(t('sh_p_image_hint')) ?></p>
        <?php if (!empty($editing['image'])): ?><p class="hint"><img src="<?= e((string) $editing['image']) ?>" alt="" style="height:56px;border-radius:8px;display:inline-block;vertical-align:middle"> <label><input type="checkbox" name="remove_image"> <?= e(t('sh_remove')) ?></label></p><?php endif; ?></div>
      <div class="field"><label for="variants"><?= e(t('sh_p_variants')) ?> <small><?= e(t('gen_optional')) ?></small></label><textarea class="input mono" id="variants" name="variants" style="min-height:80px;font-size:13.5px" placeholder="<?= e(t('sh_p_variants_ph')) ?>"><?= $ev('variants') ?></textarea><p class="hint"><?= e(t('sh_p_variants_hint')) ?></p></div>
      <div class="field"><label class="switch"><input type="checkbox" name="active" <?= !isset($editing['id']) || !empty($editing['active']) ? 'checked' : '' ?>> <?= e(t('sh_p_visible')) ?></label></div>
      <div class="form-actions"><button class="btn btn-primary btn-lg" type="submit"><?= e(t('link_save')) ?></button><?php if ($products): ?><a class="btn btn-ghost" href="/products?shop=<?= $sid ?>">←</a><?php endif; ?></div>
    </form>
    <?php endif; ?>
  </div>
</section>
<?php page_end();
