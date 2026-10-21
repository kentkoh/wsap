<?php
declare(strict_types=1);

/**
 * Shops: catalogue + cart; an order becomes an itemised WhatsApp message.
 */

const SHOP_FIELDS = ['name', 'phone', 'address', 'city', 'notes'];   // each: req | on | off
const ORDER_STATUSES = ['pending', 'confirmed', 'completed', 'cancelled'];

function shop_default_fields(): array
{
    return ['name' => 'req', 'phone' => 'req', 'address' => 'on', 'city' => 'off', 'notes' => 'on'];
}

function shop_fields(array $shop): array
{
    $f = json_decode((string) $shop['fields'], true);
    $out = shop_default_fields();
    foreach ($out as $k => $v) {
        if (isset($f[$k]) && in_array($f[$k], ['req', 'on', 'off'], true)) {
            $out[$k] = $f[$k];
        }
    }
    return $out;
}

function shop_shipping(array $shop): array
{
    $s = json_decode((string) $shop['shipping'], true);
    return is_array($s) ? array_values(array_filter($s, fn($m) => is_array($m) && !empty($m['name']))) : [];
}

function shop_coupons(array $shop): array
{
    $c = json_decode((string) $shop['coupons'], true);
    return is_array($c) ? array_values(array_filter($c, fn($m) => is_array($m) && !empty($m['code']))) : [];
}

function shop_is_published(array $shop, ?array $owner = null): bool
{
    if ((int) $shop['active'] !== 1) {
        return false;
    }
    if (!empty($shop['premium_until']) && $shop['premium_until'] > now()) {
        return true;
    }
    return $owner !== null && is_premium($owner);
}

function shop_url(array $shop): string
{
    return cfg('env') === 'development' ? base_url() . '/' . $shop['slug'] : 'https://' . $shop['domain'] . '/' . $shop['slug'];
}

function shop_display(array $shop): string
{
    return $shop['domain'] . '/' . $shop['slug'];
}

function user_shops(int $userId): array
{
    return db_rows('SELECT * FROM shops WHERE user_id = ? ORDER BY created_at DESC', [$userId]);
}

function find_shop(string $slug): ?array
{
    $slug = strtolower($slug);
    return preg_match('/^[a-z0-9-]{3,30}$/', $slug) ? db_row('SELECT * FROM shops WHERE slug = ?', [$slug]) : null;
}

function shop_products(int $shopId, bool $activeOnly = false): array
{
    return db_rows('SELECT * FROM products WHERE shop_id = ?' . ($activeOnly ? ' AND active = 1' : '') . ' ORDER BY sort, id', [$shopId]);
}

function product_variants(array $p): array
{
    $v = json_decode((string) $p['variants'], true);
    return is_array($v) ? $v : [];
}

/** "RM12.50" / "$3" */
function money(int $sen, string $currency = 'MYR'): string
{
    $sym = ['MYR' => 'RM', 'SGD' => 'S$', 'USD' => '$', 'IDR' => 'Rp', 'THB' => '฿', 'PHP' => '₱', 'BND' => 'B$', 'AUD' => 'A$', 'GBP' => '£', 'EUR' => '€'][$currency] ?? ($currency . ' ');
    $v = $sen / 100;
    return $sym . (fmod($v, 1.0) == 0.0 ? number_format($v, 0) : number_format($v, 2));
}

/** Parse "12.50" → 1250 sen. */
function to_sen(string $s): int
{
    $s = str_replace([',', ' '], '', trim($s));
    return is_numeric($s) ? max(0, (int) round(((float) $s) * 100)) : 0;
}

/** Variants from the editor: lines "Size: S, M, L(+2)" → JSON structure. */
function parse_variants(string $text): array
{
    $out = [];
    foreach (preg_split('/\r?\n/', trim($text)) ?: [] as $line) {
        if (!str_contains($line, ':')) {
            continue;
        }
        [$name, $opts] = explode(':', $line, 2);
        $name = trim($name);
        $multi = false;
        if (str_ends_with($name, '*')) {
            $multi = true;
            $name = rtrim($name, '* ');
        }
        $name = mb_substr($name, 0, 40);
        $options = [];
        foreach (explode(',', $opts) as $o) {
            $o = trim($o);
            if ($o === '') {
                continue;
            }
            $extra = 0;
            if (preg_match('/^(.*?)\s*\(\s*\+\s*([\d.]+)\s*\)$/', $o, $m)) {
                $o = trim($m[1]);
                $extra = to_sen($m[2]);
            }
            $options[] = ['label' => mb_substr($o, 0, 40), 'extra' => $extra];
        }
        if ($name !== '' && count($options) >= 1) {
            $out[] = ['name' => $name, 'multi' => $multi, 'options' => $options];
        }
    }
    return $out;
}

/** Reverse of parse_variants for the editor textarea. */
function variants_to_text(array $variants): string
{
    $lines = [];
    foreach ($variants as $v) {
        $opts = array_map(fn($o) => $o['label'] . ((int) ($o['extra'] ?? 0) > 0 ? ' (+' . number_format($o['extra'] / 100, 2) . ')' : ''), $v['options'] ?? []);
        $lines[] = $v['name'] . (!empty($v['multi']) ? '*' : '') . ': ' . implode(', ', $opts);
    }
    return implode("\n", $lines);
}

