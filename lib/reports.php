<?php
declare(strict_types=1);

/**
 * Monthly email reports (sent by cron.php once per calendar month):
 *  - every member with activity last month gets a digest of their links, forms and shops
 *    (Premium items get the breakdown, free items get totals + a Premium nudge)
 *  - admins get a business summary (sign-ups, revenue, expiring items)
 * Users can opt out (users.notify_report, dashboard toggle or the unsubscribe link in the mail).
 */

/** [$from, $to) UTC datetime strings for a "YYYY-MM" month. */
function report_month_range(string $ym): array
{
    $from = $ym . '-01 00:00:00';
    $to = gmdate('Y-m-01 00:00:00', strtotime($ym . '-01 00:00:00 UTC +1 month'));
    return [$from, $to];
}

/** The month before "YYYY-MM". */
function report_prev_month(string $ym): string
{
    return gmdate('Y-m', strtotime($ym . '-01 00:00:00 UTC -1 month'));
}

/** Month label for emails, e.g. "October 2026". */
function report_month_label(string $ym): string
{
    return gmdate('F Y', strtotime($ym . '-01 00:00:00 UTC'));
}

/** Click totals per link for a month: [link_id => ['n' => int, 'qr' => int]]. */
function report_link_clicks(array $linkIds, string $ym): array
{
    if (!$linkIds) {
        return [];
    }
    [$from, $to] = report_month_range($ym);
    $marks = implode(',', array_fill(0, count($linkIds), '?'));
    $rows = db_rows("SELECT link_id, COUNT(*) AS n, SUM(is_qr) AS qr FROM clicks WHERE link_id IN ($marks) AND is_bot = 0 AND created_at >= ? AND created_at < ? GROUP BY link_id", [...$linkIds, $from, $to]);
    $out = [];
    foreach ($rows as $r) {
        $out[(int) $r['link_id']] = ['n' => (int) $r['n'], 'qr' => (int) $r['qr']];
    }
    return $out;
}

/** Top value of a clicks column for one link in a month ('' when none). */
function report_top(int $linkId, string $col, string $ym): string
{
    [$from, $to] = report_month_range($ym);
    $r = db_row("SELECT $col AS k, COUNT(*) AS n FROM clicks WHERE link_id = ? AND is_bot = 0 AND created_at >= ? AND created_at < ? AND $col <> '' GROUP BY $col ORDER BY n DESC LIMIT 1", [$linkId, $from, $to]);
    return $r ? (string) $r['k'] : '';
}

/**
 * Everything one user's report needs, or null when there was no activity last month.
 * Keys: links (list), total, prev_total, forms (list), orders (count), orders_total (sen), expiring (list), free_clicks.
 */
