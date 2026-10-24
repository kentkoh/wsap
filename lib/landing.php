<?php
declare(strict_types=1);

/**
 * Landing page before chat (DESIGN SAMPLE): a one-page mini website for a link.
 * Every visual choice comes from the $cfg array (landing_defaults()) so the future editor can expose it.
 * landing_render($cfg, $link, $demo) returns a standalone HTML page.
 */

function landing_defaults(): array
{
    return [
        // Design
        'theme'       => 'forest',      // forest | sunset | ocean | noir | rose
        'accent'      => '',            // optional hex override
        'font'        => 'sans',        // sans | serif | rounded
        'radius'      => 'soft',        // sharp | soft | pill
        'animation'   => 'on',          // on | off
        'hero_style'  => 'photo',       // photo (cover image with overlay) | split (text + image side by side) | plain
        // Brand
        'logo'        => '', 'brand' => '', 'tagline' => '',
        // Hero
        'badge'       => '', 'headline' => '', 'subheadline' => '', 'cover' => '', 'cover_emoji' => '',
        'cta'         => 'Chat on WhatsApp', 'cta_sub' => '',
        // Stats strip (up to 4): [['4.9 ★', 'Google rating'], ...]
        'stats'       => [],
        // Offer
        'price' => '', 'old_price' => '', 'countdown_until' => '', 'countdown_label' => '',
        // Packages / menu (up to 6): [['title','desc','price','image','emoji','message'], ...]
        'packages_title' => '', 'packages' => [],
        // Highlights (up to 6 bullets)
        'highlights_title' => '', 'highlights' => [],
        // Gallery (up to 8 image URLs)
        'gallery_title' => '', 'gallery' => [],
        // About
        'about_title' => '', 'about' => '', 'about_image' => '',
        // Reviews (up to 6): [['name','text','stars'], ...]
        'reviews_title' => '', 'reviews' => [],
        // FAQ (up to 8): [['q','a'], ...]
        'faq_title' => '', 'faq' => [],
        // Location & hours
        'location' => '', 'maps_url' => '', 'map_embed' => '', 'hours' => [], // hours: [['Mon–Fri','9am–6pm'], ...]
        // Final form
        'form_title' => '', 'form_sub' => '', 'pre_chat' => 'name', 'question' => '', 'options' => [], 'message' => '',
        // Socials & footer
        'instagram' => '', 'facebook' => '', 'tiktok' => '', 'website' => '', 'powered' => true,
    ];
}

function landing_themes(): array
{
    return [
        'forest' => ['acc' => '#0b3d2e', 'acc2' => '#25d366', 'bg' => '#f6f5ef', 'bg2' => '#e6f4ec', 'ink' => '#10201a', 'card' => '#ffffff', 'on' => '#ffffff'],
        'sunset' => ['acc' => '#b45309', 'acc2' => '#f59e0b', 'bg' => '#fff8ee', 'bg2' => '#ffe7c2', 'ink' => '#2b1a0a', 'card' => '#fffdf8', 'on' => '#ffffff'],
        'ocean'  => ['acc' => '#1d4ed8', 'acc2' => '#38bdf8', 'bg' => '#f3f7ff', 'bg2' => '#dbe8ff', 'ink' => '#0f1b33', 'card' => '#ffffff', 'on' => '#ffffff'],
        'noir'   => ['acc' => '#f5f5f4', 'acc2' => '#25d366', 'bg' => '#0f1012', 'bg2' => '#1b1d21', 'ink' => '#f5f5f4', 'card' => '#17191d', 'on' => '#111214'],
        'rose'   => ['acc' => '#9f1239', 'acc2' => '#fb7185', 'bg' => '#fff5f6', 'bg2' => '#ffdde2', 'ink' => '#2a0a12', 'card' => '#ffffff', 'on' => '#ffffff'],
    ];
}

