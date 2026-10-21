<?php
declare(strict_types=1);

/**
 * Billplz v3 API (https://www.billplz.com/api) — create bills, fetch bills, verify X-Signature.
 * Amounts are in sen. Billplz charges MYR only (FPX, cards, e-wallets).
 */

function billplz_enabled(): bool
{
    return (string) cfg('billplz.api_key') !== '' && (string) cfg('billplz.collection_id') !== '';
}

function billplz_base(): string
{
    return cfg('billplz.sandbox') ? 'https://www.billplz-sandbox.com/api/v3' : 'https://www.billplz.com/api/v3';
}

function billplz_request(string $method, string $path, array $data = []): array
{
    $ch = curl_init(billplz_base() . $path);
    $opts = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_USERPWD        => cfg('billplz.api_key') . ':',
    ];
    if ($method === 'POST') {
        $opts[CURLOPT_POST] = true;
        $opts[CURLOPT_POSTFIELDS] = http_build_query($data);
    }
    curl_setopt_array($ch, $opts);
    $body = (string) curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    $json = json_decode($body, true);
    if ($status < 200 || $status >= 300 || !is_array($json)) {
        error_log("Billplz $method $path failed ($status): $err $body");
        throw new RuntimeException('Payment provider error');
    }
    return $json;
}

/** Create a bill. Returns ['id' => ..., 'url' => ...]. */
function billplz_create_bill(string $email, string $name, int $amountSen, string $description, string $reference): array
{
    $r = billplz_request('POST', '/bills', [
        'collection_id'     => cfg('billplz.collection_id'),
        'email'             => $email,
        'name'              => $name !== '' ? mb_substr($name, 0, 60) : 'WSAP customer',
        'amount'            => $amountSen,
        'description'       => mb_substr($description, 0, 200),
        'callback_url'      => base_url() . '/payments/callback.php',
        'redirect_url'      => base_url() . '/payments/return.php',
        'reference_1_label' => 'WSAP',
        'reference_1'       => $reference,
    ]);
    return ['id' => (string) $r['id'], 'url' => (string) $r['url']];
}

function billplz_get_bill(string $billId): array
{
    if (!preg_match('/^[a-z0-9_]{3,40}$/i', $billId)) {
        throw new InvalidArgumentException('bad bill id');
    }
    return billplz_request('GET', '/bills/' . $billId);
}

/**
 * Verify X-Signature. $data = the key/value pairs Billplz sent (without x_signature),
 * $prefix = 'billplz' for the redirect (GET billplz[...] params), '' for the callback POST.
 */
function billplz_verify_signature(array $data, string $signature, string $prefix = ''): bool
{
    $key = (string) cfg('billplz.x_signature');
    if ($key === '' || $signature === '') {
        return false;
    }
    $parts = [];
    foreach ($data as $k => $v) {
        $parts[] = $prefix . $k . '=' . $v;
    }
    sort($parts, SORT_STRING);
    $computed = hash_hmac('sha256', implode('|', $parts), $key);
    return hash_equals($computed, $signature);
}
