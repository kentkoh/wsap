<?php
declare(strict_types=1);

/** Homepage v2 graphics: animated phone scenes, where-to-use tile art, product flow diagrams. Pure HTML/SVG. */

function hg_wa_svg(int $size = 18): string
{
    return '<svg viewBox="0 0 24 24" width="' . $size . '" height="' . $size . '" fill="currentColor" aria-hidden="true"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2m0 1.67c4.56 0 8.24 3.68 8.24 8.24s-3.68 8.24-8.24 8.24c-1.5 0-2.97-.41-4.26-1.18l-.3-.18-3.12.82.83-3.04-.2-.31a8.2 8.2 0 0 1-1.26-4.35c0-4.56 3.68-8.24 8.24-8.24m-3.4 4.43c-.19 0-.5.07-.76.35-.26.28-1 .98-1 2.38 0 1.4 1.02 2.76 1.17 2.95.14.19 2 3.17 4.93 4.32 2.44.96 2.93.77 3.46.72.53-.05 1.71-.7 1.95-1.37.24-.68.24-1.26.17-1.38-.07-.12-.26-.19-.55-.33-.29-.14-1.71-.84-1.97-.94-.26-.1-.46-.14-.65.14-.19.29-.75.94-.92 1.13-.17.19-.34.22-.63.07-.29-.14-1.22-.45-2.32-1.43-.86-.76-1.44-1.71-1.6-2-.17-.29-.02-.45.13-.59.13-.13.29-.34.43-.5.14-.17.19-.29.29-.48.1-.19.05-.36-.02-.5-.07-.14-.65-1.56-.89-2.14-.23-.56-.47-.49-.65-.5h-.55z"/></svg>';
}

function hg_cursor(): string
{
    return '<span class="cursor" data-cursor><svg viewBox="0 0 24 24"><path d="M4 2l16 10-7 1.5L16 21l-3 1-3-7.5L4 18z" fill="#fff" stroke="#111" stroke-width="1.5" stroke-linejoin="round"/></svg></span>';
}

/** Scene 1: Instagram-style profile with the link. */
function hg_scene_profile(string $domain): string
{
    return '<div class="scene on" data-scene="profile"><div class="ig">'
        . '<div class="ig-top"><span>‹</span><span>@tigaluna</span><span>···</span></div>'
        . '<div class="ig-head"><div class="ig-ava">🏺</div><div class="ig-stats"><span><b>312</b>posts</span><span><b>18.4K</b>followers</span><span><b>402</b>following</span></div></div>'
        . '<div class="ig-bio"><b>Tiga Luna</b>' . e(t('hw_ig_bio')) . '</div>'
        . '<span class="ig-link" data-link>' . hg_wa_svg(14) . ' ' . e($domain) . '/tigaluna</span>'
        . '<div class="ig-hl"><span>✨</span><span>☕</span><span>🎨</span><span>📦</span></div>'
        . '<div class="ig-grid"><span>☕</span><span>🏺</span><span>🫖</span><span>🪴</span><span>🥣</span><span>🍶</span></div>'
        . '</div></div>';
}

/** Scene 2: WhatsApp chat with pre-filled message + reply. */
function hg_scene_chat(string $message, string $reply = ''): string
{
    $msg = nl2br(preg_replace('/\*([^*\n]+)\*/', '<b>$1</b>', e($message)) ?? e($message));
    return '<div class="scene" data-scene="chat"><div class="wa-screen wa-in-phone"><div class="wa-top"><span class="wa-avatar"></span><span><b>Tiga Luna</b><small>' . e(t('demo_online')) . '</small></span></div>'
        . '<div class="wa-body"><div class="wa-date">' . e(t('demo_today')) . '</div><div class="wa-bubble">' . $msg . '<span class="wa-time">' . date('H:i') . ' ✓✓</span></div>'
        . ($reply !== '' ? '<div class="wa-typing"><i></i><i></i><i></i></div><div class="wa-bubble wa-left reply">' . e($reply) . '<span class="wa-time">' . date('H:i') . '</span></div>' : '')
        . '</div></div></div>';
}

