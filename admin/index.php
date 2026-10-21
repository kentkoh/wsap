<?php
declare(strict_types=1);
require dirname(__DIR__) . '/lib/bootstrap.php';
require dirname(__DIR__) . '/lib/layout.php';

$me = require_admin();
$tab = (string) ($_GET['tab'] ?? 'users');
$plans = array_keys(cfg('plans'));

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
    $act = (string) ($_POST['act'] ?? '');
    try {
        if ($act === 'plan') {
            $uid = (int) $_POST['uid'];
            $plan = in_array($_POST['plan'] ?? '', $plans, true) ? $_POST['plan'] : 'free';
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
            foreach (['announcement'] as $k) {
                set_setting($k, trim((string) ($_POST[$k] ?? '')));
            }
            flash('ok', 'Settings saved.');
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
    <?php foreach (['users' => 'Users', 'links' => 'Links', 'payments' => 'Payments', 'settings' => 'Settings'] as $k => $l): ?><a class="<?= $tab === $k ? 'on' : '' ?>" href="?tab=<?= $k ?>"><?= $l ?></a><?php endforeach; ?>
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
        <select class="select" name="plan"><?php foreach ($plans as $p): ?><option value="<?= $p ?>" <?= $r['plan'] === $p ? 'selected' : '' ?>><?= e(plan_name($p)) ?></option><?php endforeach; ?></select>
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
  <form method="post" class="card form-narrow"><?= csrf_field() ?><input type="hidden" name="act" value="setting">
    <div class="field"><label for="announcement">Announcement bar (empty = hidden)</label><input class="input" id="announcement" name="announcement" value="<?= e(setting('announcement')) ?>"></div>
    <p class="muted small">Secrets (DB, Brevo, Google, support WhatsApp) live in <code>config.local.php</code> on the server, not here.</p>
    <button class="btn btn-primary">Save</button>
  </form>
  <?php endif; ?>
</section>
<?php page_end();
