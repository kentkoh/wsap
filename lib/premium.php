<?php
declare(strict_types=1);

/**
 * Per-link Premium (walink model: pay per link, per year) + applying payments.
 * A link is Premium when links.premium_until is in the future, OR its owner has an
 * account-level plan (premium/business) set by admin.
 */

function link_is_premium(array $link, ?array $owner = null): bool
{
    if (!empty($link['premium_until']) && $link['premium_until'] > now()) {
        return true;
    }
    if ($owner && is_premium($owner)) {
        return true;
    }
    return false;
}

/** Premium price in sen (MYR), from config plans.premium.myr. */
function premium_price_sen(string $plan = 'premium'): int
{
    return (int) round(((float) plan_cfg($plan)['myr']) * 100);
}

/** Start a checkout for a link: creates a pending payment + Billplz bill, returns the bill URL. */
function start_premium_checkout(array $user, array $link, string $handle = '', int $years = 1): string
{
    $handle = strtolower(trim($handle));
    if ($handle !== '' && !(int) $link['is_custom']) {
        if (!valid_custom_code($handle)) {
            throw new InvalidArgumentException(t('err_code_invalid'));
        }
        if ($handle !== $link['code'] && db_value('SELECT 1 FROM links WHERE code = ?', [$handle])) {
            throw new InvalidArgumentException(t('err_code_taken'));
        }
    } else {
        $handle = '';
    }
    return start_checkout($user, 'link', (int) $link['id'], 'premium', 'WSAP Premium link ' . short_display($link), $years, $handle);
}

/** Start a checkout for a form (plan 'form', per form per year). */
function start_form_checkout(array $user, array $form, int $years = 1): string
{
    return start_checkout($user, 'form', (int) $form['id'], 'form', 'WSAP Form ' . $form['domain'] . '/f/' . $form['slug'], $years);
}

/** Generic: pending payment row + Billplz bill. Returns the bill URL. */
function start_checkout(array $user, string $itemType, int $itemId, string $plan, string $desc, int $years = 1, string $handle = ''): string
{
    require_once WSAP_ROOT . '/lib/billplz.php';
    $years = max(1, min(3, $years));
    $amount = premium_price_sen($plan) * $years;
    if ($amount <= 0) {
        throw new RuntimeException('Price not set');
    }
    $desc .= ' × ' . $years . ' year' . ($years > 1 ? 's' : '');
    $bill = billplz_create_bill((string) $user['email'], (string) $user['name'], $amount, $desc, $itemType . ':' . $itemId);
    db_insert(
        'INSERT INTO payments (user_id, link_id, item_type, item_id, bill_id, provider, amount, currency, status, handle, years, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
        [(int) $user['id'], $itemType === 'link' ? $itemId : null, $itemType, $itemId, $bill['id'], 'billplz', $amount, 'MYR', 'pending', $handle, $years, now()]
    );
    return $bill['url'];
}

/** Add $years to a premium_until value (from now if expired). */
function extend_until(?string $current, int $years): string
{
    $from = ($current && $current > now()) ? strtotime($current . ' UTC') : time();
    return gmdate('Y-m-d H:i:s', $from + 365 * 86400 * $years);
}

/**
 * Mark a payment paid and extend the item. Idempotent: a bill already marked paid is ignored.
 * Returns the updated payment row or null when nothing changed.
 */
function apply_paid_bill(string $billId, ?string $paidAt = null): ?array
{
    $p = db_row('SELECT * FROM payments WHERE bill_id = ?', [$billId]);
    if (!$p || $p['status'] === 'paid') {
        return null;
    }
    db_exec('UPDATE payments SET status = ?, paid_at = ? WHERE id = ?', ['paid', $paidAt ?? now(), (int) $p['id']]);
    $type = $p['item_type'] ?? 'link';
    $itemId = (int) ($p['item_id'] ?? $p['link_id'] ?? 0);
    $label = '';
    $until = '';
    if ($type === 'link' && $itemId) {
        $link = db_row('SELECT * FROM links WHERE id = ?', [$itemId]);
        if ($link) {
            $until = extend_until($link['premium_until'] ?? null, (int) $p['years']);
            $code = $link['code'];
            $custom = (int) $link['is_custom'];
            if ($p['handle'] !== '' && !$custom && valid_custom_code($p['handle'])
                && !db_value('SELECT 1 FROM links WHERE code = ? AND id <> ?', [$p['handle'], $itemId])) {
                $code = $p['handle'];
                $custom = 1;
            }
            db_exec('UPDATE links SET premium_until = ?, code = ?, is_custom = ?, active = 1, updated_at = ? WHERE id = ?',
                [$until, $code, $custom, now(), $itemId]);
            $label = $link['domain'] . '/' . $code;
        }
    } elseif ($type === 'form' && $itemId) {
        $form = db_row('SELECT * FROM forms WHERE id = ?', [$itemId]);
        if ($form) {
            $until = extend_until($form['premium_until'] ?? null, (int) $p['years']);
            db_exec('UPDATE forms SET premium_until = ?, active = 1, updated_at = ? WHERE id = ?', [$until, now(), $itemId]);
            $label = $form['domain'] . '/f/' . $form['slug'];
        }
    }
    try {
        require_once WSAP_ROOT . '/lib/mail.php';
        $u = db_row('SELECT * FROM users WHERE id = ?', [(int) $p['user_id']]);
        if ($u && $label !== '') {
            send_mail((string) $u['email'], (string) $u['name'], t('mail_receipt_subject', ['site' => cfg('site_name')]),
                mail_template(t('mail_receipt_title'), '<p>' . t($type === 'form' ? 'mail_receipt_body_form' : 'mail_receipt_body', ['link' => $label, 'until' => substr($until, 0, 10), 'amount' => 'RM' . number_format($p['amount'] / 100, 2)]) . '</p>', base_url() . '/dashboard', t('nav_dashboard')));
        }
    } catch (Throwable $e) {
        error_log('receipt mail: ' . $e->getMessage());
    }
    return db_row('SELECT * FROM payments WHERE id = ?', [(int) $p['id']]);
}

/** Re-check a pending bill with Billplz and apply it if paid. */
function sync_bill(string $billId): ?array
{
    require_once WSAP_ROOT . '/lib/billplz.php';
    $b = billplz_get_bill($billId);
    if (($b['paid'] ?? false) === true) {
        return apply_paid_bill($billId, isset($b['paid_at']) ? gmdate('Y-m-d H:i:s', strtotime((string) $b['paid_at'])) : null);
    }
    if (($b['state'] ?? '') === 'deleted') {
        db_exec('UPDATE payments SET status = ? WHERE bill_id = ? AND status = ?', ['failed', $billId, 'pending']);
    }
    return null;
}
