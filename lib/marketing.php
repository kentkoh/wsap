<?php
declare(strict_types=1);

/** Shared graphic components for the product pages (pure HTML/CSS, no images). */

/** A phone frame with arbitrary inner HTML. */
function phone_mock(string $inner, string $class = ''): string
{
    return '<div class="phone ' . $class . '"><div class="phone-notch"></div><div class="phone-screen">' . $inner . '</div></div>';
}

/** A WhatsApp chat screen with one incoming bubble (business view). */
function wa_screen(string $message, string $from = 'Customer'): string
{
    $html = e($message);
    $html = preg_replace('/\*([^*\n]+)\*/', '<b>$1</b>', $html) ?? $html;
    $html = nl2br($html);
    return '<div class="wa-screen wa-in-phone"><div class="wa-top"><span class="wa-avatar"></span><span><b>' . e($from) . '</b><small>' . e(t('demo_online')) . '</small></span></div>'
        . '<div class="wa-body"><div class="wa-date">' . e(t('demo_today')) . '</div><div class="wa-bubble wa-left">' . $html . '<span class="wa-time">' . date('H:i') . '</span></div></div></div>';
}

/** The form step mock (one question with progress). */
function form_step_mock(string $title, string $question, string $step, array $options = []): string
{
    $opts = '';
    foreach ($options as $i => $o) {
        $opts .= '<div class="fm-opt' . ($i === 1 ? ' on' : '') . '">' . e($o) . '</div>';
    }
    return '<div class="fm-mock"><div class="fm-bar"><i style="width:50%"></i></div><small class="kicker" style="margin:0 0 6px">' . e($step) . '</small><div class="fm-title">' . e($title) . '</div><div class="fm-q">' . e($question) . '</div>'
        . ($opts !== '' ? $opts : '<div class="fm-input">2026-11-03</div>') . '<div class="fm-btn">' . e(t('fm_next')) . ' →</div></div>';
}

function product_cta_band(string $title, string $lead, string $href, string $cta): string
{
    return '<section class="section" style="padding-bottom:0"><div class="wrap"><div class="cta-band reveal"><h2>' . e($title) . '</h2><p class="lead">' . e($lead) . '</p><a class="btn btn-wa btn-lg" href="' . e($href) . '">' . e($cta) . ' →</a></div></div></section>';
}
