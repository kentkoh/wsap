<?php
declare(strict_types=1);

/**
 * Landing page before chat (SAMPLE / design preview).
 * Every visual choice comes from the $cfg array below so the future editor can expose it as settings.
 * landing_render($cfg, $link, $demo) returns a standalone HTML page.
 */

/** All settings the dashboard editor will expose, with defaults. */
function landing_defaults(): array
{
    return [
        // Design
        'layout'      => 'card',        // card | split | story
        'theme'       => 'forest',      // forest | sunset | ocean | noir | rose  (sets accent + background colours)
        'accent'      => '',            // optional override, hex
        'background'  => 'aurora',      // aurora | gradient | solid | dots
        'font'        => 'sans',        // sans | serif | rounded
        'radius'      => 'soft',        // sharp | soft | pill
        'animation'   => 'on',          // on | off
        // Brand
        'logo'        => '',            // uploaded image (store_image) or URL
        'brand'       => '',
        'tagline'     => '',
        // Hero
        'badge'       => '',            // small pill above the headline, e.g. "Raya promo"
        'headline'    => '',
        'subheadline' => '',
        'cover'       => '',            // image URL; empty = no image
        'cover_emoji' => '',            // fallback art when there is no image
        // Offer
        'price'       => '',            // "RM 450 / night"
        'old_price'   => '',            // struck through
        'countdown_until' => '',        // ISO datetime; empty = no countdown
        'countdown_label' => '',        // "Promo ends in"
        'bullets'     => [],            // ['✅ text', ...] up to 5
        'gallery'     => [],            // up to 4 image URLs
        // Call to action
        'cta'         => 'Chat on WhatsApp',
        'cta_sub'     => '',            // "Replies within minutes"
        'pre_chat'    => 'none',        // none | name | name_question
        'question'    => '',            // label of the single question
        'options'     => [],            // choices for the question (empty = free text)
        'message'     => '',            // opening line, {name} and {answer} are filled in
        // Trust
        'rating'      => '',            // "4.9 ★ · 212 reviews"
        'hours'       => '',            // "Daily 9am–10pm"
        'location'    => '',            // text; with maps_url becomes a link
        'maps_url'    => '',
        'instagram'   => '',
        'facebook'    => '',
        'tiktok'      => '',
        // Footer
        'powered'     => true,          // free: on; Premium: can switch off
    ];
}

function landing_themes(): array
{
    return [
        'forest' => ['accent' => '#0b3d2e', 'accent2' => '#25d366', 'bg1' => '#eaf6ef', 'bg2' => '#d7f0df', 'ink' => '#10201a', 'card' => '#ffffff'],
        'sunset' => ['accent' => '#b45309', 'accent2' => '#f59e0b', 'bg1' => '#fff4e5', 'bg2' => '#ffd9a8', 'ink' => '#2b1a0a', 'card' => '#fffaf3'],
        'ocean'  => ['accent' => '#1d4ed8', 'accent2' => '#38bdf8', 'bg1' => '#e8f1ff', 'bg2' => '#cfe3ff', 'ink' => '#0f1b33', 'card' => '#ffffff'],
        'noir'   => ['accent' => '#f5f5f4', 'accent2' => '#25d366', 'bg1' => '#111214', 'bg2' => '#1f2227', 'ink' => '#f5f5f4', 'card' => '#1a1c20'],
        'rose'   => ['accent' => '#9f1239', 'accent2' => '#fb7185', 'bg1' => '#fff1f2', 'bg2' => '#ffd6dc', 'ink' => '#2a0a12', 'card' => '#ffffff'],
    ];
}

