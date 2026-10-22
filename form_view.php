<?php
declare(strict_types=1);
/** wsap.me/{slug} (via go.php) — public form page. POST submits answers and redirects to WhatsApp. */
require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/forms.php';
require_once __DIR__ . '/lib/ua.php';
require_once __DIR__ . '/lib/demo.php';

header('Cache-Control: no-store');
$form = find_form((string) ($_GET['s'] ?? ''));
$owner = $form && $form['user_id'] ? db_row('SELECT * FROM users WHERE id = ?', [(int) $form['user_id']]) : null;
$viewer = current_user();
$isOwner = $form && $viewer && (int) $viewer['id'] === (int) $form['user_id'];
$preview = $isOwner && isset($_GET['preview']);

if (!$form || (!form_is_published($form, $owner) && !$preview)) {
    http_response_code(404);
    $site = e((string) cfg('site_name'));
    echo '<!doctype html><html lang="' . lang() . '"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . e(t('fm_404_t')) . '</title><link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml"><link rel="stylesheet" href="/assets/css/app.css"></head><body><div class="open-page"><div class="open-card"><h1>' . e(t('fm_404_t')) . '</h1><p>' . e(t('fm_404_d')) . '</p><a class="btn btn-primary" href="/">' . $site . '</a></div></div></body></html>';
    exit;
}

$questions = form_questions($form);
$theme = form_theme($form);
$err = null;
$info = click_info();
$isDemo = (int) ($form['demo'] ?? 0) === 1;
$demoPopup = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    try {
        if (!$preview && !rate_limit('form_submit', 30, 3600)) {
            throw new InvalidArgumentException(t('err_rate'));
        }
        if (!empty($_POST['website'])) { // honeypot
            throw new InvalidArgumentException(t('err_generic'));
        }
        $answers = validate_answers($questions, $_POST);
        $message = compose_form_message($form, $answers);
        if ($isDemo || $preview) {
            if ($isDemo && !$preview) {
                db_exec('UPDATE forms SET submits = submits + 1 WHERE id = ?', [(int) $form['id']]);
            }
            $demoPopup = demo_popup_html($message, $form['phone'], '/form', t('demo_cta_form'), 'form');
            $_POST = [];
        } else {
            db_exec('UPDATE forms SET submits = submits + 1 WHERE id = ?', [(int) $form['id']]);
            if ((int) $form['save_responses'] === 1) {
                db_exec('INSERT INTO form_responses (form_id, created_at, answers, country, device) VALUES (?, ?, ?, ?, ?)',
                    [(int) $form['id'], now(), json_encode($answers, JSON_UNESCAPED_UNICODE), $info['country'], $info['device']]);
            }
            $target = 'https://wa.me/' . $form['phone'] . '?text=' . rawurlencode($message);
            if ($info['in_app'] !== '') {
                $target = 'whatsapp://send?phone=' . $form['phone'] . '&text=' . rawurlencode($message);
            }
            redirect($target);
        }
    } catch (InvalidArgumentException $e) {
        $err = $e->getMessage();
    } catch (Throwable $e) {
        error_log('form submit: ' . $e->getMessage());
        $err = t('err_generic');
    }
} elseif (!$preview && !$info['is_bot']) {
    db_exec('UPDATE forms SET views = views + 1 WHERE id = ?', [(int) $form['id']]);
}

