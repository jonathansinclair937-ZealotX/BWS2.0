<?php
// Stripe config loader (prototype). In production, use a proper env loader.
function env($key, $default='') {
  $envFile = __DIR__ . '/../../../.env';
  static $cache = null;
  if ($cache === null && file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
      if (strpos($line, '=') !== false) {
        list($k,$v) = array_map('trim', explode('=', $line, 2));
        $cache[$k] = $v;
      }
    }
  }
  return $cache[$key] ?? getenv($key) ?: $default;
}

define('STRIPE_PUBLISHABLE_KEY', env('STRIPE_PUBLISHABLE_KEY', 'pk_test_xxx'));
define('STRIPE_SECRET_KEY', env('STRIPE_SECRET_KEY', 'sk_test_xxx'));
define('STRIPE_PRICE_BASIC', env('STRIPE_PRICE_BASIC', 'price_basic_xxx'));
define('STRIPE_PRICE_PRO', env('STRIPE_PRICE_PRO', 'price_pro_xxx'));
define('STRIPE_WEBHOOK_SECRET', env('STRIPE_WEBHOOK_SECRET', 'whsec_xxx'));
define('APP_BASE_URL', rtrim(env('APP_BASE_URL', 'http://localhost:8000'), '/'));
define('SUCCESS_PATH', env('SUCCESS_PATH', '/apps/web/subscriptions-success.html'));
define('CANCEL_PATH', env('CANCEL_PATH', '/apps/web/subscriptions-cancel.html'));
?>