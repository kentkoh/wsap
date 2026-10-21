<?php
declare(strict_types=1);

/**
 * Short links: code generation, validation, creation, lookup, click logging.
 *
 * links: id, code, domain, user_id (NULL = anonymous), phone, message, title,
 *        is_custom, active, clicks, created_at, updated_at, session_key
 * clicks: id, link_id, created_at, ip_hash, country, referrer, device, os, browser,
 *         is_bot, is_qr, utm_source
 */

/** Slugs that can never be link codes (they are pages or folders). */
const RESERVED_CODES = [
    'admin', 'api', 'assets', 'auth', 'lib', 'db', 'dev', 'lang', 'login', 'logout', 'signup', 'register',
    'dashboard', 'pricing', 'premium', 'faq', 'terms', 'privacy', 'contact', 'help', 'blog', 'about',
    'link', 'links', 'new', 'edit', 'delete', 'go', 'qr', 'analytics', 'stats', 'settings', 'account',
    'forgot', 'reset', 'verify', 'claim', 'sitemap', 'robots', 'index', 'wsap', 'whatsapp', 'wa', 'www',
    'forms', 'form', 'shop', 'bio', 'page', 'pages', 'smart', 'app', 'mail', 'email', 'support', 'status',
    'upgrade', 'checkout', 'billplz', 'webhook', 'webhooks', 'static', 'img', 'css', 'js', 'uploads',
];

/**
 * Links, forms and shops share one namespace (wsap.me/slug). True when the slug is used by any of them.
 * $except = ['links'|'forms'|'shops', id] ignores that row (editing).
 */
function slug_taken(string $slug, array $except = []): bool
{
    $slug = strtolower($slug);
    foreach (['links' => 'code', 'forms' => 'slug', 'shops' => 'slug'] as $table => $col) {
        $sql = "SELECT 1 FROM $table WHERE $col = ?";
        $p = [$slug];
        if ($except && $except[0] === $table) {
            $sql .= ' AND id <> ?';
            $p[] = (int) $except[1];
        }
        try {
            if (db_value($sql, $p)) {
                return true;
            }
        } catch (Throwable $e) {
            // table may not exist yet on an install that has not run the upgrade
        }
    }
    return false;
}

function random_code(): string
{
    $alphabet = cfg('code_alphabet');
    $len = (int) cfg('code_length');
    $max = strlen($alphabet) - 1;
    $out = '';
    for ($i = 0; $i < $len; $i++) {
        $out .= $alphabet[random_int(0, $max)];
    }
    return $out;
}

/** A random code that is not yet used. */
function unique_code(): string
{
    for ($i = 0; $i < 20; $i++) {
        $c = random_code();
        if (!in_array($c, RESERVED_CODES, true) && !slug_taken($c)) {
            return $c;
        }
    }
    throw new RuntimeException('Could not allocate a code');
}

/**
 * Custom handle rules: slug_min–30 chars, a-z 0-9 and hyphen, no leading/trailing hyphen,
 * not reserved, not a trademark, not offensive. Use custom_code_error() for the reason.
 */
function valid_custom_code(string $code): bool
{
    return custom_code_error($code) === '';
}

/** Translation key explaining why a handle is rejected, or '' when valid. $userId: who wants it (premium handles). */
function custom_code_error(string $code, ?int $userId = null): string
{
    require_once WSAP_ROOT . '/lib/blocklist.php';
    if (!preg_match('/^[a-z0-9](?:[a-z0-9-]{1,28}[a-z0-9])?$/', $code) || strlen($code) < slug_min_length()) {
        return 'err_code_invalid';
    }
    if (in_array($code, RESERVED_CODES, true)) {
        return 'err_code_taken';
    }
    $r = slug_block_reason($code);
    if ($r !== '') {
        return $r;
    }
    if ($userId === null) {
        $u = current_user();
        $userId = $u ? (int) $u['id'] : null;
    }
    return premium_slug_block($code, $userId) ? 'err_code_premium' : '';
}

/** Throws InvalidArgumentException with the right message when a handle is not allowed. */
function assert_custom_code(string $code, ?int $userId = null): void
{
    $err = custom_code_error($code, $userId);
    if ($err === '') {
        return;
    }
    $vars = ['n' => slug_min_length()];
    if ($err === 'err_code_premium') {
        $row = premium_slug_block($code, $userId);
        $wa = (string) cfg('support_whatsapp');
        $vars['price'] = 'RM' . number_format((float) ($row['price_myr'] ?? 0), 0);
        $vars['handle'] = $code;
        $msg = t($err, $vars);
        if ($wa !== '') {
            $msg .= ' ' . t('err_code_premium_wa') . ': wa.me/' . $wa;
        }
        throw new InvalidArgumentException($msg);
    }
    throw new InvalidArgumentException(t($err, $vars));
}

/** Digits only, 7–15 digits (E.164 without "+"). */
function normalize_phone(string $cc, string $number): string
{
    $cc = preg_replace('/\D/', '', $cc) ?? '';
    $n = preg_replace('/\D/', '', $number) ?? '';
    // Strip a leading 0 from the national number (common in MY/ID/SG input).
    if ($cc !== '' && str_starts_with($n, '0')) {
        $n = ltrim($n, '0');
    }
    // If the user already typed the country code, do not double it.
    if ($cc !== '' && str_starts_with($n, $cc) && strlen($n) >= 10) {
        return $n;
    }
    return $cc . $n;
}

function valid_phone(string $digits): bool
{
    return (bool) preg_match('/^[1-9]\d{6,14}$/', $digits);
}