$steps = $form['mode'] === 'steps';
$site = (string) cfg('site_name');
$old = fn(string $k) => e((string) ($_POST[$k] ?? ''));
?>
<!doctype html>
<html lang="<?= lang() ?>">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($form['title']) ?></title>
<meta name="robots" content="noindex">
<meta property="og:title" content="<?= e($form['title']) ?>"><meta property="og:description" content="<?= e($form['intro']) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com"><link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml"><link rel="stylesheet" href="/assets/css/app.css">
<?= demo_popup_css() ?>
<style>
  :root{--f-bg:<?= $theme['bg'] ?>;--f-text:<?= $theme['text'] ?>;--f-title:<?= $theme['title'] ?>;--f-bar:<?= $theme['bar'] ?>;--f-btn:<?= $theme['button'] ?>}
  body{background:var(--f-bg);color:var(--f-text)}
  .fpage{max-width:560px;margin:0 auto;padding:28px 16px 60px}
  .fhead{text-align:center;margin-bottom:22px}.fhead img{height:72px;width:72px;object-fit:cover;margin:0 auto 14px;border-radius:18px;box-shadow:0 8px 24px rgba(0,0,0,.12);display:block}
  .fhead h1{color:var(--f-title);font-size:26px}.fhead p{color:var(--f-text);opacity:.8;font-size:15px;margin:8px 0 0}
  .fcard{background:#fff;border-radius:20px;padding:24px;box-shadow:var(--shadow-1)}
  .fbar{height:6px;background:rgba(0,0,0,.08);border-radius:999px;overflow:hidden;margin-bottom:20px}.fbar i{display:block;height:100%;background:var(--f-bar);transition:width .3s}
  .fq{display:none}.fq.on{display:block}.single .fq{display:block;margin-bottom:22px}
  .fq label.q{display:block;font-weight:700;font-size:17px;margin-bottom:6px;color:var(--f-title)}.fq .help{font-size:13.5px;opacity:.7;margin:0 0 10px}
  .opt{display:flex;align-items:center;gap:10px;padding:11px 14px;border:1.5px solid var(--line-2);border-radius:12px;margin-bottom:8px;cursor:pointer;font-size:15px}.opt:has(input:checked){border-color:var(--f-btn);background:rgba(0,0,0,.03)}
  .fnav{display:flex;gap:10px;margin-top:18px}.fnav .btn-primary{background:var(--f-btn);border-color:var(--f-btn)}.fnav .btn-wa{margin-left:auto}
  .ffoot{text-align:center;font-size:12.5px;opacity:.6;margin-top:22px}.ffoot a{color:inherit}
  .fwarn{background:var(--warn-bg);color:#6a4a00;padding:10px 14px;border-radius:10px;font-size:14px;margin-bottom:14px}
</style>
</head>
<body>
<div class="fpage <?= $steps ? 'steps' : 'single' ?>">
  <?php if ($preview): ?><div class="fwarn"><?= e(t('fm_preview_note')) ?></div><?php elseif ($isDemo): ?><div class="fwarn">✦ <?= e(t('demo_banner')) ?></div><?php endif; ?>
  <?= $demoPopup ?>
  <div class="fhead">
    <?php if ($form['logo'] !== ''): ?><img src="<?= e($form['logo']) ?>" alt=""><?php endif; ?>
    <h1><?= e($form['title']) ?></h1>
    <?php if ($form['intro'] !== ''): ?><p><?= nl2br(e($form['intro'])) ?></p><?php endif; ?>
  </div>
  <form method="post" class="fcard" id="f" novalidate>
    <input type="text" name="website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">
    <?php if ($err): ?><div class="flash flash-error" role="alert" style="margin:0 0 14px"><?= e($err) ?></div><?php endif; ?>
    <?php if ($steps): ?><div class="fbar"><i id="bar" style="width:0"></i></div><?php endif; ?>
    <?php foreach ($questions as $i => $q): $id = $q['id']; $req = $q['required'] ? 'required' : ''; ?>
    <div class="fq<?= $i === 0 ? ' on' : '' ?>" data-i="<?= $i ?>">
      <label class="q" for="<?= $id ?>"><?= e($q['label']) ?><?= $q['required'] ? ' <span style="color:#b4232c">*</span>' : '' ?></label>
      <?php if ($q['help'] !== ''): ?><p class="help"><?= e($q['help']) ?></p><?php endif; ?>
      <?php switch ($q['type']):
        case 'paragraph': ?><textarea class="input" id="<?= $id ?>" name="<?= $id ?>" <?= $req ?>><?= $old($id) ?></textarea><?php break;
        case 'email': ?><input class="input" type="email" id="<?= $id ?>" name="<?= $id ?>" value="<?= $old($id) ?>" <?= $req ?>><?php break;
        case 'phone': ?><input class="input" type="tel" id="<?= $id ?>" name="<?= $id ?>" value="<?= $old($id) ?>" <?= $req ?>><?php break;
        case 'number': ?><input class="input" type="number" step="any" id="<?= $id ?>" name="<?= $id ?>" value="<?= $old($id) ?>" <?= $req ?>><?php break;
        case 'time': ?><input class="input" type="time" id="<?= $id ?>" name="<?= $id ?>" value="<?= $old($id) ?>" <?= $req ?>><?php break;
        case 'date': ?><input class="input" type="date" id="<?= $id ?>" name="<?= $id ?>" value="<?= $old($id) ?>" <?= $req ?>><?php break;
        case 'daterange': ?><div style="display:flex;gap:8px;align-items:center"><input class="input" type="date" id="<?= $id ?>" name="<?= $id ?>_from" value="<?= $old($id . '_from') ?>" <?= $req ?>> → <input class="input" type="date" name="<?= $id ?>_to" value="<?= $old($id . '_to') ?>" <?= $req ?>></div><?php break;
        case 'dropdown': ?><select class="select" id="<?= $id ?>" name="<?= $id ?>" <?= $req ?>><option value="">—</option><?php foreach ($q['options'] as $o): ?><option <?= ($_POST[$id] ?? '') === $o ? 'selected' : '' ?>><?= e($o) ?></option><?php endforeach; ?></select><?php break;
        case 'single': foreach ($q['options'] as $o): ?><label class="opt"><input type="radio" name="<?= $id ?>" value="<?= e($o) ?>" <?= ($_POST[$id] ?? '') === $o ? 'checked' : '' ?> <?= $req ?>> <?= e($o) ?></label><?php endforeach; break;
        case 'multiple': foreach ($q['options'] as $o): ?><label class="opt"><input type="checkbox" name="<?= $id ?>[]" value="<?= e($o) ?>" <?= in_array($o, (array) ($_POST[$id] ?? []), true) ? 'checked' : '' ?>> <?= e($o) ?></label><?php endforeach; break;
        default: ?><input class="input" type="text" id="<?= $id ?>" name="<?= $id ?>" value="<?= $old($id) ?>" <?= $req ?>><?php endswitch; ?>
    </div>
    <?php endforeach; ?>
    <div class="fnav">
      <?php if ($steps): ?><button class="btn btn-ghost" type="button" id="prev" style="display:none"><?= e(t('fm_back')) ?></button><button class="btn btn-primary" type="button" id="next"><?= e(t('fm_next')) ?> →</button><?php endif; ?>
      <button class="btn btn-wa" type="submit" id="send" <?= $steps ? 'style="display:none"' : '' ?>><?= e(t('fm_send')) ?> →</button>
    </div>
  </form>
  <div class="ffoot"><?php if ($form['privacy_url'] !== ''): ?><a href="<?= e($form['privacy_url']) ?>" target="_blank" rel="noopener"><?= e(t('footer_privacy')) ?></a> · <?php endif; ?><?= e(t('open_pow')) ?> <a href="/"><?= e($site) ?></a></div>
</div>
<?php if ($steps): ?>
<script>
(function(){ var qs=[].slice.call(document.querySelectorAll('.fq')), i=0, bar=document.getElementById('bar'), prev=document.getElementById('prev'), next=document.getElementById('next'), send=document.getElementById('send'), started=false;
  function show(){ qs.forEach(function(q,k){ q.classList.toggle('on',k===i); }); bar.style.width=((i+1)/qs.length*100)+'%'; prev.style.display=i?'':'none'; next.style.display=i<qs.length-1?'':'none'; send.style.display=i===qs.length-1?'':'none'; var f=qs[i].querySelector('input,select,textarea'); if(f&&f.type!=='radio'&&f.type!=='checkbox') f.focus(); }
  function valid(){ var ok=true; qs[i].querySelectorAll('input,select,textarea').forEach(function(el){ if(!el.checkValidity()){ ok=false; el.reportValidity(); } }); var r=qs[i].querySelector('input[type=radio][required]'); if(r && !qs[i].querySelector('input[type=radio]:checked')){ ok=false; r.reportValidity(); } return ok; }
  next.addEventListener('click', function(){ if(!valid()) return; if(!started){ started=true; navigator.sendBeacon && navigator.sendBeacon('/api/form_start.php?id=<?= (int) $form['id'] ?>'); } i++; show(); });
  prev.addEventListener('click', function(){ i--; show(); });
  document.getElementById('f').addEventListener('keydown', function(e){ if(e.key==='Enter' && e.target.tagName!=='TEXTAREA' && i<qs.length-1){ e.preventDefault(); next.click(); } });
  show(); })();
</script>
<?php endif; ?>
</body></html>
