<?php
declare(strict_types=1);

/**
 * Authentication: email+password, Google sign-in, email verification, password reset.
 * Users table: id, email, name, password_hash, google_sub, role, plan, plan_until,
 *              verified_at, created_at, last_login_at
 */

const ROLE_ADMIN = 'admin';

function current_user(): ?array
{
    static $user = false;
    if ($user !== false) {
        return $user;
    }
    session_start_safe();
    $id = $_SESSION['uid'] ?? null;
    if (!$id) {
        return $user = null;
    }
    try {
        $user = db_row('SELECT * FROM users WHERE id = ?', [(int) $id]);
    } catch (Throwable $e) {
        $user = null;
    }
    return $user;
}

function require_login(): array
{
    $u = current_user();
    if (!$u) {
        session_start_safe();
        $_SESSION['after_login'] = $_SERVER['REQUEST_URI'] ?? '/dashboard';
        redirect('/login');
    }
    return $u;
}

function require_admin(): array
{
    $u = require_login();
    if (($u['role'] ?? '') !== ROLE_ADMIN) {
        http_response_code(403);
        exit('Forbidden');
    }
    return $u;
}

function login_user(array $user): void
{
    session_start_safe();
    session_regenerate_id(true);
    $_SESSION['uid'] = (int) $user['id'];
    unset($_SESSION['csrf']);
    db_exec('UPDATE users SET last_login_at = ? WHERE id = ?', [now(), (int) $user['id']]);
    claim_session_links((int) $user['id']);
}

function logout_user(): void
{
    session_start_safe();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/** After login, redirect to the page the user originally wanted. */
function after_login_url(): string
{
    session_start_safe();
    $to = $_SESSION['after_login'] ?? '/dashboard';
    unset($_SESSION['after_login']);
    return (is_string($to) && str_starts_with($to, '/') && !str_starts_with($to, '//')) ? $to : '/dashboard';
}

function valid_email(string $email): bool
{
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($email) <= 190;
}

function find_user_by_email(string $email): ?array
{
    return db_row('SELECT * FROM users WHERE email = ?', [strtolower(trim($email))]);
}

/** Create an email+password account. Returns the new user row. Throws InvalidArgumentException. */
function register_user(string $email, string $password, string $name = ''): array
{
    $email = strtolower(trim($email));
    if (!valid_email($email)) {
        throw new InvalidArgumentException(t('err_email'));
    }
    if (strlen($password) < 8) {
        throw new InvalidArgumentException(t('err_password_short'));
    }
    if (find_user_by_email($email)) {
        throw new InvalidArgumentException(t('err_email_taken'));
    }
    $first = (int) db_value('SELECT COUNT(*) FROM users') === 0;
    $id = db_insert(
        'INSERT INTO users (email, name, password_hash, role, plan, created_at) VALUES (?, ?, ?, ?, ?, ?)',
        [$email, mb_substr(trim($name), 0, 80), password_hash($password, PASSWORD_DEFAULT), $first ? ROLE_ADMIN : 'user', 'free', now()]
    );
    return db_row('SELECT * FROM users WHERE id = ?', [$id]);
}

/** Verify email+password. Returns the user or null. */
function authenticate(string $email, string $password): ?array
{
    $u = find_user_by_email($email);
    if (!$u || empty($u['password_hash']) || !password_verify($password, $u['password_hash'])) {
        return null;
    }
    if (password_needs_rehash($u['password_hash'], PASSWORD_DEFAULT)) {
        db_exec('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($password, PASSWORD_DEFAULT), (int) $u['id']]);
    }
    return $u;
}

/** Find or create a user from a verified Google profile. */
function user_from_google(string $sub, string $email, string $name): array
{
    $email = strtolower(trim($email));
    $u = db_row('SELECT * FROM users WHERE google_sub = ?', [$sub]);
    if ($u) {
        return $u;
    }
    $u = find_user_by_email($email);
    if ($u) {
        db_exec('UPDATE users SET google_sub = ?, verified_at = COALESCE(verified_at, ?) WHERE id = ?', [$sub, now(), (int) $u['id']]);
        return db_row('SELECT * FROM users WHERE id = ?', [(int) $u['id']]);
    }
    $first = (int) db_value('SELECT COUNT(*) FROM users') === 0;
    $id = db_insert(
        'INSERT INTO users (email, name, google_sub, role, plan, verified_at, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)',
        [$email, mb_substr($name, 0, 80), $sub, $first ? ROLE_ADMIN : 'user', 'free', now(), now()]
    );
    return db_row('SELECT * FROM users WHERE id = ?', [$id]);
}

// ---------------------------------------------------------------- one-time tokens (verify / reset)

function issue_token(int $userId, string $purpose, int $ttlSeconds = 3600 * 24): string
{
    $raw = bin2hex(random_bytes(24));
    db_exec('DELETE FROM tokens WHERE user_id = ? AND purpose = ?', [$userId, $purpose]);
    db_exec(
        'INSERT INTO tokens (user_id, purpose, token_hash, expires_at, created_at) VALUES (?, ?, ?, ?, ?)',
        [$userId, $purpose, hash('sha256', $raw), now($ttlSeconds), now()]
    );
    return $raw;
}

/** Consume a token; returns the user id or null. */
function consume_token(string $raw, string $purpose): ?int
{
    if (!preg_match('/^[a-f0-9]{48}$/', $raw)) {
        return null;
    }
    $row = db_row('SELECT * FROM tokens WHERE token_hash = ? AND purpose = ?', [hash('sha256', $raw), $purpose]);
    if (!$row || $row['expires_at'] < now()) {
        return null;
    }
    db_exec('DELETE FROM tokens WHERE id = ?', [(int) $row['id']]);
    return (int) $row['user_id'];
}

// ---------------------------------------------------------------- plan helpers

function user_plan(array $u): string
{
    $plan = $u['plan'] ?? 'free';
    if ($plan !== 'free' && !empty($u['plan_until']) && $u['plan_until'] < now()) {
        return 'free';
    }
    return $plan;
}

function is_premium(array $u): bool
{
    return user_plan($u) !== 'free';
}

function plan_name(string $plan): string
{
    return cfg('plans.' . $plan . '.name') ?? ucfirst($plan);
}

// ---------------------------------------------------------------- rate limiting (per IP + action)

/** Returns true when the caller is within limit; records the hit. */
function rate_limit(string $action, int $max, int $windowSeconds): bool
{
    $key = $action . ':' . hash('sha256', client_ip() . cfg('ip_salt'));
    try {
        $row = db_row('SELECT * FROM rate_limits WHERE `key` = ?', [$key]);
        $windowStart = now(-$windowSeconds);
        if (!$row || $row['window_start'] < $windowStart) {
            if ($row) {
                db_exec('UPDATE rate_limits SET hits = 1, window_start = ? WHERE `key` = ?', [now(), $key]);
            } else {
                db_exec('INSERT INTO rate_limits (`key`, hits, window_start) VALUES (?, 1, ?)', [$key, now()]);
            }
            return true;
        }
        if ((int) $row['hits'] >= $max) {
            return false;
        }
        db_exec('UPDATE rate_limits SET hits = hits + 1 WHERE `key` = ?', [$key]);
        return true;
    } catch (Throwable $e) {
        error_log('rate_limit: ' . $e->getMessage());
        return true;
    }
}
