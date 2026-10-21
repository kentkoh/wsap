<?php
declare(strict_types=1);
require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/layout.php';

$u = require_login();
$links = user_links((int) $u['id']);
$plan = user_plan($u);
$max = (int) cfg('plans.' . $plan . '.max_links');
$wa = (string) cfg('support_whatsapp');

page_start(['title' => t('dash_title'), 'noindex' => true, 'body_class' => 'page-app']);
?>
<section class="app wrap">
  <?php if (empty($u['verified_at'])): ?>
  <form method="post" action="/verify" class="flash flash-warn" style="display:flex;gap:12px;align-items:center;margin-bottom:18px">
    <?= csrf_field() ?><span><?= e(t('auth_verify_banner')) ?></span><button class="btn btn-sm btn-ghost" type="submit" style="margin-left:auto"><?= e(t('auth_verify_resend')) ?></button>
  </form>
  <?php endif; ?>
  <div class="app-head">
    <h1><?= e(t('dash_title')) ?></h1>
    <span class="plan-chip"><?= e(t('dash_plan')) ?>: <b><?= e(plan_name($plan)) ?></b> · <?= e(t('dash_links_used', ['used' => count($links), 'max' => $max])) ?>
      <?php if ($plan === 'free'): ?> · <a href="/pricing"><?= e(t('dash_upgrade')) ?></a><?php endif; ?></span>
    <?php if (($u['role'] ?? '') === ROLE_ADMIN): ?><a class="btn btn-ghost btn-sm" href="/admin" style="margin-left:auto">Admin</a><?php endif; ?>
    <a class="btn btn-primary" href="/link" <?= ($u['role'] ?? '') === ROLE_ADMIN ? 'style="margin-left:0"' : '' ?>>+ <?= e(t('dash_new')) ?></a>
    <form method="post" action="/logout" style="margin:0"><?= csrf_field() ?><button class="btn btn-ghost btn-sm" type="submit"><?= e(t('nav_logout')) ?></button></form>
  </div>

  <?php if (!$links): ?>
    <div class="empty"><h2><?= e(t('dash_empty_t')) ?></h2><p class="muted"><?= e(t('dash_empty_d')) ?></p><a class="btn btn-wa btn-lg" href="/link"><?= e(t('dash_new')) ?> →</a></div>
  <?php else: ?>
  <div class="link-list">
    <?php foreach ($links as $l): ?>
    <article class="link-card">
      <div>
        <div class="code"><a href="<?= e(short_url($l)) ?>" target="_blank" rel="noopener"><?= e(short_display($l)) ?></a>
          <?php if ((int) $l['is_custom']): ?><span class="badge badge-prem"><?= e(t('dash_prem_badge')) ?></span><?php else: ?><span class="badge"><?= e(t('dash_free_badge')) ?></span><?php endif; ?>
          <?php if (!(int) $l['active']): ?><span class="badge badge-off"><?= e(t('dash_paused')) ?></span><?php endif; ?>
        </div>
        <div class="sub">
          <?php if ($l['title'] !== ''): ?><b><?= e($l['title']) ?></b><?php endif; ?>
          <span>+<?= e($l['phone']) ?></span>
          <?php if ($l['message'] !== ''): ?><span class="msg">“<?= e($l['message']) ?>”</span><?php endif; ?>
          <span><?= e(t('dash_created')) ?> <time data-utc="<?= e($l['created_at']) ?>"><?= e(substr($l['created_at'], 0, 10)) ?></time></span>
        </div>
      </div>
      <div class="stats"><b><?= number_format((int) $l['clicks']) ?></b><span><?= e(t('dash_clicks')) ?></span></div>
      <div class="actions">
        <button class="btn btn-sm btn-ghost" type="button" data-copy="<?= e(short_url($l)) ?>" data-copied="<?= e(t('res_copied')) ?>"><?= e(t('res_copy')) ?></button>
        <a class="btn btn-sm btn-ghost" href="/qr?id=<?= (int) $l['id'] ?>"><?= e(t('dash_qr')) ?></a>
        <a class="btn btn-sm btn-ghost" href="/analytics?id=<?= (int) $l['id'] ?>"><?= e(t('dash_stats')) ?></a>
        <a class="btn btn-sm btn-ghost" href="/link?id=<?= (int) $l['id'] ?>"><?= e(t('dash_edit')) ?></a>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>
<?php page_end();
