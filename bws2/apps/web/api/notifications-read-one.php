<?php
require_once __DIR__ . '/../../../packages/auth/session-middleware.php';
require_once __DIR__ . '/../../../packages/shared/db.php';

$user = auth_require();
$username = $user['sub'];

$body = json_decode(file_get_contents('php://input'), true);
$id = isset($body['id']) ? intval($body['id']) : 0;
if ($id <= 0) { http_response_code(400); echo 'invalid_id'; exit; }

$pdo = pdo_mysql();
$stmt = $pdo->prepare("UPDATE bws2_notifications SET read_at=NOW() WHERE recipient=? AND id=?");
$stmt->execute([$username, $id]);

header('Content-Type: application/json');
echo json_encode(['ok'=>true, 'updated'=>$stmt->rowCount()]);
