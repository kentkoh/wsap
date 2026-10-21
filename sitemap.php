<?php
declare(strict_types=1);
require __DIR__ . '/lib/bootstrap.php';
header('Content-Type: application/xml; charset=utf-8');
$base = base_url();
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach (['/', '/pricing', '/terms', '/privacy'] as $p) {
    echo '  <url><loc>' . e($base . $p) . '</loc></url>' . "\n";
}
echo '</urlset>';
