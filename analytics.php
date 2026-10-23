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
$premium = link_is_premium($link, $u);
$days = (int) ($_GET['days'] ?? 30);
$days = in_array($days, [7, 30, 90, 365], true) ? $days : 30;

if ($premium && isset($_GET['csv'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="wsap-' . $link['code'] . '-clicks.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['time_utc', 'country', 'referrer', 'device', 'os', 'browser', 'qr_scan', 'utm_source']);
    $st = db()->prepare('SELECT created_at, country, referrer, device, os, browser, is_qr, utm_source FROM clicks WHERE link_id = ? AND is_bot = 0 AND created_at >= ? ORDER BY created_at DESC');
    $st->execute([$id, now(-$days * 86400)]);
    while ($r = $st->fetch()) {
        fputcsv($out, $r);
    }
    exit;
}

$s = $premium ? link_stats($id, $days) : null;
$free = null;
if (!$premium) {
    $cnt = fn(string $extra, array $p) => (int) db_value('SELECT COUNT(*) FROM clicks WHERE link_id = ? AND is_bot = 0 ' . $extra, [$id, ...$p]);
    $free = ['d30' => $cnt('AND created_at >= ?', [now(-30 * 86400)]), 'd7' => $cnt('AND created_at >= ?', [now(-7 * 86400)]), 'qr' => $cnt('AND is_qr = 1', [])];
}
$maxDay = $s ? max(1, ...array_map(fn($r) => (int) $r['n'], $s['by_day'] ?: [['n' => 1]])) : 1;

page_start(['title' => t('an_title') . ' · ' . short_display($link), 'noindex' => true, 'body_class' => 'page-app']);
?>
<section class="app wrap">
  <p><a href="/dashboard">← <?= e(t('dash_title')) ?></a></p>
  <div class="app-head">
    <h1><?= e(t('an_title')) ?> <span class="mono" style="font-size:.6em;color:var(--green-2)"><?= e(short_display($link)) ?></span></h1>
    <?php if ($premium): ?>
    <div class="range" style="margin-left:auto"><?php foreach ([7, 30, 90, 365] as $d): ?><a class="<?= $d === $days ? 'on' : '' ?>" href="?id=<?= $id ?>&days=<?= $d ?>"><?= $d ?>d</a><?php endforeach; ?></div>
    <a class="btn btn-sm btn-ghost" href="?id=<?= $id ?>&days=<?= $days ?>&csv=1" style="margin-left:0">⤓ <?= e(t('an_csv')) ?></a>
    <?php endif; ?>
  </div>

  <?php if (!$premium): ?>
    <div class="stat-grid">
      <div class="stat"><b><?= number_format((int) $link['clicks']) ?></b><span><?= e(t('an_clicks')) ?> · <?= e(t('an_all_time')) ?></span></div>
      <div class="stat"><b><?= number_format($free['d30']) ?></b><span><?= e(t('an_range', ['n' => 30])) ?></span></div>
      <div class="stat"><b><?= number_format($free['d7']) ?></b><span><?= e(t('an_range', ['n' => 7])) ?></span></div>
      <div class="stat"><b><?= number_format($free['qr']) ?></b><span><?= e(t('an_qr')) ?></span></div>
    </div>
    <div class="locked">🔒 <?= e(t('an_free_note')) ?> <a href="/upgrade?id=<?= $id ?>"><b><?= e(t('dash_upgrade')) ?> →</b></a></div>
  <?php else: ?>
    <div class="stat-grid">
      <div class="stat"><b><?= number_format($s['total']) ?></b><span><?= e(t('an_clicks')) ?> · <?= e(t('an_range', ['n' => $days])) ?></span></div>
      <div class="stat"><b><?= number_format($s['unique']) ?></b><span><?= e(t('an_unique')) ?></span></div>
      <div class="stat"><b><?= number_format($s['qr']) ?></b><span><?= e(t('an_qr')) ?></span></div>
      <div class="stat"><b><?= number_format($s['bots']) ?></b><span><?= e(t('an_bots')) ?></span></div>
    </div>
    <div class="card">
      <h3 style="font-size:15px;margin-bottom:6px"><?= e(t('an_by_day')) ?></h3>
      <?php if (!$s['by_day']): ?><p class="muted"><?= e(t('an_none')) ?></p><?php else: ?>
      <div class="chart-days">
        <?php
        $byDay = array_column($s['by_day'], 'n', 'k');
        $span = min($days, 90);
        for ($i = $span - 1; $i >= 0; $i--):
            $d = gmdate('Y-m-d', time() - $i * 86400);
            $n = (int) ($byDay[$d] ?? 0);
        ?><div class="bar" style="height:<?= max(1.5, $n / $maxDay * 100) ?>%" data-l="<?= e(substr($d, 5)) ?>: <?= $n ?>"></div><?php endfor; ?>
      </div>
      <?php endif; ?>
    </div>
    <div class="breakdowns">
      <?php $groups = ['country' => 'an_country', 'referrer' => 'an_referrer', 'device' => 'an_device', 'os' => 'an_os', 'browser' => 'an_browser', 'utm' => 'an_utm'];
      if (($link['type'] ?? 'chat') === 'smart') { $groups = ['destination' => 'an_destination'] + $groups; }
      foreach ($groups as $key => $label): ?>
      <div class="card bd"><h3><?= e(t($label)) ?></h3>
        <?php if (!$s[$key]): ?><p class="muted small"><?= e(t('an_none')) ?></p><?php else: $top = max(1, (int) $s[$key][0]['n']); foreach ($s[$key] as $r): ?>
          <div class="row"><span><?= e($r['k'] !== '' ? (string) $r['k'] : t('an_unknown')) ?></span><b><?= number_format((int) $r['n']) ?></b><i style="width:<?= (int) $r['n'] / $top * 100 ?>%"></i></div>
        <?php endforeach; endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
<?php page_end();
