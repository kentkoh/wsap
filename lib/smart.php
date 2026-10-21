<?php
declare(strict_types=1);

/**
 * Smart Links: one short link, several destinations.
 * Rules are evaluated in sort order; the first matching rule-based destination wins.
 * If none matches, one of the "always" destinations is chosen by weight (catch-all).
 * With no active destinations at all, the link's own phone/message is used.
 */

const SMART_RULE_TYPES = ['always', 'hours', 'country', 'source'];
const SMART_SOURCES = ['instagram', 'tiktok', 'facebook', 'google', 'youtube', 'twitter', 'linkedin', 'telegram', 'direct', 'qr'];

function link_destinations(int $linkId, bool $activeOnly = false): array
{
    return db_rows('SELECT * FROM destinations WHERE link_id = ?' . ($activeOnly ? ' AND active = 1' : '') . ' ORDER BY sort, id', [$linkId]);
}

function decode_rule(array $d): array
{
    $r = json_decode((string) $d['rule'], true);
    return is_array($r) ? $r : [];
}

/** Does this destination's rule match the current request? $ctx: country, referrer, is_qr, now (timestamp). */
function rule_matches(array $d, array $ctx): bool
{
    $rule = decode_rule($d);
    switch ($d['rule_type']) {
        case 'always':
            return true;
        case 'hours':
            $tz = $rule['tz'] ?? 'Asia/Kuala_Lumpur';
            try {
                $dt = new DateTime('@' . ($ctx['now'] ?? time()));
                $dt->setTimezone(new DateTimeZone($tz));
            } catch (Throwable $e) {
                return false;
            }
            $day = (int) $dt->format('N'); // 1 = Monday
            $days = array_map('intval', $rule['days'] ?? [1, 2, 3, 4, 5, 6, 7]);
            if (!in_array($day, $days, true)) {
                return false;
            }
            $hm = $dt->format('H:i');
            $from = $rule['from'] ?? '00:00';
            $to = $rule['to'] ?? '23:59';
            return $from <= $to ? ($hm >= $from && $hm <= $to) : ($hm >= $from || $hm <= $to); // overnight ranges
        case 'country':
            $c = strtoupper((string) ($ctx['country'] ?? ''));
            return $c !== '' && in_array($c, array_map('strtoupper', $rule['countries'] ?? []), true);
        case 'source':
            $src = source_of($ctx);
            return in_array($src, $rule['sources'] ?? [], true);
    }
    return false;
}

/** Normalise referrer (or an explicit utm_source) into one of SMART_SOURCES. */
function source_of(array $ctx): string
{
    if (!empty($ctx['is_qr'])) {
        return 'qr';
    }
    $utm = strtolower((string) ($ctx['utm_source'] ?? ''));
    if ($utm !== '') {
        foreach (SMART_SOURCES as $s) {
            if ($utm === $s || str_contains($utm, $s)) {
                return $s;
            }
        }
        if (str_contains($utm, 'ig')) {
            return 'instagram';
        }
        if (str_contains($utm, 'fb')) {
            return 'facebook';
        }
    }
    $ref = strtolower((string) ($ctx['referrer'] ?? ''));
    if ($ref === '' || $ref === 'direct') {
        return 'direct';
    }
    foreach (['instagram', 'tiktok', 'facebook', 'google', 'youtube', 'linkedin', 'telegram'] as $s) {
        if (str_contains($ref, $s)) {
            return $s;
        }
    }
    if (str_contains($ref, 'twitter') || str_contains($ref, 't.co') || $ref === 'x.com') {
        return 'twitter';
    }
    if (str_contains($ref, 'fb.') || str_contains($ref, 'messenger')) {
        return 'facebook';
    }
    if (str_contains($ref, 'l.instagram')) {
        return 'instagram';
    }
    return 'direct';
}

