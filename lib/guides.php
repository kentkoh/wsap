<?php
declare(strict_types=1);

/**
 * SEO guide pages (rendered by guide.php at /{slug}; slugs are reserved so no short link can take them).
 * Each guide: title (<title>), h1, lead, sections [[h2, html]], steps [[name, text]] (HowTo),
 * faq [[q, a]] (FAQPage), related [[label, path]]. "{domain}" is replaced with the current domain.
 */

function guide_slugs(): array
{
    return [
        'whatsapp-link-generator' => '🔗', 'wa-me-link' => '📱', 'whatsapp-qr-code' => '▦', 'whatsapp-link-instagram-bio' => '📸',
        'whatsapp-business-link' => '🏪', 'walink-alternative' => '⚖️', 'cara-buat-link-whatsapp' => '🇲🇾',
    ];
}

function guide(string $slug, string $lang): ?array
{
    $all = $lang === 'ms' ? guides_ms() : guides_en();
    return $all[$slug] ?? null;
}

function guides_en(): array
{
    $cta = [['Free WhatsApp link generator', '/whatsapp-link-generator'], ['WhatsApp QR code', '/whatsapp-qr-code'], ['Premium branded links', '/premium-links'], ['WhatsApp order forms', '/whatsapp-forms'], ['WhatsApp shop', '/whatsapp-shop']];
    return [
        'whatsapp-link-generator' => [
            'title' => 'Free WhatsApp Link Generator with Pre-filled Message & QR Code',
            'h1' => 'Free WhatsApp link generator',
            'lead' => 'Turn any phone number into a short {domain} link that opens a WhatsApp chat with your message already typed. Free, no sign-up, never expires. QR code included.',
            'sections' => [
                ['What a WhatsApp link does', '<p>A WhatsApp link (also called a click-to-chat link) opens a conversation with a specific number the moment someone taps it. The customer does not save your contact, type your number, or search for you. If you add a message, it is already in the text box, so they only press Send.</p><p>WSAP gives you a short address like <code>{domain}/x7k2pq</code> instead of the long official format, plus a QR code for print, and it works on iPhone, Android, WhatsApp Web and WhatsApp Desktop.</p>'],
                ['Why use a short link instead of wa.me', '<p>The official <code>wa.me</code> format works, but once you add a pre-filled message it becomes a long string of <code>%20</code> codes that breaks in bios and looks suspicious on a flyer. A short WSAP link stays clean, fits anywhere, and if you create it from a free account you can change the number or the message later without reprinting anything.</p><ul><li><strong>Short and readable</strong>, so it fits an Instagram bio, a TikTok profile, a business card.</li><li><strong>Editable from your dashboard</strong> when you sign up for free. The printed QR keeps working.</li><li><strong>Click counter</strong> on every link, so you know which flyer or ad actually brings chats.</li><li><strong>Opens WhatsApp correctly from in-app browsers</strong> like Instagram and TikTok, where plain wa.me links often fail.</li></ul>'],
                ['Where businesses use it', '<p>Instagram and TikTok bios, Facebook ads with a WhatsApp call to action, Google Business Profile, email signatures, printed menus and packaging, shop windows, event banners and car stickers. Put the QR code anywhere a phone camera can see it.</p>'],
            ],
            'steps' => [
                ['Enter your WhatsApp number', 'Pick your country code and type the number. Use the number WhatsApp is registered on, personal or Business.'],
                ['Write the first message (optional)', 'For example "Hi, I saw your ad for the weekend promo". The customer can edit it before sending.'],
                ['Create the link', 'You get a short {domain} link and a QR code instantly. Copy it, download the PNG, or test it.'],
                ['Save it to a free account', 'Sign up to keep every link in a dashboard, see clicks, and edit the number or message later.'],
            ],
            'faq' => [
                ['Is the WhatsApp link generator really free?', 'Yes. Free links have no limit on clicks and never expire. A free account adds a dashboard, click counts and editing, also free. Premium is only for a branded address like {domain}/yourbrand and full analytics.'],
                ['Does it work with WhatsApp Business?', 'Yes. Any number registered on WhatsApp or WhatsApp Business works. The customer sees your business profile when the chat opens.'],
                ['Can I change the number later?', 'If you created the link from a free account, yes, at any time from the dashboard. Guest links cannot be edited, which is why we recommend signing up before you print a QR code.'],
                ['What happens when someone clicks?', 'On a phone, WhatsApp opens with your number and the message. On a computer, WhatsApp Web or Desktop opens. We count the click and send the visitor on in a fraction of a second.'],
                ['Do you see my chats?', 'No. The link only opens WhatsApp. Everything after that happens inside WhatsApp, between you and the customer.'],
            ],
            'related' => $cta,
        ],
        'wa-me-link' => [
            'title' => 'wa.me Link: How to Create a WhatsApp Link with a Message (and a Shorter Alternative)',
            'h1' => 'The wa.me link, explained',
            'lead' => 'wa.me is WhatsApp\'s official click-to-chat domain. Here is the exact format, how to add a pre-filled message, the mistakes that break it, and how to get a shorter link you can edit.',
            'sections' => [
                ['The wa.me format', '<p>The link is <code>https://wa.me/</code> followed by the full number in international format with no plus sign, spaces, dashes or brackets. A Malaysian number 012-345 6789 becomes <code>https://wa.me/60123456789</code>. A Singapore number 9123 4567 becomes <code>https://wa.me/6591234567</code>.</p><p>To pre-fill a message, add <code>?text=</code> and the message with every space written as <code>%20</code>: <code>https://wa.me/60123456789?text=Hi%2C%20I%20want%20to%20order</code>. The older <code>api.whatsapp.com/send?phone=</code> form does the same thing.</p>'],
                ['Common mistakes', '<ul><li><strong>Keeping the leading zero.</strong> 0123456789 must become 60123456789.</li><li><strong>Adding the plus sign.</strong> wa.me/+60... does not work on every device.</li><li><strong>Unencoded message.</strong> A space, a comma or an ampersand that is not encoded cuts the message short.</li><li><strong>Posting it in an Instagram bio.</strong> Instagram only allows one link and some in-app browsers do not hand wa.me links to the WhatsApp app.</li></ul>'],
                ['A shorter link you can edit', '<p>WSAP wraps the same wa.me behaviour in a short {domain} address. We handle the encoding, generate a QR code, open WhatsApp correctly from Instagram and TikTok browsers, and count the clicks. If you sign up for free you can change the number or message any time, so the link you printed never goes stale.</p>'],
            ],
            'steps' => [
                ['Write the number in international format', 'Country code first, no plus sign, no leading zero, no spaces.'],
                ['Add the message', 'Use the generator below and we encode spaces and punctuation for you.'],
                ['Shorten it', 'Create the link to get a {domain} address and QR code, then share it anywhere.'],
            ],
            'faq' => [
                ['Is wa.me free to use?', 'Yes, wa.me is a free service from WhatsApp. WSAP is also free; it adds a short address, a QR code, click counting and editing.'],
                ['Can a wa.me link open a group?', 'No. wa.me opens a one-to-one chat with a number. Groups use a separate invite link from inside WhatsApp.'],
                ['Why does my wa.me link say the phone number is not on WhatsApp?', 'The number is wrong or not registered on WhatsApp. Check the country code and drop the leading zero.'],
                ['Does wa.me work on desktop?', 'Yes. On a computer it opens WhatsApp Web or the WhatsApp Desktop app, with the message already typed.'],
            ],
            'related' => $cta,
        ],
        'whatsapp-qr-code' => [
            'title' => 'WhatsApp QR Code Generator: Free QR That Opens a Chat with Your Message',
            'h1' => 'WhatsApp QR code generator',
            'lead' => 'Make a QR code that opens a WhatsApp chat with your number and a ready message. Free PNG download, works on any phone camera, scans counted separately from link clicks.',
            'sections' => [
                ['Where a WhatsApp QR code pays off', '<p>Anywhere a customer is standing near your business but not yet talking to you: the shop counter, a restaurant table, product packaging, a delivery box, a roadside banner, a car window, a trade show stand, a clinic reception. One scan and they are in a chat with your message already typed, for example "Hi, I would like to book a table".</p>'],
                ['Why not the QR code from inside WhatsApp?', '<p>WhatsApp Business has its own QR code, but it cannot carry a pre-filled message, it cannot be edited once printed, and it gives you no idea how many people scanned it. A WSAP QR code points at a short link, so you can change the number or message later, add a logo and colours with Premium, and see scans in your dashboard.</p>'],
                ['Print tips', '<ul><li>Download the PNG at full size and never stretch it.</li><li>Keep a white margin around the code and print it at least 2 cm wide for a counter, 10 cm or more for a banner.</li><li>Put a one-line call to action next to it, for example "Scan to order on WhatsApp".</li><li>Test the printed proof with two different phones before you order a thousand flyers.</li></ul>'],
            ],
            'steps' => [
                ['Enter the number and message', 'The message is what the customer will see typed in the chat after scanning.'],
                ['Create the link', 'The QR code appears next to your short link.'],
                ['Download and print', 'Click Download PNG. Premium links can style the QR with your logo and colours.'],
            ],
            'faq' => [
                ['Is the QR code free?', 'Yes. The black-and-white QR code is free and has no scan limit. Logo and colours are part of Premium.'],
                ['Does the QR code expire?', 'No. Free links never expire, so the QR code keeps working for as long as you want.'],
                ['Can I change the number after printing?', 'Yes, if the link belongs to a free account. Edit it in the dashboard and the printed QR code keeps working with the new number.'],
                ['Can I see how many people scanned it?', 'Yes. Scans are counted separately from link clicks on every link in your dashboard.'],
            ],
            'related' => $cta,
        ],
        'whatsapp-link-instagram-bio' => [
            'title' => 'WhatsApp Link for Instagram Bio, TikTok and Facebook: Open a Chat from Your Profile',
            'h1' => 'Put WhatsApp in your Instagram bio',
            'lead' => 'A short link in your Instagram, TikTok or Facebook profile that opens a WhatsApp chat with your message typed. Works inside the in-app browsers where plain wa.me links often fail.',
            'sections' => [
                ['The problem with wa.me in a bio', '<p>Instagram and TikTok open links in their own in-app browser. That browser does not always hand a wa.me link over to the WhatsApp app, so the visitor sees a blank page or a "download WhatsApp" screen even though WhatsApp is installed. Long wa.me links with encoded messages also look messy and get cut off.</p>'],
                ['How WSAP fixes it', '<p>A WSAP link detects the in-app browser and uses the right hand-off so WhatsApp opens with your number and message. It is short enough to read out loud, counts every tap so you know how many followers actually start a chat, and you can change the message per campaign without touching your bio.</p>'],
                ['Set it up on each platform', '<ul><li><strong>Instagram</strong>: Edit profile → Links → Add external link → paste your {domain} link. Add a line in the bio like "Order on WhatsApp ↓".</li><li><strong>TikTok</strong>: Edit profile → Website (available on business accounts and accounts over 1,000 followers) → paste the link.</li><li><strong>Facebook Page</strong>: Add a "Send WhatsApp message" button, or put the link in the About section and in posts.</li><li><strong>YouTube</strong>: Channel → Customise → Links.</li></ul><p>Use a different link per platform if you want to see which one brings the most chats.</p>'],
            ],
            'steps' => [
                ['Create your link', 'Enter your number and a message such as "Hi, I came from your Instagram".'],
                ['Copy the short link', 'Example: {domain}/x7k2pq. Save it to a free account so you can edit it later.'],
                ['Paste it in your profile', 'Instagram: Edit profile → Links. TikTok: Edit profile → Website.'],
            ],
            'faq' => [
                ['Instagram only allows one link. Can it go to WhatsApp?', 'Yes. Paste your WSAP link as the website link. It opens WhatsApp directly when tapped.'],
                ['Why does my wa.me link open a blank page in Instagram?', 'The Instagram in-app browser does not always pass wa.me to the WhatsApp app. WSAP handles that hand-off for you.'],
                ['Can I use one link for Instagram and another for TikTok?', 'Yes, create as many free links as you like from a free account and compare their click counts.'],
                ['Can the message mention the platform?', 'Yes. A message like "Hi, I saw your TikTok" tells you where the customer came from before they say a word.'],
            ],
            'related' => $cta,
        ],
        'whatsapp-business-link' => [
            'title' => 'WhatsApp Business Link with Pre-filled Message: Free Short Link and QR Code',
            'h1' => 'WhatsApp Business link',
            'lead' => 'Give customers one short link that opens a chat with your WhatsApp Business number and a ready message. Free to create, with a dashboard, click counts and a branded address when you need it.',
            'sections' => [
                ['WhatsApp Business vs personal number', '<p>Both work with a WSAP link. With WhatsApp Business the customer also sees your business name, catalogue and opening hours when the chat opens, and you can use quick replies and labels on your side. The link itself is identical: it points at a registered number.</p>'],
                ['What a small business gets from a link', '<ul><li><strong>Fewer lost customers.</strong> No typing numbers wrong or giving up.</li><li><strong>Better first messages.</strong> Pre-fill "Hi, I want to order" or "Hi, I would like to book" and the chat starts with intent.</li><li><strong>Know what works.</strong> One link per channel, click counts in the dashboard, so you know whether the flyer or the Facebook ad brings chats.</li><li><strong>A branded address.</strong> Premium gives you {domain}/yourbrand, which is easier to say on a video and looks right on packaging.</li><li><strong>Team routing.</strong> Smart Links send each tap to the right person by hours, country or source.</li></ul>'],
                ['Beyond a link: forms and a shop', '<p>When the chat always starts with the same questions, a WhatsApp form asks them first and sends the answers as one tidy message. When you sell products, a WhatsApp shop lets customers pick items into a cart and send the order to your WhatsApp. Both are built into WSAP.</p>'],
            ],
            'steps' => [
                ['Enter your WhatsApp Business number', 'Country code and number, exactly as registered.'],
                ['Add the message customers should start with', 'Short and specific beats long and generic.'],
                ['Share the link and QR everywhere', 'Bio, ads, packaging, counter, business card.'],
            ],
            'faq' => [
                ['Do I need the WhatsApp Business API?', 'No. A WSAP link works with the normal WhatsApp Business app and with personal WhatsApp.'],
                ['Can I route to several staff?', 'Yes, with a Smart Link, an add-on to a Premium link. Rules by business hours, country, source or a fair split between agents, up to 8 numbers per link.'],
                ['Can I get a branded link like {domain}/myshop?', 'Yes, that is a Premium link, priced per link per year. Generic words such as "bakery" are sold as premium handles.'],
                ['Is there a limit on free links?', 'A free account can create unlimited free links with unlimited clicks. Guests get one link per visit.'],
            ],
            'related' => $cta,
        ],
        'walink-alternative' => [
            'title' => 'Walink Alternative: WSAP, a WhatsApp Link Generator with Forms, Shop and Local Pricing',
            'h1' => 'Looking for a walink alternative?',
            'lead' => 'WSAP does what walink does, free short WhatsApp links with QR codes and branded premium links, and adds a free dashboard, order forms, a WhatsApp shop and team routing, with prices in RM for Malaysia and USD elsewhere.',
            'sections' => [
                ['What is the same', '<p>Both services give you a free short link that opens a WhatsApp chat with a pre-filled message, a QR code, and a paid branded address with analytics. If you only need a simple link, either will do the job.</p>'],
                ['What WSAP adds', '<ul><li><strong>Free account with real control.</strong> Every link in one dashboard with click counts, editing of the number or message, pause and delete. No payment needed.</li><li><strong>Free links never expire</strong> and have no click limit.</li><li><strong>Local pricing and payment.</strong> wsap.my shows RM and takes FPX and cards through Billplz; wsap.me shows USD. One account works on both.</li><li><strong>Bahasa Melayu</strong> interface and support alongside English.</li><li><strong>Forms, Shop and Smart Links</strong> built in: booking and order forms, a product catalogue with cart, and routing to up to 8 numbers per link.</li><li><strong>Monthly email report</strong> so you see what every link did without logging in.</li></ul>'],
                ['How to switch', '<p>Create the same links on WSAP, update your bios and ads, and reprint QR codes when you next print anything. Links on the old service keep working until you stop paying there. If you need the same branded word on WSAP and it is free, grab it; generic business words are sold as premium handles.</p>'],
            ],
            'steps' => [
                ['Create a free account', 'Google or email, thirty seconds.'],
                ['Recreate your links', 'Same number, same message. Save each one; the dashboard keeps them together.'],
                ['Update your profiles and ads', 'Replace the old links. Download the new QR codes for print.'],
            ],
            'faq' => [
                ['Is WSAP cheaper than walink?', 'Prices are per link per year and shown on the pricing page in RM or USD. Free links and the free account cost nothing. Compare the current prices on both sites before you decide.'],
                ['Can I keep my old branded link?', 'Your old branded link belongs to the old service. On WSAP you choose a new branded address under {domain}.'],
                ['Is WSAP connected to WhatsApp or walink?', 'No. WSAP is an independent service and is not associated with or sponsored by WhatsApp Inc. or walink.'],
                ['Does WSAP work outside Malaysia?', 'Yes. wsap.me is the international site in English with USD prices. Links work with any country code.'],
            ],
            'related' => $cta,
        ],
        'cara-buat-link-whatsapp' => [
            'title' => 'How to Create a WhatsApp Link in Malaysia (Free, with QR Code and RM Pricing)',
            'h1' => 'How to create a WhatsApp link in Malaysia',
            'lead' => 'The fastest way to make a WhatsApp link for a Malaysian number: add 60, drop the leading zero, and let WSAP generate a short link and QR code. Free, with Bahasa Melayu support and RM pricing on wsap.my.',
            'sections' => [
                ['Malaysian numbers in a WhatsApp link', '<p>A Malaysian mobile number 012-345 6789 becomes <code>60123456789</code> in a WhatsApp link: country code 60, then the number without the first zero. The generator below does this for you when you pick Malaysia as the country.</p>'],
                ['Why local businesses use WSAP', '<ul><li>Short links on wsap.my or wsap.me, free, never expire.</li><li>Interface in Bahasa Melayu or English.</li><li>Premium links, forms and shops paid in RM through FPX or card.</li><li>QR codes for kedai, warung, klinik, homestay, tuition, workshop.</li></ul>'],
                ['Ideas that work in Malaysia', '<p>A QR code on the tapau counter with "Hi, nak order", a link in a Shopee or Lazada package insert for after-sales, a homestay booking form link in the Airbnb description, a clinic appointment link on the Google Business Profile, a tuition centre enquiry link on the school gate banner.</p>'],
            ],
            'steps' => [
                ['Choose Malaysia and type the number', 'Country code 60 is added automatically. Drop the leading zero.'],
                ['Write the first message', 'Example: "Hi, saya nak tanya pasal promo".'],
                ['Create and share', 'Copy the link or download the QR code. Sign up free to keep it in your dashboard.'],
            ],
            'faq' => [
                ['Is wsap.my the same as wsap.me?', 'Yes, one service and one account. wsap.my defaults to Bahasa Melayu and RM prices, wsap.me to English and USD. A link made on one works on both.'],
                ['How do I pay in Malaysia?', 'Premium is paid through Billplz with FPX online banking or a card. Prices are shown in RM on wsap.my.'],
                ['Can I use a Singapore or Indonesian number?', 'Yes. Any country code works. Pick the country from the list.'],
                ['Is there Bahasa Melayu support?', 'Yes. The whole site, the forms and the emails are available in Bahasa Melayu.'],
            ],
            'related' => $cta,
        ],
    ];
}

