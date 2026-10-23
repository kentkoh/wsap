<?php
/**
 * Router for PHP's built-in server that mimics .htaccess:
 *   php -c dev/php.ini -S localhost:8080 dev/router.php
 */
$root = dirname(__DIR__);
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$path = rtrim($path, '/');

if ($path === '' || $path === '/') {
    require $root . '/index.php';
    return true;
}
if ($path === '/sitemap.xml') {
    require $root . '/sitemap.php';
    return true;
}
if ($path === '/robots.txt') {
    require $root . '/robots.php';
    return true;
}
// SEO guide pages (mirror of the .htaccess rule)
if (preg_match('#^/(whatsapp-link-generator|wa-me-link|whatsapp-qr-code|whatsapp-link-instagram-bio|whatsapp-business-link|walink-alternative|cara-buat-link-whatsapp)$#', $path, $m)) {
    $_GET['g'] = $m[1];
    require $root . '/guide.php';
    return true;
}
// Real static files / php files
$file = $root . $path;
if (is_file($file)) {
    if (preg_match('#^/(db|lib|lang|dev|_old)/#', $path) || preg_match('/\.(md|sql|log|sqlite|ini|sh)$/', $path) || preg_match('#^/config#', $path)) {
        http_response_code(404);
        return true;
    }
    return false; // let the built-in server serve it (php files are executed)
}
// /login → login.php, /admin → admin/index.php
if (preg_match('#^/([a-z0-9/_-]+)$#', $path, $m)) {
    if (is_file($root . '/' . $m[1] . '.php')) {
        $_SERVER['SCRIPT_NAME'] = '/' . $m[1] . '.php';
        require $root . '/' . $m[1] . '.php';
        return true;
    }
    if (is_dir($root . '/' . $m[1]) && is_file($root . '/' . $m[1] . '/index.php')) {
        require $root . '/' . $m[1] . '/index.php';
        return true;
    }
}
// Old /f/slug and /s/slug addresses → /slug
if (preg_match('#^/[fs]/([A-Za-z0-9-]{3,30})$#', $path, $m)) {
    header('Location: /' . $m[1], true, 301);
    return true;
}
// Short link
if (preg_match('#^/([A-Za-z0-9-]{3,30})$#', $path, $m)) {
    $_GET['c'] = $m[1];
    require $root . '/go.php';
    return true;
}
require $root . '/404.php';
return true;
