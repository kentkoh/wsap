<?php
declare(strict_types=1);
/** robots.txt, served per domain so each one points at its own sitemap. */
require __DIR__ . '/lib/bootstrap.php';
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: public, max-age=86400');
$lines = ['User-agent: *', 'Allow: /'];
foreach (['/dashboard', '/link', '/analytics', '/qr', '/smart', '/forms', '/form', '/responses', '/shops', '/shop', '/products', '/categories', '/orders', '/customers', '/coupons', '/store', '/upgrade', '/unsubscribe', '/verify', '/reset', '/forgot', '/admin/', '/api/', '/auth/', '/login', '/signup', '/logout', '/cron'] as $p) {
    $lines[] = 'Disallow: ' . $p;
}
$lines[] = '';
$lines[] = 'Sitemap: ' . base_url() . '/sitemap.xml';
echo implode("\n", $lines), "\n";
