<?php
require_once __DIR__ . '/../../../packages/auth/session-middleware.php';
require_once __DIR__ . '/../../../packages/shared/db.php';

$user = auth_require(); // any signed in user
$username = $user['sub'];

$pdo = pdo_mysql();
$stmt = $pdo->prepare("SELECT COUNT(1) FROM bws2_notifications WHERE recipient=? AND status='queued' AND read_at IS NULL");
$stmt->execute([$username]);
$count = intval($stmt->fetchColumn());

header('Content-Type: application/json');
echo json_encode(['count'=>$count]);
