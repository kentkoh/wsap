<?php
declare(strict_types=1);
require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/layout.php';
require __DIR__ . '/lib/countries.php';
require __DIR__ . '/lib/ua.php';

$u = require_login();
$premium = is_premium($u);
$id = (int) ($_GET['id'] ?? ($_POST['id'] ?? 0));
$link = $id ? db_row('SELECT * FROM links WHERE id = ? AND user_id = ?', [$id, (int) $u['id']]) : null;
if ($id && !$link) {
    http_response_code(404);
    exit('Not found');
}
$err = null;
$domains = array_keys(cfg('domains'));
// Members can edit the number, message and pause state of every link they own (a free-account benefit).
// Only the branded handle, analytics breakdown and styled QR stay Premium.
$linkPremium = $link ? link_is_premium($link, $u) : false;
$editable = true;

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
    try {
        if (isset($_POST['delete'])) {
            if (!$link) {
                redirect('/dashboard');
            }
            db_exec('DELETE FROM links WHERE id = ?', [(int) $link['id']]);
            flash('ok', t('link_deleted'));
            redirect('/dashboard');
        }
        $title = (string) ($_POST['title'] ?? '');
        $active = isset($_POST['active']) ? 1 : 0;
        if ($link) {
            if ($editable) {
                $phone = normalize_phone((string) ($_POST['cc'] ?? ''), (string) ($_POST['phone'] ?? ''));
                $message = trim((string) ($_POST['message'] ?? ''));
                if (!valid_phone($phone)) {
                    throw new InvalidArgumentException(t('err_phone'));
                }
                if (mb_strlen($message) > 1000) {
                    throw new InvalidArgumentException(t('err_message_long'));
                }
                $domain = in_array($_POST['domain'] ?? '', $domains, true) ? $_POST['domain'] : $link['domain'];
                db_exec('UPDATE links SET phone = ?, message = ?, title = ?, domain = ?, active = ?, updated_at = ? WHERE id = ?',
                    [$phone, $message, mb_substr(trim($title), 0, 80), $domain, $active, now(), (int) $link['id']]);
            } else {
                db_exec('UPDATE links SET title = ?, updated_at = ? WHERE id = ?', [mb_substr(trim($title), 0, 80), now(), (int) $link['id']]);
            }
            flash('ok', t('link_saved'));
            redirect('/dashboard');
        }
        if (!can_add_link($u)) {
            throw new InvalidArgumentException(t('err_limit', ['n' => plan_max_links(user_plan($u))]));
        }
        $phone = normalize_phone((string) ($_POST['cc'] ?? ''), (string) ($_POST['phone'] ?? ''));
        $opts = ['user_id' => (int) $u['id'], 'title' => $title, 'domain' => (string) ($_POST['domain'] ?? current_domain())];
        $handle = strtolower(trim((string) ($_POST['code'] ?? '')));
        if ($handle !== '') {
            // Validate now so the user gets the error before the link exists.
            assert_custom_code($handle);
            if (slug_taken($handle)) {
                throw new InvalidArgumentException(t('err_code_taken'));
            }
            if ($premium) {
                $opts['code'] = $handle; // account-level plan (admin-granted): custom handle straight away
            }
        }
        $new = create_link($phone, (string) ($_POST['message'] ?? ''), $opts);
        flash('ok', t('link_created'));
        if ($handle !== '' && !$premium) {
            // Branded handle is a per-link Premium purchase: go to checkout with the handle pre-filled.
            redirect('/upgrade?id=' . (int) $new['id'] . '&handle=' . urlencode($handle));
        }
        redirect('/qr?id=' . (int) $new['id']);
    } catch (InvalidArgumentException $e) {
        $err = $e->getMessage();
    } catch (Throwable $e) {
        error_log('link save: ' . $e->getMessage());
        $err = t('err_generic');
    }
}

[$dial, $national] = $link ? split_phone($link['phone']) : ['', ''];
$selIso = 'MY';
foreach (country_codes() as $iso => [$d]) {
    if ($d === $dial) {
        $selIso = $iso;
        break;
    }
}
$v = fn(string $k, string $fallback = '') => e((string) ($_POST[$k] ?? ($link[$k] ?? $fallback)));

