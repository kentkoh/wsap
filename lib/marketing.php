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

/** Rows for the guest / free account / Premium comparison. Values: true, false, or a string key for a note. */
function member_compare_rows(): array
{
    return [
        ['cmp_1', true, true, true], ['cmp_2', true, true, true], ['cmp_11', false, true, true], ['cmp_4', false, true, true],
        ['cmp_10', false, true, true], ['cmp_5', false, 'cmp_basic', true], ['cmp_12', false, true, true], ['cmp_13', false, true, true],
        ['cmp_3', 'cmp_random', 'cmp_random', true], ['cmp_6', false, false, true], ['cmp_7', false, false, true], ['cmp_8', false, false, true],
        ['cmp_9', true, true, true],
    ];
}

/** The three-column comparison table (homepage + /members). */
function member_compare_table(string $domain): string
{
    $cell = fn($v) => $v === true ? '<span class="yes">✓</span>' : ($v === false ? '<span class="no">—</span>' : '<span class="note">' . e(t($v)) . '</span>');
    $h = '<table class="compare compare-3 reveal"><thead><tr><th>' . e(t('cmp_feature')) . '</th><th class="c">' . e(t('cmp_guest')) . '</th><th class="c mem">' . e(t('cmp_member')) . '</th><th class="c prem">' . e(t('cmp_premium')) . '</th></tr></thead><tbody>';
    foreach (member_compare_rows() as [$k, $g, $m, $p]) {
        $h .= '<tr><td>' . e(t($k, ['domain' => $domain])) . '</td><td class="c">' . $cell($g) . '</td><td class="c mem">' . $cell($m) . '</td><td class="c prem">' . $cell($p) . '</td></tr>';
    }
    return $h . '</tbody></table>';
}

/** Guest / Free account / Premium cards. */
function member_cards(bool $withCta = true): string
{
    $cards = [
        ['guest', '👋', t('mem_guest_t'), t('mem_guest_d'), [t('mem_g1'), t('mem_g2'), t('mem_g3')], '', ''],
        ['member', '🔑', t('mem_member_t'), t('mem_member_d'), [t('mem_m1'), t('mem_m2'), t('mem_m3'), t('mem_m4'), t('mem_m5')], '/signup', t('mem_cta')],
        ['premium', '★', t('mem_premium_t'), t('mem_premium_d'), [t('mem_p1', ['domain' => current_domain()]), t('mem_p2'), t('mem_p3'), t('mem_p4')], '/premium-links', t('pr2_learn')],
    ];
    $h = '<div class="mem-grid">';
    foreach ($cards as [$cls, $ico, $title, $desc, $items, $href, $cta]) {
        $h .= '<div class="mem-card ' . $cls . ' reveal"><span class="ico">' . $ico . '</span><h3>' . e($title) . '</h3><p>' . e($desc) . '</p><ul>';
        foreach ($items as $it) {
            $h .= '<li>' . e($it) . '</li>';
        }
        $h .= '</ul>' . ($withCta && $href !== '' && !current_user() ? '<a class="btn ' . ($cls === 'member' ? 'btn-wa' : 'btn-ghost') . '" href="' . e($href) . '">' . e($cta) . ' →</a>' : '') . '</div>';
    }
    return $h . '</div>';
}
