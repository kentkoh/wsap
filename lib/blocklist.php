<?php
declare(strict_types=1);

/**
 * Handle policy for custom slugs (Premium links, forms, shops).
 *  - minimum length from config 'slug_min' (default 4)
 *  - trademarks: blocked when the slug IS the word or a hyphen/digit-separated part of it
 *    (e.g. "whatsapp", "whatsapp-my", "shopee2")
 *  - profanity (EN + BM): blocked when the word appears anywhere in the slug
 *  - admin can add more words at /admin?tab=settings (settings key 'blocked_words', one per line,
 *    prefix with "*" to block as substring)
 */

const BLOCK_TRADEMARKS = [
    'whatsapp', 'meta', 'facebook', 'instagram', 'tiktok', 'youtube', 'google', 'gmail', 'apple',
    'microsoft', 'amazon', 'netflix', 'spotify', 'telegram', 'twitter', 'snapchat', 'wechat',     'shopee', 'lazada', 'grab', 'foodpanda', 'carousell', 'mudah', 'zalora', 'tng', 'touchngo', 'maybank',
    'cimb', 'rhb', 'publicbank', 'hongleong', 'bankislam', 'ambank', 'billplz', 'ipay88', 'stripe', 'paypal',
    'walink', 'wsap', 'bitly', 'linktree', 'canva', 'samsung', 'huawei', 'xiaomi', 'oppo', 'vivo', 'nike', 'adidas',
    'coca-cola', 'cocacola', 'pepsi', 'mcdonalds', 'mcd', 'kfc', 'starbucks', 'petronas', 'celcom', 'maxis', 'digi',
    'umobile', 'unifi', 'astro', 'line', 'airasia', 'bmw', 'toyota', 'honda', 'perodua', 'proton',
    'gov', 'govmy', 'lhdn', 'jpj', 'pdrm', 'polis', 'police', 'kkm', 'moh', 'bnm', 'kwsp', 'epf', 'socso', 'perkeso',
    'admin', 'official', 'verified', ];

const BLOCK_PROFANITY = [
    'fuck', 'fuk', 'shit', 'bitch', 'cunt', 'dick', 'cock', 'pussy', 'porn', 'sex', 'xxx', 'nude', 'slut', 'whore',
    'nigger', 'nigga', 'faggot', 'rape', 'nazi', 'hitler', 'isis', 'jihad', 'terror',
    'bodoh', 'babi', 'pukimak', 'puki', 'kimak', 'pantat', 'sial', 'celaka', 'bangsat', 'lancau', 'lanjiao',
    'kote', 'konek', 'pelacur', 'sundal', 'jalang', 'bohsia', 'keling', 'cibai', 'cb', 'knn', 'kanina', 'lanjia',
    'judi', 'casino', 'togel', 'scam', 'phishing',
];

/** Short words that are also parts of innocent words (babies, sosial, crisis, grapes): only block as a whole segment. */
const BLOCK_PART_ONLY = ['babi', 'sial', 'sex', 'kote', 'puki', 'judi', 'dick', 'cock', 'rape', 'nazi', 'isis', 'scam', 'cb', 'knn', 'xxx', 'nude', 'slut', 'porn'];

/** Words the admin added (settings 'blocked_words'). Returns [exact => [...], contains => [...]]. */
function admin_blocklist(): array
{
    $exact = [];
    $contains = [];
    foreach (preg_split('/\r?\n/', setting('blocked_words')) ?: [] as $w) {
        $w = strtolower(trim($w));
        if ($w === '') {
            continue;
        }
        if (str_starts_with($w, '*')) {
            $contains[] = ltrim($w, '* ');
        } else {
            $exact[] = $w;
        }
    }
    return ['exact' => $exact, 'contains' => $contains];
}

/** Why a slug is blocked (translation key), or '' when it is fine. */
function slug_block_reason(string $slug): string
{
    $slug = strtolower($slug);
    $parts = array_filter(preg_split('/[-\d]+/', $slug) ?: []);
    $compact = str_replace('-', '', $slug);
    $admin = admin_blocklist();

    foreach (array_merge(BLOCK_TRADEMARKS, $admin['exact']) as $w) {
        if ($slug === $w || $compact === $w || in_array($w, $parts, true)) {
            return 'err_code_trademark';
        }
    }
    foreach (array_merge(BLOCK_PROFANITY, $admin['contains']) as $w) {
        $whole = $slug === $w || in_array($w, $parts, true);
        if ($whole || (strlen($w) >= 4 && !in_array($w, BLOCK_PART_ONLY, true) && str_contains($compact, $w))) {
            return 'err_code_offensive';
        }
    }
    return '';
}

/**
 * Premium handles: generic words sold separately. Returns the row when $slug is premium and
 * NOT assigned to $userId (so the caller must refuse it), or null when it may be used.
 */
function premium_slug_block(string $slug, ?int $userId): ?array
{
    try {
        $row = db_row('SELECT * FROM premium_slugs WHERE slug = ?', [strtolower($slug)]);
    } catch (Throwable $e) {
        return null; // table not installed yet
    }
    if (!$row) {
        return null;
    }
    if ($userId !== null && (int) ($row['assigned_user_id'] ?? 0) === $userId) {
        return null;
    }
    return $row;
}

function slug_min_length(): int
{
    $n = (int) (setting('slug_min') ?: cfg('slug_min'));
    return max(3, min(10, $n ?: 4));
}