/** Scene 2 (smart): routing rules + people. */
function hg_scene_route(string $domain): string
{
    return '<div class="scene" data-scene="route"><div class="route"><span class="chip">' . e($domain) . '/yourteam</span>'
        . '<div class="rule"><span>' . e(t('pl_r1')) . '</span><span>' . e(t('pl_r1d')) . '</span></div>'
        . '<div class="rule on"><span>' . e(t('pl_r2')) . '</span><span>' . e(t('pl_r2d')) . '</span></div>'
        . '<div class="rule"><span>' . e(t('pl_r3')) . '</span><span>' . e(t('pl_r3d')) . '</span></div>'
        . '<div class="people"><div class="person"><i>🧑‍💼</i>Counter</div><div class="person on"><i>👩</i>Aina</div><div class="person"><i>🧑</i>Ben</div><div class="person"><i>👩‍🦱</i>Dina</div></div>'
        . '<div class="wa-bubble" style="margin:10px 0 0;max-width:100%;font-size:12.5px;animation:popin .4s 1s both">' . e(t('hw_smart_msg')) . '<br><b>' . e(t('hw_smart_to')) . '</b></div>'
        . '</div></div>';
}

/** Scene 2 (forms): a question screen. */
function hg_scene_form(): string
{
    return '<div class="scene" data-scene="form"><div class="fmk"><div class="fm-bar"><i style="width:60%"></i></div><small class="kicker" style="margin:0">Klinik Dr. Aina · 3/5</small>'
        . '<div class="q">' . e(t('hw_form_q')) . '</div><div class="opt">2026-11-02</div><div class="opt on">2026-11-03</div><div class="opt">2026-11-04</div>'
        . '<div class="fm-btn">' . e(t('fm_next')) . ' →</div></div></div>';
}

/** Scene 2 (shops): catalogue with an item added. */
function hg_scene_shop(): string
{
    return '<div class="scene" data-scene="shop"><div class="shm"><div class="cov"></div><div class="in"><div style="width:40px;height:40px;border-radius:12px;background:#fff;margin-top:-20px;box-shadow:0 4px 12px rgba(0,0,0,.2);display:grid;place-items:center;font-weight:800;color:#b45309">G</div>'
        . '<div style="font-weight:800;font-size:14px;margin-top:6px">The Golden Bakery</div><div class="grid">'
        . '<div class="p added"><i></i>Butter Croissant · RM5.50</div><div class="p"><i style="background:linear-gradient(135deg,#f8e3c0,#c97b3a)"></i>Sourdough · RM12.50</div>'
        . '<div class="p added"><i style="background:linear-gradient(135deg,#e7d2bd,#9a6b3f)"></i>Caffè Latte · RM9</div><div class="p"><i style="background:linear-gradient(135deg,#ffe9a8,#f0a532)"></i>Orange Juice · RM8.50</div>'
        . '</div></div><div class="bar"><span>' . e(t('sh_view_cart')) . ' · 3</span><span>RM24.80</span></div></div></div>';
}

