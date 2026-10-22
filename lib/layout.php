<?php
declare(strict_types=1);

/**
 * Page chrome. Usage:
 *   page_start(['title' => '...', 'description' => '...', 'body_class' => 'page-auth']);
 *   ... html ...
 *   page_end();
 */

function page_start(array $o = []): void
{
    $site = (string) cfg('site_name');
    $title = isset($o['title']) ? $o['title'] . ' · ' . $site : $site . ' — ' . t('hero_title_1') . ' ' . t('hero_title_2');
    $desc = $o['description'] ?? t('hero_lead', ['domain' => current_domain()]);
    $canonical = $o['canonical'] ?? (base_url() . strtok($_SERVER['REQUEST_URI'] ?? '/', '?'));
    $noindex = !empty($o['noindex']);
    $u = current_user();
    $l = lang();
    $ga = (string) cfg('ga_id');
    $gsv = (string) cfg('google_site_verification');
    ?>
<!doctype html>
<html lang="<?= $l ?>"<?php $geo = request_country_header(); echo $geo !== '' ? ' data-geo="' . e($geo) . '"' : ''; ?>>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<link rel="canonical" href="<?= e($canonical) ?>">
<?php if ($noindex): ?><meta name="robots" content="noindex,nofollow"><?php endif; ?>
<?php if ($gsv !== ''): ?><meta name="google-site-verification" content="<?= e($gsv) ?>"><?php endif; ?>
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= e($site) ?>">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:image" content="<?= e(base_url()) ?>/assets/img/og.png">
<meta name="twitter:card" content="summary_large_image">
<meta name="theme-color" content="#0b3d2e">
<link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/app.css?v=<?= filemtime(WSAP_ROOT . '/assets/css/app.css') ?>">
<?php if ($ga !== ''): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($ga) ?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config','<?= e($ga) ?>');</script>
<?php endif; ?>
</head>
<body class="<?= e($o['body_class'] ?? '') ?>">
<a class="skip" href="#main">Skip to content</a>
<header class="site-header">
  <div class="wrap nav">
    <a class="brand" href="/" aria-label="<?= e($site) ?>">
      <span class="brand-mark" aria-hidden="true"></span>
      <span class="brand-name">wsap<span>.<?= e(substr(current_domain(), strrpos(current_domain(), '.') + 1)) ?></span></span>
    </a>
    <nav class="nav-links" aria-label="Main">
      <a href="/#generator"><?= e(t('nav_free')) ?></a>
      <div class="nav-dd"><a href="/premium-links" aria-haspopup="true"><?= e(t('nav_products')) ?></a>
        <div class="nav-dd-menu">
          <a href="/premium-links"><span class="ico">✨</span><span><?= e(t('nav_p_links')) ?><small><?= e(t('nav_p_links_d')) ?></small></span></a>
          <a href="/smart-links"><span class="ico">⚡</span><span><?= e(t('nav_p_smart')) ?><small><?= e(t('nav_p_smart_d')) ?></small></span></a>
          <a href="/whatsapp-forms"><span class="ico">📝</span><span><?= e(t('nav_p_forms')) ?><small><?= e(t('nav_p_forms_d')) ?></small></span></a>
          <a href="/whatsapp-shop"><span class="ico">🛍️</span><span><?= e(t('nav_p_shops')) ?><small><?= e(t('nav_p_shops_d')) ?></small></span></a>
        </div>
      </div>
      <a href="/pricing"><?= e(t('nav_pricing')) ?></a>
      <a href="/#faq"><?= e(t('nav_faq')) ?></a>
    </nav>
    <div class="nav-actions">
      <?php $cur = currency(); ?>
      <a class="lang-switch cur-switch" href="?cur=<?= $cur === 'MYR' ? 'USD' : 'MYR' ?>" title="<?= e(t('cur_switch_title')) ?>"><span class="<?= $cur === 'MYR' ? 'on' : '' ?>">RM</span><span class="<?= $cur === 'USD' ? 'on' : '' ?>">$</span></a>
      <a class="lang-switch" href="?lang=<?= $l === 'en' ? 'ms' : 'en' ?>" title="<?= e(t('footer_lang')) ?>"><?= $l === 'en' ? 'BM' : 'EN' ?></a>
      <?php if ($u): ?>
        <a class="btn btn-ghost" href="/dashboard"><?= e(t('nav_dashboard')) ?></a>
      <?php else: ?>
        <a class="btn btn-ghost" href="/login"><?= e(t('nav_login')) ?></a>
        <a class="btn btn-primary" href="/signup"><?= e(t('nav_signup')) ?></a>
      <?php endif; ?>
    </div>
    <button class="nav-burger" aria-label="Menu" aria-expanded="false" data-burger><span></span></button>
  </div>
</header>
<main id="main">
<?php foreach (flashes() as $f): ?>
  <div class="wrap"><div class="flash flash-<?= e($f['type']) ?>" role="status"><?= e($f['msg']) ?></div></div>
<?php endforeach; ?>
<?php
}