/** The wa.me target for a link. */
function wa_url(array $link): string
{
    $u = 'https://wa.me/' . $link['phone'];
    if ($link['message'] !== '') {
        $u .= '?text=' . rawurlencode($link['message']);
    }
    return $u;
}

/** Create a link. $opts: user_id, domain, code (custom), title, session_key. */
function create_link(string $phone, string $message, array $opts = []): array
{
    if (!valid_phone($phone)) {
        throw new InvalidArgumentException(t('err_phone'));
    }
    $message = trim($message);
    if (mb_strlen($message) > 1000) {
        throw new InvalidArgumentException(t('err_message_long'));
    }
    $domains = array_keys(cfg('domains'));
    $domain = $opts['domain'] ?? current_domain();
    if (!in_array($domain, $domains, true)) {
        $domain = $domains[0];
    }
    $custom = isset($opts['code']) && $opts['code'] !== '';
    if ($custom) {
        $code = strtolower(trim($opts['code']));
        assert_custom_code($code);
        if (slug_taken($code)) {
            throw new InvalidArgumentException(t('err_code_taken'));
        }
    } else {
        $code = unique_code();
    }
    $id = db_insert(
        'INSERT INTO links (code, domain, user_id, phone, message, title, is_custom, active, clicks, session_key, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, 1, 0, ?, ?, ?)',
        [
            $code, $domain, $opts['user_id'] ?? null, $phone, $message,
            mb_substr(trim($opts['title'] ?? ''), 0, 80), $custom ? 1 : 0,
            $opts['session_key'] ?? null, now(), now(),
        ]
    );
    return db_row('SELECT * FROM links WHERE id = ?', [$id]);
}

function find_link(string $code): ?array
{
    $code = strtolower($code);
    if ($code === '' || strlen($code) > 30) {
        return null;
    }
    return db_row('SELECT * FROM links WHERE code = ?', [$code]);
}

function user_links(int $userId): array
{
    return db_rows('SELECT * FROM links WHERE user_id = ? ORDER BY created_at DESC', [$userId]);
}

function count_user_links(int $userId): int
{
    return (int) db_value('SELECT COUNT(*) FROM links WHERE user_id = ?', [$userId]);
}

/** Anonymous links created in this browser session are attached to the account on login/signup. */
function anon_session_key(): string
{
    session_start_safe();
    if (empty($_SESSION['anon_key'])) {
        $_SESSION['anon_key'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['anon_key'];
}

function claim_session_links(int $userId): int
{
    session_start_safe();
    $key = $_SESSION['anon_key'] ?? null;
    if (!$key) {
        return 0;
    }
    try {
        return db_exec('UPDATE links SET user_id = ?, session_key = NULL WHERE user_id IS NULL AND session_key = ?', [$userId, $key]);
    } catch (Throwable $e) {
        return 0;
    }
}

// ---------------------------------------------------------------- click logging

function record_click(array $link, array $info, ?int $destinationId = null): void
{
    $ipHash = substr(hash('sha256', client_ip() . cfg('ip_salt') . gmdate('Y-m-d')), 0, 32);
    db_exec(
        'INSERT INTO clicks (link_id, destination_id, created_at, ip_hash, country, referrer, device, os, browser, is_bot, is_qr, utm_source)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
        [
            (int) $link['id'], $destinationId, now(), $ipHash, $info['country'], $info['referrer'], $info['device'],
            $info['os'], $info['browser'], $info['is_bot'] ? 1 : 0, $info['is_qr'] ? 1 : 0, $info['utm_source'],
        ]
    );
    if (!$info['is_bot']) {
        db_exec('UPDATE links SET clicks = clicks + 1 WHERE id = ?', [(int) $link['id']]);
        if ($destinationId) {
            db_exec('UPDATE destinations SET clicks = clicks + 1 WHERE id = ?', [$destinationId]);
        }
    }
}

/** Aggregates for the analytics page. $days window (max 365). */
function link_stats(int $linkId, int $days = 30): array
{
    $since = now(-$days * 86400);
    $base = 'FROM clicks WHERE link_id = ? AND is_bot = 0 AND created_at >= ?';
    $p = [$linkId, $since];
    $group = function (string $col) use ($base, $p): array {
        return db_rows("SELECT $col AS k, COUNT(*) AS n $base GROUP BY $col ORDER BY n DESC LIMIT 12", $p);
    };
    return [
        'total'    => (int) db_value("SELECT COUNT(*) $base", $p),
        'qr'       => (int) db_value("SELECT COUNT(*) $base AND is_qr = 1", $p),
        'unique'   => (int) db_value("SELECT COUNT(DISTINCT ip_hash) $base", $p),
        'bots'     => (int) db_value('SELECT COUNT(*) FROM clicks WHERE link_id = ? AND is_bot = 1 AND created_at >= ?', $p),
        'by_day'   => db_rows("SELECT SUBSTR(created_at, 1, 10) AS k, COUNT(*) AS n $base GROUP BY SUBSTR(created_at, 1, 10) ORDER BY k", $p),
        'country'  => $group('country'),
        'referrer' => $group('referrer'),
        'device'   => $group('device'),
        'os'       => $group('os'),
        'browser'  => $group('browser'),
        'utm'      => $group('utm_source'),
        'destination' => db_rows("SELECT COALESCE(d.name, '') AS k, COUNT(*) AS n FROM clicks c LEFT JOIN destinations d ON d.id = c.destination_id WHERE c.link_id = ? AND c.is_bot = 0 AND c.created_at >= ? AND c.destination_id IS NOT NULL GROUP BY d.name ORDER BY n DESC LIMIT 12", $p),
    ];
}
