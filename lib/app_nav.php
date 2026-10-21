<?php
declare(strict_types=1);

/** Product tabs shown at the top of every logged-in page. */
function app_nav(string $active): string
{
    $tabs = ['links' => ['/dashboard', t('nav_tab_links')], 'forms' => ['/forms', t('nav_tab_forms')], 'shops' => ['/shops', t('nav_tab_shops')]];
    $h = '<nav class="tabs" aria-label="Products">';
    foreach ($tabs as $k => [$href, $label]) {
        $h .= '<a class="' . ($k === $active ? 'on' : '') . '" href="' . $href . '">' . e($label) . '</a>';
    }
    return $h . '</nav>';
}
