<?php
declare(strict_types=1);
require dirname(__DIR__) . '/lib/bootstrap.php';
require dirname(__DIR__) . '/lib/layout.php';

$me = require_admin();
$tab = (string) ($_GET['tab'] ?? 'users');
$plans = array_keys(cfg('plans'));                       // all priced items (for pricing tab)
$userPlans = array_values(array_diff($plans, ['form', 'shop'])); // account-level plans only

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
    $act = (string) ($_POST['act'] ?? '');
    try {
        if ($act === 'plan') {
            $uid = (int) $_POST['uid'];
            $plan = in_array($_POST['plan'] ?? '', $userPlans, true) ? $_POST['plan'] : 'free';
            $until = trim((string) ($_POST['until'] ?? ''));
            $until = $plan === 'free' || $until === '' ? null : $until . ' 23:59:59';
            db_exec('UPDATE users SET plan = ?, plan_until = ? WHERE id = ?', [$plan, $until, $uid]);
            flash('ok', 'Plan updated.');
        } elseif ($act === 'role') {
            $uid = (int) $_POST['uid'];
            if ($uid !== (int) $me['id']) {
                db_exec('UPDATE users SET role = ? WHERE id = ?', [($_POST['role'] ?? '') === 'admin' ? 'admin' : 'user', $uid]);
                flash('ok', 'Role updated.');
            }
        } elseif ($act === 'link_toggle') {
            db_exec('UPDATE links SET active = 1 - active WHERE id = ?', [(int) $_POST['lid']]);
            flash('ok', 'Link toggled.');
        } elseif ($act === 'link_delete') {
            db_exec('DELETE FROM links WHERE id = ?', [(int) $_POST['lid']]);
            flash('ok', 'Link deleted.');
        } elseif ($act === 'pay_sync') {
            $r = sync_bill((string) ($_POST['bill'] ?? ''));
            flash('ok', $r ? 'Payment confirmed and link upgraded.' : 'Still not paid according to Billplz.');
        } elseif ($act === 'setting') {
            foreach (['announcement', 'blocked_words'] as $k) {
                set_setting($k, trim((string) ($_POST[$k] ?? '')));
            }
            $min = (int) ($_POST['slug_min'] ?? 4);
            set_setting('slug_min', (string) max(3, min(10, $min)));
            flash('ok', 'Settings saved.');
        } elseif ($act === 'ps_add') {
            $price = max(0, (int) ($_POST['price'] ?? 299));
            $n = 0;
            foreach (preg_split('/[\s,]+/', strtolower((string) ($_POST['slugs'] ?? ''))) ?: [] as $w) {
                $w = trim($w);
                if ($w === '' || !preg_match('/^[a-z0-9][a-z0-9-]{1,28}[a-z0-9]$/', $w)) {
                    continue;
                }
                if (!db_value('SELECT 1 FROM premium_slugs WHERE slug = ?', [$w])) {
                    db_exec('INSERT INTO premium_slugs (slug, price_myr, created_at) VALUES (?, ?, ?)', [$w, $price, now()]);
                    $n++;
                }
            }
            flash('ok', "$n premium handle(s) added.");
        } elseif ($act === 'ps_update') {
            $slug = strtolower((string) ($_POST['slug'] ?? ''));
            $email = strtolower(trim((string) ($_POST['email'] ?? '')));
            $uid = null;
            if ($email !== '') {
                $uid = db_value('SELECT id FROM users WHERE email = ?', [$email]);
                if (!$uid) {
                    throw new InvalidArgumentException("No account with email $email");
                }
            }
            db_exec('UPDATE premium_slugs SET price_myr = ?, assigned_user_id = ?, note = ? WHERE slug = ?',
                [max(0, (int) ($_POST['price'] ?? 0)), $uid ? (int) $uid : null, mb_substr(trim((string) ($_POST['note'] ?? '')), 0, 120), $slug]);
            flash('ok', $uid ? "$slug assigned to $email. They can now create a link, form or shop with it." : "$slug updated.");
        } elseif ($act === 'ps_delete') {
            db_exec('DELETE FROM premium_slugs WHERE slug = ?', [strtolower((string) ($_POST['slug'] ?? ''))]);
            flash('ok', 'Premium handle removed (now a normal handle).');
        } elseif ($act === 'item_toggle') {
            $table = in_array($_POST['table'] ?? '', ['forms', 'shops'], true) ? $_POST['table'] : 'forms';
            db_exec("UPDATE $table SET active = 1 - active WHERE id = ?", [(int) $_POST['iid']]);
            flash('ok', 'Updated.');
        } elseif ($act === 'item_delete') {
            $table = in_array($_POST['table'] ?? '', ['forms', 'shops'], true) ? $_POST['table'] : 'forms';
            db_exec("DELETE FROM $table WHERE id = ?", [(int) $_POST['iid']]);
            flash('ok', 'Deleted.');
        } elseif ($act === 'pricing') {
            foreach ($plans as $p) {
                foreach (['usd', 'myr', 'max_links'] as $k) {
                    $v = trim((string) ($_POST["plan_{$p}_{$k}"] ?? ''));
                    if ($v !== '' && (!is_numeric($v) || $v < 0)) {
                        throw new InvalidArgumentException("Invalid value for $p $k");
                    }
                    set_setting("plan.$p.$k", $v === '' ? '' : (string) ($k === 'max_links' ? (int) $v : round((float) $v, 2)));
                }
            }
            flash('ok', 'Pricing saved.');
        }
    } catch (Throwable $e) {
        error_log('admin: ' . $e->getMessage());
        flash('error', 'Error: ' . $e->getMessage());
    }
    redirect('/admin?tab=' . urlencode($tab));
}

