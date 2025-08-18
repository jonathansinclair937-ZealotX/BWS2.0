<?php
require_once __DIR__ . '/../../../packages/auth/session-middleware.php';
require_once __DIR__ . '/../../../packages/shared/db.php';

$user = auth_require();
$username = $user['sub'];

$body = json_decode(file_get_contents('php://input'), true);
$ids = $body['ids'] ?? [];
if (!is_array($ids) || empty($ids)) { http_response_code(400); echo 'invalid_ids'; exit; }

$placeholders = implode(',', array_fill(0, count($ids), '?'));
$params = $ids;
array_unshift($params, $username);

$pdo = pdo_mysql();
$stmt = $pdo->prepare("UPDATE bws2_notifications SET read_at=NOW() WHERE recipient=? AND id IN ($placeholders)");
$stmt->execute($params);

header('Content-Type: application/json');
echo json_encode(['ok'=>true, 'updated'=>$stmt->rowCount()]);
