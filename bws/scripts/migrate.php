<?php
require_once __DIR__ . '/../packages/shared/db.php';
try {
  $pdo = pdo_mysql();
  $dir = __DIR__ . '/../database/migrations';
  $files = array_filter(scandir($dir), fn($f) => preg_match('/\.sql$/',$f));
  sort($files);
  foreach ($files as $f) {
    $sql = file_get_contents($dir . '/' . $f);
    echo "Applying migration: $f\n";
    $pdo->exec($sql);
  }
  echo "Migrations complete.\n";
} catch (Throwable $e) {
  http_response_code(500);
  echo "Migration failed: " . $e->getMessage() . "\n";
  exit(1);
}
