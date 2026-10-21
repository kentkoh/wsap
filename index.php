<?php
declare(strict_types=1);
require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/layout.php';
require __DIR__ . '/lib/countries.php';
require __DIR__ . '/lib/ua.php';

$domain = current_domain();
$domains = cfg('domains');
$premPrice = price('premium');
$waIcon = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2m0 1.67c4.56 0 8.24 3.68 8.24 8.24s-3.68 8.24-8.24 8.24c-1.5 0-2.97-.41-4.26-1.18l-.3-.18-3.12.82.83-3.04-.2-.31a8.2 8.2 0 0 1-1.26-4.35c0-4.56 3.68-8.24 8.24-8.24m-3.4 4.43c-.19 0-.5.07-.76.35-.26.28-1 .98-1 2.38 0 1.4 1.02 2.76 1.17 2.95.14.19 2 3.17 4.93 4.32 2.44.96 2.93.77 3.46.72.53-.05 1.71-.7 1.95-1.37.24-.68.24-1.26.17-1.38-.07-.12-.26-.19-.55-.33-.29-.14-1.71-.84-1.97-.94-.26-.1-.46-.14-.65.14-.19.29-.75.94-.92 1.13-.17.19-.34.22-.63.07-.29-.14-1.22-.45-2.32-1.43-.86-.76-1.44-1.71-1.6-2-.17-.29-.02-.45.13-.59.13-.13.29-.34.43-.5.14-.17.19-.29.29-.48.1-.19.05-.36-.02-.5-.07-.14-.65-1.56-.89-2.14-.23-.56-.47-.49-.65-.5h-.55z"/></svg>';

