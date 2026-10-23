<?php
declare(strict_types=1);

/**
 * WSAP bootstrap — include at the top of every entry script:
 *   require __DIR__ . '/lib/bootstrap.php';
 */

define('WSAP_ROOT', dirname(__DIR__));

/** cfg() returns the whole config; cfg('db.name') walks dot-paths. config.local.php wins. */
function cfg(?string $path = null)
{
    static $config = null;
    if ($config === null) {
        $config = require WSAP_ROOT . '/config.php';
        $localFile = WSAP_ROOT . '/config.local.php';
        if (is_file($localFile)) {
            $local = require $localFile;
            if (is_array($local)) {
                $config = array_replace_recursive($config, $local);
            }
        }
    }
    if ($path === null) {
        return $config;
    }
    $value = $config;
    foreach (explode('.', $path) as $key) {
        if (!is_array($value) || !array_key_exists($key, $value)) {
            return null;
        }
        $value = $value[$key];
    }
    return $value;
}

if (cfg('env') === 'development') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

// ---------------------------------------------------------------- database

/** Shared PDO connection. Throws on failure (callers that can run without DB catch it). */
function db(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }
    $c = cfg('db');
    $opts = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    if (($c['driver'] ?? 'mysql') === 'sqlite') {
        $pdo = new PDO('sqlite:' . $c['sqlite'], null, null, $opts);
        $pdo->exec('PRAGMA foreign_keys = ON');
        return $pdo;
    }
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $c['host'], $c['name'], $c['charset']);
    $pdo = new PDO($dsn, $c['user'], $c['pass'], $opts);
    return $pdo;
}

function db_row(string $sql, array $p = []): ?array
{
    $st = db()->prepare($sql);
    $st->execute($p);
    $r = $st->fetch();
    return $r === false ? null : $r;
}

function db_rows(string $sql, array $p = []): array
{
    $st = db()->prepare($sql);
    $st->execute($p);
    return $st->fetchAll();
}

function db_value(string $sql, array $p = [])
{
    $st = db()->prepare($sql);
    $st->execute($p);
    $r = $st->fetch(PDO::FETCH_NUM);
    return $r === false ? null : $r[0];
}

function db_exec(string $sql, array $p = []): int
{
    $st = db()->prepare($sql);
    $st->execute($p);
    return $st->rowCount();
}

function db_insert(string $sql, array $p = []): int
{
    db_exec($sql, $p);
    return (int) db()->lastInsertId();
}

/** UTC "Y-m-d H:i:s" (stored in DB; rendered in the viewer's zone by JS). */
function now(int $offsetSeconds = 0): string
{
    return gmdate('Y-m-d H:i:s', time() + $offsetSeconds);
}

function setting(string $key, string $default = ''): string
{
    try {
        $v = db_value('SELECT value FROM settings WHERE `key` = ?', [$key]);
    } catch (Throwable $e) {
        return $default;
    }
    return $v === null ? $default : (string) $v;
}

function set_setting(string $key, string $value): void
{
    if (db_value('SELECT COUNT(*) FROM settings WHERE `key` = ?', [$key])) {
        db_exec('UPDATE settings SET value = ? WHERE `key` = ?', [$value, $key]);
    } else {
        db_exec('INSERT INTO settings (`key`, value) VALUES (?, ?)', [$key, $value]);
    }
}

// ---------------------------------------------------------------- request / domain

/** Host of the current request without port or www, lower-cased. */
function current_host(): string
{
    $h = strtolower($_SERVER['HTTP_HOST'] ?? '');
    $h = preg_replace('/:\d+$/', '', $h) ?? '';
    return preg_replace('/^www\./', '', $h) ?? '';
}

/** The configured domain that matches this request (falls back to the first one). */
function current_domain(): string
{
    $domains = array_keys(cfg('domains'));
    $h = current_host();
    return in_array($h, $domains, true) ? $h : $domains[0];
}

function domain_cfg(?string $domain = null): array
{
    $domain = $domain ?? current_domain();
    return cfg('domains')[$domain] ?? ['lang' => 'en', 'currency' => 'USD', 'label' => $domain];
}

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
}

