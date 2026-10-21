<?php
declare(strict_types=1);
/** POST from the public shop: validates the cart server-side, stores the order, returns the WhatsApp URL. */
require dirname(__DIR__) . '/lib/bootstrap.php';
require dirname(__DIR__) . '/lib/shops.php';
require dirname(__DIR__) . '/lib/ua.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    json_out(['ok' => false, 'error' => 'POST only'], 405);
}
session_start_safe();
if (!hash_equals($_SESSION['csrf'] ?? '', (string) ($_POST['_csrf'] ?? ''))) {
    json_out(['ok' => false, 'error' => t('err_generic')], 419);
}
if (!empty($_POST['website'])) {
    json_out(['ok' => false, 'error' => t('err_generic')], 400);
}
$shop = db_row('SELECT * FROM shops WHERE id = ?', [(int) ($_POST['shop'] ?? 0)]);
$owner = $shop ? db_row('SELECT * FROM users WHERE id = ?', [(int) $shop['user_id']]) : null;
$viewer = current_user();
$preview = !empty($_POST['preview']) && $shop && $viewer && (int) $viewer['id'] === (int) $shop['user_id'];
if (!$shop || (!shop_is_published($shop, $owner) && !$preview)) {
    json_out(['ok' => false, 'error' => t('sh_404_t')], 404);
}
if ((int) $shop['is_open'] !== 1) {
    json_out(['ok' => false, 'error' => t('sh_closed_msg')], 403);
}
if (!$preview && !rate_limit('shop_order', 20, 3600)) {
    json_out(['ok' => false, 'error' => t('err_rate')], 429);
}

try {
    $cart = json_decode((string) ($_POST['cart'] ?? '[]'), true);
    $v = validate_cart($shop, is_array($cart) ? $cart : []);
    $fields = shop_fields($shop);
    $customer = [];
    foreach (SHOP_FIELDS as $f) {
        if ($fields[$f] === 'off') {
            continue;
        }
        $val = mb_substr(trim((string) ($_POST[$f] ?? '')), 0, $f === 'address' || $f === 'notes' ? 300 : 80);
        if ($fields[$f] === 'req' && $val === '') {
            throw new InvalidArgumentException(t('fm_err_required', ['q' => t('sh_f_' . $f)]));
        }
        if ($f === 'phone' && $val !== '' && !preg_match('/^\+?[\d\s()-]{6,20}$/', $val)) {
            throw new InvalidArgumentException(t('fm_err_phone', ['q' => t('sh_f_phone')]));
        }
        $customer[$f] = $val;
    }
    $shipping = shop_shipping($shop);
    $shipIdx = (int) ($_POST['shipping'] ?? 0);
    $ship = $shipping[$shipIdx] ?? ($shipping[0] ?? null);
    $shipName = $ship['name'] ?? '';
    $shipPrice = (int) ($ship['price'] ?? 0);
    $cp = coupon_discount($shop, (string) ($_POST['coupon'] ?? ''), $v['subtotal']);
    $total = max(0, $v['subtotal'] + $shipPrice - $cp['discount']);
    $order = [
        'number' => order_number(), 'customer' => json_encode($customer, JSON_UNESCAPED_UNICODE), 'items' => json_encode($v['items'], JSON_UNESCAPED_UNICODE),
        'subtotal' => $v['subtotal'], 'shipping_name' => $shipName, 'shipping' => $shipPrice, 'coupon' => $cp['code'], 'discount' => $cp['discount'], 'total' => $total,
    ];
    if (!$preview && (int) ($shop['demo'] ?? 0) !== 1) {
        db_insert('INSERT INTO orders (shop_id, number, customer, items, subtotal, shipping_name, shipping, coupon, discount, total, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [(int) $shop['id'], $order['number'], $order['customer'], $order['items'], $order['subtotal'], $order['shipping_name'], $order['shipping'], $order['coupon'], $order['discount'], $order['total'], 'pending', now()]);
    }
    $msg = compose_order_message($shop, $order);
    $info = click_info();
    if ((int) ($shop['demo'] ?? 0) === 1 || $preview) {
        require_once dirname(__DIR__) . '/lib/demo.php';
        json_out(['ok' => true, 'demo' => true, 'number' => $order['number'], 'html' => demo_popup_html($msg, $shop['phone'], '/shop', t('demo_cta_shop'), 'shop')]);
    }
    $url = ($info['in_app'] !== '' ? 'whatsapp://send?phone=' . $shop['phone'] . '&text=' : 'https://wa.me/' . $shop['phone'] . '?text=') . rawurlencode($msg);
    json_out(['ok' => true, 'url' => $url, 'number' => $order['number']]);
} catch (InvalidArgumentException $e) {
    json_out(['ok' => false, 'error' => $e->getMessage()], 422);
} catch (Throwable $e) {
    error_log('shop order: ' . $e->getMessage());
    json_out(['ok' => false, 'error' => t('err_generic')], 500);
}
