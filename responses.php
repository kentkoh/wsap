<?php
declare(strict_types=1);
/** /responses?id=FORM — inbox + funnel; ?csv=1 exports. */
require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/layout.php';
require __DIR__ . '/lib/forms.php';
require __DIR__ . '/lib/app_nav.php';

$u = require_login();
$id = (int) ($_GET['id'] ?? 0);
$form = db_row('SELECT * FROM forms WHERE id = ? AND user_id = ?', [$id, (int) $u['id']]);
if (!$form) {
    http_response_code(404);
    exit('Not found');
}
$questions = form_questions($form);
$labels = array_map(fn($q) => $q['label'], $questions);

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
    if (isset($_POST['del'])) {
        db_exec('DELETE FROM form_responses WHERE id = ? AND form_id = ?', [(int) $_POST['del'], $id]);
        flash('ok', t('fm_resp_deleted'));
    }
    redirect('/responses?id=' . $id);
}

if (isset($_GET['csv'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="wsap-form-' . $form['slug'] . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, array_merge(['time_utc', 'country', 'device'], $labels));
    $st = db()->prepare('SELECT * FROM form_responses WHERE form_id = ? ORDER BY id DESC');
    $st->execute([$id]);
    while ($r = $st->fetch()) {
        $a = json_decode((string) $r['answers'], true) ?: [];
        $byQ = [];
        foreach ($a as $x) {
            $byQ[$x['q']] = $x['a'];
        }
        fputcsv($out, array_merge([$r['created_at'], $r['country'], $r['device']], array_map(fn($l) => $byQ[$l] ?? '', $labels)));
    }
    exit;
}

$q = trim((string) ($_GET['q'] ?? ''));
$rows = db_rows('SELECT * FROM form_responses WHERE form_id = ?' . ($q !== '' ? ' AND answers LIKE ?' : '') . ' ORDER BY id DESC LIMIT 300', $q !== '' ? [$id, "%$q%"] : [$id]);
$views = (int) $form['views'];
$starts = (int) $form['starts'];
$submits = (int) $form['submits'];
$rate = $views > 0 ? min(100, (int) round($submits / $views * 100)) : 0;

page_start(['title' => t('fm_responses') . ' · ' . $form['title'], 'noindex' => true, 'body_class' => 'page-app']);
?>
<section class="app wrap">
  <?= app_nav('forms') ?>
  <p><a href="/forms">← <?= e(t('fm_title')) ?></a></p>
  <div class="app-head"><h1><?= e($form['title']) ?> <span class="mono" style="font-size:.6em;color:var(--green-2)"><?= e(form_display($form)) ?></span></h1>
    <a class="btn btn-sm btn-ghost" href="?id=<?= $id ?>&csv=1">⤓ <?= e(t('an_csv')) ?></a></div>
  <div class="stat-grid">
    <div class="stat"><b><?= number_format($views) ?></b><span><?= e(t('fm_views')) ?></span></div>
    <div class="stat"><b><?= number_format($starts) ?></b><span><?= e(t('fm_starts')) ?></span></div>
    <div class="stat"><b><?= number_format($submits) ?></b><span><?= e(t('fm_sent')) ?></span></div>
    <div class="stat"><b><?= $rate ?>%</b><span><?= e(t('fm_rate')) ?></span></div>
  </div>
  <?php if (!(int) $form['save_responses']): ?><div class="locked"><?= e(t('fm_not_saving')) ?></div><?php endif; ?>
  <form method="get" style="display:flex;gap:8px;margin-bottom:14px"><input type="hidden" name="id" value="<?= $id ?>"><input class="input" name="q" value="<?= e($q) ?>" placeholder="<?= e(t('fm_search')) ?>" style="max-width:320px"><button class="btn btn-ghost"><?= e(t('fm_search_btn')) ?></button></form>
  <?php if (!$rows): ?><div class="empty"><h2><?= e(t('an_none')) ?></h2></div><?php else: ?>
  <div class="table-wrap"><table class="table">
    <tr><th>#</th><th><?= e(t('dash_created')) ?></th><?php foreach ($labels as $l): ?><th><?= e($l) ?></th><?php endforeach; ?><th></th></tr>
    <?php foreach ($rows as $r): $a = json_decode((string) $r['answers'], true) ?: []; $byQ = []; foreach ($a as $x) { $byQ[$x['q']] = $x['a']; } ?>
    <tr><td><?= (int) $r['id'] ?></td><td class="small"><time data-utc="<?= e($r['created_at']) ?>"><?= e(substr($r['created_at'], 0, 16)) ?></time><br><span class="muted"><?= e($r['country'] . ' ' . $r['device']) ?></span></td>
      <?php foreach ($labels as $l): ?><td style="max-width:260px"><?= nl2br(e((string) ($byQ[$l] ?? ''))) ?></td><?php endforeach; ?>
      <td><form method="post" data-confirm="<?= e(t('fm_resp_delete_conf')) ?>"><?= csrf_field() ?><button class="btn btn-sm btn-danger" name="del" value="<?= (int) $r['id'] ?>">✕</button></form></td></tr>
    <?php endforeach; ?>
  </table></div>
  <?php endif; ?>
</section>
<?php page_end();