/** Absolute base URL of the current host, no trailing slash. */
function base_url(): string
{
    if (cfg('env') === 'development') {
        return (is_https() ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    }
    return 'https://' . current_domain();
}

/** Public short URL for a link row, e.g. https://wsap.me/abc123 */
function short_url(array $link): string
{
    if (cfg('env') === 'development') {
        return base_url() . '/' . $link['code'];
    }
    return 'https://' . $link['domain'] . '/' . $link['code'];
}

function short_display(array $link): string
{
    return $link['domain'] . '/' . $link['code'];
}

function client_ip(): string
{
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $k) {
        if (!empty($_SERVER[$k])) {
            $ip = trim(explode(',', $_SERVER[$k])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    return '0.0.0.0';
}

function redirect(string $to, int $code = 302): never
{
    header('Location: ' . $to, true, $code);
    exit;
}

// ---------------------------------------------------------------- session / csrf / flash

function session_start_safe(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_set_cookie_params([
        'lifetime' => 60 * 60 * 24 * 30,
        'path'     => '/',
        'secure'   => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_name('wsap_s');
    session_start();
}

function csrf_token(): string
{
    session_start_safe();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    session_start_safe();
    $sent = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF'] ?? '');
    if (!is_string($sent) || !hash_equals($_SESSION['csrf'] ?? '', $sent)) {
        http_response_code(419);
        exit('Session expired. Please go back and try again.');
    }
}

function flash(string $type, string $msg): void
{
    session_start_safe();
    $_SESSION['flash'][] = ['type' => $type, 'msg' => $msg];
}

function flashes(): array
{
    session_start_safe();
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

// ---------------------------------------------------------------- i18n

function lang(): string
{
    static $lang = null;
    if ($lang !== null) {
        return $lang;
    }
    $allowed = ['en', 'ms'];
    $q = $_GET['lang'] ?? null;
    if (is_string($q) && in_array($q, $allowed, true)) {
        setcookie('wsap_lang', $q, ['expires' => time() + 86400 * 365, 'path' => '/', 'samesite' => 'Lax']);
        return $lang = $q;
    }
    $c = $_COOKIE['wsap_lang'] ?? null;
    if (is_string($c) && in_array($c, $allowed, true)) {
        return $lang = $c;
    }
    return $lang = domain_cfg()['lang'];
}

/** t('key') with {placeholders}. Falls back to English, then to the key itself. */
function t(string $key, array $vars = []): string
{
    static $strings = [];
    $l = lang();
    if (!isset($strings[$l])) {
        $strings[$l] = require WSAP_ROOT . '/lang/' . $l . '.php';
    }
    if ($l !== 'en' && !isset($strings['en'])) {
        $strings['en'] = require WSAP_ROOT . '/lang/en.php';
    }
    $s = $strings[$l][$key] ?? ($strings['en'][$key] ?? $key);
    foreach ($vars as $k => $v) {
        $s = str_replace('{' . $k . '}', (string) $v, $s);
    }
    return $s;
}

// ---------------------------------------------------------------- output helpers

function e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function json_out(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Plan settings: config.php defaults overridden by admin-edited rows in the settings
 * table (keys plan.<plan>.<field>). Fields: name, usd, myr, max_links (0 = unlimited).
 */
function plan_cfg(string $plan): array
{
    static $cache = [];
    if (isset($cache[$plan])) {
        return $cache[$plan];
    }
    $p = cfg('plans.' . $plan) ?? ['name' => ucfirst($plan), 'usd' => 0, 'myr' => 0, 'max_links' => 0];
    foreach (['usd', 'myr', 'max_links'] as $k) {
        $v = setting('plan.' . $plan . '.' . $k);
        if ($v !== '' && is_numeric($v)) {
            $p[$k] = $v + 0;
        }
    }
    return $cache[$plan] = $p;
}

/** Link allowance for a plan; 0 means unlimited. */
function plan_max_links(string $plan): int
{
    return (int) plan_cfg($plan)['max_links'];
}

function can_add_link(array $user): bool
{
    $max = plan_max_links(user_plan($user));
    return $max === 0 || count_user_links((int) $user['id']) < $max;
}

/**
 * Display currency: ?cur=MYR|USD sets a cookie; otherwise the cookie; otherwise the domain default.
 * (Billplz always charges MYR; this only changes what is shown.)
 */
function currency(): string
{
    static $cur = null;
    if ($cur !== null) {
        return $cur;
    }
    $allowed = ['MYR', 'USD'];
    $q = strtoupper((string) ($_GET['cur'] ?? ''));
    if (in_array($q, $allowed, true)) {
        setcookie('wsap_cur', $q, ['expires' => time() + 86400 * 365, 'path' => '/', 'samesite' => 'Lax']);
        return $cur = $q;
    }
    $c = strtoupper((string) ($_COOKIE['wsap_cur'] ?? ''));
    if (in_array($c, $allowed, true)) {
        return $cur = $c;
    }
    // No choice yet: pick by visitor country (Malaysia → RM, elsewhere → USD, unknown → domain default) and remember it.
    // The RM/$ toggle in the header overwrites this cookie, so visitors can always change it.
    $auto = domain_cfg()['currency'];
    try {
        require_once WSAP_ROOT . '/lib/geoip.php';
        $country = visitor_country(true);
        if ($country !== '') {
            $auto = $country === 'MY' ? 'MYR' : 'USD';
        }
    } catch (Throwable $e) {
        error_log('currency geo: ' . $e->getMessage());
    }
    if (!headers_sent()) {
        setcookie('wsap_cur', $auto, ['expires' => time() + 86400 * 365, 'path' => '/', 'samesite' => 'Lax']);
    }
    return $cur = $auto;
}

/** Money for a plan in the chosen display currency: "RM39" / "$9". */
function price(string $plan, ?string $currency = null): string
{
    $currency = $currency ?? currency();
    $p = plan_cfg($plan);
    $fmt = fn($n) => fmod((float) $n, 1.0) == 0.0 ? number_format((float) $n, 0) : number_format((float) $n, 2);
    return $currency === 'MYR' ? 'RM' . $fmt($p['myr']) : '$' . $fmt($p['usd']);
}

/** Two-letter country from CDN/proxy headers (Cloudflare, LiteSpeed GeoIP), or ''. */
function request_country_header(): string
{
    foreach (['HTTP_CF_IPCOUNTRY', 'GEOIP_COUNTRY_CODE', 'HTTP_X_COUNTRY_CODE', 'HTTP_X_GEOIP_COUNTRY'] as $k) {
        $v = strtoupper(trim((string) ($_SERVER[$k] ?? '')));
        if (preg_match('/^[A-Z]{2}$/', $v)) {
            return $v;
        }
    }
    return '';
}

/** Only allow a safe https:// URL. */
function safe_url(string $url): string
{
    $url = trim($url);
    return preg_match('#^https://[^\s"\'<>]+$#i', $url) ? $url : '';
}

require_once WSAP_ROOT . '/lib/auth.php';
require_once WSAP_ROOT . '/lib/links.php';
require_once WSAP_ROOT . '/lib/premium.php';
require_once WSAP_ROOT . '/lib/smart.php';
