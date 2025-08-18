<?php
require_once __DIR__ . '/../../../packages/shared/db.php';
header('Content-Type: application/json');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) { http_response_code(400); echo json_encode(['error'=>'invalid_id']); exit; }

$pdo = pdo_mysql();

$stmt = $pdo->prepare("SELECT b.*, JSON_ARRAYAGG(JSON_OBJECT('url', COALESCE(l.url,''), 'label', COALESCE(l.label,''))) AS links_json
  FROM bws2_businesses b
  LEFT JOIN bws2_links l ON l.business_id = b.id
  WHERE b.id=?");
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) { http_response_code(404); echo json_encode(['error'=>'not_found']); exit; }

$links = json_decode($row['links_json'] ?? '[]', true) ?: [];
echo json_encode([
  'id'=>(int)$row['id'],
  'name'=>$row['name'],
  'logo'=>$row['logo'],
  'header'=>$row['header'],
  'description'=>$row['description'],
  'black_ownership_pct'=>(int)$row['black_ownership_pct'],
  'rating_avg'=>(float)$row['rating_avg'],
  'rating_count'=>(int)$row['rating_count'],
  'boosted'=>(bool)$row['boosted'],
  'lat'=>$row['lat'],
  'lon'=>$row['lon'],
  'links'=>$links
]);