function user_month_report(array $u, string $ym): ?array
{
    $uid = (int) $u['id'];
    [$from, $to] = report_month_range($ym);
    $links = db_rows('SELECT * FROM links WHERE user_id = ? ORDER BY created_at', [$uid]);
    $ids = array_map(fn($l) => (int) $l['id'], $links);
    $cur = report_link_clicks($ids, $ym);
    $prev = report_link_clicks($ids, report_prev_month($ym));

    $rows = [];
    $total = 0;
    $prevTotal = 0;
    $freeClicks = 0;
    foreach ($links as $l) {
        $id = (int) $l['id'];
        $n = $cur[$id]['n'] ?? 0;
        $p = $prev[$id]['n'] ?? 0;
        $total += $n;
        $prevTotal += $p;
        if ($n === 0) {
            continue;
        }
        $premium = link_is_premium($l, $u);
        if (!$premium) {
            $freeClicks += $n;
        }
        $rows[] = [
            'link' => $l, 'premium' => $premium, 'n' => $n, 'prev' => $p, 'qr' => $cur[$id]['qr'] ?? 0,
            'country' => $premium ? report_top($id, 'country', $ym) : '',
            'source' => $premium ? report_top($id, 'referrer', $ym) : '',
        ];
    }
    usort($rows, fn($a, $b) => $b['n'] <=> $a['n']);
    $rows = array_slice($rows, 0, 10);

    $forms = [];
    foreach (db_rows('SELECT f.id, f.slug, f.domain, f.title, COUNT(r.id) AS n FROM forms f LEFT JOIN form_responses r ON r.form_id = f.id AND r.created_at >= ? AND r.created_at < ? WHERE f.user_id = ? GROUP BY f.id, f.slug, f.domain, f.title HAVING COUNT(r.id) > 0 ORDER BY n DESC', [$from, $to, $uid]) as $f) {
        $forms[] = $f;
    }
    $orders = db_row('SELECT COUNT(o.id) AS n, COALESCE(SUM(o.total), 0) AS total FROM orders o JOIN shops s ON s.id = o.shop_id WHERE s.user_id = ? AND o.created_at >= ? AND o.created_at < ? AND o.status <> ?', [$uid, $from, $to, 'cancelled']);

    $expiring = [];
    $soon = now(30 * 86400);
    foreach (db_rows('SELECT code AS label, premium_until FROM links WHERE user_id = ? AND premium_until BETWEEN ? AND ?', [$uid, now(), $soon]) as $r) {
        $expiring[] = ['label' => current_domain() . '/' . $r['label'], 'date' => substr((string) $r['premium_until'], 0, 10)];
    }
    foreach (['forms', 'shops'] as $tbl) {
        foreach (db_rows("SELECT slug, premium_until FROM $tbl WHERE user_id = ? AND premium_until BETWEEN ? AND ?", [$uid, now(), $soon]) as $r) {
            $expiring[] = ['label' => current_domain() . '/' . $r['slug'], 'date' => substr((string) $r['premium_until'], 0, 10)];
        }
    }

    if ($total === 0 && !$forms && (int) $orders['n'] === 0 && !$expiring) {
        return null;
    }
    return [
        'links' => $rows, 'total' => $total, 'prev_total' => $prevTotal, 'free_clicks' => $freeClicks,
        'forms' => $forms, 'orders' => (int) $orders['n'], 'orders_total' => (int) $orders['total'], 'expiring' => $expiring,
    ];
}

