<?php
declare(strict_types=1);
/**
 * /smart?id=LINK — Smart Link editor: destinations + routing rules.
 */
require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/layout.php';
require __DIR__ . '/lib/countries.php';
require __DIR__ . '/lib/ua.php';

$u = require_login();
$id = (int) ($_GET['id'] ?? ($_POST['id'] ?? 0));
$link = db_row('SELECT * FROM links WHERE id = ? AND user_id = ?', [$id, (int) $u['id']]);
if (!$link) {
    http_response_code(404);
    exit('Not found');
}
$premium = link_is_premium($link, $u);
$maxDest = (int) (setting('smart_max_destinations') ?: 10);
$err = null;
$editing = null;

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
    $act = (string) ($_POST['act'] ?? '');
    try {
        if ($act === 'enable') {
            db_exec('UPDATE links SET type = ?, updated_at = ? WHERE id = ?', [isset($_POST['on']) ? 'smart' : 'chat', now(), $id]);
            flash('ok', t('sm_saved'));
            redirect('/smart?id=' . $id);
        }
        if ($act === 'delete') {
            db_exec('DELETE FROM destinations WHERE id = ? AND link_id = ?', [(int) $_POST['did'], $id]);
            flash('ok', t('sm_deleted'));
            redirect('/smart?id=' . $id);
        }
        if ($act === 'move') {
            $dests = link_destinations($id);
            $ids = array_map(fn($d) => (int) $d['id'], $dests);
            $i = array_search((int) $_POST['did'], $ids, true);
            $j = $i + ((string) ($_POST['dir'] ?? '') === 'up' ? -1 : 1);
            if ($i !== false && isset($ids[$j])) {
                [$ids[$i], $ids[$j]] = [$ids[$j], $ids[$i]];
                foreach ($ids as $pos => $did) {
                    db_exec('UPDATE destinations SET sort = ? WHERE id = ?', [$pos, $did]);
                }
            }
            redirect('/smart?id=' . $id);
        }
        if ($act === 'save') {
            $did = (int) ($_POST['did'] ?? 0);
            $phone = normalize_phone((string) ($_POST['cc'] ?? ''), (string) ($_POST['phone'] ?? ''));
            if (!valid_phone($phone)) {
                throw new InvalidArgumentException(t('err_phone'));
            }
            $type = in_array($_POST['rule_type'] ?? '', SMART_RULE_TYPES, true) ? $_POST['rule_type'] : 'always';
            $rule = build_rule($type, $_POST);
            $name = mb_substr(trim((string) ($_POST['name'] ?? '')), 0, 60);
            $message = mb_substr(trim((string) ($_POST['message'] ?? '')), 0, 1000);
            $weight = max(1, min(100, (int) ($_POST['weight'] ?? 1)));
            $active = isset($_POST['active']) ? 1 : 0;
            if ($did) {
                db_exec('UPDATE destinations SET name = ?, phone = ?, message = ?, rule_type = ?, rule = ?, weight = ?, active = ? WHERE id = ? AND link_id = ?',
                    [$name, $phone, $message, $type, json_encode($rule), $weight, $active, $did, $id]);
            } else {
                if (count(link_destinations($id)) >= $maxDest) {
                    throw new InvalidArgumentException(t('sm_err_max', ['n' => $maxDest]));
                }
                $sort = (int) db_value('SELECT COALESCE(MAX(sort), -1) + 1 FROM destinations WHERE link_id = ?', [$id]);
                db_exec('INSERT INTO destinations (link_id, name, phone, message, rule_type, rule, weight, sort, active, clicks, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?)',
                    [$id, $name, $phone, $message, $type, json_encode($rule), $weight, $sort, $active, now()]);
                if (($link['type'] ?? 'chat') !== 'smart') {
                    db_exec('UPDATE links SET type = ?, updated_at = ? WHERE id = ?', ['smart', now(), $id]);
                }
            }
            flash('ok', t('sm_saved'));
            redirect('/smart?id=' . $id);
        }
    } catch (InvalidArgumentException $e) {
        $err = $e->getMessage();
        $editing = $_POST + ['id' => (int) ($_POST['did'] ?? 0)];
    } catch (Throwable $e) {
        error_log('smart: ' . $e->getMessage());
        $err = t('err_generic');
    }
}

$dests = link_destinations($id);
if (!$editing && isset($_GET['edit'])) {
    foreach ($dests as $d) {
        if ((int) $d['id'] === (int) $_GET['edit']) {
            $editing = $d + decode_rule($d);
            [$editing['cc'], $editing['phone']] = split_phone($d['phone']);
        }
    }
}
$showForm = $editing !== null || isset($_GET['new']);
$ev = fn(string $k, $def = '') => e((string) ($editing[$k] ?? $def));
$selIso = 'MY';
if ($editing && !empty($editing['cc'])) {
    foreach (country_codes() as $iso => [$d]) {
        if ($d === $editing['cc']) {
            $selIso = $iso;
        }
    }
}
$days = ['1' => 'Mon', '2' => 'Tue', '3' => 'Wed', '4' => 'Thu', '5' => 'Fri', '6' => 'Sat', '7' => 'Sun'];
$ruleDays = array_map('strval', (array) ($editing['days'] ?? [1, 2, 3, 4, 5]));
$ruleSources = (array) ($editing['sources'] ?? []);

