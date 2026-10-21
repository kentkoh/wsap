<?php
declare(strict_types=1);

/** Sub-navigation for one shop's workspace. */
function shop_nav(array $shop, string $active): string
{
    $id = (int) $shop['id'];
    $tabs = [
        'dashboard'  => ['/store?shop=' . $id, t('sh_nav_dashboard')],
        'products'   => ['/products?shop=' . $id, t('sh_products')],
        'categories' => ['/categories?shop=' . $id, t('sh_nav_categories')],
        'coupons'    => ['/coupons?shop=' . $id, t('sh_coupons')],
        'customers'  => ['/customers?shop=' . $id, t('sh_nav_customers')],
        'orders'     => ['/orders?shop=' . $id, t('sh_orders')],
        'settings'   => ['/shop?id=' . $id, t('sh_settings')],
    ];
    $h = '<div class="shop-bar"><a class="shop-bar-name" href="/shops">← ' . e(t('nav_tab_shops')) . '</a><b>' . e($shop['name']) . '</b>'
        . '<span class="sstatus ' . ((int) $shop['is_open'] ? '' : 'off') . '" style="margin:0 0 0 6px">' . e((int) $shop['is_open'] ? t('sh_open_now') : t('sh_closed_now')) . '</span>'
        . '<a class="btn btn-sm btn-ghost" href="' . e(shop_url($shop)) . '?preview=1" target="_blank" rel="noopener" style="margin-left:auto">' . e(t('fm_preview')) . ' ↗</a></div>';
    $h .= '<nav class="tabs" aria-label="Shop">';
    foreach ($tabs as $k => [$href, $label]) {
        $h .= '<a class="' . ($k === $active ? 'on' : '') . '" href="' . $href . '">' . e($label) . '</a>';
    }
    return $h . '</nav>';
}

/** Load a shop owned by the logged-in user from ?shop= (or ?id=), or 404. */
function require_shop(array $u, string $param = 'shop'): array
{
    $sid = (int) ($_GET[$param] ?? ($_POST[$param] ?? 0));
    $shop = db_row('SELECT * FROM shops WHERE id = ? AND user_id = ?', [$sid, (int) $u['id']]);
    if (!$shop) {
        http_response_code(404);
        exit('Not found');
    }
    return $shop;
}
