<?php
// Local development overrides. dev/run.sh copies this to config.local.php (gitignored).
return [
  'env' => 'development',
  'db'  => ['driver' => 'sqlite'],
  'ip_salt' => 'dev-salt',
  'support_whatsapp' => '60123456789',
];