/**
 * Validate a cart posted from the public page. $cart = [{id, qty, options: {variantName: label|[labels]}}]
 * Returns ['items' => [...], 'subtotal' => sen]. Throws InvalidArgumentException.
 */
function validate_cart(array $shop, array $cart): array
{
    $products = [];
    foreach (shop_products((int) $shop['id'], true) as $p) {
        $products[(int) $p['id']] = $p;
    }
    $items = [];
    $subtotal = 0;
    foreach ($cart as $row) {
        if (!is_array($row)) {
            continue;
        }
        $p = $products[(int) ($row['id'] ?? 0)] ?? null;
        $qty = max(1, min(99, (int) ($row['qty'] ?? 1)));
        if (!$p) {
            continue;
        }
        $unit = (int) $p['price'];
        $chosen = [];
        foreach (product_variants($p) as $v) {
            $sel = $row['options'][$v['name']] ?? null;
            $labels = array_map('strval', is_array($sel) ? $sel : ($sel === null || $sel === '' ? [] : [$sel]));
            if (!empty($v['multi']) === false && count($labels) > 1) {
                $labels = [reset($labels)];
            }
            $valid = [];
            foreach ($v['options'] as $o) {
                if (in_array($o['label'], $labels, true)) {
                    $valid[] = $o['label'];
                    $unit += (int) ($o['extra'] ?? 0);
                }
            }
            if (!$valid && empty($v['multi'])) {
                throw new InvalidArgumentException(t('sh_err_option', ['p' => $p['name'], 'v' => $v['name']]));
            }
            if ($valid) {
                $chosen[] = $v['name'] . ': ' . implode(', ', $valid);
            }
        }
        $line = $unit * $qty;
        $subtotal += $line;
        $items[] = ['name' => $p['name'], 'options' => implode(' · ', $chosen), 'qty' => $qty, 'unit' => $unit, 'total' => $line];
    }
    if (!$items) {
        throw new InvalidArgumentException(t('sh_err_empty'));
    }
    return ['items' => $items, 'subtotal' => $subtotal];
}

/** Discount in sen for a coupon code, 0 if invalid. */
function coupon_discount(array $shop, string $code, int $subtotal): array
{
    $code = strtoupper(trim($code));
    foreach (shop_coupons($shop) as $c) {
        if (strtoupper((string) $c['code']) === $code) {
            $d = ($c['type'] ?? 'percent') === 'fixed' ? (int) $c['value'] : (int) round($subtotal * ((float) $c['value']) / 100);
            return ['code' => $code, 'discount' => max(0, min($subtotal, $d))];
        }
    }
    return ['code' => '', 'discount' => 0];
}

function order_number(): string
{
    return strtoupper(substr(base_convert((string) time(), 10, 36), -4) . bin2hex(random_bytes(1)));
}

/** The WhatsApp order message. */
function compose_order_message(array $shop, array $order): string
{
    $cur = $shop['currency'];
    $c = json_decode((string) $order['customer'], true) ?: [];
    $items = json_decode((string) $order['items'], true) ?: [];
    $l = [];
    $l[] = '*' . t('sh_msg_new_order', ['shop' => $shop['name']]) . '* #' . $order['number'];
    $l[] = '';
    foreach ($items as $it) {
        $l[] = '▪ ' . $it['qty'] . ' × ' . $it['name'] . ($it['options'] !== '' ? ' (' . $it['options'] . ')' : '') . ' — ' . money((int) $it['total'], $cur);
    }
    $l[] = '';
    $l[] = t('sh_msg_subtotal') . ': ' . money((int) $order['subtotal'], $cur);
    if ($order['shipping_name'] !== '') {
        $l[] = t('sh_msg_shipping') . ' (' . $order['shipping_name'] . '): ' . money((int) $order['shipping'], $cur);
    }
    if ((int) $order['discount'] > 0) {
        $l[] = t('sh_msg_discount') . ' (' . $order['coupon'] . '): -' . money((int) $order['discount'], $cur);
    }
    $l[] = '*' . t('sh_msg_total') . ': ' . money((int) $order['total'], $cur) . '*';
    $l[] = '';
    foreach (['name' => 'sh_f_name', 'phone' => 'sh_f_phone', 'address' => 'sh_f_address', 'city' => 'sh_f_city', 'notes' => 'sh_f_notes'] as $k => $label) {
        if (!empty($c[$k])) {
            $l[] = t($label) . ': ' . $c[$k];
        }
    }
    return implode("\n", $l);
}

