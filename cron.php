<?php
declare(strict_types=1);
/**
 * Daily housekeeping. cPanel → Cron Jobs (once a day):
 *   php /home/kuantan1/public_html/wsap.me/cron.php KEY      (or curl https://wsap.me/cron?key=KEY)
 *  - reminds owners 7 days before a Premium link expires, and on the day it expires
 *  - syncs payments still pending after 15 minutes
 *  - deletes clicks older than 13 months (privacy policy)
 */
require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/mail.php';

$key = PHP_SAPI === 'cli' ? (string) ($argv[1] ?? '') : (string) ($_GET['key'] ?? '');
if ((string) cfg('cron_key') === '' || !hash_equals((string) cfg('cron_key'), $key)) {
    http_response_code(403);
    exit("forbidden\n");
}
header('Content-Type: text/plain');
$out = [];

// 1. expiry reminders
foreach ([7, 0] as $daysAhead) {
    $day = gmdate('Y-m-d', time() + $daysAhead * 86400);
    $rows = db_rows('SELECT l.*, u.email, u.name FROM links l JOIN users u ON u.id = l.user_id WHERE l.premium_until >= ? AND l.premium_until < ?', [$day . ' 00:00:00', $day . ' 23:59:59']);
    foreach ($rows as $l) {
        $flag = 'remind:' . $l['id'] . ':' . $daysAhead . ':' . $day;
        if (setting($flag) !== '') {
            continue;
        }
        $ok = send_mail((string) $l['email'], (string) $l['name'],
            t($daysAhead ? 'mail_expiring_subject' : 'mail_expired_subject', ['link' => short_display($l)]),
            mail_template(t($daysAhead ? 'mail_expiring_title' : 'mail_expired_title'),
                '<p>' . t($daysAhead ? 'mail_expiring_body' : 'mail_expired_body', ['link' => short_display($l), 'date' => substr((string) $l['premium_until'], 0, 10)]) . '</p>',
                base_url() . '/upgrade?id=' . (int) $l['id'], t('up_extend')));
        set_setting($flag, $ok ? now() : 'failed');
        $out[] = "reminder $daysAhead d: {$l['code']}";
    }
}

// 1b. form expiry reminders (same cadence)
foreach ([7, 0] as $daysAhead) {
    $day = gmdate('Y-m-d', time() + $daysAhead * 86400);
    $rows = db_rows('SELECT f.*, u.email, u.name FROM forms f JOIN users u ON u.id = f.user_id WHERE f.premium_until >= ? AND f.premium_until < ?', [$day . ' 00:00:00', $day . ' 23:59:59']);
    foreach ($rows as $f) {
        $flag = 'remindf:' . $f['id'] . ':' . $daysAhead . ':' . $day;
        if (setting($flag) !== '') {
            continue;
        }
        $label = $f['domain'] . '/f/' . $f['slug'];
        $ok = send_mail((string) $f['email'], (string) $f['name'],
            t($daysAhead ? 'mail_expiring_subject' : 'mail_expired_subject', ['link' => $label]),
            mail_template(t($daysAhead ? 'mail_expiring_title' : 'mail_expired_title'),
                '<p>' . t($daysAhead ? 'mail_expiring_body' : 'mail_expired_body', ['link' => $label, 'date' => substr((string) $f['premium_until'], 0, 10)]) . '</p>',
                base_url() . '/upgrade?form=' . (int) $f['id'], t('up_extend')));
        set_setting($flag, $ok ? now() : 'failed');
        $out[] = "form reminder $daysAhead d: {$f['slug']}";
    }
}

// 2. pending payments older than 15 min → ask Billplz
try {
    require __DIR__ . '/lib/billplz.php';
    if (billplz_enabled()) {
        foreach (db_rows('SELECT bill_id FROM payments WHERE status = ? AND created_at < ? AND created_at > ?', ['pending', now(-900), now(-7 * 86400)]) as $p) {
            $r = sync_bill($p['bill_id']);
            $out[] = 'sync ' . $p['bill_id'] . ': ' . ($r ? 'paid' : 'still pending');
        }
    }
} catch (Throwable $e) {
    $out[] = 'sync error: ' . $e->getMessage();
}

// 3. click retention (13 months)
$n = db_exec('DELETE FROM clicks WHERE created_at < ?', [now(-395 * 86400)]);
$out[] = "purged $n old clicks";
// 4. stale rate-limit rows and expired tokens
db_exec('DELETE FROM rate_limits WHERE window_start < ?', [now(-86400)]);
db_exec('DELETE FROM tokens WHERE expires_at < ?', [now()]);

echo implode("\n", $out ?: ['nothing to do']), "\n";
