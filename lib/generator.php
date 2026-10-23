<?php
declare(strict_types=1);

/** The free link generator card (homepage hero + SEO guide pages). JS lives in assets/js/app.js. */

function wa_icon_svg(): string
{
    return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2m0 1.67c4.56 0 8.24 3.68 8.24 8.24s-3.68 8.24-8.24 8.24c-1.5 0-2.97-.41-4.26-1.18l-.3-.18-3.12.82.83-3.04-.2-.31a8.2 8.2 0 0 1-1.26-4.35c0-4.56 3.68-8.24 8.24-8.24m-3.4 4.43c-.19 0-.5.07-.76.35-.26.28-1 .98-1 2.38 0 1.4 1.02 2.76 1.17 2.95.14.19 2 3.17 4.93 4.32 2.44.96 2.93.77 3.46.72.53-.05 1.71-.7 1.95-1.37.24-.68.24-1.26.17-1.38-.07-.12-.26-.19-.55-.33-.29-.14-1.71-.84-1.97-.94-.26-.1-.46-.14-.65.14-.19.29-.75.94-.92 1.13-.17.19-.34.22-.63.07-.29-.14-1.22-.45-2.32-1.43-.86-.76-1.44-1.71-1.6-2-.17-.29-.02-.45.13-.59.13-.13.29-.34.43-.5.14-.17.19-.29.29-.48.1-.19.05-.36-.02-.5-.07-.14-.65-1.56-.89-2.14-.23-.56-.47-.49-.65-.5h-.55z"/></svg>';
}

function generator_card(): string
{
    require_once WSAP_ROOT . '/lib/countries.php';
    require_once WSAP_ROOT . '/lib/auth_ui.php';
    $domain = current_domain();
    $domains = cfg('domains');
    $premPrice = price('premium');
    $waIcon = wa_icon_svg();
    ob_start();
    ?>
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

      <div class="gen-gate" id="gen-gate" hidden>
        <span class="kicker"><?= e(t('gen_gate_k')) ?></span>
        <h2 style="font-size:26px;margin-bottom:8px"><?= e(t('gen_gate_t')) ?></h2>
        <p class="muted"><?= e(t('gen_gate_d')) ?></p>
        <div class="save-btns" style="margin-top:16px"><?= google_button() ?><a class="btn btn-wa" href="/signup?claim=1"><?= e(t('res_save_email')) ?> →</a><a class="btn btn-ghost" href="/login?claim=1"><?= e(t('nav_login')) ?></a></div>
        <p class="small muted" style="margin-top:12px"><?= e(t('mp_cta_sub')) ?> · <a href="/members"><?= e(t('mem_more')) ?> →</a></p>
      </div>
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
        <?php if (!current_user()): ?>
        <div class="save-box" id="res-savebox">
          <div><b><?= e(t('res_save_t')) ?></b><p><?= e(t('res_save_d')) ?></p></div>
          <div class="save-btns"><?= google_button() ?><a class="btn btn-primary" id="res-save" href="/signup?claim=1"><?= e(t('res_save_email')) ?> →</a><a class="small" href="/login?claim=1"><?= e(t('res_save_login')) ?></a></div>
        </div>
        <?php else: ?><p class="result-note"><a href="/dashboard"><b><?= e(t('nav_dashboard')) ?> →</b></a></p><?php endif; ?>
        <div class="upsell-row" style="margin-top:14px">
          <span><span class="kicker" style="margin:0 0 2px;display:block"><?= e(t('res_upsell_k', ['price' => $premPrice])) ?></span><b><?= e(t('res_upsell_t', ['domain' => $domain])) ?></b></span>
          <a href="/pricing"><?= e(t('res_upsell_cta')) ?> →</a>
        </div>
      </div>
    </div>
    <?php
    return (string) ob_get_clean();
}
