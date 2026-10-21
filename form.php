<?php
declare(strict_types=1);
/** /form?id= — form editor (settings + question builder). */
require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/layout.php';
require __DIR__ . '/lib/forms.php';
require __DIR__ . '/lib/countries.php';
require __DIR__ . '/lib/ua.php';
require __DIR__ . '/lib/app_nav.php';

$u = require_login();
$id = (int) ($_GET['id'] ?? ($_POST['id'] ?? 0));
$form = $id ? db_row('SELECT * FROM forms WHERE id = ? AND user_id = ?', [$id, (int) $u['id']]) : null;
if ($id && !$form) {
    http_response_code(404);
    exit('Not found');
}
$err = null;
$domains = array_keys(cfg('domains'));

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
    try {
        if (isset($_POST['delete']) && $form) {
            db_exec('DELETE FROM forms WHERE id = ?', [(int) $form['id']]);
            flash('ok', t('fm_deleted'));
            redirect('/forms');
        }
        $slug = strtolower(trim((string) ($_POST['slug'] ?? '')));
        if (!valid_custom_code($slug)) {
            throw new InvalidArgumentException(t('err_code_invalid'));
        }
        if (db_value('SELECT 1 FROM forms WHERE slug = ? AND id <> ?', [$slug, (int) ($form['id'] ?? 0)])) {
            throw new InvalidArgumentException(t('err_code_taken'));
        }
        $title = mb_substr(trim((string) ($_POST['title'] ?? '')), 0, 80);
        if ($title === '') {
            throw new InvalidArgumentException(t('fm_err_title'));
        }
        $phone = normalize_phone((string) ($_POST['cc'] ?? ''), (string) ($_POST['phone'] ?? ''));
        if (!valid_phone($phone)) {
            throw new InvalidArgumentException(t('err_phone'));
        }
        $questions = clean_questions((string) ($_POST['questions'] ?? '[]'));
        $theme = [];
        foreach (form_default_theme() as $k => $v) {
            $c = (string) ($_POST['theme_' . $k] ?? $v);
            $theme[$k] = preg_match('/^#[0-9a-f]{6}$/i', $c) ? strtolower($c) : $v;
        }
        $row = [
            'slug' => $slug, 'domain' => in_array($_POST['domain'] ?? '', $domains, true) ? $_POST['domain'] : current_domain(),
            'title' => $title, 'intro' => mb_substr(trim((string) ($_POST['intro'] ?? '')), 0, 500), 'phone' => $phone,
            'opening_line' => mb_substr(trim((string) ($_POST['opening_line'] ?? '')), 0, 200),
            'mode' => ($_POST['mode'] ?? 'steps') === 'single' ? 'single' : 'steps', 'theme' => json_encode($theme),
            'logo' => safe_url((string) ($_POST['logo'] ?? '')), 'questions' => json_encode($questions, JSON_UNESCAPED_UNICODE),
            'save_responses' => isset($_POST['save_responses']) ? 1 : 0, 'privacy_url' => safe_url((string) ($_POST['privacy_url'] ?? '')),
            'active' => isset($_POST['active']) ? 1 : 0,
        ];
        if ($form) {
            $sets = implode(', ', array_map(fn($k) => "$k = ?", array_keys($row)));
            db_exec("UPDATE forms SET $sets, updated_at = ? WHERE id = ?", [...array_values($row), now(), (int) $form['id']]);
            flash('ok', t('fm_saved'));
            redirect('/forms');
        }
        $cols = implode(', ', array_keys($row));
        $marks = implode(', ', array_fill(0, count($row), '?'));
        $newId = db_insert("INSERT INTO forms (user_id, $cols, views, starts, submits, created_at, updated_at) VALUES (?, $marks, 0, 0, 0, ?, ?)", [(int) $u['id'], ...array_values($row), now(), now()]);
        flash('ok', t('fm_saved'));
        redirect(is_premium($u) ? '/forms' : '/upgrade?form=' . $newId);
    } catch (InvalidArgumentException $e) {
        $err = $e->getMessage();
    } catch (Throwable $e) {
        error_log('form save: ' . $e->getMessage());
        $err = t('err_generic');
    }
}