/** Where-to-use tile art. */
function hg_tile_art(string $kind, string $domain): string
{
    $link = e($domain) . '/tigaluna';
    switch ($kind) {
        case 'bio':
            return '<div style="text-align:center"><div class="ig-ava" style="width:40px;height:40px;font-size:18px;margin:0 auto 8px">🏺</div><div style="width:60px;height:5px;border-radius:3px;background:var(--line-2);margin:0 auto 10px"></div><span class="pillink">' . hg_wa_svg(13) . ' ' . $link . '</span></div>';
        case 'story':
            return '<div class="story-mock"><span class="pillink">' . hg_wa_svg(11) . ' ' . $link . '</span></div>';
        case 'fb':
            return '<div class="mini-card"><div style="display:flex;gap:8px;align-items:center;margin-bottom:6px"><span style="width:26px;height:26px;border-radius:50%;background:#f6d5b8;display:grid;place-items:center;font-size:13px">🏺</span><b>Tiga Luna</b> <span style="color:#1877f2;font-weight:800;margin-left:auto">f</span></div>' . e(t('wh_m_post')) . ' <span class="mono">' . $link . '</span></div>';
        case 'gbp':
            return '<div class="mini-card"><b>Tiga Luna</b><div style="color:#f5a623;font-size:11px">★★★★★ <span style="color:#777">4.9 · Ceramics shop</span></div><div style="display:flex;gap:6px;margin-top:8px"><span style="border:1px solid var(--line-2);border-radius:999px;padding:3px 8px;font-size:11px">' . e(t('wh_m_directions')) . '</span><span class="pillink light" style="font-size:10.5px;padding:3px 8px">' . $link . '</span></div></div>';
        case 'ads':
            return '<div class="mini-card"><div style="display:flex;justify-content:space-between;font-size:11px"><b>Tiga Luna</b><span style="color:#999;letter-spacing:.1em;font-size:9px">' . strtoupper(e(t('wh_m_sponsored'))) . '</span></div><div style="height:34px;border-radius:8px;background:#fff6d6;margin:8px 0"></div><div style="background:var(--green);color:#fff;text-align:center;border-radius:8px;padding:7px;font-weight:700">' . e(t('wh_m_order')) . '</div></div>';
        case 'qr':
            $cells = '';
            mt_srand(7);
            for ($i = 0; $i < 64; $i++) {
                $r = (int) ($i / 8);
                $c = $i % 8;
                $finder = ($r < 3 && $c < 3) || ($r < 3 && $c > 4) || ($r > 4 && $c < 3);
                $cells .= '<i class="' . ($finder ? (($r === 1 && ($c === 1 || $c === 6)) || ($r === 6 && $c === 1) ? '' : (($r === 0 || $r === 2 || $r === 5 || $r === 7 || $c === 0 || $c === 2 || $c === 5 || $c === 7) ? '' : 'o')) : (mt_rand(0, 1) ? '' : 'o')) . '"></i>';
            }
            return '<div style="display:flex;gap:14px;align-items:center"><div class="qr-mini">' . $cells . '</div><div style="font-size:12px"><b style="font-size:13px">' . e(t('wh_m_scan')) . '</b><br><span class="mono" style="font-size:11px;color:var(--green-2)">' . $link . '</span><br><span style="font-family:var(--mono);font-size:9.5px;letter-spacing:.1em;color:#999">' . strtoupper(e(t('wh_m_menu'))) . '</span></div></div>';
        case 'sms':
            return '<div style="text-align:center;font-size:12px"><div style="font-family:var(--mono);font-size:9.5px;letter-spacing:.12em;color:#999;margin-bottom:8px">TIGA LUNA · 9:41</div><div class="mini-card" style="text-align:left">' . e(t('wh_m_sms')) . ' <span class="mono">' . $link . '</span></div></div>';
        case 'email':
            return '<div class="mini-card" style="border-left:3px solid var(--green-2)"><b>Marta Luna</b><div style="color:#777;font-size:11px">' . e(t('wh_m_founder')) . '</div><div class="mono" style="margin-top:6px">' . hg_wa_svg(11) . ' ' . $link . '</div></div>';
        case 'web':
            return '<div class="site-mock"><i></i><i></i><i></i><span class="fab">' . hg_wa_svg(18) . '</span></div>';
    }
    return '';
}

