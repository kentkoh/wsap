<?php
declare(strict_types=1);
/** Design preview of "landing page before chat": /landing-demo?p=homestay|bakery|clinic (demo popup instead of WhatsApp). */
require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/demo.php';
require __DIR__ . '/lib/landing.php';

$presets = landing_presets();
$p = (string) ($_GET['p'] ?? 'homestay');
$cfg = $presets[$p] ?? $presets['homestay'];
// optional overrides for trying settings: ?theme=noir&layout=split&font=serif&background=dots&radius=pill&animation=off
foreach (['theme', 'layout', 'font', 'background', 'radius', 'animation'] as $k) {
    if (isset($_GET[$k]) && is_string($_GET[$k]) && preg_match('/^[a-z_]+$/', $_GET[$k])) {
        $cfg[$k] = $_GET[$k];
    }
}
header('X-Robots-Tag: noindex');
echo landing_render($cfg, ['phone' => '60145866666'], true);
