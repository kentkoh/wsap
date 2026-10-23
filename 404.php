<?php
declare(strict_types=1);
/** Friendly 404 (Apache ErrorDocument). Keeps the real 404 status so search engines drop the URL. */
require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/layout.php';
http_response_code(404);
page_start(['title' => t('not_found_t'), 'noindex' => true]);
?>
<section class="wrap" style="max-width:560px;padding:80px 16px;text-align:center">
  <span class="kicker">404</span>
  <h1 style="font-size:32px;margin:8px 0 12px"><?= e(t('nf_page_t')) ?></h1>
  <p class="muted"><?= e(t('nf_page_d')) ?></p>
  <p style="margin-top:24px;display:flex;gap:10px;justify-content:center;flex-wrap:wrap"><a class="btn btn-wa" href="/#generator"><?= e(t('make_own')) ?></a><a class="btn btn-ghost" href="/"><?= e(t('nav_home')) ?></a></p>
</section>
<?php page_end();
