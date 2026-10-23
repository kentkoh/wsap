<?php
declare(strict_types=1);
/** sitemap.xml for the current domain, with hreflang alternates pointing at the other domain. */
require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/seo.php';
header('Content-Type: application/xml; charset=utf-8');
header('Cache-Control: public, max-age=3600');
$base = base_url();
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
    . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";
foreach (seo_public_pages() as $path => $file) {
    $mod = @filemtime(WSAP_ROOT . '/' . $file) ?: time();
    $priority = $path === '/' ? '1.0' : (in_array($path, ['/terms', '/privacy'], true) ? '0.3' : '0.8');
    echo '  <url><loc>' . e($base . $path) . '</loc><lastmod>' . gmdate('Y-m-d', $mod) . '</lastmod><priority>' . $priority . '</priority>';
    foreach (seo_alternate_urls($path) as $lang => $url) {
        echo '<xhtml:link rel="alternate" hreflang="' . e($lang === 'ms' ? 'ms-MY' : 'en') . '" href="' . e($url) . '"/>';
    }
    echo '</url>' . "\n";
}
echo '</urlset>';
