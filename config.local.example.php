<?php
/**
 * Copy to config.local.php on the SERVER only (gitignored).
 * Only override what differs from config.php defaults.
 */
return [
  'env' => 'production',
  'db' => [
    'name' => 'kuantan1_wsap',
    'user' => 'kuantan1_wsap',
    'pass' => 'DBPASS_HERE',
  ],
  'ip_salt' => 'CHANGE_ME_random_string',
  'support_whatsapp' => '60123456789',
  'brevo'  => ['api_key' => 'xkeysib-...'],
  'google' => ['client_id' => '....apps.googleusercontent.com', 'client_secret' => 'GOCSPX-...'],
];
