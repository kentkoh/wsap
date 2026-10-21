<?php
declare(strict_types=1);
/**
 * Google sign-in (OAuth 2.0 authorization code flow, no SDK).
 * Redirect URI to register in Google Cloud Console: https://wsap.me/auth/google.php (and wsap.my)
 */
require dirname(__DIR__) . '/lib/bootstrap.php';

$clientId = (string) cfg('google.client_id');
$secret = (string) cfg('google.client_secret');
if ($clientId === '' || $secret === '') {
    flash('error', t('auth_google_off'));
    redirect('/login');
}
$redirectUri = base_url() . '/auth/google.php';
session_start_safe();

if (!isset($_GET['code'])) {
    $state = bin2hex(random_bytes(16));
    $_SESSION['g_state'] = $state;
    $params = [
        'client_id'     => $clientId,
        'redirect_uri'  => $redirectUri,
        'response_type' => 'code',
        'scope'         => 'openid email profile',
        'state'         => $state,
        'prompt'        => 'select_account',
    ];
    redirect('https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params));
}

if (!hash_equals($_SESSION['g_state'] ?? '', (string) ($_GET['state'] ?? ''))) {
    flash('error', t('auth_google_err'));
    redirect('/login');
}
unset($_SESSION['g_state']);

require WSAP_ROOT . '/lib/http.php';
$tokRes = http_post_form('https://oauth2.googleapis.com/token', [
    'code'          => (string) $_GET['code'],
    'client_id'     => $clientId,
    'client_secret' => $secret,
    'redirect_uri'  => $redirectUri,
    'grant_type'    => 'authorization_code',
]);
$tok = json_decode($tokRes['body'], true);
$idToken = $tok['id_token'] ?? null;
if (!is_string($idToken)) {
    error_log('google token: ' . json_encode($tok));
    flash('error', t('auth_google_err'));
    redirect('/login');
}

// Validate the id_token with Google's tokeninfo endpoint (simple, no JWKS handling needed).
$infoRes = http_request('https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($idToken));
$info = json_decode($infoRes['body'], true);

if (!is_array($info) || ($info['aud'] ?? '') !== $clientId || empty($info['sub']) || empty($info['email']) || ($info['email_verified'] ?? 'false') !== 'true') {
    error_log('google tokeninfo: ' . json_encode($info));
    flash('error', t('auth_google_err'));
    redirect('/login');
}

try {
    $user = user_from_google((string) $info['sub'], (string) $info['email'], (string) ($info['name'] ?? ''));
    login_user($user);
} catch (Throwable $e) {
    error_log('google user: ' . $e->getMessage());
    flash('error', t('err_generic'));
    redirect('/login');
}
redirect(after_login_url());