page_start(['body_class' => 'page-home']);
?>
<section class="hero">
  <div class="wrap hero-grid">
    <div>
      <span class="kicker"><?= e(t('hero_kicker')) ?></span>
      <h1><?= e(t('hero_title_1')) ?><br><span class="serif-i"><?= e(t('hero_title_2')) ?></span></h1>
      <p class="lead"><?= e(t('hero_lead', ['domain' => $domain])) ?></p>
      <div class="hero-ctas">
        <a class="btn btn-wa btn-lg" href="#generator"><?= $waIcon ?> <?= e(t('hero_cta_free')) ?></a>
        <a class="btn btn-ghost btn-lg" href="/pricing"><?= e(t('hero_cta_prem')) ?></a>
      </div>
      <p class="hero-trust"><?= e(t('hero_trust')) ?></p>
      <div class="hero-domains"><?php foreach ($domains as $d => $dc): ?><span><?= e($d) ?> · <?= e($dc['label']) ?></span><?php endforeach; ?></div>
    </div>

    <div class="gen-card" id="generator">
      <form id="gen-form" data-csrf="<?= e(csrf_token()) ?>"
            data-working="<?= e(t('gen_working')) ?>"
            data-opens="<?= e(t('res_opens')) ?>"
            data-opensNoMsg="<?= e(t('res_no_message')) ?>" novalidate>
        <div id="gen-fields">
          <div class="gen-head"><span class="wa-ico"><?= $waIcon ?></span><h2><?= e(t('gen_title')) ?></h2><span class="pill pill-green"><?= e(t('gen_free')) ?></span></div>
          <p class="gen-lead"><?= e(t('gen_lead')) ?></p>
          <div class="gen-error" id="gen-error" role="alert"></div>

          <div class="field">
            <label for="phone"><?= e(t('gen_phone')) ?></label>
            <div class="phone-row">
              <select class="select" name="cc" aria-label="<?= e(t('link_phone_cc')) ?>"><?= country_options(default_country()) ?></select>
              <input class="input" id="phone" name="phone" type="tel" inputmode="numeric" autocomplete="tel-national" placeholder="12 345 6789" required data-phone>
            </div>
            <p class="hint"><?= e(t('gen_phone_hint')) ?></p>
          </div>

          <div class="field">
            <label for="message"><?= e(t('gen_message')) ?> <small><?= e(t('gen_optional')) ?></small></label>
            <textarea class="input" id="message" name="message" maxlength="1000" placeholder="<?= e(t('gen_message_ph')) ?>"></textarea>
            <p class="hint"><?= e(t('gen_message_hint')) ?></p>
          </div>

          <?php if (count($domains) > 1): ?>
          <div class="field">
            <span class="label"><?= e(t('gen_domain')) ?></span>
            <div class="seg">
              <?php foreach ($domains as $d => $dc): ?>
                <input type="radio" name="domain" id="dom-<?= e($d) ?>" value="<?= e($d) ?>" <?= $d === $domain ? 'checked' : '' ?>>
                <label for="dom-<?= e($d) ?>"><?= e($d) ?></label>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>

          <div class="upsell-row">✦ <?= e(t('gen_premium_row')) ?> <a href="/pricing"><?= e(t('nav_premium')) ?> →</a></div>

          <button class="btn btn-wa btn-lg btn-block" id="gen-submit" type="submit"><?= e(t('gen_submit')) ?> →</button>
          <div class="gen-foot"><span class="check"><?= e(t('gen_unlimited')) ?></span><span class="check"><?= e(t('gen_forever')) ?></span></div>
        </div>
      </form>

      <div class="result" id="gen-result" aria-live="polite">
        <span class="kicker"><?= e(t('res_kicker')) ?></span>
        <h2 style="font-size:28px;margin-bottom:16px"><?= e(t('res_title')) ?></h2>
        <div class="result-box">
          <div>
            <span class="result-link" id="res-url"></span>
            <p class="result-meta" id="res-meta"></p>
            <span class="bubble" id="res-bubble"></span>
          </div>
          <div>
            <div class="qr-wrap"><div id="res-qr"></div></div>
            <a href="#" id="res-dl" class="small mono" style="display:block;text-align:center;margin-top:8px">⤓ <?= e(t('res_download')) ?></a>
          </div>
        </div>
        <div class="result-actions">
          <button class="btn btn-primary" id="res-copy" type="button" data-copy="" data-copied="<?= e(t('res_copied')) ?>"><?= e(t('res_copy')) ?></button>
          <button class="btn btn-ghost" id="res-share" type="button" data-share="" data-copied="<?= e(t('res_copied')) ?>"><?= e(t('res_share')) ?></button>
          <a class="btn btn-ghost" id="res-try" href="#" target="_blank" rel="noopener"><?= e(t('res_try')) ?> ↗</a>
          <a class="btn btn-ghost" id="res-again" href="#"><?= e(t('res_another')) ?></a>
        </div>
        <p class="result-note"><?= e(t('res_note')) ?>
          <?php if (!current_user()): ?><a href="/signup" id="res-save"><b><?= e(t('res_save')) ?> →</b></a><?php else: ?><a href="/dashboard"><b><?= e(t('nav_dashboard')) ?> →</b></a><?php endif; ?>
        </p>
        <div class="upsell-row" style="margin-top:14px">
          <span><span class="kicker" style="margin:0 0 2px;display:block"><?= e(t('res_upsell_k', ['price' => $premPrice])) ?></span><b><?= e(t('res_upsell_t', ['domain' => $domain])) ?></b></span>
          <a href="/pricing"><?= e(t('res_upsell_cta')) ?> →</a>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section" id="where">
  <div class="wrap">
    <div class="section-head reveal">
      <span class="kicker"><?= e(t('use_kicker')) ?></span>
      <h2><?= e(t('use_title')) ?></h2>
      <p class="lead"><?= e(t('use_lead')) ?></p>
    </div>
    <div class="bento">
      <div class="cell wide reveal"><div class="ico">📱</div><h3><?= e(t('use_1_t')) ?></h3><p><?= e(t('use_1_d')) ?></p></div>
      <div class="cell wide reveal"><div class="ico">🎯</div><h3><?= e(t('use_2_t')) ?></h3><p><?= e(t('use_2_d')) ?></p></div>
      <div class="cell accent reveal"><div class="ico">▦</div><h3><?= e(t('use_3_t')) ?></h3><p><?= e(t('use_3_d')) ?></p></div>
      <div class="cell reveal"><div class="ico">🛍️</div><h3><?= e(t('use_4_t')) ?></h3><p><?= e(t('use_4_d')) ?></p></div>
      <div class="cell reveal"><div class="ico">✉️</div><h3><?= e(t('use_5_t')) ?></h3><p><?= e(t('use_5_d', ['domain' => $domain])) ?></p></div>
      <div class="cell wide reveal"><div class="ico">🌐</div><h3><?= e(t('use_6_t')) ?></h3><p><?= e(t('use_6_d')) ?></p></div>
    </div>
  </div>
