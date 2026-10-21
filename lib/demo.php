<?php
declare(strict_types=1);

/**
 * Demo preview popup: shows the WhatsApp message exactly as it would arrive, without sending.
 * Used by demo forms and demo shops (and owner previews).
 */
function demo_popup_html(string $message, string $phone, string $ctaHref, string $ctaText, string $kind = 'form'): string
{
    $lines = e($message);
    // *bold* → <b>, keep line breaks
    $lines = preg_replace('/\*([^*\n]+)\*/', '<b>$1</b>', $lines) ?? $lines;
    $lines = nl2br($lines);
    $time = date('H:i');
    return '<div class="demo-modal" id="demo-modal" role="dialog" aria-modal="true">'
        . '<div class="demo-sheet">'
        . '<span class="kicker" style="margin:0 0 6px;display:block">' . e(t('demo_kicker')) . '</span>'
        . '<h3>' . e(t($kind === 'shop' ? 'demo_title_shop' : 'demo_title_form')) . '</h3>'
        . '<p class="muted small">' . e(t('demo_lead', ['phone' => '+' . $phone])) . '</p>'
        . '<div class="wa-screen"><div class="wa-top"><span class="wa-avatar"></span><span><b>' . e(t('demo_you')) . '</b><small>' . e(t('demo_online')) . '</small></span></div>'
        . '<div class="wa-body"><div class="wa-date">' . e(t('demo_today')) . '</div><div class="wa-bubble">' . $lines . '<span class="wa-time">' . $time . ' ✓✓</span></div></div></div>'
        . '<div class="demo-actions"><a class="btn btn-primary" href="' . e($ctaHref) . '">' . e($ctaText) . ' →</a>'
        . '<button class="btn btn-ghost" type="button" onclick="document.getElementById(\'demo-modal\').remove()">' . e(t('demo_close')) . '</button></div>'
        . '<p class="hint" style="margin-top:12px">' . e(t('demo_note')) . '</p>'
        . '</div></div>';
}

function demo_popup_css(): string
{
    return '<style>
.demo-modal{position:fixed;inset:0;background:rgba(16,32,26,.6);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;z-index:100;padding:16px}
.demo-sheet{background:#fff;border-radius:22px;padding:24px;width:100%;max-width:440px;max-height:94vh;overflow:auto;box-shadow:0 30px 80px -20px rgba(0,0,0,.5)}
.demo-sheet h3{font-size:22px;margin:0 0 4px;letter-spacing:-.02em}
.wa-screen{border-radius:18px;overflow:hidden;border:1px solid #ddd;margin:14px 0 16px;font-family:system-ui,-apple-system,"Segoe UI",sans-serif}
.wa-top{background:#075e54;color:#fff;padding:10px 14px;display:flex;align-items:center;gap:10px}.wa-top small{display:block;font-size:11px;opacity:.8}.wa-top b{font-size:14px}
.wa-avatar{width:34px;height:34px;border-radius:50%;background:#25d366;flex:none}
.wa-body{background:#e5ddd5 url("data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%2740%27 height=%2740%27%3E%3Ccircle cx=%2720%27 cy=%2720%27 r=%271.2%27 fill=%27%23d2c9bf%27/%3E%3C/svg%3E");padding:14px 12px 18px;min-height:160px}
.wa-date{text-align:center;font-size:11px;background:#fff;display:table;margin:0 auto 12px;padding:3px 10px;border-radius:8px;color:#555}
.wa-bubble{background:#dcf8c6;border-radius:10px 2px 10px 10px;padding:8px 10px 18px;font-size:14px;line-height:1.45;max-width:92%;margin-left:auto;position:relative;white-space:normal;word-break:break-word;box-shadow:0 1px 1px rgba(0,0,0,.08)}
.wa-time{position:absolute;right:8px;bottom:3px;font-size:10.5px;color:#777}
.demo-actions{display:flex;gap:8px;flex-wrap:wrap}
</style>';
}