/** Escape an error message; a trailing "[[buy:handle]]" marker becomes a "Buy this handle" button. */
function error_html(string $err): string
{
    if (preg_match('/^(.*)\s\[\[buy:([a-z0-9-]+)\]\]$/s', $err, $m)) {
        return e($m[1]) . ' <a class="btn btn-sm btn-primary" style="margin-left:8px" href="/upgrade?handle=' . e($m[2]) . '">★ ' . e(t('up_buy_handle')) . '</a>';
    }
    return e($err);
}

function page_end(): void
{
    $wa = (string) cfg('support_whatsapp');
    $site = (string) cfg('site_name');
    ?>
</main>
<footer class="site-footer">
  <div class="wrap footer-grid">
    <div>
      <a class="brand" href="/"><span class="brand-mark" aria-hidden="true"></span><span class="brand-name">wsap</span></a>
      <p class="footer-tag"><?= e(t('footer_tag')) ?></p>
      <p class="footer-domains">wsap.me · wsap.my</p>
    </div>
    <div>
      <h4><?= e(t('footer_product')) ?></h4>
      <a href="/#generator"><?= e(t('nav_free')) ?></a>
      <a href="/premium-links"><?= e(t('nav_p_links')) ?></a>
      <a href="/smart-links"><?= e(t('nav_p_smart')) ?></a>
      <a href="/whatsapp-forms"><?= e(t('nav_p_forms')) ?></a>
      <a href="/whatsapp-shop"><?= e(t('nav_p_shops')) ?></a>
      <a href="/pricing"><?= e(t('nav_pricing')) ?></a>
      <a href="/#faq"><?= e(t('nav_faq')) ?></a>
    </div>
    <div>
      <h4><?= e(t('footer_legal')) ?></h4>
      <a href="/terms"><?= e(t('footer_terms')) ?></a>
      <a href="/privacy"><?= e(t('footer_privacy')) ?></a>
      <?php if ($wa !== ''): ?><a href="https://wa.me/<?= e($wa) ?>" rel="noopener"><?= e(t('footer_contact')) ?></a><?php endif; ?>
      <a href="?lang=en">English</a>
      <a href="?lang=ms">Bahasa Melayu</a>
    </div>
  </div>
  <div class="wrap footer-bottom"><span>© <?= date('Y') ?> <?= e($site) ?> · Malaysia</span><span class="footer-disclaimer"><?= e(t('footer_disclaimer')) ?></span></div>
</footer>
<script src="/assets/js/app.js?v=<?= filemtime(WSAP_ROOT . '/assets/js/app.js') ?>" defer></script>
<script src="/assets/js/geo.js?v=<?= filemtime(WSAP_ROOT . '/assets/js/geo.js') ?>" defer></script>
</body>
</html>
<?php
}