/** Realistic presets for the demo page. Images are Unsplash photos (fallback: gradient + emoji). */
function landing_presets(): array
{
    $u = fn(string $id, int $w = 1200) => 'https://images.unsplash.com/' . $id . '?auto=format&fit=crop&w=' . $w . '&q=70';
    $in2d = gmdate('Y-m-d\TH:i:s\Z', time() + 2 * 86400 + 5 * 3600 + 420);
    return [
        'homestay' => array_merge(landing_defaults(), [
            'theme' => 'forest', 'font' => 'serif', 'hero_style' => 'photo',
            'logo' => '/assets/img/demo/logo-homestay.svg', 'brand' => 'Seri Damai Homestay', 'tagline' => 'Brinchang, Cameron Highlands',
            'badge' => '🌙 Raya promo · 20% off', 'headline' => 'A villa in the tea hills, all to yourselves.', 'subheadline' => 'Three bedrooms, a private pool and a BBQ deck with a mountain view. Book on WhatsApp in under a minute, we confirm your dates straight away.',
            'cover' => $u('photo-1587061949409-02df41d5e562', 1600), 'cover_emoji' => '🏡',
            'cta' => 'Check dates on WhatsApp', 'cta_sub' => 'We reply within 10 minutes, 9am to 11pm',
            'stats' => [['4.9 ★', '212 Google reviews'], ['10 min', 'average reply'], ['3', 'villas · 24 beds'], ['Since 2018', 'family run']],
            'price' => 'From RM 360 / night', 'old_price' => 'RM 450', 'countdown_until' => $in2d, 'countdown_label' => 'Raya promo ends in',
            'packages_title' => 'Choose your stay', 'packages' => [
                ['Villa 3 bilik', 'Sleeps 8 · private pool · BBQ deck · 2 parking', 'RM 360 / night', $u('photo-1512917774080-9991f1c4c750', 800), '🏡', 'Hi Seri Damai, I am {name}. I would like to book the Villa 3 bilik. Dates: '],
                ['Studio 1 bilik', 'Sleeps 2 · balcony · kitchenette', 'RM 180 / night', $u('photo-1522708323590-d24dbb6b0267', 800), '🛏️', 'Hi Seri Damai, I am {name}. I would like to book the Studio. Dates: '],
                ['Dorm 8 beds', 'For groups · shared kitchen · hot shower', 'RM 35 / person', $u('photo-1555854877-bab0e564b8d5', 800), '🛌', 'Hi Seri Damai, I am {name}. I would like to book the Dorm for '],
            ],
            'highlights_title' => 'What is included', 'highlights' => ['🍳 Breakfast for 4 every morning', '🏊 Private pool, towels provided', '🔥 BBQ set and charcoal', '📶 Fast Wi-Fi and Netflix', '🚗 5 minutes to Brinchang night market', '🧺 Daily housekeeping on request'],
            'gallery_title' => 'A look around', 'gallery' => [$u('photo-1505693416388-ac5ce068fe85', 600), $u('photo-1540541338287-41700207dee6', 600), $u('photo-1502672260266-1c1ef2d93688', 600), $u('photo-1551882547-ff40c63fe5fa', 600), $u('photo-1507652313519-d4e9174996dd', 600)],
            'about_title' => 'The family behind Seri Damai', 'about' => 'We built Seri Damai in 2018 on our grandfather\'s strawberry farm. Today the three villas are run by our family: Pak Din keeps the garden, Mak Ani makes the breakfast, and we handle your WhatsApp messages ourselves, so you always talk to someone who knows the place.', 'about_image' => $u('photo-1512917774080-9991f1c4c750', 800),
            'reviews_title' => 'Guests say', 'reviews' => [['Nurul & family', 'The pool was perfect for the kids and breakfast was generous. Mak Ani even packed us strawberries for the road.', 5], ['Jason T.', 'Easy booking over WhatsApp, got our confirmation in five minutes. Villa was spotless.', 5], ['Aisyah', 'Woke up to clouds in the valley. The BBQ night with our friends was the highlight of the trip.', 5]],
            'faq_title' => 'Good to know', 'faq' => [['Check-in and check-out times?', 'Check-in 3pm, check-out 12pm. Early check-in is free when the villa is empty the night before, just ask.'], ['Is a deposit needed?', 'Yes, 30% to confirm, by bank transfer or Touch \'n Go after we confirm the dates on WhatsApp.'], ['Can we bring a pet?', 'Small dogs and cats are welcome in the Villa. Let us know when booking.'], ['How far from Kuala Lumpur?', 'About 3.5 hours by car. We send a pin and directions after booking.']],
            'location' => 'Lot 12, Jalan Kampung Taman Sedia, Brinchang, 39100 Cameron Highlands', 'maps_url' => 'https://maps.google.com', 'hours' => [['WhatsApp replies', '9am–11pm daily'], ['Check-in', '3pm–9pm'], ['Check-out', 'by 12pm']],
            'form_title' => 'Check availability', 'form_sub' => 'Tell us who you are and which unit. We reply on WhatsApp.', 'pre_chat' => 'name_question', 'question' => 'Which unit?', 'options' => ['Villa 3 bilik', 'Studio 1 bilik', 'Dorm 8 beds'],
            'message' => 'Hi Seri Damai, I am {name}. I would like to book the {answer} for Raya. Is it available?',
            'instagram' => 'https://instagram.com', 'tiktok' => 'https://tiktok.com', 'facebook' => 'https://facebook.com',
        ]),
        'bakery' => array_merge(landing_defaults(), [
            'theme' => 'sunset', 'font' => 'rounded', 'radius' => 'pill', 'hero_style' => 'split',
            'logo' => '', 'brand' => 'Tiga Luna Bakery', 'tagline' => 'Sourdough · croissants · custom cakes · Kuantan',
            'badge' => '🎁 Deepavali gift box · 60 only', 'headline' => 'Baked at 4am. Ordered on WhatsApp.', 'subheadline' => 'Pre-order the Deepavali box, a custom cake, or your weekly sourdough. Collect at the shop or delivery within Kuantan.',
            'cover' => $u('photo-1509440159596-0249088772ff', 1400), 'cover_emoji' => '🥐',
            'cta' => 'Order on WhatsApp', 'cta_sub' => 'Pay by bank transfer after we confirm',
            'stats' => [['4.8 ★', '540 reviews'], ['4am', 'fresh daily'], ['RM 8', 'delivery in Kuantan'], ['2019', 'since']],
            'price' => 'Deepavali box RM 88', 'old_price' => 'RM 108', 'countdown_until' => gmdate('Y-m-d\TH:i:s\Z', time() + 6 * 86400 + 11 * 3600), 'countdown_label' => 'Pre-orders close in',
            'packages_title' => 'This week', 'packages' => [
                ['Deepavali gift box', '12 mini pastries, country sourdough 800 g, salted-caramel jar', 'RM 88', $u('photo-1555507036-ab1f4038808a', 800), '🎁', 'Hi Tiga Luna, I am {name}. I would like to pre-order the Deepavali box. Quantity: '],
                ['Country sourdough', '800 g, 36-hour ferment, bakes Tue to Sun', 'RM 18', $u('photo-1585478259715-876acc5be8eb', 800), '🍞', 'Hi Tiga Luna, I am {name}. I would like to order sourdough for '],
                ['Butter croissants', 'Box of 6, French butter, best within the day', 'RM 30', $u('photo-1530610476181-d83430b64dcd', 800), '🥐', 'Hi Tiga Luna, I am {name}. I would like a box of croissants for '],
                ['Custom cake', 'Birthdays, weddings, 6 to 12 inch, 3 days notice', 'from RM 120', $u('photo-1578985545062-69928b1d9587', 800), '🎂', 'Hi Tiga Luna, I am {name}. I would like to ask about a custom cake for '],
            ],
            'highlights_title' => 'Why people queue', 'highlights' => ['🧈 French butter, no margarine', '🌾 Stoneground flour, 36-hour sourdough', '🚚 Same-day delivery in Kuantan before 11am orders', '🎂 Custom cakes with 3 days notice'],
            'gallery_title' => 'Fresh today', 'gallery' => [$u('photo-1549931319-a545dcf3bc73', 600), $u('photo-1517433670267-08bbd4be890f', 600), $u('photo-1558961363-fa8fdf82db35', 600), $u('photo-1486427944299-d1955d23e34d', 600)],
            'about_title' => 'Small bakery, long hours', 'about' => 'Tiga Luna started as a weekend stall at the Kuantan farmers market. We now bake from a small shop on Jalan Besar, still in small batches, still sold out by 2pm most days. Order on WhatsApp the night before and yours will be waiting.', 'about_image' => $u('photo-1556910103-1c02745aae4d', 800),
            'reviews_title' => 'Reviews', 'reviews' => [['Priya', 'Best croissants in Kuantan, hands down. The Deepavali box was a hit with my in-laws.', 5], ['Hafiz', 'Ordered a birthday cake on WhatsApp with two days notice. Delivered on time and looked exactly like the photo.', 5], ['Mei Ling', 'The sourdough keeps for days. Weekly order now.', 5]],
            'faq_title' => 'Questions', 'faq' => [['How do I pay?', 'Bank transfer or Touch \'n Go after we confirm your order on WhatsApp. No payment, no booking.'], ['Do you deliver?', 'Within Kuantan for RM 8, orders before 11am for same-day. Outside Kuantan by courier for breads only.'], ['Halal?', 'Yes, all ingredients are halal certified and the shop is Muslim owned.']],
            'location' => '27 Jalan Besar, 25000 Kuantan, Pahang', 'maps_url' => 'https://maps.google.com', 'hours' => [['Tue–Sun', '8am–6pm'], ['Monday', 'closed'], ['Pre-orders', 'any time on WhatsApp']],
            'form_title' => 'Place an order', 'form_sub' => 'Tell us your name and what you would like. We confirm on WhatsApp.', 'pre_chat' => 'name_question', 'question' => 'What would you like?', 'options' => ['Deepavali box', 'Sourdough', 'Croissants', 'Custom cake'],
            'message' => 'Hi Tiga Luna, I am {name}. I would like to order: {answer}.',
            'instagram' => 'https://instagram.com', 'facebook' => 'https://facebook.com',
        ]),
        'clinic' => array_merge(landing_defaults(), [
            'theme' => 'ocean', 'font' => 'sans', 'hero_style' => 'photo',
            'logo' => '/assets/img/demo/logo-clinic.svg', 'brand' => 'Klinik Dr. Aina', 'tagline' => 'Family dental · Bandar Indera Mahkota, Kuantan',
            'badge' => '🦷 New patients · free check-up this month', 'headline' => 'Gentle dentistry, booked on WhatsApp.', 'subheadline' => 'Scaling from RM 80, braces consultation free, kids welcome. Pick a slot on WhatsApp and skip the queue.',
            'cover' => $u('photo-1606811841689-23dfddce3e95', 1600), 'cover_emoji' => '🦷',
            'cta' => 'Book on WhatsApp', 'cta_sub' => 'Mon–Sat 9am–7pm · replies within the hour',
            'stats' => [['5.0 ★', '98 reviews'], ['15 min', 'from IM Hospital'], ['Panel', 'AIA · Prudential · Allianz'], ['Dr. Aina', 'MDS, 12 years']],
            'price' => 'Check-up RM 0', 'old_price' => 'RM 50',
            'packages_title' => 'Treatments', 'packages' => [
                ['Check-up & X-ray', 'Full examination, digital X-ray, treatment plan', 'Free this month', $u('photo-1588776814546-1ffcf47267a5', 800), '🔍', 'Hi Klinik Dr. Aina, I am {name}. I would like to book the free check-up. Preferred day: '],
                ['Scaling & polishing', '45 minutes, ultrasonic, fluoride finish', 'RM 80', $u('photo-1609840114035-3c981b782dfe', 800), '✨', 'Hi Klinik Dr. Aina, I am {name}. I would like to book scaling and polishing on '],
                ['Braces consultation', 'Metal, ceramic or clear aligners, instalments available', 'Free', $u('photo-1598256989800-fe5f95da9787', 800), '😁', 'Hi Klinik Dr. Aina, I am {name}. I would like a braces consultation on '],
                ['Kids first visit', 'Gentle intro visit, no drilling, sticker guaranteed', 'RM 40', $u('photo-1629909613654-28e377c37b09', 800), '🧒', 'Hi Klinik Dr. Aina, I am {name}. I would like to bring my child for a first visit on '],
            ],
            'highlights_title' => 'Why patients choose us', 'highlights' => ['🩺 Dr. Aina, MDS, 12 years of practice', '🧒 Kids-friendly room with cartoons', '💳 Panel for AIA, Prudential, Allianz, Great Eastern', '🅿️ Free parking in front', '🕖 Open until 7pm on weekdays', '📱 Reminders on WhatsApp the day before'],
            'gallery_title' => 'The clinic', 'gallery' => [$u('photo-1629909615184-74f495363b67', 600), $u('photo-1606811971618-4486d14f3f99', 600), $u('photo-1445527815219-ecbfec67492e', 600), $u('photo-1571772996211-2f02c9727629', 600)],
            'about_title' => 'About Dr. Aina', 'about' => 'Dr. Aina graduated from Universiti Malaya and spent eight years in government service before opening the clinic in 2020. The team is three dentists and two nurses, all Bahasa and English speaking, and the clinic uses digital X-rays and single-use instruments.', 'about_image' => $u('photo-1559839734-2b71ea197ec2', 800),
            'reviews_title' => 'Patient reviews', 'reviews' => [['Farah', 'My 5-year-old was terrified of dentists. He left with a sticker and asked when we can come back.', 5], ['Kumar', 'Booked on WhatsApp at 9, seen at 11. Scaling was painless.', 5], ['Siti', 'Clear explanation of the braces options and the instalment plan. No pressure.', 5]],
            'faq_title' => 'Before your visit', 'faq' => [['Do I need an appointment?', 'Walk-ins are welcome but appointments on WhatsApp are seen first. Book the night before for a morning slot.'], ['Which insurance panels?', 'AIA, Prudential, Allianz and Great Eastern. Bring your card or app.'], ['Is the check-up really free?', 'Yes for new patients until the end of this month. X-ray included. No obligation to treat.']],
            'location' => 'No 5, Jalan IM 3/1, Bandar Indera Mahkota, 25200 Kuantan', 'maps_url' => 'https://maps.google.com', 'hours' => [['Mon–Fri', '9am–7pm'], ['Saturday', '9am–3pm'], ['Sunday', 'closed']],
            'form_title' => 'Book a slot', 'form_sub' => 'Your name and the treatment. We confirm a time on WhatsApp.', 'pre_chat' => 'name_question', 'question' => 'Treatment', 'options' => ['Free check-up', 'Scaling', 'Braces consultation', 'Kids visit'],
            'message' => 'Hi Klinik Dr. Aina, I am {name}. I would like to book: {answer}.',
            'facebook' => 'https://facebook.com', 'instagram' => 'https://instagram.com',
        ]),
    ];
}

