<?php
declare(strict_types=1);
/** /categories?shop=ID — manage categories (name, description, image, order). */
require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/layout.php';
require __DIR__ . '/lib/shops.php';
require __DIR__ . '/lib/shop_nav.php';
require __DIR__ . '/lib/app_nav.php';

$u = require_login();
$shop = require_shop($u);
$sid = (int) $shop['id'];
$err = null;
$editing = null;

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
    $act = (string) ($_POST['act'] ?? '');
    $cid = (int) ($_POST['cid'] ?? 0);
    try {
        if ($act === 'delete') {
            db_exec('UPDATE products SET category_id = NULL WHERE category_id = ? AND shop_id = ?', [$cid, $sid]);
            db_exec('DELETE FROM categories WHERE id = ? AND shop_id = ?', [$cid, $sid]);
            flash('ok', t('sh_c_deleted'));
            redirect('/categories?shop=' . $sid);
        }
        if ($act === 'move') {
            $ids = array_map(fn($c) => (int) $c['id'], shop_categories($sid));
            $i = array_search($cid, $ids, true);
            $j = $i + ((string) ($_POST['dir'] ?? '') === 'up' ? -1 : 1);
            if ($i !== false && isset($ids[$j])) {
                [$ids[$i], $ids[$j]] = [$ids[$j], $ids[$i]];
                foreach ($ids as $pos => $x) {
                    db_exec('UPDATE categories SET sort = ? WHERE id = ?', [$pos, $x]);
                }
            }
            redirect('/categories?shop=' . $sid);
        }
        if ($act === 'save') {
            $name = mb_substr(trim((string) ($_POST['name'] ?? '')), 0, 60);
            if ($name === '') {
                throw new InvalidArgumentException(t('sh_c_err_name'));
            }
            $existing = $cid ? db_row('SELECT * FROM categories WHERE id = ? AND shop_id = ?', [$cid, $sid]) : null;
            $image = $existing['image'] ?? '';
            if (!empty($_FILES['image_file']['name'])) {
                $image = store_image($_FILES['image_file'], 'c' . $sid) ?: $image;
            }
            if (isset($_POST['remove_image'])) {
                $image = '';
            }
            $desc = mb_substr(trim((string) ($_POST['description'] ?? '')), 0, 160);
            if ($existing) {
                db_exec('UPDATE categories SET name = ?, description = ?, image = ? WHERE id = ?', [$name, $desc, $image, $cid]);
            } else {
                $sort = (int) db_value('SELECT COALESCE(MAX(sort), -1) + 1 FROM categories WHERE shop_id = ?', [$sid]);
                db_insert('INSERT INTO categories (shop_id, name, description, image, sort, created_at) VALUES (?, ?, ?, ?, ?, ?)', [$sid, $name, $desc, $image, $sort, now()]);
            }
            flash('ok', t('sh_c_saved'));
            redirect('/categories?shop=' . $sid);
        }
    } catch (InvalidArgumentException $e) {
        $err = $e->getMessage();
        $editing = $_POST + ['id' => $cid];
    } catch (Throwable $e) {
        error_log('category save: ' . $e->getMessage());
        $err = t('err_generic');
    }
}

$cats = shop_categories($sid);
$counts = [];
foreach (db_rows('SELECT category_id, COUNT(*) AS n FROM products WHERE shop_id = ? GROUP BY category_id', [$sid]) as $r) {
    $counts[(int) $r['category_id']] = (int) $r['n'];
}
if (!$editing && isset($_GET['edit'])) {
    foreach ($cats as $c) {
        if ((int) $c['id'] === (int) $_GET['edit']) {
            $editing = $c;
        }
    }
}
$showForm = $editing !== null || isset($_GET['new']) || !$cats;
$ev = fn(string $k, string $def = '') => e((string) ($editing[$k] ?? $def));