$q = trim((string) ($_GET['q'] ?? ''));
$totals = [
    'users'  => (int) db_value('SELECT COUNT(*) FROM users'),
    'links'  => (int) db_value('SELECT COUNT(*) FROM links'),
    'anon'   => (int) db_value('SELECT COUNT(*) FROM links WHERE user_id IS NULL'),
    'clicks' => (int) db_value('SELECT COUNT(*) FROM clicks WHERE is_bot = 0'),
    'today'  => (int) db_value('SELECT COUNT(*) FROM clicks WHERE is_bot = 0 AND created_at >= ?', [gmdate('Y-m-d') . ' 00:00:00']),
];

page_start(['title' => 'Admin', 'noindex' => true, 'body_class' => 'page-app']);
?>
<section class="app wrap">
  <div class="app-head"><h1>Admin</h1><a class="btn btn-ghost btn-sm" href="/dashboard">← Dashboard</a></div>
  <div class="stat-grid" style="grid-template-columns:repeat(5,1fr)">
    <?php foreach (['users' => 'Users', 'links' => 'Links', 'anon' => 'Anonymous links', 'clicks' => 'Human clicks', 'today' => 'Clicks today'] as $k => $l): ?>
    <div class="stat"><b><?= number_format($totals[$k]) ?></b><span><?= $l ?></span></div>
    <?php endforeach; ?>
  </div>
  <div class="tabs">
    <?php foreach (['users' => 'Users', 'links' => 'Links', 'forms' => 'Forms', 'shops' => 'Shops', 'payments' => 'Payments', 'handles' => 'Premium handles', 'settings' => 'Settings'] as $k => $l): ?><a class="<?= $tab === $k ? 'on' : '' ?>" href="?tab=<?= $k ?>"><?= $l ?></a><?php endforeach; ?>
    <form method="get" style="margin-left:auto;display:flex;gap:6px"><input type="hidden" name="tab" value="<?= e($tab) ?>"><input class="input" name="q" value="<?= e($q) ?>" placeholder="Search email / code / phone" style="padding:8px 12px;font-size:14px;width:260px"><button class="btn btn-sm btn-ghost">Search</button></form>
  </div>

  <?php if ($tab === 'users'): ?>
  <?php $users = db_rows('SELECT u.*, (SELECT COUNT(*) FROM links l WHERE l.user_id = u.id) AS n_links FROM users u ' . ($q !== '' ? 'WHERE u.email LIKE ? OR u.name LIKE ? ' : '') . 'ORDER BY u.id DESC LIMIT 200', $q !== '' ? ["%$q%", "%$q%"] : []); ?>
  <div class="table-wrap"><table class="table">
    <tr><th>#</th><th>Email</th><th>Name</th><th>Links</th><th>Verified</th><th>Joined</th><th>Plan</th><th>Role</th></tr>
    <?php foreach ($users as $r): ?>
    <tr>
      <td><?= (int) $r['id'] ?></td><td><?= e($r['email']) ?><?= $r['google_sub'] ? ' <span class="badge">G</span>' : '' ?></td><td><?= e($r['name']) ?></td>
      <td><a href="?tab=links&q=<?= urlencode($r['email']) ?>"><?= (int) $r['n_links'] ?></a></td>
      <td><?= $r['verified_at'] ? '✓' : '—' ?></td><td class="small"><?= e(substr((string) $r['created_at'], 0, 10)) ?></td>
      <td><form method="post" style="display:flex;gap:4px"><?= csrf_field() ?><input type="hidden" name="act" value="plan"><input type="hidden" name="uid" value="<?= (int) $r['id'] ?>">
        <select class="select" name="plan"><?php foreach ($userPlans as $p): ?><option value="<?= $p ?>" <?= $r['plan'] === $p ? 'selected' : '' ?>><?= e(plan_name($p)) ?></option><?php endforeach; ?></select>
        <input class="input" type="date" name="until" value="<?= e(substr((string) ($r['plan_until'] ?? ''), 0, 10)) ?>" title="Plan until"><button class="btn btn-sm btn-ghost">Save</button></form></td>
      <td><?php if ((int) $r['id'] === (int) $me['id']): ?>admin (you)<?php else: ?><form method="post" style="display:flex;gap:4px"><?= csrf_field() ?><input type="hidden" name="act" value="role"><input type="hidden" name="uid" value="<?= (int) $r['id'] ?>">
        <select class="select" name="role"><option value="user" <?= $r['role'] === 'user' ? 'selected' : '' ?>>user</option><option value="admin" <?= $r['role'] === 'admin' ? 'selected' : '' ?>>admin</option></select><button class="btn btn-sm btn-ghost">Save</button></form><?php endif; ?></td>
    </tr>
    <?php endforeach; ?>
  </table></div>

  <?php elseif ($tab === 'links'): ?>
  <?php $links = db_rows('SELECT l.*, u.email FROM links l LEFT JOIN users u ON u.id = l.user_id ' . ($q !== '' ? 'WHERE l.code LIKE ? OR l.phone LIKE ? OR u.email LIKE ? ' : '') . 'ORDER BY l.id DESC LIMIT 200', $q !== '' ? ["%$q%", "%$q%", "%$q%"] : []); ?>
  <div class="table-wrap"><table class="table">
    <tr><th>#</th><th>Link</th><th>Phone</th><th>Message</th><th>Owner</th><th>Clicks</th><th>Created</th><th></th></tr>
    <?php foreach ($links as $r): ?>
    <tr>
      <td><?= (int) $r['id'] ?></td>
      <td class="mono"><a href="<?= e(short_url($r)) ?>" target="_blank" rel="noopener"><?= e(short_display($r)) ?></a> <?= (int) $r['is_custom'] ? '<span class="badge badge-prem">custom</span>' : '' ?> <?= (int) $r['active'] ? '' : '<span class="badge badge-off">off</span>' ?></td>
      <td>+<?= e($r['phone']) ?></td><td class="small" style="max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($r['message']) ?></td>
      <td class="small"><?= $r['email'] ? e($r['email']) : '<i>anonymous</i>' ?></td><td><?= (int) $r['clicks'] ?></td><td class="small"><?= e(substr((string) $r['created_at'], 0, 10)) ?></td>
      <td style="white-space:nowrap"><form method="post" style="display:inline"><?= csrf_field() ?><input type="hidden" name="act" value="link_toggle"><input type="hidden" name="lid" value="<?= (int) $r['id'] ?>"><button class="btn btn-sm btn-ghost"><?= (int) $r['active'] ? 'Pause' : 'Resume' ?></button></form>
        <form method="post" style="display:inline" data-confirm="Delete link <?= e($r['code']) ?>?"><?= csrf_field() ?><input type="hidden" name="act" value="link_delete"><input type="hidden" name="lid" value="<?= (int) $r['id'] ?>"><button class="btn btn-sm btn-danger">Delete</button></form></td>
    </tr>
    <?php endforeach; ?>
  </table></div>

  <?php elseif ($tab === 'forms' || $tab === 'shops'): $tbl = $tab; $label = $tab === 'forms' ? 'title' : 'name'; ?>
  <?php $items = db_rows("SELECT i.*, u.email FROM $tbl i JOIN users u ON u.id = i.user_id " . ($q !== '' ? "WHERE i.slug LIKE ? OR i.$label LIKE ? OR u.email LIKE ? " : '') . 'ORDER BY i.id DESC LIMIT 200', $q !== '' ? ["%$q%", "%$q%", "%$q%"] : []); ?>
  <div class="table-wrap"><table class="table">
    <tr><th>#</th><th>Address</th><th>Name</th><th>Owner</th><th>Phone</th><th>Published until</th><th>Created</th><th></th></tr>
    <?php foreach ($items as $r): ?>
    <tr>
      <td><?= (int) $r['id'] ?></td><td class="mono"><a href="https://<?= e($r['domain'] . '/' . $r['slug']) ?>" target="_blank" rel="noopener"><?= e($r['domain'] . '/' . $r['slug']) ?></a> <?= (int) $r['active'] ? '' : '<span class="badge badge-off">off</span>' ?></td>
      <td><?= e((string) $r[$label]) ?></td><td class="small"><?= e($r['email']) ?></td><td>+<?= e($r['phone']) ?></td>
      <td class="small"><?= e(substr((string) ($r['premium_until'] ?? ''), 0, 10)) ?: '<i>draft</i>' ?></td><td class="small"><?= e(substr((string) $r['created_at'], 0, 10)) ?></td>
      <td style="white-space:nowrap"><form method="post" style="display:inline"><?= csrf_field() ?><input type="hidden" name="act" value="item_toggle"><input type="hidden" name="table" value="<?= $tbl ?>"><input type="hidden" name="iid" value="<?= (int) $r['id'] ?>"><button class="btn btn-sm btn-ghost"><?= (int) $r['active'] ? 'Disable' : 'Enable' ?></button></form>
        <form method="post" style="display:inline" data-confirm="Delete <?= e($r['slug']) ?> and all its data?"><?= csrf_field() ?><input type="hidden" name="act" value="item_delete"><input type="hidden" name="table" value="<?= $tbl ?>"><input type="hidden" name="iid" value="<?= (int) $r['id'] ?>"><button class="btn btn-sm btn-danger">Delete</button></form></td>
    </tr>
    <?php endforeach; ?>
  </table></div>

  <?php elseif ($tab === 'handles'): ?>
  <?php $ps = db_rows('SELECT p.*, u.email FROM premium_slugs p LEFT JOIN users u ON u.id = p.assigned_user_id ' . ($q !== '' ? 'WHERE p.slug LIKE ? OR u.email LIKE ? ' : '') . 'ORDER BY p.assigned_user_id IS NULL, p.slug LIMIT 500', $q !== '' ? ["%$q%", "%$q%"] : []);
        $sold = (int) db_value('SELECT COUNT(*) FROM premium_slugs WHERE assigned_user_id IS NOT NULL'); ?>
  <div class="qr-page" style="align-items:start">
    <div>
      <p class="muted small"><?= count($ps) ?> handles · <?= $sold ?> assigned. A premium handle is refused for everyone except the account it is assigned to. Sell it (one-time fee), then assign it here by email; the buyer still pays the normal yearly plan for the link, form or shop.</p>
      <div class="table-wrap"><table class="table">
        <tr><th>Handle</th><th>Fee (RM)</th><th>Assigned to (email)</th><th>Note</th><th></th></tr>
        <?php foreach ($ps as $r): ?>
        <tr><form method="post"><?= csrf_field() ?><input type="hidden" name="act" value="ps_update"><input type="hidden" name="slug" value="<?= e($r['slug']) ?>">
          <td class="mono"><b><?= e($r['slug']) ?></b></td>
          <td><input class="input" name="price" type="number" min="0" value="<?= (int) $r['price_myr'] ?>" style="width:90px"></td>
          <td><input class="input" name="email" type="email" value="<?= e((string) ($r['email'] ?? '')) ?>" placeholder="— available —" style="width:220px"></td>
          <td><input class="input" name="note" value="<?= e($r['note']) ?>" style="width:160px"></td>
          <td style="white-space:nowrap"><button class="btn btn-sm btn-ghost">Save</button> <button class="btn btn-sm btn-danger" name="act" value="ps_delete" onclick="return confirm('Remove <?= e($r['slug']) ?> from premium handles?')">✕</button></td>
        </form></tr>
        <?php endforeach; ?>
      </table></div>
    </div>
    <form method="post" class="card"><?= csrf_field() ?><input type="hidden" name="act" value="ps_add">
      <h3 style="margin-bottom:10px">Add premium handles</h3>
      <div class="field"><label for="slugs">Words (one per line or comma-separated)</label><textarea class="input mono" id="slugs" name="slugs" style="min-height:140px;font-size:13.5px" placeholder="bakery&#10;clinic&#10;petshop"></textarea></div>
      <div class="field"><label for="ps_price">One-time fee (RM)</label><input class="input" id="ps_price" name="price" type="number" min="0" value="299" style="width:140px"></div>
      <button class="btn btn-primary">Add</button>
    </form>
  </div>

  <?php elseif ($tab === 'payments'): ?>
  <?php $pays = db_rows('SELECT p.*, u.email, l.code, l.domain FROM payments p JOIN users u ON u.id = p.user_id LEFT JOIN links l ON l.id = p.link_id ' . ($q !== '' ? 'WHERE u.email LIKE ? OR p.bill_id LIKE ? OR l.code LIKE ? ' : '') . 'ORDER BY p.id DESC LIMIT 200', $q !== '' ? ["%$q%", "%$q%", "%$q%"] : []);
        $revenue = (int) db_value('SELECT COALESCE(SUM(amount),0) FROM payments WHERE status = ?', ['paid']); ?>
  <p class="muted small">Paid total: <b>RM<?= number_format($revenue / 100, 2) ?></b></p>
  <div class="table-wrap"><table class="table">
    <tr><th>#</th><th>Bill</th><th>User</th><th>Link</th><th>Amount</th><th>Years</th><th>Handle</th><th>Status</th><th>Created</th><th>Paid</th><th></th></tr>
    <?php foreach ($pays as $r): ?>
    <tr>
      <td><?= (int) $r['id'] ?></td><td class="mono small"><?= e($r['bill_id']) ?></td><td class="small"><?= e($r['email']) ?></td>
      <td class="mono"><?= $r['code'] ? e($r['domain'] . '/' . $r['code']) : '—' ?></td><td>RM<?= number_format($r['amount'] / 100, 2) ?></td><td><?= (int) $r['years'] ?></td>
      <td class="mono small"><?= e($r['handle']) ?></td><td><span class="badge <?= $r['status'] === 'paid' ? 'badge-prem' : ($r['status'] === 'failed' ? 'badge-off' : '') ?>"><?= e($r['status']) ?></span></td>
      <td class="small"><?= e(substr((string) $r['created_at'], 0, 16)) ?></td><td class="small"><?= e(substr((string) ($r['paid_at'] ?? ''), 0, 16)) ?></td>
      <td><?php if ($r['status'] === 'pending'): ?><form method="post" style="display:inline"><?= csrf_field() ?><input type="hidden" name="act" value="pay_sync"><input type="hidden" name="bill" value="<?= e($r['bill_id']) ?>"><button class="btn btn-sm btn-ghost">Re-check</button></form><?php endif; ?></td>
    </tr>
    <?php endforeach; ?>
  </table></div>

  <?php else: ?>
  <form method="post" class="card form-narrow"><?= csrf_field() ?><input type="hidden" name="act" value="pricing">
    <h3 style="margin-bottom:6px"><?= e(t('admin_pricing')) ?></h3>
    <p class="muted small"><?= e(t('admin_pricing_hint')) ?> Empty = use config.php default.</p>
    <div class="table-wrap"><table class="table">
      <tr><th>Plan</th><th>USD / year (wsap.me)</th><th>RM / year (wsap.my, charged)</th><th>Max links (0 = unlimited)</th></tr>
      <?php foreach ($plans as $p): $pc = plan_cfg($p); ?>
      <tr><td><b><?= e(plan_name($p)) ?></b></td>
        <td><input class="input" name="plan_<?= $p ?>_usd" type="number" step="0.01" min="0" value="<?= e(setting("plan.$p.usd")) ?>" placeholder="<?= e((string) cfg("plans.$p.usd")) ?>"></td>
        <td><input class="input" name="plan_<?= $p ?>_myr" type="number" step="0.01" min="0" value="<?= e(setting("plan.$p.myr")) ?>" placeholder="<?= e((string) cfg("plans.$p.myr")) ?>"></td>
        <td><input class="input" name="plan_<?= $p ?>_max_links" type="number" step="1" min="0" value="<?= e(setting("plan.$p.max_links")) ?>" placeholder="<?= e((string) cfg("plans.$p.max_links")) ?>"></td></tr>
      <?php endforeach; ?>
    </table></div>
    <p class="muted small" style="margin-top:10px">Currently in effect: Premium <?= e(price('premium', 'USD')) ?> / <?= e(price('premium', 'MYR')) ?> a year · Business <?= e(price('business', 'USD')) ?> / <?= e(price('business', 'MYR')) ?> a year.</p>
    <button class="btn btn-primary">Save pricing</button>
  </form>
  <form method="post" class="card form-narrow" style="margin-top:18px"><?= csrf_field() ?><input type="hidden" name="act" value="setting">
    <div class="field"><label for="announcement">Announcement bar (empty = hidden)</label><input class="input" id="announcement" name="announcement" value="<?= e(setting('announcement')) ?>"></div>
    <h3 style="margin:18px 0 6px"><?= e(t('admin_handles')) ?></h3>
    <div class="field"><label for="slug_min"><?= e(t('admin_slug_min')) ?></label><input class="input" id="slug_min" name="slug_min" type="number" min="3" max="10" value="<?= e(setting('slug_min') ?: (string) cfg('slug_min')) ?>" style="width:120px"></div>
    <div class="field"><label for="blocked_words"><?= e(t('admin_blocked')) ?></label><textarea class="input mono" id="blocked_words" name="blocked_words" style="min-height:120px;font-size:13.5px" placeholder="brandname&#10;*badword"><?= e(setting('blocked_words')) ?></textarea>
      <p class="hint">Built-in list already covers major brands, banks, government bodies and EN/BM profanity (lib/blocklist.php).</p></div>
    <p class="muted small">Secrets (DB, Brevo, Google, support WhatsApp) live in <code>config.local.php</code> on the server, not here.</p>
    <button class="btn btn-primary">Save</button>
  </form>
  <?php endif; ?>
</section>
<?php page_end();