$v = fn(string $k, string $def = '') => e((string) ($_POST[$k] ?? ($form[$k] ?? $def)));
$theme = $form ? form_theme($form) : form_default_theme();
$questionsJson = (string) ($_POST['questions'] ?? ($form['questions'] ?? '[]'));
[$dial, $national] = $form ? split_phone($form['phone']) : ['', ''];
$selIso = 'MY';
foreach (country_codes() as $iso => [$d]) {
    if ($d === $dial) {
        $selIso = $iso;
    }
}
$typeLabels = [];
foreach (FORM_TYPES as $ty) {
    $typeLabels[$ty] = t('fm_type_' . $ty);
}

page_start(['title' => $form ? t('fm_edit') : t('fm_new'), 'noindex' => true, 'body_class' => 'page-app']);
?>
<section class="app wrap">
  <?= app_nav('forms') ?>
  <p><a href="/forms">← <?= e(t('fm_title')) ?></a></p>
  <div class="app-head"><h1><?= e($form ? t('fm_edit') : t('fm_new')) ?></h1>
    <?php if ($form): ?><a class="btn btn-ghost btn-sm" href="<?= e(form_url($form)) ?>?preview=1" target="_blank" rel="noopener"><?= e(t('fm_preview')) ?> ↗</a><?php endif; ?></div>
  <?php if ($err): ?><div class="flash flash-error" role="alert" style="margin-bottom:16px"><?= e($err) ?></div><?php endif; ?>

  <form method="post" id="form-editor" novalidate>
    <?= csrf_field() ?><?php if ($form): ?><input type="hidden" name="id" value="<?= (int) $form['id'] ?>"><?php endif; ?>
    <div class="qr-page" style="align-items:start">
      <div class="card">
        <h3 style="margin-bottom:14px"><?= e(t('fm_settings')) ?></h3>
        <div class="field"><label for="title"><?= e(t('fm_f_title')) ?></label><input class="input" id="title" name="title" maxlength="80" value="<?= $v('title') ?>" placeholder="<?= e(t('fm_f_title_ph')) ?>" required></div>
        <div class="field"><label for="slug"><?= e(t('fm_f_slug')) ?></label>
          <div class="inline-prefix"><span><?= e(current_domain()) ?>/f/</span><input id="handle" name="slug" maxlength="30" value="<?= $v('slug') ?>" placeholder="my-form" pattern="[a-z0-9-]{3,30}" required></div>
          <p class="hint"><?= e(t('fm_f_slug_hint')) ?></p></div>
        <?php if (count($domains) > 1): ?>
        <div class="field"><span class="label"><?= e(t('gen_domain')) ?></span><div class="seg"><?php $cur = (string) ($_POST['domain'] ?? ($form['domain'] ?? current_domain())); foreach ($domains as $d): ?><input type="radio" name="domain" id="dom-<?= e($d) ?>" value="<?= e($d) ?>" <?= $d === $cur ? 'checked' : '' ?>><label for="dom-<?= e($d) ?>"><?= e($d) ?></label><?php endforeach; ?></div></div>
        <?php endif; ?>
        <div class="field"><label for="intro"><?= e(t('fm_f_intro')) ?> <small><?= e(t('gen_optional')) ?></small></label><textarea class="input" id="intro" name="intro" maxlength="500" style="min-height:70px"><?= $v('intro') ?></textarea></div>
        <div class="field"><label for="phone"><?= e(t('fm_f_phone')) ?></label>
          <div class="phone-row"><select class="select" name="cc"><?= country_options(isset($_POST['cc']) ? $selIso : ($form ? $selIso : default_country())) ?></select><input class="input" id="phone" name="phone" type="tel" inputmode="numeric" value="<?= e((string) ($_POST['phone'] ?? $national)) ?>" placeholder="12 345 6789" required data-phone></div></div>
        <div class="field"><label for="opening_line"><?= e(t('fm_f_opening')) ?></label><input class="input" id="opening_line" name="opening_line" maxlength="200" value="<?= $v('opening_line', t('fm_f_opening_def')) ?>"><p class="hint"><?= e(t('fm_f_opening_hint')) ?></p></div>
        <div class="field"><span class="label"><?= e(t('fm_f_mode')) ?></span><div class="seg"><?php $m = (string) ($_POST['mode'] ?? ($form['mode'] ?? 'steps')); ?>
          <input type="radio" name="mode" id="mode-steps" value="steps" <?= $m === 'steps' ? 'checked' : '' ?>><label for="mode-steps"><?= e(t('fm_mode_steps')) ?></label>
          <input type="radio" name="mode" id="mode-single" value="single" <?= $m === 'single' ? 'checked' : '' ?>><label for="mode-single"><?= e(t('fm_mode_single')) ?></label></div></div>
        <div class="field"><label for="logo"><?= e(t('fm_f_logo')) ?> <small><?= e(t('gen_optional')) ?></small></label><input class="input" id="logo" name="logo" type="url" value="<?= $v('logo') ?>" placeholder="https://…/logo.png"></div>
        <div class="field"><span class="label"><?= e(t('fm_f_colors')) ?></span>
          <div style="display:flex;gap:14px;flex-wrap:wrap"><?php foreach ($theme as $k => $c): ?><label class="small" style="display:flex;flex-direction:column;gap:4px;align-items:center"><input type="color" name="theme_<?= $k ?>" value="<?= e((string) ($_POST['theme_' . $k] ?? $c)) ?>" style="width:44px;height:34px;border:1px solid var(--line-2);border-radius:8px;padding:2px;background:#fff"><?= e(t('fm_color_' . $k)) ?></label><?php endforeach; ?></div></div>
        <div class="field"><label for="privacy_url"><?= e(t('fm_f_privacy')) ?> <small><?= e(t('gen_optional')) ?></small></label><input class="input" id="privacy_url" name="privacy_url" type="url" value="<?= $v('privacy_url') ?>" placeholder="https://…/privacy"></div>
        <div class="field"><label class="switch"><input type="checkbox" name="save_responses" <?= ($_POST['save_responses'] ?? ($form['save_responses'] ?? 1)) ? 'checked' : '' ?>> <?= e(t('fm_f_save')) ?></label><p class="hint"><?= e(t('fm_f_save_hint')) ?></p></div>
        <div class="field"><label class="switch"><input type="checkbox" name="active" <?= ($_POST['active'] ?? ($form['active'] ?? 1)) ? 'checked' : '' ?>> <?= e(t('link_active')) ?></label></div>
      </div>

      <div class="card">
        <h3 style="margin-bottom:6px"><?= e(t('fm_questions')) ?></h3>
        <p class="muted small"><?= e(t('fm_q_hint', ['n' => FORM_MAX_QUESTIONS])) ?></p>
        <div id="q-list" class="link-list" style="margin:12px 0"></div>
        <button class="btn btn-ghost" type="button" id="q-add">+ <?= e(t('fm_q_add')) ?></button>
        <input type="hidden" name="questions" id="questions-json" value="<?= e($questionsJson) ?>">
      </div>
    </div>
    <div class="form-actions" style="margin-top:18px">
      <button class="btn btn-primary btn-lg" type="submit"><?= e(t('link_save')) ?></button>
      <a class="btn btn-ghost" href="/forms">←</a>
      <?php if ($form): ?><button class="btn btn-sm btn-danger right" type="submit" name="delete" value="1" onclick="return confirm(<?= e(json_encode(t('fm_delete_conf'))) ?>)"><?= e(t('fm_delete')) ?></button><?php endif; ?>
    </div>
  </form>