</section>

<section class="section band-dark" id="how">
  <div class="wrap">
    <div class="section-head reveal">
      <span class="kicker"><?= e(t('how_kicker')) ?></span>
      <h2><?= e(t('how_title')) ?></h2>
    </div>
    <div class="steps">
      <div class="step reveal"><h3><?= e(t('how_1_t')) ?></h3><p><?= e(t('how_1_d')) ?></p></div>
      <div class="step reveal"><h3><?= e(t('how_2_t')) ?></h3><p><?= e(t('how_2_d', ['domain' => $domain])) ?></p></div>
      <div class="step reveal"><h3><?= e(t('how_3_t')) ?></h3><p><?= e(t('how_3_d')) ?></p></div>
    </div>
  </div>
</section>

<section class="section" id="premium">
  <div class="wrap">
    <div class="section-head reveal">
      <span class="kicker"><?= e(t('cmp_kicker')) ?></span>
      <h2><?= e(t('cmp_title')) ?></h2>
      <p class="lead"><?= e(t('cmp_lead', ['price' => $premPrice])) ?></p>
    </div>
    <table class="compare reveal">
      <thead><tr><th><?= e(t('cmp_feature')) ?></th><th class="c"><?= e(t('cmp_free')) ?></th><th class="c prem"><?= e(t('cmp_premium')) ?></th></tr></thead>
      <tbody>
        <?php
        $rows = [
            ['cmp_1', true, true], ['cmp_2', true, true], ['cmp_3', 'cmp_random', true], ['cmp_4', false, true],
            ['cmp_5', false, true], ['cmp_6', false, true], ['cmp_7', false, true], ['cmp_8', false, true],
            ['cmp_9', true, true], ['cmp_10', false, true],
        ];
        foreach ($rows as [$k, $free, $prem]):
            $cell = fn($v) => $v === true ? '<span class="yes">✓</span>' : ($v === false ? '<span class="no">—</span>' : '<span class="note">' . e(t($v)) . '</span>');
        ?>
        <tr><td><?= e(t($k, ['domain' => $domain])) ?></td><td class="c"><?= $cell($free) ?></td><td class="c prem"><?= $cell($prem) ?></td></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <p style="margin-top:22px"><a class="btn btn-primary btn-lg" href="/pricing"><?= e(t('cmp_cta')) ?> →</a></p>
  </div>
</section>

<section class="section band" id="faq">
  <div class="wrap">
    <div class="section-head reveal"><h2><?= e(t('faq_title')) ?></h2></div>
    <div class="faq reveal">
      <?php for ($i = 1; $i <= 8; $i++): ?>
      <details<?= $i === 1 ? ' open' : '' ?>><summary><?= e(t("faq_{$i}_q")) ?></summary><div class="a"><?= e(t("faq_{$i}_a")) ?></div></details>
      <?php endfor; ?>
    </div>
  </div>
</section>

<section class="section" style="padding-bottom:0">
  <div class="wrap">
    <div class="cta-band reveal">
      <h2><?= e(t('cta_title')) ?></h2>
      <p class="lead"><?= e(t('cta_lead')) ?></p>
      <a class="btn btn-wa btn-lg" href="#generator"><?= $waIcon ?> <?= e(t('hero_cta_free')) ?></a>
    </div>
  </div>
</section>

<script type="application/ld+json">
<?= json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        ['@type' => 'WebApplication', 'name' => 'WSAP', 'url' => base_url(), 'applicationCategory' => 'BusinessApplication', 'operatingSystem' => 'Web',
         'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => domain_cfg()['currency']],
         'description' => t('hero_lead', ['domain' => $domain])],
        ['@type' => 'FAQPage', 'mainEntity' => array_map(fn($i) => ['@type' => 'Question', 'name' => t("faq_{$i}_q"), 'acceptedAnswer' => ['@type' => 'Answer', 'text' => t("faq_{$i}_a")]], range(1, 8))],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
</script>
<?php page_end();
