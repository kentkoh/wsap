<?php
/**
 * Local-dev seed (SQLite): admin user, demo bakery shop with categories/products/orders, demo forms.
 *   php -c dev/php.ini dev/seed_dev.php
 * Mirrors db/seed_demo_shop.sql + db/seed_demo_forms.sql (which are MySQL-only).
 */
$_SERVER['HTTP_HOST'] = 'localhost:8080';
require dirname(__DIR__) . '/lib/bootstrap.php';
require dirname(__DIR__) . '/lib/shops.php';

if (!db_value('SELECT COUNT(*) FROM users')) {
    db_exec('INSERT INTO users (email, name, password_hash, role, plan, plan_until, verified_at, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
        ['kent@example.com', 'Kent Koh', password_hash('password123', PASSWORD_DEFAULT), 'admin', 'business', now(86400 * 365), now(), now()]);
}
$uid = (int) db_value('SELECT id FROM users ORDER BY id LIMIT 1');

if (!db_value('SELECT 1 FROM shops WHERE slug = ?', ['bakery'])) {
    $img = fn(string $id, int $w = 800) => "https://images.unsplash.com/$id?w=$w&q=80&auto=format&fit=crop";
    db_exec('INSERT INTO shops (user_id, slug, domain, name, tagline, about, logo, cover, accent, instagram, facebook, tiktok, hours_text, phone, currency, fields, shipping, coupons, payment_note, is_open, active, demo, premium_until, views, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 1, 1, ?, 412, ?, ?)', [
        $uid, 'bakery', 'wsap.me', 'The Golden Bakery', 'Artisan breads and pastries baked fresh every day. Order via WhatsApp!',
        'Small-batch sourdough, French pastries and good coffee from our bakery in Kuantan. Order before 10am for same-day pickup.',
        $img('photo-1509440159596-0249088772ff', 400), $img('photo-1517433670267-08bbd4be890f', 1600), '#b45309',
        'https://instagram.com/wsap.me', 'https://facebook.com/wsap.me', '', 'Mon–Sat 8am–6pm', '60145866666', 'MYR',
        '{"name":"req","phone":"req","address":"on","city":"off","notes":"on"}',
        '[{"name":"Store pickup","price":0,"note":"Ready in 30 min"},{"name":"Delivery (Kuantan)","price":500,"note":"Same day, order before 2pm"}]',
        '[{"code":"WELCOME10","type":"percent","value":10},{"code":"RAYA5","type":"fixed","value":500}]',
        "Pay by bank transfer, Touch 'n Go or cash on pickup. We confirm your order in WhatsApp.", now(86400 * 365), now(), now(),
    ]);
    $sid = (int) db()->lastInsertId();
    $cats = [];
    foreach ([['Breads', 'Freshly baked every morning', 'photo-1549931319-a545dcf3bc73'], ['Pastries', 'Sweet treats for any occasion', 'photo-1555507036-ab1f4038808a'], ['Drinks', 'Hot and cold beverages', 'photo-1461023058943-07fcbe16d735']] as $i => [$n, $d, $im]) {
        db_exec('INSERT INTO categories (shop_id, name, description, image, sort, created_at) VALUES (?, ?, ?, ?, ?, ?)', [$sid, $n, $d, $img($im, 600), $i, now()]);
        $cats[$n] = (int) db()->lastInsertId();
    }
    $products = [
        ['Sourdough Loaf', 'Naturally leavened, 24-hour fermented. Crackly crust, open crumb.', 1250, 'photo-1585478259715-876acc5be8eb', 'Breads', '[]'],
        ['French Baguette', 'Crisp, golden and baked twice a day.', 650, 'photo-1597079910443-60c43fc4f729', 'Breads', '[]'],
        ['Ciabatta', 'Light, airy Italian bread. Perfect for sandwiches.', 750, 'photo-1586444248902-2f64eddc13df', 'Breads', '[]'],
        ['Butter Croissant', 'Flaky, buttery layers of perfection.', 550, 'photo-1555507036-ab1f4038808a', 'Pastries', '[{"name":"Filling","multi":false,"options":[{"label":"Plain","extra":0},{"label":"Chocolate","extra":150},{"label":"Almond cream","extra":200}]}]'],
        ['Cinnamon Roll', 'Soft, gooey and glazed while warm.', 650, 'photo-1509365465985-25d11c17e812', 'Pastries', '[]'],
        ['Blueberry Muffin', 'Bursting with fresh blueberries.', 600, 'photo-1607958996333-41aef7caefaa', 'Pastries', '[{"name":"Size","multi":false,"options":[{"label":"Regular","extra":0},{"label":"Jumbo","extra":250}]},{"name":"Extras","multi":true,"options":[{"label":"Streusel top","extra":100},{"label":"Extra berries","extra":150}]}]'],
        ['Caffè Latte', 'Double shot with silky steamed milk.', 900, 'photo-1461023058943-07fcbe16d735', 'Drinks', '[{"name":"Size","multi":false,"options":[{"label":"Regular","extra":0},{"label":"Large","extra":200}]},{"name":"Milk","multi":false,"options":[{"label":"Fresh milk","extra":0},{"label":"Oat milk","extra":150}]}]'],
        ['Fresh Orange Juice', 'Squeezed to order. No sugar added.', 850, 'photo-1600271886742-f049cd451bba', 'Drinks', '[]'],
    ];
    foreach ($products as $i => [$n, $d, $p, $im, $c, $v]) {
        db_exec('INSERT INTO products (shop_id, name, description, price, image, category, category_id, variants, active, sort, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?)', [$sid, $n, $d, $p, $img($im), '', $cats[$c], $v, $i, now()]);
    }
    $orders = [
        ['Marta Ruiz', '0123456789', '12 Jalan Teluk Cempedak', 'Less sugar please', [['Butter Croissant', 'Filling: Almond cream', 2, 750, 1500], ['Caffè Latte', 'Size: Large · Milk: Oat milk', 1, 1250, 1250]], 'Delivery (Kuantan)', 500, 'WELCOME10', 275, 'pending', 0],
        ['John Carter', '0198765432', '', '', [['Sourdough Loaf', '', 1, 1250, 1250]], 'Store pickup', 0, '', 0, 'completed', 1],
        ['Ana López', '0167778888', '5 Lorong Indah 3', '', [['Blueberry Muffin', 'Size: Jumbo · Extras: Extra berries', 4, 1000, 4000], ['Fresh Orange Juice', '', 2, 850, 1700]], 'Delivery (Kuantan)', 500, '', 0, 'completed', 2],
        ['Leo Martínez', '0112223333', '', 'Call when ready', [['Cinnamon Roll', '', 6, 650, 3900]], 'Store pickup', 0, 'RAYA5', 500, 'confirmed', 3],
        ['Paula Díaz', '0134445555', '', '', [['Ciabatta', '', 2, 750, 1500], ['Caffè Latte', 'Size: Regular · Milk: Fresh milk', 2, 900, 1800]], 'Store pickup', 0, '', 0, 'pending', 5],
        ['Marta Ruiz', '0123456789', '12 Jalan Teluk Cempedak', '', [['French Baguette', '', 3, 650, 1950]], 'Delivery (Kuantan)', 500, '', 0, 'completed', 9],
    ];
    foreach ($orders as [$name, $phone, $addr, $notes, $items, $shipName, $ship, $coupon, $disc, $status, $daysAgo]) {
        $lines = array_map(fn($it) => ['name' => $it[0], 'options' => $it[1], 'qty' => $it[2], 'unit' => $it[3], 'total' => $it[4]], $items);
        $sub = array_sum(array_column($lines, 'total'));
        db_exec('INSERT INTO orders (shop_id, number, customer, items, subtotal, shipping_name, shipping, coupon, discount, total, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [$sid, order_number(), json_encode(['name' => $name, 'phone' => $phone, 'address' => $addr, 'notes' => $notes]), json_encode($lines), $sub, $shipName, $ship, $coupon, $disc, $sub + $ship - $disc, $status, now(-$daysAgo * 86400 - 3600)]);
    }
    echo "bakery seeded (shop #$sid)\n";
}

$forms = [
    ['demo-clinic', 'Book an appointment · Klinik Dr. Aina', 'Hi Klinik Dr. Aina, I would like to book an appointment:', '[{"id":"qname","type":"short","label":"Patient name","required":true,"options":[],"help":"","max":0},{"id":"qservice","type":"dropdown","label":"Service","required":true,"options":["General consultation","Dental check-up","Vaccination"],"help":"","max":0},{"id":"qdate","type":"date","label":"Preferred date","required":true,"options":[],"help":"","max":0},{"id":"qtime","type":"single","label":"Preferred time","required":true,"options":["Morning (9–12)","Afternoon (2–5)"],"help":"","max":0}]'],
    ['demo-homestay', 'Tempah Homestay Seri Damai', 'Hai Seri Damai! Saya nak tempah homestay:', '[{"id":"qname","type":"short","label":"Nama penuh","required":true,"options":[],"help":"","max":0},{"id":"qdates","type":"daterange","label":"Tarikh check-in dan check-out","required":true,"options":[],"help":"","max":0},{"id":"qunit","type":"single","label":"Unit","required":true,"options":["Villa 3 bilik (RM450/malam)","Studio (RM180/malam)"],"help":"","max":0}]'],
];
foreach ($forms as [$slug, $title, $open, $q]) {
    db_exec('UPDATE forms SET logo = ? WHERE slug = ? AND logo = ""', ['/assets/img/demo/logo-' . substr($slug, 5) . '.svg', $slug]);
    if (!db_value('SELECT 1 FROM forms WHERE slug = ?', [$slug])) {
        db_exec('INSERT INTO forms (user_id, slug, domain, title, intro, phone, opening_line, mode, theme, logo, questions, save_responses, privacy_url, active, demo, premium_until, views, starts, submits, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, 1, 1, ?, 0, 0, 0, ?, ?)',
            [$uid, $slug, 'wsap.me', $title, '', '60145866666', $open, 'steps', '{}', '/assets/img/demo/logo-' . substr($slug, 5) . '.svg', $q, '', now(86400 * 3650), now(), now()]);
    }
}
echo "forms seeded\n";