</section>
<template id="q-tpl">
  <article class="link-card q-row" style="grid-template-columns:1fr;gap:10px">
    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
      <span class="badge q-num">1</span>
      <select class="select q-type" style="width:180px"><?php foreach ($typeLabels as $ty => $lab): ?><option value="<?= $ty ?>"><?= e($lab) ?></option><?php endforeach; ?></select>
      <label class="switch small"><input type="checkbox" class="q-req"> <?= e(t('fm_q_required')) ?></label>
      <span style="margin-left:auto;display:flex;gap:4px"><button class="btn btn-sm btn-ghost q-up" type="button">↑</button><button class="btn btn-sm btn-ghost q-down" type="button">↓</button><button class="btn btn-sm btn-danger q-del" type="button">✕</button></span>
    </div>
    <input class="input q-label" placeholder="<?= e(t('fm_q_label_ph')) ?>" maxlength="120">
    <input class="input q-help" placeholder="<?= e(t('fm_q_help_ph')) ?>" maxlength="160" style="font-size:14px;padding:9px 12px">
    <textarea class="input q-options" placeholder="<?= e(t('fm_q_options_ph')) ?>" style="min-height:60px;font-size:14px;display:none"></textarea>
  </article>
</template>
<script>
(function(){
  var list=document.getElementById('q-list'), hidden=document.getElementById('questions-json'), tpl=document.getElementById('q-tpl');
  var optTypes={dropdown:1,single:1,multiple:1};
  function uid(){ return 'q'+Math.random().toString(36).slice(2,8); }
  function add(q){ q=q||{}; var n=tpl.content.firstElementChild.cloneNode(true); n.dataset.id=q.id||uid(); n.querySelector('.q-type').value=q.type||'short'; n.querySelector('.q-label').value=q.label||''; n.querySelector('.q-help').value=q.help||''; n.querySelector('.q-req').checked=!!q.required; n.querySelector('.q-options').value=(q.options||[]).join('\n'); list.appendChild(n); refresh(); }
  function refresh(){ list.querySelectorAll('.q-row').forEach(function(r,i){ r.querySelector('.q-num').textContent=i+1; var t=r.querySelector('.q-type').value; r.querySelector('.q-options').style.display=optTypes[t]?'':'none'; }); sync(); }
  function sync(){ var out=[]; list.querySelectorAll('.q-row').forEach(function(r){ out.push({id:r.dataset.id,type:r.querySelector('.q-type').value,label:r.querySelector('.q-label').value,help:r.querySelector('.q-help').value,required:r.querySelector('.q-req').checked,options:r.querySelector('.q-options').value.split('\n').map(function(s){return s.trim()}).filter(Boolean)}); }); hidden.value=JSON.stringify(out); }
  list.addEventListener('input', refresh); list.addEventListener('change', refresh);
  list.addEventListener('click', function(e){ var r=e.target.closest('.q-row'); if(!r) return; if(e.target.closest('.q-del')){ r.remove(); } else if(e.target.closest('.q-up')&&r.previousElementSibling){ list.insertBefore(r,r.previousElementSibling); } else if(e.target.closest('.q-down')&&r.nextElementSibling){ list.insertBefore(r.nextElementSibling,r); } refresh(); });
  document.getElementById('q-add').addEventListener('click', function(){ if(list.children.length>=<?= FORM_MAX_QUESTIONS ?>) return; add(); list.lastElementChild.querySelector('.q-label').focus(); });
  var init=[]; try{ init=JSON.parse(hidden.value||'[]'); }catch(e){} if(!init.length) init=[{type:'short',label:<?= json_encode(t('fm_q_default1')) ?>,required:true},{type:'phone',label:<?= json_encode(t('fm_q_default2')) ?>,required:true}]; init.forEach(add);
  document.getElementById('form-editor').addEventListener('submit', sync);
})();
</script>
<?php page_end();
