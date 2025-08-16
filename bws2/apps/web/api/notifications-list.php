<?php
require_once __DIR__ . '/../../../packages/auth/session-middleware.php';
require_once __DIR__ . '/../../../packages/shared/db.php';

$user = auth_require(); // any signed in user
$username = $user['sub'];

$limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 20;

$pdo = pdo_mysql();
$stmt = $pdo->prepare("SELECT id, type, payload, created_at, read_at FROM bws2_notifications WHERE recipient=? AND status IN ('queued','sent') ORDER BY id DESC LIMIT ?");
$stmt->execute([$username, $limit]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($rows as &$r) {
  $r['payload'] = json_decode($r['payload'], true);
}

header('Content-Type: application/json');
echo json_encode(['items'=>$rows]);
