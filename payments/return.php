<?php
declare(strict_types=1);
/** Billplz redirect_url: the customer lands here after paying. Verifies the signature, then confirms with the API. */
require dirname(__DIR__) . '/lib/bootstrap.php';
require dirname(__DIR__) . '/lib/billplz.php';

$b = $_GET['billplz'] ?? null;
if (!is_array($b) || empty($b['id'])) {
    redirect('/dashboard');
}
$sig = (string) ($b['x_signature'] ?? '');
$data = ['id' => (string) $b['id'], 'paid' => (string) ($b['paid'] ?? ''), 'paid_at' => (string) ($b['paid_at'] ?? '')];
$billId = (string) $b['id'];

try {
    if (!billplz_verify_signature($data, $sig, 'billplz')) {
        error_log('billplz return: bad signature for ' . $billId);
    }
    // Always confirm with the API; the redirect alone is not trusted.
    $p = sync_bill($billId);
    $row = db_row('SELECT * FROM payments WHERE bill_id = ?', [$billId]);
    if ($row && $row['status'] === 'paid') {
        flash('ok', t('up_paid'));
        redirect($row['link_id'] ? '/qr?id=' . (int) $row['link_id'] : '/dashboard');
    }
    flash('warn', t('up_not_paid'));
} catch (Throwable $e) {
    error_log('billplz return: ' . $e->getMessage());
    flash('warn', t('up_not_paid'));
}
redirect('/dashboard');
