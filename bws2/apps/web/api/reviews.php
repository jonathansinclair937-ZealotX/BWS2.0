<?php
require_once __DIR__ . '/../../../packages/shared/db.php';
header('Content-Type: application/json');

$businessId = isset($_GET['business_id']) ? intval($_GET['business_id']) : null;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;

$pdo = pdo_mysql();
if ($businessId) {
  $stmt = $pdo->prepare("SELECT r.id, r.username, r.rating, r.text, r.created_at, b.name as business, b.logo
                         FROM bws2_reviews r JOIN bws2_businesses b ON b.id = r.business_id
                         WHERE r.business_id = ? ORDER BY r.rating DESC, r.created_at DESC LIMIT ?");
  $stmt->execute([$businessId, $limit]);
} else {
  $stmt = $pdo->prepare("SELECT r.id, r.username, r.rating, r.text, r.created_at, b.name as business, b.logo
                         FROM bws2_reviews r JOIN bws2_businesses b ON b.id = r.business_id
                         ORDER BY r.rating DESC, r.created_at DESC LIMIT ?");
  $stmt->execute([$limit]);
}
echo json_encode(['items'=>$stmt->fetchAll()]);
