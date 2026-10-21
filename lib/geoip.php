<?php
declare(strict_types=1);

/**
 * Visitor country for server-side routing.
 *  1. CDN / proxy header (Cloudflare CF-IPCountry, LiteSpeed GeoIP) — instant, preferred.
 *  2. Cached lookup (geo_cache, 24 h, keyed by a salted hash of the IP; the IP itself is not stored).
 *  3. ipwho.is lookup with a 1.2 s timeout — only called when the caller really needs a country.
 * Returns a 2-letter code or ''.
 */
function visitor_country(bool $allowLookup = true): string
{
    static $memo = null;
    if ($memo !== null) {
        return $memo;
    }
    $c = request_country_header();
    if ($c !== '') {
        return $memo = $c;
    }
    $ip = client_ip();
    if ($ip === '0.0.0.0' || $ip === '127.0.0.1' || $ip === '::1' || !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        return $memo = '';
    }
    $key = substr(hash('sha256', $ip . cfg('ip_salt')), 0, 32);
    try {
        $row = db_row('SELECT country, created_at FROM geo_cache WHERE ip_hash = ?', [$key]);
        if ($row && $row['created_at'] > now(-86400)) {
            return $memo = (string) $row['country'];
        }
    } catch (Throwable $e) {
        // table not installed yet: fall through to the lookup
    }
    if (!$allowLookup) {
        return $memo = '';
    }
    $country = '';
    try {
        require_once WSAP_ROOT . '/lib/http.php';
        $r = http_request('https://ipwho.is/' . rawurlencode($ip) . '?fields=success,country_code', ['timeout' => 2]);
        $j = json_decode($r['body'], true);
        if (is_array($j) && !empty($j['success']) && preg_match('/^[A-Z]{2}$/', (string) ($j['country_code'] ?? ''))) {
            $country = (string) $j['country_code'];
        }
    } catch (Throwable $e) {
        error_log('geoip: ' . $e->getMessage());
    }
    try {
        db_exec('DELETE FROM geo_cache WHERE ip_hash = ?', [$key]);
        db_exec('INSERT INTO geo_cache (ip_hash, country, created_at) VALUES (?, ?, ?)', [$key, $country, now()]);
    } catch (Throwable $e) {
    }
    return $memo = $country;
}