/** Pick the destination for this request, or null to use the link itself. */
function resolve_destination(array $link, array $ctx): ?array
{
    if (($link['type'] ?? 'chat') !== 'smart') {
        return null;
    }
    $dests = link_destinations((int) $link['id'], true);
    if (!$dests) {
        return null;
    }
    // Country is only looked up (and only then may cost a network call) when a country rule exists.
    if (($ctx['country'] ?? '') === '') {
        foreach ($dests as $d) {
            if ($d['rule_type'] === 'country') {
                require_once WSAP_ROOT . '/lib/geoip.php';
                $ctx['country'] = visitor_country(true);
                break;
            }
        }
    }
    $catchAll = [];
    foreach ($dests as $d) {
        if ($d['rule_type'] === 'always') {
            $catchAll[] = $d;
            continue;
        }
        if (rule_matches($d, $ctx)) {
            return $d;
        }
    }
    if (!$catchAll) {
        return null;
    }
    $total = 0;
    foreach ($catchAll as $d) {
        $total += max(1, (int) $d['weight']);
    }
    $pick = random_int(1, $total);
    foreach ($catchAll as $d) {
        $pick -= max(1, (int) $d['weight']);
        if ($pick <= 0) {
            return $d;
        }
    }
    return $catchAll[0];
}

/** Effective phone + message after routing. */
function effective_target(array $link, ?array $dest): array
{
    if (!$dest) {
        return ['phone' => $link['phone'], 'message' => $link['message']];
    }
    return ['phone' => $dest['phone'], 'message' => $dest['message'] !== '' ? $dest['message'] : $link['message']];
}

/** Validate + normalise a rule from the editor form. Throws InvalidArgumentException. */
function build_rule(string $type, array $in): array
{
    switch ($type) {
        case 'hours':
            $days = array_values(array_filter(array_map('intval', (array) ($in['days'] ?? [])), fn($d) => $d >= 1 && $d <= 7));
            $from = preg_match('/^\d{2}:\d{2}$/', (string) ($in['from'] ?? '')) ? $in['from'] : '09:00';
            $to = preg_match('/^\d{2}:\d{2}$/', (string) ($in['to'] ?? '')) ? $in['to'] : '18:00';
            $tz = (string) ($in['tz'] ?? 'Asia/Kuala_Lumpur');
            if (!in_array($tz, timezone_identifiers_list(), true)) {
                $tz = 'Asia/Kuala_Lumpur';
            }
            if (!$days) {
                throw new InvalidArgumentException(t('sm_err_days'));
            }
            return ['days' => $days, 'from' => $from, 'to' => $to, 'tz' => $tz];
        case 'country':
            $cs = array_values(array_unique(array_filter(array_map(fn($c) => strtoupper(trim($c)), preg_split('/[\s,]+/', (string) ($in['countries'] ?? '')) ?: []), fn($c) => preg_match('/^[A-Z]{2}$/', $c))));
            if (!$cs) {
                throw new InvalidArgumentException(t('sm_err_countries'));
            }
            return ['countries' => $cs];
        case 'source':
            $ss = array_values(array_intersect(SMART_SOURCES, array_map('strval', (array) ($in['sources'] ?? []))));
            if (!$ss) {
                throw new InvalidArgumentException(t('sm_err_sources'));
            }
            return ['sources' => $ss];
    }
    return [];
}

/** Human summary of a rule for the list. */
function rule_summary(array $d): string
{
    $r = decode_rule($d);
    switch ($d['rule_type']) {
        case 'hours':
            $names = ['', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            $days = implode(' ', array_map(fn($x) => $names[(int) $x] ?? '', $r['days'] ?? []));
            return $days . ' ' . ($r['from'] ?? '') . '–' . ($r['to'] ?? '') . ' ' . ($r['tz'] ?? '');
        case 'country':
            return implode(', ', $r['countries'] ?? []);
        case 'source':
            return implode(', ', $r['sources'] ?? []);
    }
    return t('sm_weight') . ' ' . (int) $d['weight'];
}
