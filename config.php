<?php
/**
 * WSAP — base configuration (safe to commit).
 * Real secrets live in config.local.php (gitignored, server-only), which
 * returns an array merged over these defaults. Never commit secrets here.
 */
return [
  'env'       => 'production',          // 'development' on local
  'site_name' => 'WSAP',

  // Every domain that serves this install. The first one is the default.
  // 'lang' = default UI language, 'currency' = default pricing display.
  'domains' => [
    'wsap.me' => ['lang' => 'en', 'currency' => 'USD', 'label' => 'International'],
    'wsap.my' => ['lang' => 'ms', 'currency' => 'MYR', 'label' => 'Malaysia'],
  ],

  'db' => [
    'driver'  => 'mysql',               // 'sqlite' for local dev (see dev/)
    'host'    => 'localhost',
    'name'    => '',
    'user'    => '',
    'pass'    => '',
    'charset' => 'utf8mb4',
    'sqlite'  => __DIR__ . '/dev/wsap.sqlite',
  ],

  // Random string used to hash visitor IPs in analytics. Set in config.local.php.
  'ip_salt' => '',

  // Short codes: 6 chars from an alphabet without look-alikes (no 0/o/1/l/i).
  'code_alphabet' => 'abcdefghjkmnpqrstuvwxyz23456789',
  'code_length'   => 6,
  // Minimum length for custom handles / form / shop addresses (admin can override in Settings).
  'slug_min'      => 4,
  'smart_max_destinations' => 8,   // WhatsApp numbers per Smart Link (admin-editable in Settings; 0 = unlimited)

  // Plans. Prices are per LINK per YEAR (walink style) in the two display currencies.
  // These are defaults only: admin can change prices and limits at /admin?tab=settings
  // (stored in the settings table). max_links 0 = unlimited.
  'plans' => [
    'free'     => ['name' => 'Free',     'usd' => 0,  'myr' => 0,  'max_links' => 0],
    'premium'  => ['name' => 'Premium',  'usd' => 9,  'myr' => 39, 'max_links' => 0],
    'business' => ['name' => 'Business', 'usd' => 19, 'myr' => 79, 'max_links' => 0],
    'form'     => ['name' => 'Form',     'usd' => 29, 'myr' => 119, 'max_links' => 0],  // per published form, per year
    'shop'     => ['name' => 'Shop',     'usd' => 49, 'myr' => 199, 'max_links' => 0],  // per published shop, per year
    'smart'    => ['name' => 'Smart Links', 'usd' => 5, 'myr' => 19, 'max_links' => 0, 'period' => 'month'],  // add-on per link, PER MONTH (billed yearly ×12)
  ],

  // Billplz (https://www.billplz.com → Settings → Account → API Secret Key, X Signature Key; Billing → Collections)
  'billplz' => [
    'api_key'       => '',
    'x_signature'   => '',
    'collection_id' => '',
    'sandbox'       => false,   // true = billplz-sandbox.com
  ],
  // Shared secret for cron.php?key=... (cPanel cron runs it daily)
  'cron_key' => '',

  // Support WhatsApp (international format, digits only) shown in footer / upgrade.
  'support_whatsapp' => '',

  // Brevo transactional email (https://app.brevo.com → SMTP & API → API keys)
  'brevo' => [
    'api_key'    => '',
    'from_email' => 'hello@wsap.me',
    'from_name'  => 'WSAP',
  ],

  // Google sign-in (Google Cloud Console → OAuth client, type "Web application").
  // Authorised redirect URIs: https://wsap.me/auth/google.php and https://wsap.my/auth/google.php
  'google' => [
    'client_id'     => '',
    'client_secret' => '',
  ],

  'ga_id' => '',
  'google_site_verification' => '',
];