/** Three realistic presets for the demo page. */
function landing_presets(): array
{
    $soon = gmdate('Y-m-d\TH:i:s\Z', time() + 2 * 86400 + 5 * 3600 + 420);
    return [
        'homestay' => array_merge(landing_defaults(), [
            'layout' => 'card', 'theme' => 'forest', 'background' => 'aurora', 'font' => 'serif',
            'logo' => '/assets/img/demo/logo-homestay.svg', 'brand' => 'Seri Damai Homestay', 'tagline' => 'Cameron Highlands · 3 villas · pool',
            'badge' => '🌙 Raya promo', 'headline' => 'Villa 3 bilik, 20% off this Raya.', 'subheadline' => 'Private pool, BBQ deck and a mountain view. Book on WhatsApp in under a minute, we confirm your dates straight away.',
            'cover' => '', 'cover_emoji' => '🏡', 'price' => 'RM 360 / night', 'old_price' => 'RM 450',
            'countdown_until' => $soon, 'countdown_label' => 'Promo ends in',
            'bullets' => ['🛏️ 3 bedrooms, sleeps 8', '🏊 Private pool and BBQ set', '🍳 Breakfast for 4 included', '🚗 Free parking, 5 min to Brinchang'],
            'gallery' => [],
            'cta' => 'Check dates on WhatsApp', 'cta_sub' => 'We reply within 10 minutes, 9am to 11pm', 'pre_chat' => 'name_question', 'question' => 'Which unit?', 'options' => ['Villa 3 bilik', 'Studio 1 bilik', 'Dorm 8 beds'],
            'message' => 'Hi Seri Damai, I am {name}. I would like to book the {answer} for Raya. Is it available?',
            'rating' => '4.9 ★ · 212 Google reviews', 'hours' => 'Replies 9am–11pm daily', 'location' => 'Brinchang, Cameron Highlands', 'maps_url' => 'https://maps.google.com', 'instagram' => 'https://instagram.com', 'tiktok' => 'https://tiktok.com',
        ]),
        'bakery' => array_merge(landing_defaults(), [
            'layout' => 'split', 'theme' => 'sunset', 'background' => 'dots', 'font' => 'rounded', 'radius' => 'pill',
            'logo' => '', 'brand' => 'Tiga Luna Bakery', 'tagline' => 'Sourdough · croissants · custom cakes',
            'badge' => '🎁 Deepavali gift box', 'headline' => 'Pre-order the Deepavali box. 60 only.', 'subheadline' => 'Twelve mini pastries, a sourdough loaf and a jar of salted-caramel. Collect 7 to 9 November or delivery within Kuantan.',
            'cover' => '', 'cover_emoji' => '🥐', 'price' => 'RM 88', 'old_price' => 'RM 108',
            'countdown_until' => gmdate('Y-m-d\TH:i:s\Z', time() + 6 * 86400 + 11 * 3600), 'countdown_label' => 'Pre-orders close in',
            'bullets' => ['🥐 12 mini pastries, 3 flavours', '🍞 Country sourdough 800 g', '🍯 Salted-caramel jar 200 g', '🚚 Delivery RM 8 within Kuantan'],
            'cta' => 'Order on WhatsApp', 'cta_sub' => 'Pay by bank transfer after we confirm', 'pre_chat' => 'name_question', 'question' => 'How many boxes?', 'options' => ['1 box', '2 boxes', '3 boxes', 'More'],
            'message' => 'Hi Tiga Luna, I am {name}. I would like to pre-order {answer} of the Deepavali box.',
            'rating' => '4.8 ★ · 540 reviews', 'hours' => 'Tue–Sun 8am–6pm', 'location' => 'Jalan Besar, Kuantan', 'maps_url' => 'https://maps.google.com', 'instagram' => 'https://instagram.com', 'facebook' => 'https://facebook.com',
        ]),
        'clinic' => array_merge(landing_defaults(), [
            'layout' => 'story', 'theme' => 'ocean', 'background' => 'gradient', 'font' => 'sans', 'radius' => 'soft',
            'logo' => '/assets/img/demo/logo-clinic.svg', 'brand' => 'Klinik Dr. Aina', 'tagline' => 'Family dental · Bandar Indera Mahkota',
            'badge' => '🦷 New patients', 'headline' => 'Free dental check-up this month.', 'subheadline' => 'Scaling and polishing from RM 80, braces consultation free. Book a slot on WhatsApp and skip the queue.',
            'cover' => '', 'cover_emoji' => '🦷', 'price' => 'Check-up RM 0', 'old_price' => 'RM 50',
            'countdown_until' => '', 'countdown_label' => '',
            'bullets' => ['🪥 Scaling & polishing RM 80', '😁 Braces consultation free', '👶 Kids welcome, gentle care', '💳 Panel: AIA, Prudential, Allianz'],
            'cta' => 'Book on WhatsApp', 'cta_sub' => 'Mon–Sat 9am–7pm', 'pre_chat' => 'name', 'question' => '', 'options' => [],
            'message' => 'Hi Klinik Dr. Aina, I am {name}. I would like to book the free dental check-up.',
            'rating' => '5.0 ★ · 98 reviews', 'hours' => 'Mon–Sat 9am–7pm', 'location' => 'Bandar Indera Mahkota, Kuantan', 'maps_url' => 'https://maps.google.com', 'facebook' => 'https://facebook.com',
        ]),
    ];
}