/** HTML body for the member digest. */
function render_user_report(array $u, array $r, string $ym): string
{
    $month = report_month_label($ym);
    $h = '<p>' . e(t('mail_report_intro', ['month' => $month])) . '</p>';
    $h .= '<div style="font-size:40px;font-weight:800;color:#0b3d2e;line-height:1">' . number_format($r['total']) . ' <span style="font-size:16px;font-weight:600;color:#6b7280">' . e(t('mail_report_clicks')) . '</span></div>';
    if ($r['prev_total'] > 0 || $r['total'] > 0) {
        $h .= '<p style="color:#6b7280;margin:4px 0 20px">' . e(t('mail_report_vs', ['n' => number_format($r['prev_total'])])) . '</p>';
    }
    if ($r['links']) {
        $h .= '<table style="width:100%;border-collapse:collapse;font-size:14px">';
        foreach ($r['links'] as $row) {
            $l = $row['link'];
            $meta = [];
            if ($row['qr'] > 0) {
                $meta[] = t('mail_report_qr', ['n' => number_format($row['qr'])]);
            }
            if ($row['premium']) {
                if ($row['country'] !== '') {
                    $meta[] = t('mail_report_top_country', ['c' => $row['country']]);
                }
                if ($row['source'] !== '') {
                    $meta[] = t('mail_report_top_source', ['s' => $row['source']]);
                }
            }
            $delta = $row['prev'] > 0 ? ($row['n'] >= $row['prev'] ? '▲' : '▼') . ' ' . abs($row['n'] - $row['prev']) : '';
            $h .= '<tr><td style="padding:10px 0;border-top:1px solid #eee"><a href="' . e(base_url() . '/analytics?id=' . (int) $l['id']) . '" style="color:#0b3d2e;font-weight:700;text-decoration:none">' . e(short_display($l)) . '</a>'
                . ($l['title'] !== '' ? ' <span style="color:#6b7280">· ' . e($l['title']) . '</span>' : '')
                . ($meta ? '<br><span style="color:#6b7280;font-size:12.5px">' . e(implode(' · ', $meta)) . '</span>' : '')
                . '</td><td style="padding:10px 0;border-top:1px solid #eee;text-align:right;white-space:nowrap"><b>' . number_format($row['n']) . '</b>'
                . ($delta !== '' ? ' <span style="color:#9ca3af;font-size:12px">' . $delta . '</span>' : '') . '</td></tr>';
        }
        $h .= '</table>';
    }
    if ($r['forms']) {
        $h .= '<h3 style="font-size:15px;margin:22px 0 6px">' . e(t('nav_p_forms')) . '</h3><table style="width:100%;border-collapse:collapse;font-size:14px">';
        foreach ($r['forms'] as $f) {
            $h .= '<tr><td style="padding:8px 0;border-top:1px solid #eee"><b>' . e($f['domain'] . '/' . $f['slug']) . '</b> <span style="color:#6b7280">· ' . e($f['title']) . '</span></td><td style="padding:8px 0;border-top:1px solid #eee;text-align:right;white-space:nowrap">' . e(t('mail_report_forms', ['n' => number_format((int) $f['n'])])) . '</td></tr>';
        }
        $h .= '</table>';
    }
    if ($r['orders'] > 0) {
        $h .= '<p style="margin:18px 0 0"><b>' . e(t('nav_p_shops')) . ':</b> ' . e(t('mail_report_orders', ['n' => number_format($r['orders']), 'total' => 'RM' . number_format($r['orders_total'] / 100, 2)])) . '</p>';
    }
    if ($r['free_clicks'] > 0) {
        $h .= '<div style="background:#f0fdf4;border-radius:12px;padding:14px 16px;margin-top:22px;font-size:14px">✦ ' . e(t('mail_report_upsell', ['n' => number_format($r['free_clicks']), 'price' => price('premium')])) . ' <a href="' . e(base_url() . '/premium-links') . '" style="color:#0b3d2e;font-weight:700">' . e(t('pr2_learn')) . ' →</a></div>';
    }
    if ($r['expiring']) {
        $h .= '<div style="background:#fef3c7;border-radius:12px;padding:14px 16px;margin-top:14px;font-size:14px">';
        foreach ($r['expiring'] as $x) {
            $h .= '⏳ ' . e(t('mail_report_expiring', ['item' => $x['label'], 'date' => $x['date']])) . '<br>';
        }
        $h .= '</div>';
    }
    $unsub = base_url() . '/unsubscribe?t=' . issue_token((int) $u['id'], 'unsub', 86400 * 90);
    $h .= '<p style="color:#9ca3af;font-size:12px;margin-top:28px">' . e(t('mail_report_unsub')) . ' <a href="' . e($unsub) . '" style="color:#6b7280">' . e(t('mail_report_unsub_link')) . '</a></p>';
    return $h;
}

/** Send the digest to every opted-in member with activity. Returns log lines. */
function send_monthly_reports(string $ym): array
{
    $out = [];
    $sent = 0;
    $skipped = 0;
    foreach (db_rows('SELECT * FROM users WHERE notify_report = 1 ORDER BY id') as $u) {
        try {
            $r = user_month_report($u, $ym);
            if ($r === null) {
                $skipped++;
                continue;
            }
            $ok = send_mail((string) $u['email'], (string) $u['name'],
                t('mail_report_subject', ['site' => cfg('site_name'), 'month' => report_month_label($ym)]),
                mail_template(t('mail_report_title', ['month' => report_month_label($ym)]), render_user_report($u, $r, $ym), base_url() . '/dashboard', t('mail_report_cta')));
            $sent += $ok ? 1 : 0;
            if (!$ok) {
                $out[] = 'report failed: ' . $u['email'];
            }
        } catch (Throwable $e) {
            $out[] = 'report error ' . $u['email'] . ': ' . $e->getMessage();
        }
    }
    $out[] = "monthly reports $ym: sent $sent, skipped $skipped (no activity)";
    return $out;
}