function guides_ms(): array
{
    $cta = [['Penjana link WhatsApp percuma', '/whatsapp-link-generator'], ['Kod QR WhatsApp', '/whatsapp-qr-code'], ['Link Premium berjenama', '/premium-links'], ['Borang pesanan WhatsApp', '/whatsapp-forms'], ['Kedai WhatsApp', '/whatsapp-shop']];
    return [
        'whatsapp-link-generator' => [
            'title' => 'Penjana Link WhatsApp Percuma dengan Mesej Siap Taip & Kod QR',
            'h1' => 'Penjana link WhatsApp percuma',
            'lead' => 'Tukar mana-mana nombor telefon kepada link pendek {domain} yang terus buka chat WhatsApp dengan mesej siap ditaip. Percuma, tak perlu daftar, tak luput. Kod QR disertakan.',
            'sections' => [
                ['Apa yang link WhatsApp buat', '<p>Link WhatsApp (juga dipanggil link click-to-chat) membuka perbualan dengan nombor tertentu sebaik sahaja seseorang mengetiknya. Pelanggan tak perlu simpan nombor anda, taip nombor, atau cari anda. Jika anda tambah mesej, ia sudah ada dalam kotak teks, jadi mereka hanya tekan Hantar.</p><p>WSAP beri anda alamat pendek seperti <code>{domain}/x7k2pq</code> dan bukannya format rasmi yang panjang, serta kod QR untuk cetakan, dan ia berfungsi di iPhone, Android, WhatsApp Web dan WhatsApp Desktop.</p>'],
                ['Kenapa guna link pendek dan bukan wa.me', '<p>Format rasmi <code>wa.me</code> berfungsi, tetapi sebaik anda tambah mesej siap taip ia menjadi rentetan panjang kod <code>%20</code> yang rosak dalam bio dan nampak mencurigakan di atas risalah. Link pendek WSAP kekal bersih, muat di mana-mana, dan jika dibuat dari akaun percuma anda boleh tukar nombor atau mesej kemudian tanpa cetak semula.</p><ul><li><strong>Pendek dan mudah dibaca</strong>, muat dalam bio Instagram, profil TikTok, kad nama.</li><li><strong>Boleh diedit dari dashboard</strong> bila anda daftar percuma. QR yang sudah dicetak terus berfungsi.</li><li><strong>Kiraan klik</strong> pada setiap link, jadi anda tahu risalah atau iklan mana yang betul-betul bawa chat.</li><li><strong>Buka WhatsApp dengan betul dari browser dalam app</strong> seperti Instagram dan TikTok, di mana link wa.me biasa selalu gagal.</li></ul>'],
                ['Di mana perniagaan menggunakannya', '<p>Bio Instagram dan TikTok, iklan Facebook dengan butang WhatsApp, Google Business Profile, tandatangan emel, menu dan bungkusan bercetak, tingkap kedai, banner acara dan pelekat kereta. Letak kod QR di mana sahaja kamera telefon boleh nampak.</p>'],
            ],
            'steps' => [
                ['Masukkan nombor WhatsApp anda', 'Pilih kod negara dan taip nombor. Guna nombor yang berdaftar di WhatsApp, peribadi atau Business.'],
                ['Tulis mesej pertama (pilihan)', 'Contoh "Hi, saya nampak iklan promo hujung minggu". Pelanggan boleh edit sebelum hantar.'],
                ['Buat link', 'Anda terus dapat link pendek {domain} dan kod QR. Salin, muat turun PNG, atau cuba.'],
                ['Simpan ke akaun percuma', 'Daftar untuk simpan semua link dalam dashboard, lihat klik, dan edit nombor atau mesej kemudian.'],
            ],
            'faq' => [
                ['Betul ke penjana link WhatsApp ini percuma?', 'Ya. Link percuma tiada had klik dan tak luput. Akaun percuma tambah dashboard, kiraan klik dan edit, juga percuma. Premium hanya untuk alamat berjenama seperti {domain}/jenamaanda dan analitik penuh.'],
                ['Boleh guna dengan WhatsApp Business?', 'Ya. Mana-mana nombor yang berdaftar di WhatsApp atau WhatsApp Business boleh. Pelanggan nampak profil perniagaan anda bila chat dibuka.'],
                ['Boleh tukar nombor kemudian?', 'Jika link dibuat dari akaun percuma, ya, bila-bila masa dari dashboard. Link tetamu tak boleh diedit, sebab itu kami cadangkan daftar sebelum cetak kod QR.'],
                ['Apa jadi bila seseorang klik?', 'Di telefon, WhatsApp terbuka dengan nombor dan mesej anda. Di komputer, WhatsApp Web atau Desktop terbuka. Kami kira klik dan hantar pelawat dalam sekelip mata.'],
                ['Anda nampak chat saya?', 'Tidak. Link hanya membuka WhatsApp. Semua selepas itu berlaku dalam WhatsApp, antara anda dan pelanggan.'],
            ],
            'related' => $cta,
        ],
        'wa-me-link' => [
            'title' => 'Link wa.me: Cara Buat Link WhatsApp dengan Mesej (dan Alternatif Lebih Pendek)',
            'h1' => 'Link wa.me, dijelaskan',
            'lead' => 'wa.me ialah domain click-to-chat rasmi WhatsApp. Ini format tepatnya, cara tambah mesej siap taip, kesilapan yang merosakkannya, dan cara dapat link lebih pendek yang boleh diedit.',
            'sections' => [
                ['Format wa.me', '<p>Link ialah <code>https://wa.me/</code> diikuti nombor penuh dalam format antarabangsa tanpa tanda tambah, ruang, sengkang atau kurungan. Nombor Malaysia 012-345 6789 menjadi <code>https://wa.me/60123456789</code>. Nombor Singapura 9123 4567 menjadi <code>https://wa.me/6591234567</code>.</p><p>Untuk mesej siap taip, tambah <code>?text=</code> dan mesej dengan setiap ruang ditulis sebagai <code>%20</code>: <code>https://wa.me/60123456789?text=Hi%2C%20saya%20nak%20order</code>. Bentuk lama <code>api.whatsapp.com/send?phone=</code> buat perkara yang sama.</p>'],
                ['Kesilapan biasa', '<ul><li><strong>Kekalkan sifar di depan.</strong> 0123456789 mesti jadi 60123456789.</li><li><strong>Tambah tanda tambah.</strong> wa.me/+60... tak berfungsi di semua peranti.</li><li><strong>Mesej tak dikodkan.</strong> Ruang, koma atau ampersand yang tak dikodkan memotong mesej.</li><li><strong>Letak dalam bio Instagram.</strong> Instagram hanya benarkan satu link dan sesetengah browser dalam app tidak menyerahkan link wa.me kepada app WhatsApp.</li></ul>'],
                ['Link lebih pendek yang boleh diedit', '<p>WSAP membalut kelakuan wa.me yang sama dalam alamat pendek {domain}. Kami uruskan pengekodan, jana kod QR, buka WhatsApp dengan betul dari browser Instagram dan TikTok, dan kira klik. Jika anda daftar percuma anda boleh tukar nombor atau mesej bila-bila masa, jadi link yang anda cetak tak pernah basi.</p>'],
            ],
            'steps' => [
                ['Tulis nombor dalam format antarabangsa', 'Kod negara dahulu, tiada tanda tambah, tiada sifar di depan, tiada ruang.'],
                ['Tambah mesej', 'Guna penjana di bawah dan kami kodkan ruang dan tanda baca untuk anda.'],
                ['Pendekkan', 'Buat link untuk dapat alamat {domain} dan kod QR, kemudian kongsi di mana-mana.'],
            ],
            'faq' => [
                ['wa.me percuma?', 'Ya, wa.me ialah perkhidmatan percuma dari WhatsApp. WSAP juga percuma; ia tambah alamat pendek, kod QR, kiraan klik dan edit.'],
                ['Boleh link wa.me buka group?', 'Tidak. wa.me membuka chat satu-ke-satu dengan nombor. Group guna link jemputan berasingan dari dalam WhatsApp.'],
                ['Kenapa link wa.me saya kata nombor tiada di WhatsApp?', 'Nombor salah atau tak berdaftar di WhatsApp. Semak kod negara dan buang sifar di depan.'],
                ['wa.me berfungsi di desktop?', 'Ya. Di komputer ia membuka WhatsApp Web atau app WhatsApp Desktop, dengan mesej siap ditaip.'],
            ],
            'related' => $cta,
        ],
        'whatsapp-qr-code' => [
            'title' => 'Penjana Kod QR WhatsApp: QR Percuma yang Buka Chat dengan Mesej Anda',
            'h1' => 'Penjana kod QR WhatsApp',
            'lead' => 'Buat kod QR yang membuka chat WhatsApp dengan nombor anda dan mesej sedia. Muat turun PNG percuma, berfungsi dengan mana-mana kamera telefon, imbasan dikira berasingan dari klik link.',
            'sections' => [
                ['Di mana kod QR WhatsApp berbaloi', '<p>Di mana sahaja pelanggan berdiri dekat dengan perniagaan anda tetapi belum berbual dengan anda: kaunter kedai, meja restoran, bungkusan produk, kotak penghantaran, banner tepi jalan, tingkap kereta, gerai pameran, kaunter klinik. Satu imbasan dan mereka sudah dalam chat dengan mesej siap taip, contohnya "Hi, saya nak tempah meja".</p>'],
                ['Kenapa bukan kod QR dari dalam WhatsApp?', '<p>WhatsApp Business ada kod QR sendiri, tetapi ia tak boleh bawa mesej siap taip, tak boleh diedit selepas dicetak, dan anda tak tahu berapa ramai yang mengimbasnya. Kod QR WSAP menunjuk ke link pendek, jadi anda boleh tukar nombor atau mesej kemudian, tambah logo dan warna dengan Premium, dan lihat imbasan dalam dashboard.</p>'],
                ['Tips cetakan', '<ul><li>Muat turun PNG saiz penuh dan jangan regangkan.</li><li>Kekalkan margin putih sekeliling kod dan cetak sekurang-kurangnya 2 cm lebar untuk kaunter, 10 cm atau lebih untuk banner.</li><li>Letak seruan satu baris di sebelahnya, contoh "Imbas untuk order di WhatsApp".</li><li>Uji cetakan contoh dengan dua telefon berbeza sebelum tempah seribu risalah.</li></ul>'],
            ],
            'steps' => [
                ['Masukkan nombor dan mesej', 'Mesej ialah apa yang pelanggan nampak siap ditaip dalam chat selepas imbas.'],
                ['Buat link', 'Kod QR muncul di sebelah link pendek anda.'],
                ['Muat turun dan cetak', 'Klik Muat turun PNG. Link Premium boleh gayakan QR dengan logo dan warna anda.'],
            ],
            'faq' => [
                ['Kod QR percuma?', 'Ya. Kod QR hitam putih percuma dan tiada had imbasan. Logo dan warna sebahagian dari Premium.'],
                ['Kod QR luput?', 'Tidak. Link percuma tak luput, jadi kod QR terus berfungsi selama mana anda mahu.'],
                ['Boleh tukar nombor selepas cetak?', 'Ya, jika link milik akaun percuma. Edit dalam dashboard dan kod QR yang dicetak terus berfungsi dengan nombor baru.'],
                ['Boleh nampak berapa ramai imbas?', 'Ya. Imbasan dikira berasingan dari klik link pada setiap link dalam dashboard anda.'],
            ],
            'related' => $cta,
        ],
        'whatsapp-link-instagram-bio' => [
            'title' => 'Link WhatsApp untuk Bio Instagram, TikTok dan Facebook: Buka Chat dari Profil Anda',
            'h1' => 'Letak WhatsApp dalam bio Instagram anda',
            'lead' => 'Link pendek dalam profil Instagram, TikTok atau Facebook yang membuka chat WhatsApp dengan mesej siap taip. Berfungsi dalam browser dalam app di mana link wa.me biasa selalu gagal.',
            'sections' => [
                ['Masalah wa.me dalam bio', '<p>Instagram dan TikTok membuka link dalam browser dalam app mereka sendiri. Browser itu tidak selalu menyerahkan link wa.me kepada app WhatsApp, jadi pelawat nampak halaman kosong atau skrin "muat turun WhatsApp" walaupun WhatsApp sudah dipasang. Link wa.me panjang dengan mesej berkod juga nampak bersepah dan terpotong.</p>'],
                ['Cara WSAP betulkannya', '<p>Link WSAP mengesan browser dalam app dan guna penyerahan yang betul supaya WhatsApp terbuka dengan nombor dan mesej anda. Ia cukup pendek untuk disebut, kira setiap ketikan supaya anda tahu berapa ramai pengikut betul-betul mula chat, dan anda boleh tukar mesej setiap kempen tanpa sentuh bio.</p>'],
                ['Tetapkan di setiap platform', '<ul><li><strong>Instagram</strong>: Edit profile → Links → Add external link → tampal link {domain} anda. Tambah baris dalam bio seperti "Order di WhatsApp ↓".</li><li><strong>TikTok</strong>: Edit profile → Website (untuk akaun perniagaan dan akaun melebihi 1,000 pengikut) → tampal link.</li><li><strong>Facebook Page</strong>: Tambah butang "Send WhatsApp message", atau letak link dalam bahagian About dan dalam post.</li><li><strong>YouTube</strong>: Channel → Customise → Links.</li></ul><p>Guna link berbeza setiap platform jika anda mahu tahu yang mana bawa paling banyak chat.</p>'],
            ],
            'steps' => [
                ['Buat link anda', 'Masukkan nombor dan mesej seperti "Hi, saya datang dari Instagram anda".'],
                ['Salin link pendek', 'Contoh: {domain}/x7k2pq. Simpan ke akaun percuma supaya boleh edit kemudian.'],
                ['Tampal dalam profil anda', 'Instagram: Edit profile → Links. TikTok: Edit profile → Website.'],
            ],
            'faq' => [
                ['Instagram hanya benarkan satu link. Boleh ke WhatsApp?', 'Ya. Tampal link WSAP anda sebagai link laman web. Ia terus membuka WhatsApp bila diketik.'],
                ['Kenapa link wa.me saya buka halaman kosong dalam Instagram?', 'Browser dalam app Instagram tidak selalu menyerahkan wa.me kepada app WhatsApp. WSAP uruskan penyerahan itu untuk anda.'],
                ['Boleh guna satu link untuk Instagram dan satu lagi untuk TikTok?', 'Ya, buat seberapa banyak link percuma dari akaun percuma dan bandingkan kiraan klik.'],
                ['Boleh mesej sebut platform?', 'Ya. Mesej seperti "Hi, saya nampak TikTok anda" beritahu dari mana pelanggan datang sebelum mereka cakap apa-apa.'],
            ],
            'related' => $cta,
        ],
        'whatsapp-business-link' => [
            'title' => 'Link WhatsApp Business dengan Mesej Siap Taip: Link Pendek dan Kod QR Percuma',
            'h1' => 'Link WhatsApp Business',
            'lead' => 'Beri pelanggan satu link pendek yang membuka chat dengan nombor WhatsApp Business anda dan mesej sedia. Percuma dibuat, dengan dashboard, kiraan klik dan alamat berjenama bila anda perlukan.',
            'sections' => [
                ['WhatsApp Business vs nombor peribadi', '<p>Kedua-duanya berfungsi dengan link WSAP. Dengan WhatsApp Business pelanggan juga nampak nama perniagaan, katalog dan waktu buka anda bila chat dibuka, dan anda boleh guna balasan pantas dan label di pihak anda. Link itu sendiri sama: ia menunjuk ke nombor berdaftar.</p>'],
                ['Apa perniagaan kecil dapat dari link', '<ul><li><strong>Kurang pelanggan hilang.</strong> Tiada salah taip nombor atau putus asa.</li><li><strong>Mesej pertama lebih baik.</strong> Siap taip "Hi, saya nak order" atau "Hi, saya nak tempah" dan chat bermula dengan niat.</li><li><strong>Tahu apa yang berkesan.</strong> Satu link setiap saluran, kiraan klik dalam dashboard, jadi anda tahu sama ada risalah atau iklan Facebook yang bawa chat.</li><li><strong>Alamat berjenama.</strong> Premium beri anda {domain}/jenamaanda, lebih mudah disebut dalam video dan nampak kena di bungkusan.</li><li><strong>Halaan pasukan.</strong> Smart Links hantar setiap ketikan kepada orang yang betul mengikut waktu, negara atau sumber.</li></ul>'],
                ['Lebih dari link: borang dan kedai', '<p>Bila chat selalu bermula dengan soalan yang sama, borang WhatsApp tanya dahulu dan hantar jawapan sebagai satu mesej kemas. Bila anda jual produk, kedai WhatsApp biar pelanggan pilih item ke troli dan hantar pesanan ke WhatsApp anda. Kedua-duanya terbina dalam WSAP.</p>'],
            ],
            'steps' => [
                ['Masukkan nombor WhatsApp Business anda', 'Kod negara dan nombor, tepat seperti berdaftar.'],
                ['Tambah mesej yang pelanggan patut mula dengan', 'Pendek dan spesifik lebih baik dari panjang dan umum.'],
                ['Kongsi link dan QR di mana-mana', 'Bio, iklan, bungkusan, kaunter, kad nama.'],
            ],
            'faq' => [
                ['Perlu WhatsApp Business API?', 'Tidak. Link WSAP berfungsi dengan app WhatsApp Business biasa dan WhatsApp peribadi.'],
                ['Boleh hala ke beberapa staf?', 'Ya, dengan Smart Link, tambahan kepada link Premium. Peraturan mengikut waktu perniagaan, negara, sumber atau bahagian adil antara ejen, sehingga 8 nombor setiap link.'],
                ['Boleh dapat link berjenama seperti {domain}/kedaisaya?', 'Ya, itu link Premium, berharga setiap link setahun. Perkataan generik seperti "bakery" dijual sebagai handle premium.'],
                ['Ada had link percuma?', 'Akaun percuma boleh buat link percuma tanpa had dengan klik tanpa had. Tetamu dapat satu link setiap lawatan.'],
            ],
            'related' => $cta,
        ],
        'walink-alternative' => [
            'title' => 'Alternatif Walink: WSAP, Penjana Link WhatsApp dengan Borang, Kedai dan Harga RM',
            'h1' => 'Cari alternatif walink?',
            'lead' => 'WSAP buat apa yang walink buat, link WhatsApp pendek percuma dengan kod QR dan link premium berjenama, dan tambah dashboard percuma, borang pesanan, kedai WhatsApp dan halaan pasukan, dengan harga RM untuk Malaysia dan USD di tempat lain.',
            'sections' => [
                ['Apa yang sama', '<p>Kedua-dua perkhidmatan beri anda link pendek percuma yang membuka chat WhatsApp dengan mesej siap taip, kod QR, dan alamat berjenama berbayar dengan analitik. Jika anda hanya perlukan link mudah, mana-mana pun boleh.</p>'],
                ['Apa yang WSAP tambah', '<ul><li><strong>Akaun percuma dengan kawalan sebenar.</strong> Setiap link dalam satu dashboard dengan kiraan klik, edit nombor atau mesej, jeda dan padam. Tak perlu bayar.</li><li><strong>Link percuma tak luput</strong> dan tiada had klik.</li><li><strong>Harga dan bayaran tempatan.</strong> wsap.my tunjuk RM dan terima FPX dan kad melalui Billplz; wsap.me tunjuk USD. Satu akaun untuk kedua-duanya.</li><li><strong>Antara muka Bahasa Melayu</strong> dan sokongan bersama Bahasa Inggeris.</li><li><strong>Borang, Kedai dan Smart Links</strong> terbina: borang tempahan dan pesanan, katalog produk dengan troli, dan halaan ke sehingga 8 nombor setiap link.</li><li><strong>Laporan emel bulanan</strong> supaya anda nampak prestasi setiap link tanpa log masuk.</li></ul>'],
                ['Cara bertukar', '<p>Buat link yang sama di WSAP, kemas kini bio dan iklan anda, dan cetak semula kod QR bila anda cetak apa-apa seterusnya. Link di perkhidmatan lama terus berfungsi sehingga anda berhenti bayar di sana. Jika anda perlukan perkataan berjenama yang sama di WSAP dan ia masih kosong, ambil; perkataan perniagaan generik dijual sebagai handle premium.</p>'],
            ],
            'steps' => [
                ['Buat akaun percuma', 'Google atau emel, tiga puluh saat.'],
                ['Buat semula link anda', 'Nombor sama, mesej sama. Simpan setiap satu; dashboard simpan semuanya.'],
                ['Kemas kini profil dan iklan', 'Ganti link lama. Muat turun kod QR baru untuk cetakan.'],
            ],
            'faq' => [
                ['WSAP lebih murah dari walink?', 'Harga ialah setiap link setahun dan dipaparkan di halaman harga dalam RM atau USD. Link percuma dan akaun percuma tiada kos. Bandingkan harga semasa di kedua-dua laman sebelum buat keputusan.'],
                ['Boleh kekalkan link berjenama lama?', 'Link berjenama lama anda milik perkhidmatan lama. Di WSAP anda pilih alamat berjenama baru di bawah {domain}.'],
                ['WSAP berkaitan dengan WhatsApp atau walink?', 'Tidak. WSAP ialah perkhidmatan bebas dan tidak berkaitan dengan atau ditaja oleh WhatsApp Inc. atau walink.'],
                ['WSAP berfungsi di luar Malaysia?', 'Ya. wsap.me ialah laman antarabangsa dalam Bahasa Inggeris dengan harga USD. Link berfungsi dengan mana-mana kod negara.'],
            ],
            'related' => $cta,
        ],
        'cara-buat-link-whatsapp' => [
            'title' => 'Cara Buat Link WhatsApp (Percuma, dengan Kod QR dan Harga RM)',
            'h1' => 'Cara buat link WhatsApp',
            'lead' => 'Cara paling pantas buat link WhatsApp untuk nombor Malaysia: tambah 60, buang sifar di depan, dan biar WSAP jana link pendek dan kod QR. Percuma, dalam Bahasa Melayu, harga RM di wsap.my.',
            'sections' => [
                ['Nombor Malaysia dalam link WhatsApp', '<p>Nombor telefon Malaysia 012-345 6789 menjadi <code>60123456789</code> dalam link WhatsApp: kod negara 60, kemudian nombor tanpa sifar pertama. Penjana di bawah buat ini untuk anda bila anda pilih Malaysia sebagai negara.</p>'],
                ['Kenapa perniagaan tempatan guna WSAP', '<ul><li>Link pendek di wsap.my atau wsap.me, percuma, tak luput.</li><li>Antara muka dalam Bahasa Melayu atau Inggeris.</li><li>Link Premium, borang dan kedai dibayar dalam RM melalui FPX atau kad.</li><li>Kod QR untuk kedai, warung, klinik, homestay, tuisyen, bengkel.</li></ul>'],
                ['Idea yang berkesan di Malaysia', '<p>Kod QR di kaunter tapau dengan "Hi, nak order", link dalam sisipan bungkusan Shopee atau Lazada untuk selepas jualan, link borang tempahan homestay dalam penerangan Airbnb, link temujanji klinik di Google Business Profile, link pertanyaan pusat tuisyen di banner pagar sekolah.</p>'],
            ],
            'steps' => [
                ['Pilih Malaysia dan taip nombor', 'Kod negara 60 ditambah automatik. Buang sifar di depan.'],
                ['Tulis mesej pertama', 'Contoh: "Hi, saya nak tanya pasal promo".'],
                ['Buat dan kongsi', 'Salin link atau muat turun kod QR. Daftar percuma untuk simpan dalam dashboard anda.'],
            ],
            'faq' => [
                ['wsap.my sama dengan wsap.me?', 'Ya, satu perkhidmatan dan satu akaun. wsap.my lalai ke Bahasa Melayu dan harga RM, wsap.me ke Bahasa Inggeris dan USD. Link yang dibuat di satu berfungsi di kedua-duanya.'],
                ['Macam mana bayar di Malaysia?', 'Premium dibayar melalui Billplz dengan FPX perbankan online atau kad. Harga dipaparkan dalam RM di wsap.my.'],
                ['Boleh guna nombor Singapura atau Indonesia?', 'Ya. Mana-mana kod negara boleh. Pilih negara dari senarai.'],
                ['Ada sokongan Bahasa Melayu?', 'Ya. Seluruh laman, borang dan emel tersedia dalam Bahasa Melayu.'],
            ],
            'related' => $cta,
        ],
    ];
}