/** Standalone HTML for a landing page. $link needs phone; $demo shows a preview popup instead of opening WhatsApp. */
function landing_render(array $cfg, array $link, bool $demo = false): string
{
    $cfg = array_merge(landing_defaults(), $cfg);
    $th = landing_themes()[$cfg['theme']] ?? landing_themes()['forest'];
    $accent = $cfg['accent'] !== '' ? $cfg['accent'] : $th['accent'];
    $fonts = ['sans' => "'Plus Jakarta Sans', system-ui, sans-serif", 'serif' => "'Instrument Serif', Georgia, serif", 'rounded' => "'Nunito', 'Plus Jakarta Sans', system-ui, sans-serif"];
    $radius = ['sharp' => '6px', 'soft' => '18px', 'pill' => '28px'][$cfg['radius']] ?? '18px';
    $btnRadius = ['sharp' => '6px', 'soft' => '14px', 'pill' => '999px'][$cfg['radius']] ?? '14px';
    $dark = $cfg['theme'] === 'noir';
    $anim = $cfg['animation'] === 'on';
    $site = (string) cfg('site_name');
    $wa = 'https://wa.me/' . $link['phone'];

    ob_start(); ?>
<!doctype html>
<html lang="<?= lang() ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($cfg['headline'] !== '' ? $cfg['headline'] . ' · ' . $cfg['brand'] : $cfg['brand']) ?></title>
<meta name="description" content="<?= e($cfg['subheadline']) ?>">
<meta name="robots" content="noindex">
<meta property="og:title" content="<?= e($cfg['headline']) ?>"><meta property="og:description" content="<?= e($cfg['subheadline']) ?>">
<meta name="theme-color" content="<?= e($th['bg1']) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Nunito:wght@500;700;800;900&display=swap" rel="stylesheet">
<style>
:root{--acc:<?= e($accent) ?>;--acc2:<?= e($th['accent2']) ?>;--bg1:<?= e($th['bg1']) ?>;--bg2:<?= e($th['bg2']) ?>;--ink:<?= e($th['ink']) ?>;--card:<?= e($th['card']) ?>;--r:<?= $radius ?>;--rb:<?= $btnRadius ?>;--font:<?= $fonts[$cfg['font']] ?? $fonts['sans'] ?>;--on-acc:<?= $dark ? '#111214' : '#ffffff' ?>;--muted:<?= $dark ? 'rgba(245,245,244,.68)' : 'rgba(16,32,26,.64)' ?>;--line:<?= $dark ? 'rgba(255,255,255,.1)' : 'rgba(16,32,26,.09)' ?>}
*{box-sizing:border-box}html{-webkit-text-size-adjust:100%}
body{margin:0;font-family:'Plus Jakarta Sans',system-ui,sans-serif;color:var(--ink);background:var(--bg1);min-height:100svh;overflow-x:hidden}
h1,h2,.brand{font-family:var(--font)}
.bg{position:fixed;inset:0;z-index:0;overflow:hidden;background:var(--bg1)}
.bg.gradient{background:linear-gradient(160deg,var(--bg1),var(--bg2) 60%,var(--bg1))}
.bg.dots{background-color:var(--bg1);background-image:radial-gradient(var(--bg2) 1.6px,transparent 1.7px);background-size:22px 22px}
.bg.aurora .blob{position:absolute;width:60vmax;height:60vmax;border-radius:50%;filter:blur(70px);opacity:.75;mix-blend-mode:multiply}
.bg.aurora .b1{background:var(--bg2);top:-20vmax;left:-15vmax;animation:drift 18s ease-in-out infinite alternate}
.bg.aurora .b2{background:color-mix(in srgb,var(--acc2) 35%,var(--bg1));bottom:-25vmax;right:-15vmax;animation:drift 22s ease-in-out infinite alternate-reverse}
.bg.aurora .b3{background:color-mix(in srgb,var(--acc) 18%,var(--bg1));top:30%;left:40%;width:40vmax;height:40vmax;animation:drift 26s ease-in-out infinite alternate}
<?php if ($dark): ?>.bg.aurora .blob{mix-blend-mode:screen;opacity:.35}<?php endif; ?>
.grain{position:fixed;inset:0;z-index:0;pointer-events:none;opacity:.18;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='160' height='160'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='2' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='.5'/%3E%3C/svg%3E")}
@keyframes drift{from{transform:translate(0,0) scale(1)}to{transform:translate(8vmax,6vmax) scale(1.15)}}
.wrap{position:relative;z-index:1;max-width:520px;margin:0 auto;padding:22px 16px 110px}
.split .wrap{max-width:1040px}
@media(min-width:900px){.split .grid{display:grid;grid-template-columns:1.05fr .95fr;gap:40px;align-items:center;min-height:80vh}}
.story .wrap{padding-top:0;max-width:560px}
.top{display:flex;align-items:center;gap:12px;margin-bottom:18px}
.logo{width:48px;height:48px;border-radius:14px;object-fit:cover;background:var(--card);box-shadow:0 6px 18px rgba(0,0,0,.08)}
.logo.ph{display:grid;place-items:center;font-weight:800;color:var(--on-acc);background:var(--acc)}
.brand{font-weight:700;font-size:17px;line-height:1.1}.tag{font-size:12.5px;color:var(--muted)}
.card{background:var(--card);border:1px solid var(--line);border-radius:var(--r);box-shadow:0 24px 60px rgba(0,0,0,.08);overflow:hidden}
.cover{position:relative;height:220px;display:grid;place-items:center;background:linear-gradient(135deg,var(--bg2),color-mix(in srgb,var(--acc2) 45%,var(--bg2)));overflow:hidden}
.cover img{width:100%;height:100%;object-fit:cover;display:block}
.cover .emoji{font-size:110px;line-height:1;filter:drop-shadow(0 18px 24px rgba(0,0,0,.18));animation:float 5s ease-in-out infinite}
.cover .ring{position:absolute;width:260px;height:260px;border-radius:50%;border:2px dashed color-mix(in srgb,var(--acc) 40%,transparent);animation:spin 40s linear infinite}
.story .cover{height:300px;border-radius:0 0 var(--r) var(--r)}
.split .cover{height:100%;min-height:420px;border-radius:var(--r)}
@keyframes float{0%,100%{transform:translateY(0) rotate(-4deg)}50%{transform:translateY(-14px) rotate(4deg)}}
@keyframes spin{to{transform:rotate(360deg)}}
.badge{position:absolute;top:14px;left:14px;background:var(--card);color:var(--ink);font-weight:700;font-size:12.5px;padding:7px 12px;border-radius:999px;box-shadow:0 6px 16px rgba(0,0,0,.12)}
.body{padding:22px 20px 20px}
h1{font-size:clamp(28px,6.5vw,40px);line-height:1.05;margin:0 0 10px;letter-spacing:-.01em}
.sub{color:var(--muted);font-size:15.5px;line-height:1.55;margin:0 0 16px}
.price{display:flex;align-items:baseline;gap:10px;margin:0 0 14px}.price b{font-size:28px;color:var(--acc)}.price s{color:var(--muted);font-size:15px}
.cd{display:grid;grid-template-columns:repeat(4,1fr);gap:6px;margin:0 0 16px}.cd div{background:color-mix(in srgb,var(--acc) 8%,var(--card));border:1px solid var(--line);border-radius:12px;text-align:center;padding:8px 4px}.cd b{display:block;font-size:22px;font-variant-numeric:tabular-nums;line-height:1}.cd small{font-size:10.5px;color:var(--muted);text-transform:uppercase;letter-spacing:.08em}
.cdl{font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;margin:0 0 6px;font-weight:700}
.list{list-style:none;padding:0;margin:0 0 18px;display:grid;gap:8px}.list li{display:flex;gap:10px;align-items:flex-start;font-size:15px;padding:10px 12px;border-radius:12px;background:color-mix(in srgb,var(--acc) 5%,var(--card));border:1px solid var(--line)}
.gal{display:grid;grid-template-columns:repeat(4,1fr);gap:6px;margin:0 0 16px}.gal img{width:100%;aspect-ratio:1;object-fit:cover;border-radius:10px}
.field{margin:0 0 10px}.field label{display:block;font-size:12.5px;font-weight:700;margin-bottom:5px;color:var(--muted)}
.input{width:100%;font:inherit;font-size:16px;padding:12px 14px;border-radius:12px;border:1.5px solid var(--line);background:var(--card);color:var(--ink)}
.input:focus{outline:none;border-color:var(--acc2);box-shadow:0 0 0 3px color-mix(in srgb,var(--acc2) 25%,transparent)}
.chips{display:flex;flex-wrap:wrap;gap:8px}.chips label{cursor:pointer}.chips input{position:absolute;opacity:0}.chips span{display:inline-block;padding:9px 14px;border-radius:999px;border:1.5px solid var(--line);font-size:14px;font-weight:600;background:var(--card)}.chips input:checked+span{background:var(--acc);color:var(--on-acc);border-color:var(--acc)}
.cta{display:flex;align-items:center;justify-content:center;gap:10px;width:100%;padding:16px 20px;border:0;border-radius:var(--rb);background:#25d366;color:#062b16;font:inherit;font-weight:800;font-size:17px;cursor:pointer;box-shadow:0 14px 30px rgba(37,211,102,.35);transition:transform .15s,box-shadow .15s;position:relative}
.cta:hover{transform:translateY(-2px);box-shadow:0 18px 36px rgba(37,211,102,.45)}.cta svg{width:24px;height:24px;fill:currentColor}
.cta.pulse::after{content:"";position:absolute;inset:-4px;border-radius:inherit;border:2px solid #25d366;animation:pulse 2s ease-out infinite}
@keyframes pulse{0%{opacity:.8;transform:scale(1)}100%{opacity:0;transform:scale(1.12)}}
.ctasub{text-align:center;font-size:13px;color:var(--muted);margin:10px 0 0}
.trust{display:flex;flex-wrap:wrap;gap:8px 16px;justify-content:center;margin:18px 0 0;font-size:13px;color:var(--muted)}.trust a{color:inherit}
.soc{display:flex;gap:8px;justify-content:center;margin:14px 0 0}.soc a{width:38px;height:38px;border-radius:12px;display:grid;place-items:center;background:var(--card);border:1px solid var(--line);color:var(--ink);text-decoration:none;font-size:15px;font-weight:800}
.powered{position:relative;z-index:1;text-align:center;font-size:12px;color:var(--muted);padding:10px 0 20px}.powered a{color:inherit}
.sticky{position:fixed;left:0;right:0;bottom:0;z-index:5;padding:10px 16px calc(10px + env(safe-area-inset-bottom));background:linear-gradient(to top,var(--bg1) 60%,transparent);display:none}
@media(max-width:899px){.sticky.show{display:block}.sticky .cta{box-shadow:0 10px 24px rgba(37,211,102,.4)}}
<?php if ($anim): ?>
.in{opacity:0;transform:translateY(16px);animation:in .7s cubic-bezier(.16,1,.3,1) forwards}
.in:nth-child(2){animation-delay:.08s}.in:nth-child(3){animation-delay:.16s}.in:nth-child(4){animation-delay:.24s}.in:nth-child(5){animation-delay:.32s}.in:nth-child(6){animation-delay:.4s}.in:nth-child(7){animation-delay:.48s}.in:nth-child(8){animation-delay:.56s}
@keyframes in{to{opacity:1;transform:none}}
<?php endif; ?>
@media(prefers-reduced-motion:reduce){*{animation:none!important;transition:none!important}.in{opacity:1;transform:none}}
</style>
<?php if ($demo): ?><?= demo_popup_css() ?><style>.demo-sheet{font-family:'Plus Jakarta Sans',system-ui,sans-serif;color:#10201a}.demo-sheet h3{margin:0 0 6px;font-size:20px}.demo-sheet .kicker{font-family:ui-monospace,monospace;font-size:10.5px;letter-spacing:.14em;text-transform:uppercase;color:#1f7a4f}.demo-sheet .muted{color:#5b6b63}.demo-sheet .small{font-size:13px}.demo-sheet .hint{font-size:12px;color:#5b6b63}.demo-actions{display:flex;gap:8px;margin-top:14px;flex-wrap:wrap}.demo-sheet .btn{display:inline-flex;align-items:center;justify-content:center;padding:11px 16px;border-radius:12px;font-weight:700;text-decoration:none;border:1.5px solid #0b3d2e;background:#0b3d2e;color:#fff;font:inherit;cursor:pointer}.demo-sheet .btn-ghost{background:#fff;color:#0b3d2e}</style><?php endif; ?>
</head>
<body class="<?= e($cfg['layout']) ?>">
<div class="bg <?= e($cfg['background']) ?>"><?php if ($cfg['background'] === 'aurora'): ?><div class="blob b1"></div><div class="blob b2"></div><div class="blob b3"></div><?php endif; ?></div>
<div class="grain"></div>
<main class="wrap">
  <?php if ($cfg['layout'] === 'story'): ?>
  <div class="cover in"><?= landing_cover($cfg) ?><?php if ($cfg['badge'] !== ''): ?><span class="badge"><?= e($cfg['badge']) ?></span><?php endif; ?></div>
  <?php endif; ?>
  <div class="grid">
  <?php if ($cfg['layout'] === 'split'): ?><div class="cover in" style="order:2"><?= landing_cover($cfg) ?><?php if ($cfg['badge'] !== ''): ?><span class="badge"><?= e($cfg['badge']) ?></span><?php endif; ?></div><?php endif; ?>
  <div>
    <div class="top in">
      <?php if ($cfg['logo'] !== ''): ?><img class="logo" src="<?= e($cfg['logo']) ?>" alt=""><?php else: ?><span class="logo ph"><?= e(mb_substr($cfg['brand'], 0, 1)) ?></span><?php endif; ?>
      <div><div class="brand"><?= e($cfg['brand']) ?></div><?php if ($cfg['tagline'] !== ''): ?><div class="tag"><?= e($cfg['tagline']) ?></div><?php endif; ?></div>
    </div>
    <div class="card in">
      <?php if ($cfg['layout'] === 'card'): ?><div class="cover"><?= landing_cover($cfg) ?><?php if ($cfg['badge'] !== ''): ?><span class="badge"><?= e($cfg['badge']) ?></span><?php endif; ?></div><?php endif; ?>
      <div class="body">
        <h1 class="in"><?= e($cfg['headline']) ?></h1>
        <?php if ($cfg['subheadline'] !== ''): ?><p class="sub in"><?= e($cfg['subheadline']) ?></p><?php endif; ?>
        <?php if ($cfg['price'] !== ''): ?><div class="price in"><b><?= e($cfg['price']) ?></b><?php if ($cfg['old_price'] !== ''): ?><s><?= e($cfg['old_price']) ?></s><?php endif; ?></div><?php endif; ?>
        <?php if ($cfg['countdown_until'] !== ''): ?>
        <div class="in"><?php if ($cfg['countdown_label'] !== ''): ?><div class="cdl"><?= e($cfg['countdown_label']) ?></div><?php endif; ?>
        <div class="cd" data-until="<?= e($cfg['countdown_until']) ?>"><div><b data-d>0</b><small>days</small></div><div><b data-h>00</b><small>hours</small></div><div><b data-m>00</b><small>min</small></div><div><b data-s>00</b><small>sec</small></div></div></div>
        <?php endif; ?>
        <?php if ($cfg['bullets']): ?><ul class="list in"><?php foreach ($cfg['bullets'] as $b): ?><li><?= e($b) ?></li><?php endforeach; ?></ul><?php endif; ?>
        <?php if ($cfg['gallery']): ?><div class="gal in"><?php foreach (array_slice($cfg['gallery'], 0, 4) as $g): ?><img src="<?= e($g) ?>" alt=""><?php endforeach; ?></div><?php endif; ?>
        <form class="in" id="lp" data-wa="<?= e($wa) ?>" data-msg="<?= e($cfg['message']) ?>" data-demo="<?= $demo ? '1' : '0' ?>">
          <?php if ($cfg['pre_chat'] !== 'none'): ?>
          <div class="field"><label for="nm"><?= e(t('lp_your_name')) ?></label><input class="input" id="nm" name="name" autocomplete="name" placeholder="<?= e(t('lp_name_ph')) ?>" required></div>
          <?php endif; ?>
          <?php if ($cfg['pre_chat'] === 'name_question' && $cfg['question'] !== ''): ?>
          <div class="field"><label><?= e($cfg['question']) ?></label>
            <?php if ($cfg['options']): ?><div class="chips"><?php foreach ($cfg['options'] as $i => $o): ?><label><input type="radio" name="answer" value="<?= e($o) ?>" <?= $i === 0 ? 'checked' : '' ?>><span><?= e($o) ?></span></label><?php endforeach; ?></div>
            <?php else: ?><input class="input" name="answer" placeholder="…"><?php endif; ?>
          </div>
          <?php endif; ?>
          <button class="cta <?= $anim ? 'pulse' : '' ?>" type="submit" id="cta"><svg viewBox="0 0 24 24"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2m0 1.67c4.56 0 8.24 3.68 8.24 8.24s-3.68 8.24-8.24 8.24c-1.5 0-2.97-.41-4.26-1.18l-.3-.18-3.12.82.83-3.04-.2-.31a8.2 8.2 0 0 1-1.26-4.35c0-4.56 3.68-8.24 8.24-8.24m-3.4 4.43c-.19 0-.5.07-.76.35-.26.28-1 .98-1 2.38 0 1.4 1.02 2.76 1.17 2.95.14.19 2 3.17 4.93 4.32 2.44.96 2.93.77 3.46.72.53-.05 1.71-.7 1.95-1.37.24-.68.24-1.26.17-1.38-.07-.12-.26-.19-.55-.33-.29-.14-1.71-.84-1.97-.94-.26-.1-.46-.14-.65.14-.19.29-.75.94-.92 1.13-.17.19-.34.22-.63.07-.29-.14-1.22-.45-2.32-1.43-.86-.76-1.44-1.71-1.6-2-.17-.29-.02-.45.13-.59.13-.13.29-.34.43-.5.14-.17.19-.29.29-.48.1-.19.05-.36-.02-.5-.07-.14-.65-1.56-.89-2.14-.23-.56-.47-.49-.65-.5h-.55z"/></svg><?= e($cfg['cta']) ?></button>
          <?php if ($cfg['cta_sub'] !== ''): ?><p class="ctasub"><?= e($cfg['cta_sub']) ?></p><?php endif; ?>
        </form>
      </div>
    </div>
    <?php $trust = array_filter([$cfg['rating'], $cfg['hours']]); if ($trust || $cfg['location'] !== ''): ?>
    <div class="trust in"><?php foreach ($trust as $x): ?><span><?= e($x) ?></span><?php endforeach; ?><?php if ($cfg['location'] !== ''): ?><span>📍 <?php if ($cfg['maps_url'] !== ''): ?><a href="<?= e($cfg['maps_url']) ?>" target="_blank" rel="noopener"><?= e($cfg['location']) ?></a><?php else: ?><?= e($cfg['location']) ?><?php endif; ?></span><?php endif; ?></div>
    <?php endif; ?>
    <?php $soc = array_filter(['IG' => $cfg['instagram'], 'FB' => $cfg['facebook'], 'TT' => $cfg['tiktok']]); if ($soc): ?>
    <div class="soc in"><?php foreach ($soc as $k => $u): ?><a href="<?= e($u) ?>" target="_blank" rel="noopener" aria-label="<?= e($k) ?>"><?= e($k) ?></a><?php endforeach; ?></div>
    <?php endif; ?>
  </div>
  </div>
</main>
<?php if ($cfg['powered']): ?><div class="powered"><?= e(t('lp_powered')) ?> <a href="<?= e(base_url()) ?>"><?= e($site) ?></a></div><?php endif; ?>
<div class="sticky" id="sticky"><button class="cta" type="button" onclick="document.getElementById('cta').scrollIntoView({behavior:'smooth',block:'center'})"><?= e($cfg['cta']) ?> →</button></div>
<script>
(function(){
  var cd=document.querySelector('.cd[data-until]');
  if(cd){var until=new Date(cd.dataset.until).getTime();var el={d:cd.querySelector('[data-d]'),h:cd.querySelector('[data-h]'),m:cd.querySelector('[data-m]'),s:cd.querySelector('[data-s]')};
    function tick(){var t=Math.max(0,until-Date.now());var d=Math.floor(t/864e5),h=Math.floor(t%864e5/36e5),m=Math.floor(t%36e5/6e4),s=Math.floor(t%6e4/1e3);el.d.textContent=d;el.h.textContent=String(h).padStart(2,'0');el.m.textContent=String(m).padStart(2,'0');el.s.textContent=String(s).padStart(2,'0');}
    tick();setInterval(tick,1000);}
  var form=document.getElementById('lp'),cta=document.getElementById('cta'),sticky=document.getElementById('sticky');
  if('IntersectionObserver' in window){new IntersectionObserver(function(es){sticky.classList.toggle('show',!es[0].isIntersecting);}).observe(cta);}
  form.addEventListener('submit',function(e){e.preventDefault();
    var name=(form.querySelector('[name=name]')||{}).value||'';var ans=form.querySelector('[name=answer]:checked')||form.querySelector('[name=answer]');ans=ans?ans.value:'';
    var msg=(form.dataset.msg||'').replace('{name}',name.trim()).replace('{answer}',ans.trim()).replace(/\s+\./g,'.');
    var url=form.dataset.wa+(msg?'?text='+encodeURIComponent(msg):'');
    if(form.dataset.demo==='1'){window.lpDemo&&window.lpDemo(msg);return;}
    window.location.href=url;});
})();
</script>
<?php if ($demo): ?>
<script>window.lpDemo=function(msg){var old=document.getElementById('demo-modal');if(old)old.remove();var tpl=document.getElementById('demo-tpl').innerHTML.replace('{{MSG}}',msg.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/\n/g,'<br>'));document.body.insertAdjacentHTML('beforeend',tpl);};</script>
<template id="demo-tpl"><?= str_replace(['{MSGPH}'], ['{{MSG}}'], demo_popup_html('{MSGPH}', $link['phone'], base_url() . '/landing-demo', t('lp_demo_cta'), 'form')) ?></template>
<?php endif; ?>
</body>
</html>
<?php
    return (string) ob_get_clean();
}

function landing_cover(array $cfg): string
{
    if ($cfg['cover'] !== '') {
        return '<img src="' . e($cfg['cover']) . '" alt="">';
    }
    return '<span class="ring"></span><span class="emoji">' . e($cfg['cover_emoji'] !== '' ? $cfg['cover_emoji'] : '💬') . '</span>';
}
