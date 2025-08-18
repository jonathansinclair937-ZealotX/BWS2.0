<?php
require_once __DIR__ . '/../../../packages/shared/db.php';
header('Content-Type: application/json');

$sort = $_GET['sort'] ?? 'weighted';
$userLat = isset($_GET['lat']) ? floatval($_GET['lat']) : null;
$userLon = isset($_GET['lon']) ? floatval($_GET['lon']) : null;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;

$pdo = pdo_mysql();

if ($userLat !== null && $userLon !== null) {
  $sql = "SELECT b.*, (6371 * acos(cos(radians(:ulat)) * cos(radians(b.lat)) * cos(radians(b.lon) - radians(:ulon)) + sin(radians(:ulat)) * sin(radians(b.lat)))) AS distance_km
          FROM bws2_businesses b";
  $params = [':ulat'=>$userLat, ':ulon'=>$userLon];
} else {
  $sql = "SELECT b.*, NULL AS distance_km FROM bws2_businesses b";
  $params = [];
}

$order = "";
if ($sort === 'distance' && $userLat !== null) $order = " ORDER BY distance_km ASC";
elseif ($sort === 'reviews') $order = " ORDER BY rating_avg DESC, rating_count DESC";
elseif ($sort === 'ownership') $order = " ORDER BY black_ownership_pct DESC, rating_avg DESC";

$sql .= $order . " LIMIT " . intval($limit);
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

if ($sort === 'weighted') {
  $now = time();
  foreach ($rows as &$r) {
    $score = 0;
    $score += intval($r['black_ownership_pct']) * 2;
    $score += intval($r['boosted']) ? 50 : 0;
    $score += floatval($r['rating_avg']) * 10;
    if ($userLat !== null && $r['distance_km'] !== null) $score -= floatval($r['distance_km']) * 2;
    $freshDays = max(0, (int) round(($now - strtotime($r['updated_at'])) / 86400));
    $score += max(0, 30 - $freshDays);
    $r['score'] = $score;
  }
  usort($rows, fn($a,$b) => $b['score'] <=> $a['score']);
}

echo json_encode(['items'=>array_map(function($r){
  return [
    'id'=>(int)$r['id'],'name'=>$r['name'],'logo'=>$r['logo'],'header'=>$r['header'],
    'description'=>$r['description'],'black_ownership_pct'=>(int)$r['black_ownership_pct'],
    'rating_avg'=>(float)$r['rating_avg'],'rating_count'=>(int)$r['rating_count'],
    'boosted'=>(bool)$r['boosted'],'distance'=>isset($r['distance_km'])?(float)$r['distance_km']:null,
    'updated_at'=>$r['updated_at'] ?? null,'score'=>isset($r['score'])?(float)$r['score']:null
  ];
}, $rows)]);