/** Product flow diagrams (SVG). $kind: link | smart | form | shop */
function hg_flow(string $kind, string $domain, string $color): string
{
    $wa = '<g transform="translate(%d,%d)"><circle r="18" fill="#25d366"/><g transform="translate(-10,-10) scale(.83)" fill="#fff">' . substr(hg_wa_svg(24), strpos(hg_wa_svg(24), '<path'), -6) . '</g></g>';
    $chip = fn(string $txt, int $w) => '<g transform="translate(' . (115 - $w / 2) . ',14)"><rect width="' . $w . '" height="30" rx="15" fill="#fff" stroke="#e2e1d8"/><text x="' . ($w / 2) . '" y="19" text-anchor="middle" font-family="JetBrains Mono,monospace" font-size="11" fill="#10201a">' . e($txt) . '</text></g>';
    $cursor = '<g class="svg-cursor"><path d="M0 0l12 7.5-5.2 1.1L10 16l-2.2.8-3.2-7.4L0 13z" fill="#fff" stroke="#111" stroke-width="1.2" stroke-linejoin="round"/></g>';
    $svg = '<svg viewBox="0 0 230 250" xmlns="http://www.w3.org/2000/svg">';
    switch ($kind) {
        case 'link':
            $svg .= $chip($domain . '/yourbrand', 150) . '<g transform="translate(112,40)">' . $cursor . '</g>'
                . '<line class="flow-line" x1="115" y1="48" x2="115" y2="100" stroke="' . $color . '" stroke-width="2"/><circle class="flow-dot" cx="115" cy="48" r="4" fill="' . $color . '"/>'
                . '<g transform="translate(70,100)"><rect width="90" height="42" rx="21" fill="#fff" stroke="' . $color . '" stroke-width="2"/><path d="M38 21h14M40 15a6 6 0 0 0 0 12h3M50 27a6 6 0 0 0 0-12h-3" stroke="' . $color . '" stroke-width="2" fill="none" stroke-linecap="round"/></g>'
                . '<line class="flow-line" x1="115" y1="142" x2="115" y2="195" stroke="' . $color . '" stroke-width="2"/>' . sprintf($wa, 115, 215);
            break;
        case 'smart':
            $svg .= $chip($domain . '/yourteam', 150) . '<g transform="translate(112,40)">' . $cursor . '</g>'
                . '<line class="flow-line" x1="115" y1="48" x2="115" y2="95" stroke="' . $color . '" stroke-width="2"/><circle class="flow-dot" cx="115" cy="48" r="4" fill="' . $color . '"/>'
                . '<path d="M45 95H185M45 95V120M115 95V120M185 95V120" stroke="' . $color . '" stroke-width="2" fill="none"/>';
            foreach ([45, 115, 185] as $x) {
                $svg .= '<g transform="translate(' . $x . ',140)"><circle r="20" fill="#fff" stroke="' . $color . '" stroke-width="2"/><circle cy="-4" r="6" fill="none" stroke="' . $color . '" stroke-width="2"/><path d="M-10 12a10 10 0 0 1 20 0" fill="none" stroke="' . $color . '" stroke-width="2"/></g>'
                    . '<line class="flow-line" x1="' . $x . '" y1="160" x2="' . $x . '" y2="195" stroke="' . $color . '" stroke-width="2"/>' . sprintf($wa, $x, 215);
            }
            break;
        case 'form':
            $svg .= $chip($domain . '/booking', 140) . '<g transform="translate(112,40)">' . $cursor . '</g>'
                . '<line class="flow-line" x1="115" y1="48" x2="115" y2="85" stroke="' . $color . '" stroke-width="2"/><circle class="flow-dot" cx="115" cy="48" r="4" fill="' . $color . '"/>';
            foreach ([88, 114, 140] as $i => $y) {
                $svg .= '<g transform="translate(55,' . $y . ')"><rect width="120" height="20" rx="10" fill="#fff" stroke="' . $color . '" stroke-width="2"/><rect x="10" y="8" width="' . (50 + $i * 12) . '" height="4" rx="2" fill="#e2e1d8"/><circle cx="106" cy="10" r="6" fill="' . $color . '"/><path d="M103 10l2 2 4-4" stroke="#fff" stroke-width="1.6" fill="none"/></g>';
            }
            $svg .= '<line class="flow-line" x1="115" y1="162" x2="115" y2="195" stroke="' . $color . '" stroke-width="2"/>' . sprintf($wa, 115, 215);
            break;
        case 'shop':
            $svg .= $chip($domain . '/yourshop', 145) . '<g transform="translate(112,40)">' . $cursor . '</g>'
                . '<line class="flow-line" x1="115" y1="48" x2="115" y2="82" stroke="' . $color . '" stroke-width="2"/><circle class="flow-dot" cx="115" cy="48" r="4" fill="' . $color . '"/>';
            foreach ([[62, 84], [100, 84], [138, 84]] as $i => [$x, $y]) {
                $svg .= '<rect x="' . $x . '" y="' . $y . '" width="30" height="30" rx="7" fill="' . ['#f5d9a8', '#e7d2bd', '#ffe9a8'][$i] . '" stroke="' . $color . '" stroke-width="1.5"/>';
            }
            $svg .= '<g transform="translate(62,128)"><rect width="106" height="30" rx="15" fill="#fff" stroke="' . $color . '" stroke-width="2"/><path d="M14 10h4l3 10h12l2-7H19" stroke="' . $color . '" stroke-width="1.8" fill="none" stroke-linecap="round" stroke-linejoin="round"/><circle cx="22" cy="23" r="1.5" fill="' . $color . '"/><circle cx="31" cy="23" r="1.5" fill="' . $color . '"/><text x="68" y="19" text-anchor="middle" font-family="JetBrains Mono,monospace" font-size="10" fill="#10201a">RM24.80</text></g>'
                . '<line class="flow-line" x1="115" y1="158" x2="115" y2="195" stroke="' . $color . '" stroke-width="2"/>' . sprintf($wa, 115, 215);
            break;
    }
    return $svg . '</svg>';
}