/** Store an uploaded image (re-encoded, max 1200px) and return its public path, or '' . */
function store_image(array $file, string $prefix): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        return '';
    }
    if ((int) $file['size'] > 6 * 1024 * 1024 || !function_exists('imagecreatefromstring')) {
        throw new InvalidArgumentException(t('sh_err_image'));
    }
    $data = file_get_contents($file['tmp_name']);
    $im = @imagecreatefromstring((string) $data);
    if (!$im) {
        throw new InvalidArgumentException(t('sh_err_image'));
    }
    $w = imagesx($im);
    $h = imagesy($im);
    $max = 1200;
    if ($w > $max || $h > $max) {
        $r = min($max / $w, $max / $h);
        $nw = (int) round($w * $r);
        $nh = (int) round($h * $r);
        $dst = imagecreatetruecolor($nw, $nh);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $im, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($im);
        $im = $dst;
    }
    $dir = WSAP_ROOT . '/assets/uploads';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $name = $prefix . '-' . bin2hex(random_bytes(6));
    if (function_exists('imagewebp')) {
        imagewebp($im, "$dir/$name.webp", 82);
        $out = "/assets/uploads/$name.webp";
    } else {
        imagejpeg($im, "$dir/$name.jpg", 85);
        $out = "/assets/uploads/$name.jpg";
    }
    imagedestroy($im);
    return $out;
}

// ---------------------------------------------------------------- categories

function shop_categories(int $shopId): array
{
    return db_rows('SELECT * FROM categories WHERE shop_id = ? ORDER BY sort, id', [$shopId]);
}

/** Products grouped by category row (uncategorised last). Each entry: ['category' => row|null, 'products' => [...]]. */
function shop_catalog(int $shopId, bool $activeOnly = true): array
{
    $cats = shop_categories($shopId);
    $groups = [];
    foreach ($cats as $c) {
        $groups[(int) $c['id']] = ['category' => $c, 'products' => []];
    }
    $other = ['category' => null, 'products' => []];
    foreach (shop_products($shopId, $activeOnly) as $p) {
        $cid = (int) ($p['category_id'] ?? 0);
        if ($cid && isset($groups[$cid])) {
            $groups[$cid]['products'][] = $p;
        } else {
            $other['products'][] = $p;
        }
    }
    $out = array_values(array_filter($groups, fn($g) => $g['products'] || !$activeOnly));
    if ($other['products']) {
        $out[] = $other;
    }
    return $out;
}

/** Orders grouped by customer phone (fallback name). */
function shop_customers(int $shopId, string $q = ''): array
{
    $rows = db_rows('SELECT customer, total, status, created_at FROM orders WHERE shop_id = ? ORDER BY id DESC', [$shopId]);
    $by = [];
    foreach ($rows as $r) {
        $c = json_decode((string) $r['customer'], true) ?: [];
        $phone = preg_replace('/\D/', '', (string) ($c['phone'] ?? '')) ?? '';
        $key = $phone !== '' ? $phone : strtolower(trim((string) ($c['name'] ?? '?')));
        if ($q !== '' && stripos($key . ' ' . ($c['name'] ?? ''), $q) === false) {
            continue;
        }
        if (!isset($by[$key])) {
            $by[$key] = ['name' => (string) ($c['name'] ?? ''), 'phone' => $phone, 'orders' => 0, 'spent' => 0, 'last' => $r['created_at'], 'city' => (string) ($c['city'] ?? ''), 'address' => (string) ($c['address'] ?? '')];
        }
        $by[$key]['orders']++;
        if ($r['status'] !== 'cancelled') {
            $by[$key]['spent'] += (int) $r['total'];
        }
    }
    uasort($by, fn($a, $b) => strcmp($b['last'], $a['last']));
    return $by;
}

/** Metrics for a window, with the previous window for comparison. */
function shop_metrics(int $shopId, int $days = 30): array
{
    $now = now();
    $since = now(-$days * 86400);
    $prevSince = now(-2 * $days * 86400);
    $cur = db_row('SELECT COUNT(*) AS n, COALESCE(SUM(total),0) AS v, COUNT(DISTINCT customer) AS c FROM orders WHERE shop_id = ? AND status <> ? AND created_at >= ?', [$shopId, 'cancelled', $since]);
    $prev = db_row('SELECT COUNT(*) AS n, COALESCE(SUM(total),0) AS v, COUNT(DISTINCT customer) AS c FROM orders WHERE shop_id = ? AND status <> ? AND created_at >= ? AND created_at < ?', [$shopId, 'cancelled', $prevSince, $since]);
    $pct = fn($a, $b) => $b > 0 ? (int) round(($a - $b) / $b * 100) : ($a > 0 ? 100 : 0);
    $n = (int) $cur['n'];
    $pn = (int) $prev['n'];
    $aov = $n ? (int) round((int) $cur['v'] / $n) : 0;
    $paov = $pn ? (int) round((int) $prev['v'] / $pn) : 0;
    return [
        'value' => (int) $cur['v'], 'value_pct' => $pct((int) $cur['v'], (int) $prev['v']),
        'orders' => $n, 'orders_pct' => $pct($n, $pn),
        'aov' => $aov, 'aov_pct' => $pct($aov, $paov),
        'customers' => (int) $cur['c'], 'customers_pct' => $pct((int) $cur['c'], (int) $prev['c']),
        'pending' => (int) db_value('SELECT COUNT(*) FROM orders WHERE shop_id = ? AND status = ?', [$shopId, 'pending']),
    ];
}
