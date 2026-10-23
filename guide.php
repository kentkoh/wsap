<?php
declare(strict_types=1);
/** SEO guide pages: /whatsapp-link-generator, /wa-me-link, ... (routed by .htaccess, content in lib/guides.php). */
require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/layout.php';
require __DIR__ . '/lib/seo.php';
require __DIR__ . '/lib/guides.php';
require __DIR__ . '/lib/generator.php';

$slug = preg_replace('/[^a-z0-9-]/', '', (string) ($_GET['g'] ?? ''));
$g = guide($slug, lang());
if (!$g) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}
$domain = current_domain();
$fill = fn(string $s) => str_replace('{domain}', $domain, $s);
$path = '/' . $slug;
$faq = array_map(fn($p) => [$fill($p[0]), $fill($p[1])], $g['faq']);
$steps = array_map(fn($p) => [$fill($p[0]), $fill($p[1])], $g['steps']);
$jsonld = [
    seo_breadcrumbs([[t('nav_home'), '/'], [$fill($g['h1']), $path]]),
    seo_howto($fill($g['h1']), $steps, $fill($g['lead'])),
    seo_faq($faq),
    seo_app(cfg('site_name') . ' ' . t('nav_free'), $fill($g['lead']), $path, '0', currency(), true),
];
page_start(['title_raw' => $fill($g['title']) . ' · ' . cfg('site_name'), 'description' => $fill($g['lead']), 'path' => $path, 'jsonld' => $jsonld, 'body_class' => 'page-guide']);
?>
<section class="hero">
  <div class="wrap hero-grid">
    <div>
      <nav class="crumbs" aria-label="Breadcrumb"><a href="/"><?= e(t('nav_home')) ?></a> › <span><?= e(t('gd_guides')) ?></span></nav>
      <h1><?= e($fill($g['h1'])) ?></h1>
      <p class="lead"><?= e($fill($g['lead'])) ?></p>
      <div class="hero-ctas"><a class="btn btn-wa btn-lg" href="#generator"><?= e(t('hero_cta_free')) ?></a><a class="btn btn-ghost btn-lg" href="/members"><?= e(t('mem_cta')) ?></a></div>
      <p class="hero-trust"><?= e(t('hero_trust')) ?></p>
    </div>
    <?= generator_card() ?>
  </div>
</section>

<section class="section">
  <div class="wrap guide-body">
    <?php foreach ($g['sections'] as [$h2, $html]): ?>
    <h2><?= e($fill($h2)) ?></h2>
    <?= $fill($html) ?>
    <?php endforeach; ?>
  </div>
</section>

<section class="section band-dark">
  <div class="wrap">
    <div class="section-head reveal"><span class="kicker"><?= e(t('gd_steps_k')) ?></span><h2><?= e(t('gd_steps_t')) ?></h2></div>
    <div class="steps">
      <?php foreach ($steps as [$n, $d]): ?><div class="step reveal"><h3><?= e($n) ?></h3><p><?= e($d) ?></p></div><?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section band" id="faq">
  <div class="wrap">
    <div class="section-head reveal"><h2><?= e(t('faq_title')) ?></h2></div>
    <div class="faq reveal">
      <?php foreach ($faq as $i => [$q, $a]): ?><details<?= $i === 0 ? ' open' : '' ?>><summary><?= e($q) ?></summary><div class="a"><?= e($a) ?></div></details><?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="wrap">
    <div class="section-head reveal"><span class="kicker"><?= e(t('gd_more_k')) ?></span><h2><?= e(t('gd_more_t')) ?></h2></div>
    <div class="guide-links reveal">
      <?php foreach ($g['related'] as [$label, $href]): ?><a class="guide-link" href="<?= e($href) ?>"><?= e($label) ?> →</a><?php endforeach; ?>
      <?php foreach (guide_slugs() as $s => $ico): if ($s === $slug) { continue; } $o = guide($s, lang()); ?><a class="guide-link" href="/<?= e($s) ?>"><?= $ico ?> <?= e($fill($o['h1'])) ?> →</a><?php endforeach; ?>
    </div>
  </div>
</section>
<?php page_end();
