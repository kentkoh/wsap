<?php
declare(strict_types=1);
require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/layout.php';
require __DIR__ . '/lib/forms.php';
require __DIR__ . '/lib/app_nav.php';

$u = require_login();
$forms = user_forms((int) $u['id']);

page_start(['title' => t('fm_title'), 'noindex' => true, 'body_class' => 'page-app']);
?>
<section class="app wrap">
  <?= app_nav('forms') ?>
  <div class="app-head"><h1><?= e(t('fm_title')) ?></h1><a class="btn btn-primary" href="/form">+ <?= e(t('fm_new')) ?></a></div>
  <p class="lead" style="font-size:15px;margin-bottom:18px"><?= e(t('fm_lead')) ?></p>
  <?php if (!$forms): ?>
    <div class="empty"><h2><?= e(t('fm_empty_t')) ?></h2><p class="muted"><?= e(t('fm_empty_d')) ?></p><a class="btn btn-wa btn-lg" href="/form"><?= e(t('fm_new')) ?> →</a></div>
  <?php else: ?>
  <div class="link-list">
    <?php foreach ($forms as $f): $pub = form_is_published($f, $u); ?>
    <article class="link-card">
      <div>
        <div class="code"><a href="<?= e(form_url($f)) ?>" target="_blank" rel="noopener"><?= e(form_display($f)) ?></a>
          <?php if ($pub): ?><span class="badge badge-prem"><?= e(t('fm_published')) ?><?= !empty($f['premium_until']) ? ' · ' . e(substr((string) $f['premium_until'], 0, 10)) : '' ?></span><?php else: ?><span class="badge badge-off"><?= e(t('fm_draft')) ?></span><?php endif; ?></div>
        <div class="sub"><b><?= e($f['title']) ?></b><span>+<?= e($f['phone']) ?></span><span><?= count(form_questions($f)) ?> <?= e(t('fm_questions')) ?></span></div>
      </div>
      <div class="stats"><b><?= number_format((int) $f['submits']) ?></b><span><?= e(t('fm_responses')) ?></span></div>
      <div class="actions">
        <button class="btn btn-sm btn-ghost" type="button" data-copy="<?= e(form_url($f)) ?>" data-copied="<?= e(t('res_copied')) ?>"><?= e(t('res_copy')) ?></button>
        <a class="btn btn-sm btn-ghost" href="/form?id=<?= (int) $f['id'] ?>"><?= e(t('dash_edit')) ?></a>
        <a class="btn btn-sm btn-ghost" href="/responses?id=<?= (int) $f['id'] ?>"><?= e(t('fm_responses')) ?></a>
        <?php if (!$pub): ?><a class="btn btn-sm btn-primary" href="/upgrade?form=<?= (int) $f['id'] ?>">★ <?= e(t('fm_publish')) ?></a><?php else: ?><a class="btn btn-sm btn-ghost" href="/upgrade?form=<?= (int) $f['id'] ?>"><?= e(t('dash_extend')) ?></a><?php endif; ?>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>
<?php page_end();