page_start(['title' => t('sh_nav_categories') . ' · ' . $shop['name'], 'noindex' => true, 'body_class' => 'page-app']);
?>
<section class="app wrap">
  <?= app_nav('shops') ?>
  <?= shop_nav($shop, 'categories') ?>
  <div class="app-head"><h1><?= e(t('sh_nav_categories')) ?></h1><a class="btn btn-primary" href="/categories?shop=<?= $sid ?>&new=1">+ <?= e(t('sh_c_add')) ?></a></div>
  <p class="muted" style="margin-bottom:16px"><?= e(t('sh_c_lead')) ?></p>
  <?php if ($err): ?><div class="flash flash-error" role="alert" style="margin-bottom:16px"><?= e($err) ?></div><?php endif; ?>
  <div class="qr-page" style="align-items:start">
    <div>
      <?php if (!$cats): ?><div class="empty"><h2><?= e(t('sh_c_empty')) ?></h2><p class="muted"><?= e(t('sh_c_empty_d')) ?></p></div><?php else: ?>
      <div class="link-list">
        <?php foreach ($cats as $i => $c): ?>
        <article class="link-card" style="grid-template-columns:auto 1fr auto">
          <div style="width:56px;height:56px;border-radius:10px;background:var(--paper-2);overflow:hidden"><?php if ($c['image'] !== ''): ?><img src="<?= e($c['image']) ?>" alt="" style="width:100%;height:100%;object-fit:cover"><?php endif; ?></div>
          <div><div class="code" style="font-family:var(--sans);font-size:16px"><?= e($c['name']) ?> <span class="badge"><?= $counts[(int) $c['id']] ?? 0 ?> <?= e(t('sh_products')) ?></span></div><div class="sub"><?= e($c['description']) ?></div></div>
          <div class="actions" style="grid-column:auto;justify-content:flex-end">
            <a class="btn btn-sm btn-ghost" href="/categories?shop=<?= $sid ?>&edit=<?= (int) $c['id'] ?>"><?= e(t('dash_edit')) ?></a>
            <form method="post" style="display:inline"><?= csrf_field() ?><input type="hidden" name="shop" value="<?= $sid ?>"><input type="hidden" name="cid" value="<?= (int) $c['id'] ?>"><input type="hidden" name="act" value="move"><button class="btn btn-sm btn-ghost" name="dir" value="up" <?= $i === 0 ? 'disabled' : '' ?>>↑</button><button class="btn btn-sm btn-ghost" name="dir" value="down" <?= $i === count($cats) - 1 ? 'disabled' : '' ?>>↓</button></form>
            <form method="post" style="display:inline" data-confirm="<?= e(t('sh_c_delete_conf')) ?>"><?= csrf_field() ?><input type="hidden" name="shop" value="<?= $sid ?>"><input type="hidden" name="cid" value="<?= (int) $c['id'] ?>"><button class="btn btn-sm btn-danger" name="act" value="delete">✕</button></form>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
    <?php if ($showForm): ?>
    <form method="post" class="card" enctype="multipart/form-data" novalidate>
      <?= csrf_field() ?><input type="hidden" name="act" value="save"><input type="hidden" name="shop" value="<?= $sid ?>"><input type="hidden" name="cid" value="<?= (int) ($editing['id'] ?? 0) ?>">
      <h3 style="margin-bottom:14px"><?= e(!empty($editing['id']) ? t('sh_c_edit') : t('sh_c_add')) ?></h3>
      <div class="field"><label for="cname"><?= e(t('sh_c_name')) ?></label><input class="input" id="cname" name="name" maxlength="60" value="<?= $ev('name') ?>" placeholder="<?= e(t('sh_p_category_ph')) ?>" required></div>
      <div class="field"><label for="cdesc"><?= e(t('sh_c_desc')) ?> <small><?= e(t('gen_optional')) ?></small></label><input class="input" id="cdesc" name="description" maxlength="160" value="<?= $ev('description') ?>" placeholder="<?= e(t('sh_c_desc_ph')) ?>"></div>
      <div class="field"><label for="cimg"><?= e(t('sh_p_image')) ?> <small><?= e(t('gen_optional')) ?></small></label><input class="input" type="file" id="cimg" name="image_file" accept="image/*">
        <?php if (!empty($editing['image'])): ?><p class="hint"><img src="<?= e((string) $editing['image']) ?>" alt="" style="height:56px;border-radius:8px;display:inline-block;vertical-align:middle"> <label><input type="checkbox" name="remove_image"> <?= e(t('sh_remove')) ?></label></p><?php endif; ?></div>
      <div class="form-actions"><button class="btn btn-primary btn-lg" type="submit"><?= e(t('link_save')) ?></button><?php if ($cats): ?><a class="btn btn-ghost" href="/categories?shop=<?= $sid ?>">←</a><?php endif; ?></div>
    </form>
    <?php endif; ?>
  </div>
</section>
<?php page_end();
