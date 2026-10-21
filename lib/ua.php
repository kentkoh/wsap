<?php
declare(strict_types=1);

/**
 * Tiny user-agent / request classifier for click analytics. No external deps.
 */

function ua_is_bot(string $ua): bool
{
    return (bool) preg_match(
        '/bot|crawl|spider|slurp|facebookexternalhit|facebookcatalog|whatsapp|telegrambot|twitterbot|linkedinbot|'
        . 'pinterest|discordbot|skypeuripreview|embedly|quora link preview|outbrain|vkshare|w3c_validator|'
        . 'headlesschrome|lighthouse|pingdom|uptimerobot|gtmetrix|curl\/|wget\/|python-requests|go-http-client|'
        . 'preview|snapchat|bytespider|tiktokspider/i',
        $ua
    );
}

/** facebook / instagram / tiktok / snapchat in-app browsers often block wa.me deep links. */
function ua_in_app(string $ua): string
{
    if (preg_match('/FBAN|FBAV|FB_IAB/i', $ua)) {
        return 'facebook';
    }
    if (preg_match('/Instagram/i', $ua)) {
        return 'instagram';
    }
    if (preg_match('/musical_ly|TikTok|BytedanceWebview/i', $ua)) {
        return 'tiktok';
    }
    if (preg_match('/Snapchat/i', $ua)) {
        return 'snapchat';
    }
    return '';
}

function ua_device(string $ua): string
{
    if (preg_match('/iPad|Tablet|Nexus 7|Nexus 10|SM-T/i', $ua)) {
        return 'tablet';
    }
    if (preg_match('/Mobi|iPhone|Android|IEMobile|Opera Mini/i', $ua)) {
        return 'mobile';
    }
    return 'desktop';
}

function ua_os(string $ua): string
{
    if (preg_match('/iPhone|iPad|iPod/i', $ua)) {
        return 'iOS';
    }
    if (preg_match('/Android/i', $ua)) {
        return 'Android';
    }
    if (preg_match('/Windows/i', $ua)) {
        return 'Windows';
    }
    if (preg_match('/Mac OS X|Macintosh/i', $ua)) {
        return 'macOS';
    }
    if (preg_match('/CrOS/i', $ua)) {
        return 'ChromeOS';
    }
    if (preg_match('/Linux/i', $ua)) {
        return 'Linux';
    }
    return 'Other';
}

function ua_browser(string $ua): string
{
    $app = ua_in_app($ua);
    if ($app !== '') {
        return ucfirst($app) . ' app';
    }
    if (preg_match('/Edg\//i', $ua)) {
        return 'Edge';
    }
    if (preg_match('/OPR\/|Opera/i', $ua)) {
        return 'Opera';
    }
    if (preg_match('/SamsungBrowser/i', $ua)) {
        return 'Samsung';
    }
    if (preg_match('/Chrome|CriOS/i', $ua)) {
        return 'Chrome';
    }
    if (preg_match('/Firefox|FxiOS/i', $ua)) {
        return 'Firefox';
    }
    if (preg_match('/Safari/i', $ua)) {
        return 'Safari';
    }
    return 'Other';
}

/** Referrer host without www, or "direct". */
function referrer_host(?string $ref): string
{
    if (!$ref) {
        return 'direct';
    }
    $h = strtolower((string) parse_url($ref, PHP_URL_HOST));
    $h = preg_replace('/^(www|m|l|lm)\./', '', $h) ?? $h;
    return $h === '' ? 'direct' : substr($h, 0, 60);
}

/** Country from Cloudflare / LiteSpeed / nginx GeoIP headers when available. */
function request_country(): string
{
    return request_country_header();
}

function click_info(): array
{
    $ua = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
    $utm = (string) ($_GET['utm_source'] ?? '');
    return [
        'ua'         => $ua,
        'is_bot'     => ua_is_bot($ua),
        'in_app'     => ua_in_app($ua),
        'device'     => ua_device($ua),
        'os'         => ua_os($ua),
        'browser'    => ua_browser($ua),
        'referrer'   => referrer_host($_SERVER['HTTP_REFERER'] ?? null),
        'country'    => request_country(),
        'is_qr'      => isset($_GET['qr']),
        'utm_source' => mb_substr(preg_replace('/[^\w.-]/u', '', $utm) ?? '', 0, 40),
    ];
}