page_start(['title' => t('sm_title') . ' · ' . short_display($link), 'noindex' => true, 'body_class' => 'page-app']);
?>
<section class="app wrap">
  <p><a href="/dashboard">← <?= e(t('dash_title')) ?></a></p>
  <div class="app-head"><h1><?= e(t('sm_title')) ?> <span class="mono" style="font-size:.6em;color:var(--green-2)"><?= e(short_display($link)) ?></span></h1>
    <?php if ($premium): ?><a class="btn btn-primary" href="/smart?id=<?= $id ?>&new=1">+ <?= e(t('sm_add')) ?></a><?php endif; ?></div>
  <?php if ($err): ?><div class="flash flash-error" role="alert" style="margin-bottom:16px"><?= e($err) ?></div><?php endif; ?>
  <p class="lead" style="font-size:15px;margin-bottom:18px"><?= e(t('sm_lead')) ?></p>

  <?php if (!$premium): ?>
    <div class="locked">🔒 <?= e(t('sm_premium')) ?> <a href="/upgrade?id=<?= $id ?>"><b><?= e(t('dash_upgrade')) ?> →</b></a></div>
  <?php else: ?>
    <form method="post" class="card" style="display:flex;gap:14px;align-items:center;flex-wrap:wrap;margin-bottom:18px"><?= csrf_field() ?><input type="hidden" name="act" value="enable"><input type="hidden" name="id" value="<?= $id ?>">
      <label class="switch"><input type="checkbox" name="on" <?= ($link['type'] ?? '') === 'smart' ? 'checked' : '' ?> onchange="this.form.submit()"> <?= e(t('sm_enabled')) ?></label>
      <span class="muted small"><?= e(t('sm_enabled_hint', ['phone' => '+' . $link['phone']])) ?></span>
    </form>
  <?php endif; ?>

  <?php if ($dests): ?>
  <div class="link-list" style="margin-bottom:18px">
    <?php foreach ($dests as $i => $d): ?>
    <article class="link-card">
      <div>
        <div class="code" style="font-family:var(--sans);font-size:16px"><?= e($d['name'] !== '' ? $d['name'] : '+' . $d['phone']) ?>
          <span class="badge"><?= e(t('sm_rule_' . $d['rule_type'])) ?></span> <?php if (!(int) $d['active']): ?><span class="badge badge-off"><?= e(t('dash_paused')) ?></span><?php endif; ?></div>
        <div class="sub"><span>+<?= e($d['phone']) ?></span><span><?= e(rule_summary($d)) ?></span><?php if ($d['message'] !== ''): ?><span class="msg">“<?= e($d['message']) ?>”</span><?php endif; ?></div>
      </div>
      <div class="stats"><b><?= number_format((int) $d['clicks']) ?></b><span><?= e(t('dash_clicks')) ?></span></div>
      <?php if ($premium): ?>
      <div class="actions">
        <a class="btn btn-sm btn-ghost" href="/smart?id=<?= $id ?>&edit=<?= (int) $d['id'] ?>"><?= e(t('dash_edit')) ?></a>
        <form method="post" style="display:inline"><?= csrf_field() ?><input type="hidden" name="act" value="move"><input type="hidden" name="id" value="<?= $id ?>"><input type="hidden" name="did" value="<?= (int) $d['id'] ?>"><button class="btn btn-sm btn-ghost" name="dir" value="up" <?= $i === 0 ? 'disabled' : '' ?>>↑</button> <button class="btn btn-sm btn-ghost" name="dir" value="down" <?= $i === count($dests) - 1 ? 'disabled' : '' ?>>↓</button></form>
        <form method="post" style="display:inline" data-confirm="<?= e(t('sm_delete_conf')) ?>"><?= csrf_field() ?><input type="hidden" name="act" value="delete"><input type="hidden" name="id" value="<?= $id ?>"><input type="hidden" name="did" value="<?= (int) $d['id'] ?>"><button class="btn btn-sm btn-danger">✕</button></form>
      </div>
      <?php endif; ?>
    </article>
    <?php endforeach; ?>
  </div>
  <p class="muted small"><?= e(t('sm_order_hint')) ?></p>
  <?php elseif ($premium && !$showForm): ?>
    <div class="empty"><h2><?= e(t('sm_empty_t')) ?></h2><p class="muted"><?= e(t('sm_empty_d')) ?></p><a class="btn btn-wa btn-lg" href="/smart?id=<?= $id ?>&new=1">+ <?= e(t('sm_add')) ?></a></div>
  <?php endif; ?>

  <?php if ($premium && $showForm): ?>
  <form method="post" class="card form-narrow" id="dest-form" novalidate>
    <?= csrf_field() ?><input type="hidden" name="act" value="save"><input type="hidden" name="id" value="<?= $id ?>"><input type="hidden" name="did" value="<?= (int) ($editing['id'] ?? 0) ?>">
    <h3 style="margin-bottom:16px"><?= e(!empty($editing['id']) ? t('sm_edit_dest') : t('sm_add')) ?></h3>
    <div class="field"><label for="name"><?= e(t('sm_name')) ?></label><input class="input" id="name" name="name" maxlength="60" value="<?= $ev('name') ?>" placeholder="<?= e(t('sm_name_ph')) ?>"></div>
    <div class="field"><label for="phone"><?= e(t('gen_phone')) ?></label>
      <div class="phone-row"><select class="select" name="cc"><?= country_options($selIso) ?></select><input class="input" id="phone" name="phone" type="tel" inputmode="numeric" value="<?= $ev('phone') ?>" placeholder="12 345 6789" required data-phone></div></div>
    <div class="field"><label for="message"><?= e(t('sm_message')) ?> <small><?= e(t('gen_optional')) ?></small></label><textarea class="input" id="message" name="message" maxlength="1000" placeholder="<?= e(t('sm_message_ph')) ?>"><?= $ev('message') ?></textarea></div>

    <div class="field"><span class="label"><?= e(t('sm_when')) ?></span>
      <div class="seg" id="rule-seg">
        <?php $rt = (string) ($editing['rule_type'] ?? 'always'); foreach (SMART_RULE_TYPES as $r): ?>
          <input type="radio" name="rule_type" id="rt-<?= $r ?>" value="<?= $r ?>" <?= $rt === $r ? 'checked' : '' ?>><label for="rt-<?= $r ?>"><?= e(t('sm_rule_' . $r)) ?></label>
        <?php endforeach; ?>
      </div></div>

    <div class="field rule-box" data-rule="always"><label for="weight"><?= e(t('sm_weight')) ?></label><input class="input" id="weight" name="weight" type="number" min="1" max="100" value="<?= $ev('weight', '1') ?>" style="width:120px"><p class="hint"><?= e(t('sm_weight_hint')) ?></p></div>

    <div class="field rule-box" data-rule="hours">
      <span class="label"><?= e(t('sm_days')) ?></span>
      <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:10px"><?php foreach ($days as $n => $lab): ?><label class="btn btn-sm btn-ghost" style="cursor:pointer"><input type="checkbox" name="days[]" value="<?= $n ?>" <?= in_array($n, $ruleDays, true) ? 'checked' : '' ?>> <?= $lab ?></label><?php endforeach; ?></div>
      <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap"><input class="input" type="time" name="from" value="<?= $ev('from', '09:00') ?>" style="width:140px"> – <input class="input" type="time" name="to" value="<?= $ev('to', '18:00') ?>" style="width:140px">
        <select class="select" name="tz" style="width:240px"><?php foreach (['Asia/Kuala_Lumpur', 'Asia/Singapore', 'Asia/Jakarta', 'Asia/Bangkok', 'Asia/Manila', 'Asia/Dubai', 'Asia/Kolkata', 'Europe/London', 'America/New_York', 'Australia/Sydney'] as $tz): ?><option value="<?= $tz ?>" <?= ($editing['tz'] ?? 'Asia/Kuala_Lumpur') === $tz ? 'selected' : '' ?>><?= $tz ?></option><?php endforeach; ?></select></div>
      <p class="hint"><?= e(t('sm_hours_hint')) ?></p>
    </div>

    <div class="field rule-box" data-rule="country"><label for="countries"><?= e(t('sm_countries')) ?></label><input class="input" id="countries" name="countries" value="<?= e(implode(', ', (array) ($editing['countries'] ?? []))) ?>" placeholder="MY, SG, ID"><p class="hint"><?= e(t('sm_countries_hint')) ?></p></div>

    <div class="field rule-box" data-rule="source"><span class="label"><?= e(t('sm_sources')) ?></span>
      <div style="display:flex;gap:6px;flex-wrap:wrap"><?php foreach (SMART_SOURCES as $s): ?><label class="btn btn-sm btn-ghost" style="cursor:pointer"><input type="checkbox" name="sources[]" value="<?= $s ?>" <?= in_array($s, $ruleSources, true) ? 'checked' : '' ?>> <?= ucfirst($s) ?></label><?php endforeach; ?></div></div>

    <div class="field"><label class="switch"><input type="checkbox" name="active" <?= !isset($editing['id']) || !empty($editing['active']) ? 'checked' : '' ?>> <?= e(t('link_active')) ?></label></div>
    <div class="form-actions"><button class="btn btn-primary btn-lg" type="submit"><?= e(t('link_save')) ?></button><a class="btn btn-ghost" href="/smart?id=<?= $id ?>">←</a></div>
  </form>
  <script>
  (function(){ var f=document.getElementById('dest-form'); function show(){ var v=f.querySelector('input[name=rule_type]:checked').value; f.querySelectorAll('.rule-box').forEach(function(b){ b.style.display = b.dataset.rule===v ? '' : 'none'; }); } f.querySelectorAll('input[name=rule_type]').forEach(function(r){ r.addEventListener('change', show); }); show(); })();
  </script>
  <?php endif; ?>
</section>
<?php page_end();