page_start(['title' => $link ? t('link_edit_title') : t('link_new_title'), 'noindex' => true, 'body_class' => 'page-app']);
?>
<section class="app wrap form-narrow">
  <p><a href="/dashboard">← <?= e(t('dash_title')) ?></a></p>
  <div class="app-head"><h1><?= e($link ? t('link_edit_title') : t('link_new_title')) ?></h1></div>
  <?php if ($err): ?><div class="flash flash-error" role="alert" style="margin-bottom:16px"><?= error_html($err) ?></div><?php endif; ?>
  <?php if ($link && !$editable): ?><div class="locked">🔒 <?= e(t('link_free_locked')) ?> <a href="/upgrade?id=<?= (int) $link['id'] ?>"><b><?= e(t('dash_upgrade')) ?> →</b></a></div><?php endif; ?>

  <form method="post" class="card" novalidate>
    <?= csrf_field() ?>
    <?php if ($link): ?><input type="hidden" name="id" value="<?= (int) $link['id'] ?>"><?php endif; ?>

    <?php if ($link): ?>
      <div class="field"><span class="label">Link</span><div class="result-link" style="font-size:20px"><?= e(short_display($link)) ?></div></div>
    <?php else: ?>
      <div class="field">
        <label for="handle"><?= e(t('link_handle')) ?> <small><?= e(t('link_premium_only')) ?> · <?= e(price('premium')) ?>/<?= e(t('up_year')) ?></small></label>
        <div class="inline-prefix"><span><?= e(current_domain()) ?>/</span><input id="handle" name="code" maxlength="30" value="<?= $v('code') ?>" placeholder="<?= e(t('up_handle_ph')) ?>" pattern="[a-z0-9-]{3,30}"></div>
        <p class="hint"><?= e($premium ? t('link_handle_hint') : t('link_handle_free')) ?></p>
      </div>
    <?php endif; ?>

    <div class="field">
      <label for="title"><?= e(t('link_title')) ?></label>
      <input class="input" id="title" name="title" maxlength="80" value="<?= $v('title') ?>" placeholder="<?= e(t('link_title_ph')) ?>">
    </div>

    <fieldset style="border:0;padding:0;margin:0" <?= $editable ? '' : 'disabled' ?>>
      <div class="field">
        <label for="phone"><?= e(t('gen_phone')) ?></label>
        <div class="phone-row">
          <select class="select" name="cc"><?= country_options(isset($_POST['cc']) ? $selIso : ($link ? $selIso : default_country())) ?></select>
          <input class="input" id="phone" name="phone" type="tel" inputmode="numeric" value="<?= e((string) ($_POST['phone'] ?? $national)) ?>" placeholder="12 345 6789" required data-phone>
        </div>
        <p class="hint"><?= e(t('gen_phone_hint')) ?></p>
      </div>
      <div class="field">
        <label for="message"><?= e(t('gen_message')) ?> <small><?= e(t('gen_optional')) ?></small></label>
        <textarea class="input" id="message" name="message" maxlength="1000" placeholder="<?= e(t('gen_message_ph')) ?>"><?= $v('message') ?></textarea>
      </div>
      <?php if (count($domains) > 1): ?>
      <div class="field">
        <span class="label"><?= e(t('gen_domain')) ?></span>
        <div class="seg">
          <?php $cur = (string) ($_POST['domain'] ?? ($link['domain'] ?? current_domain())); foreach ($domains as $d): ?>
            <input type="radio" name="domain" id="dom-<?= e($d) ?>" value="<?= e($d) ?>" <?= $d === $cur ? 'checked' : '' ?>><label for="dom-<?= e($d) ?>"><?= e($d) ?></label>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
      <?php if ($link): ?>
      <div class="field"><label class="switch"><input type="checkbox" name="active" <?= (int) $link['active'] ? 'checked' : '' ?>> <?= e(t('link_active')) ?></label></div>
      <?php endif; ?>
    </fieldset>

    <div class="form-actions">
      <button class="btn btn-primary btn-lg" type="submit"><?= e($link ? t('link_save') : t('link_create')) ?></button>
      <a class="btn btn-ghost" href="/dashboard">←</a>
    </div>
  </form>

  <?php if ($link): ?>
  <form method="post" class="form-actions" data-confirm="<?= e(t('link_delete_conf')) ?>" style="margin-top:18px">
    <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $link['id'] ?>"><input type="hidden" name="delete" value="1">
    <button class="btn btn-sm btn-danger right" type="submit"><?= e(t('link_delete')) ?></button>
  </form>
  <?php endif; ?>
</section>
<?php page_end();
