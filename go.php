<?php
declare(strict_types=1);
/**
 * /{code} → WhatsApp. Logs the click, serves OG tags to link-preview bots,
 * and shows a one-tap page inside in-app browsers that block wa.me.
 */
require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/ua.php';

header('Cache-Control: no-store');
header('X-Robots-Tag: noindex');

$code = (string) ($_GET['c'] ?? '');
$link = null;
try {
    $link = find_link($code);
} catch (Throwable $e) {
    error_log('go: ' . $e->getMessage());
}

$waIcon = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2m0 1.67c4.56 0 8.24 3.68 8.24 8.24s-3.68 8.24-8.24 8.24c-1.5 0-2.97-.41-4.26-1.18l-.3-.18-3.12.82.83-3.04-.2-.31a8.2 8.2 0 0 1-1.26-4.35c0-4.56 3.68-8.24 8.24-8.24m-3.4 4.43c-.19 0-.5.07-.76.35-.26.28-1 .98-1 2.38 0 1.4 1.02 2.76 1.17 2.95.14.19 2 3.17 4.93 4.32 2.44.96 2.93.77 3.46.72.53-.05 1.71-.7 1.95-1.37.24-.68.24-1.26.17-1.38-.07-.12-.26-.19-.55-.33-.29-.14-1.71-.84-1.97-.94-.26-.1-.46-.14-.65.14-.19.29-.75.94-.92 1.13-.17.19-.34.22-.63.07-.29-.14-1.22-.45-2.32-1.43-.86-.76-1.44-1.71-1.6-2-.17-.29-.02-.45.13-.59.13-.13.29-.34.43-.5.14-.17.19-.29.29-.48.1-.19.05-.36-.02-.5-.07-.14-.65-1.56-.89-2.14-.23-.56-.47-.49-.65-.5h-.55z"/></svg>';

function go_page(string $title, string $lead, string $buttonHtml, string $extra = ''): never
{
    $site = e((string) cfg('site_name'));
    echo '<!doctype html><html lang="' . lang() . '"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
        . '<title>' . e($title) . ' · ' . $site . '</title><link rel="stylesheet" href="/assets/css/app.css"></head>'
        . '<body><div class="open-page"><div class="open-card">' . $extra
        . '<h1>' . e($title) . '</h1><p>' . e($lead) . '</p>' . $buttonHtml
        . '<p class="pow">' . e(t('open_pow')) . ' <a href="/">' . $site . '</a> · <a href="/">' . e(t('make_own')) . '</a></p>'
        . '</div></div></body></html>';
    exit;
}

if (!$link) {
    // Forms and shops share the root namespace: wsap.me/slug
    try {
        require_once __DIR__ . '/lib/forms.php';
        if (find_form($code)) {
            $_GET['s'] = $code;
            require __DIR__ . '/form_view.php';
            exit;
        }
        require_once __DIR__ . '/lib/shops.php';
        if (find_shop($code)) {
            $_GET['s'] = $code;
            require __DIR__ . '/shop_view.php';
            exit;
        }
    } catch (Throwable $e) {
        error_log('go form/shop: ' . $e->getMessage());
    }
    http_response_code(404);
    go_page(t('not_found_t'), t('not_found_d'), '<a class="btn btn-primary" href="/">' . e(t('make_own')) . '</a>');
}
if ((int) $link['active'] !== 1) {
    http_response_code(410);
    go_page(t('paused_t'), t('paused_d'), '<a class="btn btn-primary" href="/">' . e(t('make_own')) . '</a>');
}

$info = click_info();
$dest = null;
try {
    // Smart routing only while the link is Premium; otherwise the link's own number is used.
    $owner = $link['user_id'] ? db_row('SELECT * FROM users WHERE id = ?', [(int) $link['user_id']]) : null;
    if (($link['type'] ?? 'chat') === 'smart' && link_has_smart($link, $owner)) {
        $dest = resolve_destination($link, ['country' => $info['country'], 'referrer' => $info['referrer'], 'is_qr' => $info['is_qr'], 'now' => time()]);
    }
} catch (Throwable $e) {
    error_log('smart route: ' . $e->getMessage());
}
$eff = effective_target($link, $dest);
$target = wa_url($eff);
$link['phone'] = $eff['phone'];
$link['message'] = $eff['message'];
try {
    record_click($link, $info, $dest ? (int) $dest['id'] : null);
} catch (Throwable $e) {
    error_log('record_click: ' . $e->getMessage());
}

// Link-preview crawlers get a tiny page with OG tags instead of a redirect (so WhatsApp/FB show a nice card).
if ($info['is_bot']) {
    $title = $link['title'] !== '' ? $link['title'] : 'WhatsApp · +' . $link['phone'];
    $desc = t('og_desc', ['phone' => '+' . $link['phone']]);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html><head><meta charset="utf-8"><title>' . e($title) . '</title>'
        . '<meta property="og:title" content="' . e($title) . '"><meta property="og:description" content="' . e($desc) . '">'
        . '<meta property="og:type" content="website"><meta property="og:url" content="' . e(short_url($link)) . '">'
        . '<meta property="og:image" content="' . e(base_url()) . '/assets/img/og.png">'
        . '<meta http-equiv="refresh" content="0;url=' . e($target) . '"></head><body><a href="' . e($target) . '">' . e($title) . '</a></body></html>';
    exit;
}

// In-app browsers (TikTok, Instagram, Facebook, Snapchat) block wa.me: show a one-tap opener.
if ($info['in_app'] !== '') {
    $intent = 'whatsapp://send?phone=' . $link['phone'] . ($link['message'] !== '' ? '&text=' . rawurlencode($link['message']) : '');
    $btn = '<a class="btn btn-wa btn-lg btn-block" href="' . e($intent) . '" id="open">' . $waIcon . ' ' . e(t('open_btn')) . '</a>'
        . '<p class="small muted" style="margin-top:14px">' . e(t('open_alt')) . '</p>'
        . '<script>setTimeout(function(){location.href=' . json_encode($target) . '},1200)</script>';
    go_page(t('open_title'), t('open_lead'), $btn, '<div class="wa-big">' . $waIcon . '</div>');
}

redirect($target, 302);
