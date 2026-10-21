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

/** Money for a plan in the current domain's currency: "RM39" / "$9". */
function price(string $plan, ?string $currency = null): string
{
    $currency = $currency ?? domain_cfg()['currency'];
    $p = cfg('plans.' . $plan);
    if (!$p) {
        return '';
    }
    return $currency === 'MYR' ? 'RM' . $p['myr'] : '$' . $p['usd'];
}

/** Only allow a safe https:// URL. */
function safe_url(string $url): string
{
    $url = trim($url);
    return preg_match('#^https://[^\s"\'<>]+$#i', $url) ? $url : '';
}

require_once WSAP_ROOT . '/lib/auth.php';
require_once WSAP_ROOT . '/lib/links.php';
