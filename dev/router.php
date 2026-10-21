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
// Forms / shops
if (preg_match('#^/f/([A-Za-z0-9-]{3,30})$#', $path, $m)) {
    $_GET['s'] = $m[1];
    require $root . '/form_view.php';
    return true;
}
if (preg_match('#^/s/([A-Za-z0-9-]{3,30})$#', $path, $m) && is_file($root . '/shop_view.php')) {
    $_GET['s'] = $m[1];
    require $root . '/shop_view.php';
    return true;
}
// Short link
if (preg_match('#^/([A-Za-z0-9-]{3,30})$#', $path, $m)) {
    $_GET['c'] = $m[1];
    require $root . '/go.php';
    return true;
}
http_response_code(404);
echo 'Not found';
return true;
