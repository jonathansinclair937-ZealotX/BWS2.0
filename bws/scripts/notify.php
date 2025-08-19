<?php
// bws/scripts/notify.php
declare(strict_types=1);

/**
 * Notification dispatcher (placeholder implementation).
 * - Kept intentionally simple so CI (php -l) passes without syntax errors.
 * - You can wire real mail/DB logic later.
 */

// Exit early if run accidentally on production without config
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo "Forbidden\n";
    exit(0);
}

echo "[notify] Placeholder script loaded.\n";

// If you want to implement real logic later, start here:
// 1) Load environment and DB connection
// require_once __DIR__ . '/../packages/shared/db.php';
// require_once __DIR__ . '/../packages/shared/mailer.php';
//
// 2) Fetch pending notifications
// $pdo = \Bws\Shared\Db::pdo();
// $rows = $pdo->query("SELECT id, user_id, message FROM notifications WHERE read_at IS NULL LIMIT 100")->fetchAll();
//
// 3) Send emails or in-app notifications
// foreach ($rows as $n) { /* ... */ }
//
// 4) Mark as sent/read as appropriate
// $pdo->prepare("UPDATE notifications SET read_at = NOW() WHERE id = ?")->execute([$n['id']]);

echo "[notify] Done.\n";
exit(0);
