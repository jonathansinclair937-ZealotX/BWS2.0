<?php
function env_load($path) {
  static $cache = null;
  if ($cache !== null) return $cache;
  $cache = [];
  if (file_exists($path)) {
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
      if (strpos($line, '=') !== false) {
        list($k,$v) = array_map('trim', explode('=', $line, 2));
        $cache[$k] = $v;
      }
    }
  }
  return $cache;
}
function env_get($key, $default='') {
  static $env = null;
  if ($env === null) $env = env_load(__DIR__ . '/../../.env');
  return isset($env[$key]) ? $env[$key] : (getenv($key) ?: $default);
}
function pdo_mysql() {
  $dsn = sprintf("mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4",
    env_get('DB_HOST','127.0.0.1'), env_get('DB_PORT','3306'), env_get('DB_NAME','bws2'));
  $pdo = new PDO($dsn, env_get('DB_USER','root'), env_get('DB_PASS',''), [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
  return $pdo;
}