/** Business summary for admins (English only). */
function send_admin_summary(string $ym): array
{
    [$from, $to] = report_month_range($ym);
    $admins = db_rows('SELECT * FROM users WHERE role = ?', [ROLE_ADMIN]);
    if (!$admins) {
        return ['admin summary: no admin users'];
    }
    $n = fn(string $sql, array $p = []) => (int) db_value($sql, $p);
    $signups = $n('SELECT COUNT(*) FROM users WHERE created_at >= ? AND created_at < ?', [$from, $to]);
    $paid = db_row('SELECT COUNT(*) AS n, COALESCE(SUM(amount), 0) AS sen FROM payments WHERE status = ? AND paid_at >= ? AND paid_at < ?', ['paid', $from, $to]);
    $clicks = $n('SELECT COUNT(*) FROM clicks WHERE is_bot = 0 AND created_at >= ? AND created_at < ?', [$from, $to]);
    $newLinks = $n('SELECT COUNT(*) FROM links WHERE created_at >= ? AND created_at < ?', [$from, $to]);
    $anonLinks = $n('SELECT COUNT(*) FROM links WHERE user_id IS NULL AND created_at >= ? AND created_at < ?', [$from, $to]);
    $responses = $n('SELECT COUNT(*) FROM form_responses WHERE created_at >= ? AND created_at < ?', [$from, $to]);
    $orders = $n('SELECT COUNT(*) FROM orders WHERE created_at >= ? AND created_at < ?', [$from, $to]);
    $soon = now(30 * 86400);
    $expiring = $n('SELECT COUNT(*) FROM links WHERE premium_until BETWEEN ? AND ?', [now(), $soon])
        + $n('SELECT COUNT(*) FROM forms WHERE premium_until BETWEEN ? AND ?', [now(), $soon])
        + $n('SELECT COUNT(*) FROM shops WHERE premium_until BETWEEN ? AND ?', [now(), $soon]);
    $activePremium = $n('SELECT COUNT(*) FROM links WHERE premium_until > ?', [now()]) + $n('SELECT COUNT(*) FROM forms WHERE premium_until > ?', [now()]) + $n('SELECT COUNT(*) FROM shops WHERE premium_until > ?', [now()]);
    $users = $n('SELECT COUNT(*) FROM users');

    $row = fn(string $k, string $v) => '<tr><td style="padding:8px 0;border-top:1px solid #eee">' . e($k) . '</td><td style="padding:8px 0;border-top:1px solid #eee;text-align:right"><b>' . e($v) . '</b></td></tr>';
    $html = '<p>' . e(report_month_label($ym)) . ' at a glance.</p><table style="width:100%;border-collapse:collapse;font-size:14px">'
        . $row('New sign-ups', number_format($signups))
        . $row('Payments', number_format((int) $paid['n']) . ' · RM' . number_format((int) $paid['sen'] / 100, 2))
        . $row('Clicks (all links)', number_format($clicks))
        . $row('New links', number_format($newLinks) . ' (' . number_format($anonLinks) . ' by guests)')
        . $row('Form submissions', number_format($responses))
        . $row('Shop orders', number_format($orders))
        . $row('Premium items active now', number_format($activePremium))
        . $row('Expiring in the next 30 days', number_format($expiring))
        . $row('Total users', number_format($users))
        . '</table>';
    $out = [];
    foreach ($admins as $a) {
        $ok = send_mail((string) $a['email'], (string) $a['name'], cfg('site_name') . ' admin summary · ' . report_month_label($ym),
            mail_template('Admin summary · ' . report_month_label($ym), $html, base_url() . '/admin?tab=payments', 'Open admin'));
        $out[] = 'admin summary → ' . $a['email'] . ': ' . ($ok ? 'sent' : 'failed');
    }
    return $out;
}