function landing_render(array $cfg, array $link, bool $demo = false): string
{
    $cfg = array_merge(landing_defaults(), $cfg);
    $th = landing_themes()[$cfg['theme']] ?? landing_themes()['forest'];
    $accent = $cfg['accent'] !== '' ? $cfg['accent'] : $th['acc'];
    $fonts = ['sans' => "'Plus Jakarta Sans', system-ui, sans-serif", 'serif' => "'Instrument Serif', Georgia, serif", 'rounded' => "'Nunito', system-ui, sans-serif"];
    $rad = ['sharp' => ['6px', '6px'], 'soft' => ['20px', '14px'], 'pill' => ['28px', '999px']][$cfg['radius']] ?? ['20px', '14px'];
    $dark = $cfg['theme'] === 'noir';
    $anim = $cfg['animation'] === 'on';
    $site = (string) cfg('site_name');
    $wa = 'https://wa.me/' . $link['phone'];
    $waSvg = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2m0 1.67c4.56 0 8.24 3.68 8.24 8.24s-3.68 8.24-8.24 8.24c-1.5 0-2.97-.41-4.26-1.18l-.3-.18-3.12.82.83-3.04-.2-.31a8.2 8.2 0 0 1-1.26-4.35c0-4.56 3.68-8.24 8.24-8.24m-3.4 4.43c-.19 0-.5.07-.76.35-.26.28-1 .98-1 2.38 0 1.4 1.02 2.76 1.17 2.95.14.19 2 3.17 4.93 4.32 2.44.96 2.93.77 3.46.72.53-.05 1.71-.7 1.95-1.37.24-.68.24-1.26.17-1.38-.07-.12-.26-.19-.55-.33-.29-.14-1.71-.84-1.97-.94-.26-.1-.46-.14-.65.14-.19.29-.75.94-.92 1.13-.17.19-.34.22-.63.07-.29-.14-1.22-.45-2.32-1.43-.86-.76-1.44-1.71-1.6-2-.17-.29-.02-.45.13-.59.13-.13.29-.34.43-.5.14-.17.19-.29.29-.48.1-.19.05-.36-.02-.5-.07-.14-.65-1.56-.89-2.14-.23-.56-.47-.49-.65-.5h-.55z"/></svg>';
    $navItems = array_values(array_filter([
        $cfg['packages'] ? [$cfg['packages_title'] ?: 'Packages', '#packages'] : null,
        $cfg['gallery'] ? [$cfg['gallery_title'] ?: 'Gallery', '#gallery'] : null,
        $cfg['reviews'] ? [$cfg['reviews_title'] ?: 'Reviews', '#reviews'] : null,
        $cfg['faq'] ? [$cfg['faq_title'] ?: 'FAQ', '#faq'] : null,
        $cfg['location'] !== '' ? ['Location', '#location'] : null,
    ]));
    $stars = fn(int $n) => str_repeat('★', max(0, min(5, $n))) . str_repeat('☆', 5 - max(0, min(5, $n)));
    $locParts = array_map('trim', explode(',', $cfg['location']));
    $locShort = $cfg['location'] !== '' ? (string) end($locParts) : '';
    ob_start(); ?>
<!doctype html>
<html lang="<?= lang() ?>">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($cfg['brand']) ?><?= $cfg['headline'] !== '' ? ' · ' . e($cfg['headline']) : '' ?></title>
<meta name="description" content="<?= e($cfg['subheadline']) ?>"><meta name="robots" content="noindex">
<meta property="og:title" content="<?= e($cfg['brand'] . ' · ' . $cfg['headline']) ?>"><meta property="og:description" content="<?= e($cfg['subheadline']) ?>"><?php if ($cfg['cover'] !== ''): ?><meta property="og:image" content="<?= e($cfg['cover']) ?>"><?php endif; ?>
<meta name="theme-color" content="<?= e($th['bg']) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Nunito:wght@500;700;800;900&display=swap" rel="stylesheet">
<style>
:root{--acc:<?= e($accent) ?>;--acc2:<?= e($th['acc2']) ?>;--bg:<?= e($th['bg']) ?>;--bg2:<?= e($th['bg2']) ?>;--ink:<?= e($th['ink']) ?>;--card:<?= e($th['card']) ?>;--on:<?= e($th['on']) ?>;--r:<?= $rad[0] ?>;--rb:<?= $rad[1] ?>;--head:<?= $fonts[$cfg['font']] ?? $fonts['sans'] ?>;--muted:<?= $dark ? 'rgba(245,245,244,.66)' : 'rgba(16,32,26,.62)' ?>;--line:<?= $dark ? 'rgba(255,255,255,.1)' : 'rgba(16,32,26,.09)' ?>;--shadow:0 20px 50px rgba(0,0,0,<?= $dark ? '.5' : '.08' ?>)}
*{box-sizing:border-box}html{scroll-behavior:smooth;-webkit-text-size-adjust:100%}
body{margin:0;font-family:'Plus Jakarta Sans',system-ui,sans-serif;color:var(--ink);background:var(--bg);line-height:1.5;overflow-x:hidden}
h1,h2,h3,.brand{font-family:var(--head);letter-spacing:-.01em;margin:0}
img{max-width:100%;display:block}
a{color:inherit}
.wrap{max-width:1080px;margin:0 auto;padding:0 18px}
.nav{position:sticky;top:0;z-index:20;backdrop-filter:blur(14px);background:color-mix(in srgb,var(--bg) 78%,transparent);border-bottom:1px solid var(--line)}
.nav .wrap{display:flex;align-items:center;gap:14px;height:64px}
.nav .brand{font-size:18px;font-weight:700;display:flex;align-items:center;gap:10px;text-decoration:none}
.nav .logo{width:36px;height:36px;border-radius:11px;object-fit:cover}.nav .logo.ph{display:grid;place-items:center;background:var(--acc);color:var(--on);font-weight:800;font-family:'Plus Jakarta Sans',sans-serif}
.nav .links{display:none;gap:22px;margin-left:auto;font-size:14px;font-weight:600}.nav .links a{text-decoration:none;color:var(--muted)}.nav .links a:hover{color:var(--ink)}
.nav .btn{margin-left:auto}@media(min-width:860px){.nav .links{display:flex}.nav .btn{margin-left:0}}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:9px;padding:12px 18px;border-radius:var(--rb);border:0;background:#25d366;color:#062b16;font:inherit;font-weight:800;font-size:15px;text-decoration:none;cursor:pointer;box-shadow:0 10px 26px rgba(37,211,102,.35);transition:transform .15s,box-shadow .15s;white-space:nowrap}
.btn:hover{transform:translateY(-2px);box-shadow:0 16px 32px rgba(37,211,102,.45)}.btn svg{width:20px;height:20px;fill:currentColor}
.btn.lg{padding:16px 24px;font-size:17px}.btn.ghost{background:var(--card);color:var(--ink);box-shadow:none;border:1.5px solid var(--line)}.btn.ghost:hover{box-shadow:var(--shadow)}
.btn.sm{padding:9px 14px;font-size:13.5px;box-shadow:none}
.hero{position:relative;overflow:hidden}
.hero.photo{min-height:min(88svh,760px);display:flex;align-items:flex-end;color:#fff}
.hero.photo .ph{position:absolute;inset:0;background:linear-gradient(135deg,var(--bg2),var(--acc2));transform:scale(1.04);will-change:transform}
.hero.photo .ph img{width:100%;height:100%;object-fit:cover}
.hero.photo::after{content:"";position:absolute;inset:0;background:linear-gradient(to top,rgba(8,20,14,.88) 0%,rgba(8,20,14,.45) 45%,rgba(8,20,14,.1) 100%)}
.hero.photo .wrap{position:relative;z-index:1;padding-top:120px;padding-bottom:44px;width:100%}
.hero.photo .emoji{position:absolute;right:6%;top:14%;font-size:clamp(90px,16vw,200px);filter:drop-shadow(0 20px 30px rgba(0,0,0,.3));animation:float 6s ease-in-out infinite;z-index:1}
.hero.split .wrap{display:grid;gap:30px;padding-top:40px;padding-bottom:40px;align-items:center}
@media(min-width:900px){.hero.split .wrap{grid-template-columns:1.05fr .95fr;min-height:620px;gap:48px}}
.hero.split .ph{border-radius:var(--r);overflow:hidden;aspect-ratio:4/3;background:linear-gradient(135deg,var(--bg2),var(--acc2));box-shadow:var(--shadow);display:grid;place-items:center;position:relative}
.hero.split .ph img{width:100%;height:100%;object-fit:cover}.hero.split .emoji{font-size:120px;animation:float 6s ease-in-out infinite}
.hero.plain .wrap{padding:70px 18px 40px;text-align:center;max-width:760px}
.badge{display:inline-block;background:color-mix(in srgb,var(--acc2) 22%,var(--card));color:var(--ink);border:1px solid color-mix(in srgb,var(--acc2) 45%,transparent);font-weight:700;font-size:13px;padding:7px 12px;border-radius:999px;margin-bottom:16px}
.hero.photo .badge{background:rgba(255,255,255,.14);color:#fff;border-color:rgba(255,255,255,.3);backdrop-filter:blur(6px)}
h1{font-size:clamp(34px,6vw,64px);line-height:1.02;max-width:14ch;margin-bottom:14px}
.hero .sub{font-size:clamp(16px,2vw,19px);max-width:56ch;margin:0 0 22px;opacity:.9}
.hero .ctas{display:flex;gap:10px;flex-wrap:wrap;align-items:center}.hero .note{font-size:13px;opacity:.8;margin:10px 0 0}
.hero.photo .btn.ghost{background:rgba(255,255,255,.12);color:#fff;border-color:rgba(255,255,255,.3)}
@keyframes float{0%,100%{transform:translateY(0) rotate(-3deg)}50%{transform:translateY(-16px) rotate(3deg)}}
.stats{position:relative;z-index:2;margin:-26px auto 0;padding:0 18px}
.stats .in{background:var(--card);border:1px solid var(--line);border-radius:var(--r);box-shadow:var(--shadow);display:grid;grid-template-columns:repeat(2,1fr);max-width:1080px;margin:0 auto;overflow:hidden}
@media(min-width:720px){.stats .in{grid-template-columns:repeat(4,1fr)}}
.stats .s{padding:18px 16px;border-right:1px solid var(--line);border-bottom:1px solid var(--line)}.stats .s b{display:block;font-size:22px;color:var(--acc);font-family:var(--head)}.stats .s span{font-size:12.5px;color:var(--muted)}
.hero.split+.stats,.hero.plain+.stats{margin-top:8px}
.sec{padding:clamp(44px,7vw,84px) 0}.sec.alt{background:color-mix(in srgb,var(--bg2) 55%,var(--bg))}
.sec-head{margin-bottom:26px;max-width:640px}.sec-head .k{font-family:ui-monospace,monospace;font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:var(--acc2);font-weight:700;margin-bottom:8px;display:block}
.sec-head h2{font-size:clamp(26px,4vw,40px);line-height:1.08}.sec-head p{color:var(--muted);margin:10px 0 0}
.offer{display:grid;gap:18px;align-items:center;background:var(--acc);color:var(--on);border-radius:var(--r);padding:22px;box-shadow:var(--shadow)}
@media(min-width:760px){.offer{grid-template-columns:1fr auto auto}}
.offer .p b{font-size:28px;font-family:var(--head);display:block;line-height:1.1}.offer .p s{opacity:.7}.offer .l{font-size:12px;text-transform:uppercase;letter-spacing:.12em;opacity:.8;font-weight:700}
.cd{display:flex;gap:8px}.cd div{background:rgba(255,255,255,.14);border-radius:12px;padding:8px 10px;min-width:58px;text-align:center}.cd b{display:block;font-size:22px;font-variant-numeric:tabular-nums;line-height:1}.cd small{font-size:10px;text-transform:uppercase;letter-spacing:.08em;opacity:.8}
<?php if ($dark): ?>.offer{background:var(--acc2);color:#062b16}.cd div{background:rgba(0,0,0,.12)}<?php endif; ?>
.pk{display:grid;gap:16px;grid-template-columns:repeat(auto-fill,minmax(250px,1fr))}
.pk .c{background:var(--card);border:1px solid var(--line);border-radius:var(--r);overflow:hidden;display:flex;flex-direction:column;transition:transform .25s cubic-bezier(.16,1,.3,1),box-shadow .25s}
.pk .c:hover{transform:translateY(-4px);box-shadow:var(--shadow)}
.pk .im{aspect-ratio:4/3;background:linear-gradient(135deg,var(--bg2),color-mix(in srgb,var(--acc2) 40%,var(--bg2)));display:grid;place-items:center;font-size:54px;overflow:hidden}.pk .im img{width:100%;height:100%;object-fit:cover;transition:transform .5s}.pk .c:hover .im img{transform:scale(1.05)}
.pk .b{padding:16px 16px 18px;display:flex;flex-direction:column;gap:8px;flex:1}.pk h3{font-size:19px}.pk p{margin:0;color:var(--muted);font-size:14px;flex:1}
.pk .pr{font-weight:800;color:var(--acc);font-size:17px}.pk .btn{align-self:flex-start;margin-top:4px}
.hl{display:grid;gap:10px;grid-template-columns:repeat(auto-fill,minmax(240px,1fr))}.hl div{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:14px 16px;font-size:15px;font-weight:600}
.gal{display:grid;gap:8px;grid-template-columns:repeat(2,1fr)}@media(min-width:700px){.gal{grid-template-columns:repeat(3,1fr)}.gal .g:first-child{grid-row:span 2}}
.gal .g{aspect-ratio:1;border-radius:14px;overflow:hidden;background:var(--bg2)}@media(min-width:700px){.gal .g:first-child{aspect-ratio:auto}}.gal img{width:100%;height:100%;object-fit:cover;transition:transform .6s}.gal .g:hover img{transform:scale(1.06)}
.ab{display:grid;gap:26px;align-items:center}@media(min-width:800px){.ab{grid-template-columns:1fr 1fr;gap:48px}}
.ab .im{border-radius:var(--r);overflow:hidden;aspect-ratio:4/3;background:var(--bg2);box-shadow:var(--shadow)}.ab .im img{width:100%;height:100%;object-fit:cover}.ab p{color:var(--muted);font-size:16.5px;line-height:1.7}
.rv{display:flex;gap:14px;overflow-x:auto;scroll-snap-type:x mandatory;padding:4px 4px 14px;margin:0 -4px;scrollbar-width:none}.rv::-webkit-scrollbar{display:none}
.rv .c{flex:0 0 min(86vw,340px);scroll-snap-align:start;background:var(--card);border:1px solid var(--line);border-radius:var(--r);padding:20px}
.rv .st{color:#f59e0b;letter-spacing:2px;font-size:14px}.rv p{margin:10px 0 12px;font-size:15.5px;line-height:1.6}.rv b{font-size:13.5px;display:flex;align-items:center}.rv .av{display:inline-grid;place-items:center;width:30px;height:30px;border-radius:50%;background:var(--acc);color:var(--on);font-weight:800;font-size:13px;margin-right:8px;font-family:'Plus Jakarta Sans'}
.faq{max-width:760px}.faq details{border-bottom:1px solid var(--line)}.faq summary{cursor:pointer;list-style:none;padding:16px 40px 16px 0;font-weight:700;font-size:16.5px;position:relative}.faq summary::-webkit-details-marker{display:none}.faq summary::after{content:"+";position:absolute;right:2px;top:12px;font-size:24px;color:var(--acc2);transition:transform .2s}.faq details[open] summary::after{transform:rotate(45deg)}.faq .a{padding:0 0 16px;color:var(--muted)}
.loc{display:grid;gap:18px}@media(min-width:800px){.loc{grid-template-columns:1.1fr .9fr}}
.loc .map{border-radius:var(--r);overflow:hidden;min-height:260px;background:linear-gradient(135deg,var(--bg2),color-mix(in srgb,var(--acc2) 30%,var(--bg2)));position:relative;display:grid;place-items:center}
.loc .map iframe{width:100%;height:100%;min-height:300px;border:0}.loc .pin{font-size:56px;animation:bounce 2.4s ease-in-out infinite;position:relative}.loc .map .grid{position:absolute;inset:0;background-image:linear-gradient(var(--line) 1px,transparent 1px),linear-gradient(90deg,var(--line) 1px,transparent 1px);background-size:34px 34px;opacity:.7}
@keyframes bounce{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}
.loc .info{background:var(--card);border:1px solid var(--line);border-radius:var(--r);padding:22px}.loc .info h3{font-size:20px;margin-bottom:8px}.loc table{width:100%;border-collapse:collapse;margin-top:14px;font-size:14.5px}.loc td{padding:8px 0;border-top:1px solid var(--line)}.loc td:last-child{text-align:right;font-weight:700}
.fin{background:var(--acc);color:var(--on);border-radius:var(--r);padding:clamp(24px,5vw,48px);display:grid;gap:26px;box-shadow:var(--shadow)}
@media(min-width:860px){.fin{grid-template-columns:1fr 1fr;align-items:center}}
<?php if ($dark): ?>.fin{background:var(--card);border:1px solid var(--line)}<?php endif; ?>
.fin h2{font-size:clamp(26px,4vw,40px);line-height:1.08;margin-bottom:10px}.fin .sub{opacity:.85;margin:0}
.fin form{background:var(--card);color:var(--ink);border-radius:var(--r);padding:20px}
.field{margin:0 0 12px}.field label{display:block;font-size:12.5px;font-weight:700;margin-bottom:6px;color:var(--muted)}
.input{width:100%;font:inherit;font-size:16px;padding:12px 14px;border-radius:12px;border:1.5px solid var(--line);background:var(--card);color:var(--ink)}.input:focus{outline:none;border-color:var(--acc2);box-shadow:0 0 0 3px color-mix(in srgb,var(--acc2) 25%,transparent)}
.chips{display:flex;flex-wrap:wrap;gap:8px}.chips label{cursor:pointer;position:relative}.chips input{position:absolute;opacity:0}.chips span{display:inline-block;padding:9px 14px;border-radius:999px;border:1.5px solid var(--line);font-size:14px;font-weight:600}.chips input:checked+span{background:var(--acc);color:var(--on);border-color:var(--acc)}
.fin .btn{width:100%;position:relative}.fin .btn.pulse::after{content:"";position:absolute;inset:-4px;border-radius:inherit;border:2px solid #25d366;animation:pulse 2s ease-out infinite}
@keyframes pulse{0%{opacity:.8;transform:scale(1)}100%{opacity:0;transform:scale(1.08)}}
.fin .note{text-align:center;font-size:13px;color:var(--muted);margin:10px 0 0}
.ft{padding:28px 0 110px;color:var(--muted);font-size:13.5px}.ft .wrap{display:flex;flex-wrap:wrap;gap:12px 20px;align-items:center}.ft .soc{display:flex;gap:8px;margin-left:auto}.ft .soc a{width:36px;height:36px;border-radius:11px;display:grid;place-items:center;background:var(--card);border:1px solid var(--line);text-decoration:none;font-weight:800;font-size:12.5px;color:var(--ink)}
.ft .pw{width:100%;text-align:center;font-size:12px;padding-top:10px;border-top:1px solid var(--line)}
.sticky{position:fixed;left:0;right:0;bottom:0;z-index:15;padding:10px 14px calc(10px + env(safe-area-inset-bottom));background:linear-gradient(to top,var(--bg) 70%,transparent);transform:translateY(110%);transition:transform .3s cubic-bezier(.16,1,.3,1)}
.sticky.show{transform:none}.sticky .btn{width:100%}@media(min-width:860px){.sticky{display:none}}
<?php if ($anim): ?>
.rv-in{opacity:0;transform:translateY(22px);transition:opacity .7s cubic-bezier(.16,1,.3,1),transform .7s cubic-bezier(.16,1,.3,1)}.rv-in.on{opacity:1;transform:none}
.hero .h-in{opacity:0;transform:translateY(16px);animation:hin .8s cubic-bezier(.16,1,.3,1) forwards}.hero .h-in:nth-child(2){animation-delay:.1s}.hero .h-in:nth-child(3){animation-delay:.2s}.hero .h-in:nth-child(4){animation-delay:.3s}.hero .h-in:nth-child(5){animation-delay:.4s}
@keyframes hin{to{opacity:1;transform:none}}
<?php endif; ?>
@media(prefers-reduced-motion:reduce){*{animation:none!important;transition:none!important}.rv-in,.hero .h-in{opacity:1;transform:none}}
</style>
<?php if ($demo): ?><?= demo_popup_css() ?><style>.demo-sheet{font-family:'Plus Jakarta Sans',system-ui,sans-serif;color:#10201a}.demo-sheet h3{margin:0 0 6px;font-size:20px;font-family:'Plus Jakarta Sans'}.demo-sheet .kicker{font-family:ui-monospace,monospace;font-size:10.5px;letter-spacing:.14em;text-transform:uppercase;color:#1f7a4f}.demo-sheet .muted{color:#5b6b63}.demo-sheet .small{font-size:13px}.demo-sheet .hint{font-size:12px;color:#5b6b63}.demo-actions{display:flex;gap:8px;margin-top:14px;flex-wrap:wrap}.demo-sheet .btn{box-shadow:none;background:#0b3d2e;color:#fff;border-radius:12px;padding:11px 16px;font-size:14.5px}.demo-sheet .btn-ghost{background:#fff;color:#0b3d2e;border:1.5px solid #0b3d2e}</style><?php endif; ?>
</head>
<body>
<header class="nav"><div class="wrap">
  <a class="brand" href="#top"><?php if ($cfg['logo'] !== ''): ?><img class="logo" src="<?= e($cfg['logo']) ?>" alt=""><?php else: ?><span class="logo ph"><?= e(mb_substr($cfg['brand'], 0, 1)) ?></span><?php endif; ?><?= e($cfg['brand']) ?></a>
  <nav class="links"><?php foreach ($navItems as [$l, $h]): ?><a href="<?= e($h) ?>"><?= e($l) ?></a><?php endforeach; ?></nav>
  <a class="btn sm" href="#book"><?= $waSvg ?><?= e($cfg['cta']) ?></a>
</div></header>

<section class="hero <?= e($cfg['hero_style']) ?>" id="top">
  <?php if ($cfg['hero_style'] === 'photo'): ?>
  <div class="ph" data-parallax><?php if ($cfg['cover'] !== ''): ?><img src="<?= e($cfg['cover']) ?>" alt="" loading="eager"><?php endif; ?></div>
  <?php if ($cfg['cover'] === '' && $cfg['cover_emoji'] !== ''): ?><span class="emoji"><?= e($cfg['cover_emoji']) ?></span><?php endif; ?>
  <?php endif; ?>
  <div class="wrap">
    <div>
      <?php if ($cfg['badge'] !== ''): ?><span class="badge h-in"><?= e($cfg['badge']) ?></span><?php endif; ?>
      <h1 class="h-in"><?= e($cfg['headline']) ?></h1>
      <?php if ($cfg['subheadline'] !== ''): ?><p class="sub h-in"><?= e($cfg['subheadline']) ?></p><?php endif; ?>
      <div class="ctas h-in"><a class="btn lg" href="#book"><?= $waSvg ?><?= e($cfg['cta']) ?></a><?php if ($cfg['packages']): ?><a class="btn lg ghost" href="#packages"><?= e($cfg['packages_title'] ?: 'See packages') ?> ↓</a><?php endif; ?></div>
      <?php if ($cfg['cta_sub'] !== ''): ?><p class="note h-in"><?= e($cfg['cta_sub']) ?></p><?php endif; ?>
    </div>
    <?php if ($cfg['hero_style'] === 'split'): ?><div class="ph h-in"><?php if ($cfg['cover'] !== ''): ?><img src="<?= e($cfg['cover']) ?>" alt=""><?php else: ?><span class="emoji"><?= e($cfg['cover_emoji'] ?: '💬') ?></span><?php endif; ?></div><?php endif; ?>
  </div>
</section>

<?php if ($cfg['stats']): ?>
<div class="stats rv-in"><div class="in"><?php foreach (array_slice($cfg['stats'], 0, 4) as [$v, $l]): ?><div class="s"><b><?= e($v) ?></b><span><?= e($l) ?></span></div><?php endforeach; ?></div></div>
<?php endif; ?>

<?php if ($cfg['price'] !== '' || $cfg['countdown_until'] !== ''): ?>
<section class="sec" style="padding-bottom:0"><div class="wrap"><div class="offer rv-in">
  <div class="p"><?php if ($cfg['badge'] !== ''): ?><span class="l"><?= e($cfg['badge']) ?></span><?php endif; ?><b><?= e($cfg['price']) ?> <?php if ($cfg['old_price'] !== ''): ?><s style="font-size:.55em"><?= e($cfg['old_price']) ?></s><?php endif; ?></b></div>
  <?php if ($cfg['countdown_until'] !== ''): ?><div><div class="l" style="margin-bottom:6px"><?= e($cfg['countdown_label']) ?></div><div class="cd" data-until="<?= e($cfg['countdown_until']) ?>"><div><b data-d>0</b><small>days</small></div><div><b data-h>00</b><small>hrs</small></div><div><b data-m>00</b><small>min</small></div><div><b data-s>00</b><small>sec</small></div></div></div><?php endif; ?>
  <a class="btn" href="#book"><?= $waSvg ?><?= e($cfg['cta']) ?></a>
</div></div></section>
<?php endif; ?>

<?php if ($cfg['packages']): ?>
<section class="sec" id="packages"><div class="wrap">
  <div class="sec-head rv-in"><span class="k"><?= e($cfg['packages_title']) ?></span><h2><?= e($cfg['packages_title']) ?></h2></div>
  <div class="pk"><?php foreach (array_slice($cfg['packages'], 0, 6) as $i => [$t, $d, $pr, $im, $em, $msg]): ?>
    <div class="c rv-in" style="transition-delay:<?= $i * 70 ?>ms"><div class="im"><?php if ($im !== ''): ?><img src="<?= e($im) ?>" alt="" loading="lazy" onerror="this.remove()"><?php else: ?><?= e($em) ?><?php endif; ?></div>
    <div class="b"><h3><?= e($t) ?></h3><p><?= e($d) ?></p><span class="pr"><?= e($pr) ?></span><button class="btn sm" type="button" data-msg="<?= e($msg) ?>"><?= $waSvg ?>Chat about this</button></div></div>
  <?php endforeach; ?></div>
</div></section>
<?php endif; ?>

<?php if ($cfg['highlights']): ?>
<section class="sec alt"><div class="wrap">
  <div class="sec-head rv-in"><span class="k"><?= e($cfg['highlights_title']) ?></span><h2><?= e($cfg['highlights_title']) ?></h2></div>
  <div class="hl"><?php foreach (array_slice($cfg['highlights'], 0, 6) as $i => $h): ?><div class="rv-in" style="transition-delay:<?= $i * 60 ?>ms"><?= e($h) ?></div><?php endforeach; ?></div>
</div></section>
<?php endif; ?>

<?php if ($cfg['gallery']): ?>
<section class="sec" id="gallery"><div class="wrap">
  <div class="sec-head rv-in"><span class="k"><?= e($cfg['gallery_title']) ?></span><h2><?= e($cfg['gallery_title']) ?></h2></div>
  <div class="gal"><?php foreach (array_slice($cfg['gallery'], 0, 8) as $i => $g): ?><div class="g rv-in" style="transition-delay:<?= $i * 60 ?>ms"><img src="<?= e($g) ?>" alt="" loading="lazy"></div><?php endforeach; ?></div>
</div></section>
<?php endif; ?>

<?php if ($cfg['about'] !== ''): ?>
<section class="sec alt"><div class="wrap"><div class="ab">
  <div class="rv-in"><div class="sec-head"><span class="k"><?= e($cfg['about_title']) ?></span><h2><?= e($cfg['about_title']) ?></h2></div><p><?= nl2br(e($cfg['about'])) ?></p><p><a class="btn ghost" href="#book"><?= $waSvg ?><?= e($cfg['cta']) ?></a></p></div>
  <?php if ($cfg['about_image'] !== ''): ?><div class="im rv-in"><img src="<?= e($cfg['about_image']) ?>" alt="" loading="lazy"></div><?php endif; ?>
</div></div></section>
<?php endif; ?>

<?php if ($cfg['reviews']): ?>
<section class="sec" id="reviews"><div class="wrap">
  <div class="sec-head rv-in"><span class="k"><?= e($cfg['reviews_title']) ?></span><h2><?= e($cfg['reviews_title']) ?></h2></div>
  <div class="rv"><?php foreach (array_slice($cfg['reviews'], 0, 6) as [$n, $txt, $st]): ?><div class="c rv-in"><div class="st"><?= $stars((int) $st) ?></div><p>“<?= e($txt) ?>”</p><b><span class="av"><?= e(mb_substr($n, 0, 1)) ?></span><?= e($n) ?></b></div><?php endforeach; ?></div>
</div></section>
<?php endif; ?>

<?php if ($cfg['faq']): ?>
<section class="sec alt" id="faq"><div class="wrap">
  <div class="sec-head rv-in"><span class="k"><?= e($cfg['faq_title']) ?></span><h2><?= e($cfg['faq_title']) ?></h2></div>
  <div class="faq rv-in"><?php foreach (array_slice($cfg['faq'], 0, 8) as $i => [$q, $a]): ?><details<?= $i === 0 ? ' open' : '' ?>><summary><?= e($q) ?></summary><div class="a"><?= e($a) ?></div></details><?php endforeach; ?></div>
</div></section>
<?php endif; ?>

<?php if ($cfg['location'] !== '' || $cfg['hours']): ?>
<section class="sec" id="location"><div class="wrap"><div class="loc">
  <div class="map rv-in"><?php if ($cfg['map_embed'] !== ''): ?><iframe src="<?= e($cfg['map_embed']) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe><?php else: ?><div class="grid"></div><span class="pin">📍</span><?php endif; ?></div>
  <div class="info rv-in"><h3>Find us</h3><?php if ($cfg['location'] !== ''): ?><p style="margin:0;color:var(--muted)"><?= e($cfg['location']) ?></p><?php endif; ?>
    <?php if ($cfg['hours']): ?><table><?php foreach ($cfg['hours'] as [$d, $h]): ?><tr><td><?= e($d) ?></td><td><?= e($h) ?></td></tr><?php endforeach; ?></table><?php endif; ?>
    <p style="margin:16px 0 0;display:flex;gap:8px;flex-wrap:wrap"><?php if ($cfg['maps_url'] !== ''): ?><a class="btn ghost sm" href="<?= e($cfg['maps_url']) ?>" target="_blank" rel="noopener">Open in Google Maps ↗</a><?php endif; ?><a class="btn sm" href="#book"><?= $waSvg ?><?= e($cfg['cta']) ?></a></p>
  </div>
</div></div></section>
<?php endif; ?>

<section class="sec" id="book"><div class="wrap"><div class="fin rv-in">
  <div><h2><?= e($cfg['form_title'] ?: $cfg['cta']) ?></h2><p class="sub"><?= e($cfg['form_sub']) ?></p></div>
  <form id="lp" data-wa="<?= e($wa) ?>" data-msg="<?= e($cfg['message']) ?>" data-demo="<?= $demo ? '1' : '0' ?>">
    <?php if ($cfg['pre_chat'] !== 'none'): ?><div class="field"><label for="nm"><?= e(t('lp_your_name')) ?></label><input class="input" id="nm" name="name" autocomplete="name" placeholder="<?= e(t('lp_name_ph')) ?>" required></div><?php endif; ?>
    <?php if ($cfg['pre_chat'] === 'name_question' && $cfg['question'] !== ''): ?>
    <div class="field"><label><?= e($cfg['question']) ?></label><?php if ($cfg['options']): ?><div class="chips"><?php foreach ($cfg['options'] as $i => $o): ?><label><input type="radio" name="answer" value="<?= e($o) ?>" <?= $i === 0 ? 'checked' : '' ?>><span><?= e($o) ?></span></label><?php endforeach; ?></div><?php else: ?><input class="input" name="answer"><?php endif; ?></div>
    <?php endif; ?>
    <button class="btn lg <?= $anim ? 'pulse' : '' ?>" type="submit" id="cta"><?= $waSvg ?><?= e($cfg['cta']) ?></button>
    <?php if ($cfg['cta_sub'] !== ''): ?><p class="note"><?= e($cfg['cta_sub']) ?></p><?php endif; ?>
  </form>
</div></div></section>

<footer class="ft"><div class="wrap">
  <span>© <?= date('Y') ?> <?= e($cfg['brand']) ?></span><?php if ($locShort !== ''): ?><span>·</span><span><?= e($locShort) ?></span><?php endif; ?>
  <?php $soc = array_filter(['IG' => $cfg['instagram'], 'FB' => $cfg['facebook'], 'TT' => $cfg['tiktok'], 'WEB' => $cfg['website']]); if ($soc): ?><div class="soc"><?php foreach ($soc as $k => $u): ?><a href="<?= e($u) ?>" target="_blank" rel="noopener"><?= e($k) ?></a><?php endforeach; ?></div><?php endif; ?>
  <?php if ($cfg['powered']): ?><div class="pw"><?= e(t('lp_powered')) ?> <a href="<?= e(base_url()) ?>"><?= e($site) ?></a></div><?php endif; ?>
</div></footer>
<div class="sticky" id="sticky"><a class="btn" href="#book"><?= $waSvg ?><?= e($cfg['cta']) ?></a></div>

<script>
(function(){
  var cd=document.querySelector('.cd[data-until]');
  if(cd){var until=new Date(cd.dataset.until).getTime();var q=function(s){return cd.querySelector(s)};var ed=q('[data-d]'),eh=q('[data-h]'),em=q('[data-m]'),es=q('[data-s]');
    var tick=function(){var t=Math.max(0,until-Date.now());ed.textContent=Math.floor(t/864e5);eh.textContent=String(Math.floor(t%864e5/36e5)).padStart(2,'0');em.textContent=String(Math.floor(t%36e5/6e4)).padStart(2,'0');es.textContent=String(Math.floor(t%6e4/1e3)).padStart(2,'0');};tick();setInterval(tick,1000);}
  if('IntersectionObserver' in window){var io=new IntersectionObserver(function(es){es.forEach(function(e){if(e.isIntersecting){e.target.classList.add('on');io.unobserve(e.target);}});},{threshold:.12});document.querySelectorAll('.rv-in').forEach(function(el){io.observe(el);});
    var sticky=document.getElementById('sticky'),book=document.getElementById('book'),hero=document.getElementById('top');var vis={};var so=new IntersectionObserver(function(es){es.forEach(function(e){vis[e.target.id]=e.isIntersecting;});sticky.classList.toggle('show',!vis.top&&!vis.book);},{threshold:.15});so.observe(book);so.observe(hero);}
  var par=document.querySelector('[data-parallax]');if(par&&!matchMedia('(prefers-reduced-motion: reduce)').matches){addEventListener('scroll',function(){var y=Math.min(window.scrollY,800);par.style.transform='scale(1.04) translateY('+(y*.18)+'px)';},{passive:true});}
  var form=document.getElementById('lp');
  function go(msg){var url=form.dataset.wa+(msg?'?text='+encodeURIComponent(msg):'');if(form.dataset.demo==='1'){window.lpDemo&&window.lpDemo(msg);return;}window.location.href=url;}
  function fill(tpl){var name=(form.querySelector('[name=name]')||{}).value||'';var a=form.querySelector('[name=answer]:checked')||form.querySelector('[name=answer]');a=a?a.value:'';return (tpl||'').replace('{name}',name.trim()||'…').replace('{answer}',a.trim());}
  form.addEventListener('submit',function(e){e.preventDefault();go(fill(form.dataset.msg));});
  document.querySelectorAll('button[data-msg]').forEach(function(b){b.addEventListener('click',function(){var inp=form.querySelector('[name=name]');if(inp&&!inp.value){document.getElementById('book').scrollIntoView({behavior:'smooth'});inp.focus();inp.dataset.pending=b.dataset.msg;return;}go(fill(b.dataset.msg));});});
  var nm=form.querySelector('[name=name]');if(nm){nm.addEventListener('change',function(){if(nm.dataset.pending&&nm.value){var m=nm.dataset.pending;nm.dataset.pending='';go(fill(m));}});}
})();
</script>
<?php if ($demo): ?>
<script>window.lpDemo=function(msg){var old=document.getElementById('demo-modal');if(old)old.remove();var tpl=document.getElementById('demo-tpl').innerHTML.replace('{{MSG}}',msg.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/\n/g,'<br>'));document.body.insertAdjacentHTML('beforeend',tpl);};</script>
<template id="demo-tpl"><?= str_replace('{MSGPH}', '{{MSG}}', demo_popup_html('{MSGPH}', $link['phone'], base_url() . '/landing-demo', t('lp_demo_cta'), 'form')) ?></template>
<?php endif; ?>
</body>
</html>
<?php
    return (string) ob_get_clean();
}
