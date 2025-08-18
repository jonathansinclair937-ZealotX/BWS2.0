<?php
require_once __DIR__ . '/../../../packages/auth/session-middleware.php';
require_once __DIR__ . '/../../../packages/shared/db.php';

$pdo = pdo_mysql();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
  $user = auth_require(); // any signed-in to view *their* queue; Initiator/Admin see all
  $role = $user['role'] ?? 'Client';
  $username = $user['sub'];

  header('Content-Type: application/json');
  if (in_array($role, ['Initiator','Admin'])) {
    $stmt = $pdo->query("SELECT r.id, r.business_id, b.name AS business, r.username, r.rating, r.text, r.created_at
                         FROM bws2_reviews r JOIN bws2_businesses b ON b.id = r.business_id
                         WHERE r.status='pending' ORDER BY r.created_at ASC LIMIT 200");
    echo json_encode(['items'=>$stmt->fetchAll()]);
    exit;
  } else {
    $stmt = $pdo->prepare("SELECT r.id, r.business_id, b.name AS business, r.username, r.rating, r.text, r.created_at
                           FROM bws2_reviews r
                           JOIN bws2_businesses b ON b.id = r.business_id
                           JOIN bws2_business_owners o ON o.business_id = b.id
                           WHERE r.status='pending' AND o.username=?
                           ORDER BY r.created_at ASC LIMIT 200");
    $stmt->execute([$username]);
    echo json_encode(['items'=>$stmt->fetchAll()]);
    exit;
  }
}

if ($method === 'POST') {
  $user = auth_require(); // signed-in
  $role = $user['role'] ?? 'Client';
  $username = $user['sub'];

  header('Content-Type: application/json');
  $body = json_decode(file_get_contents('php://input'), true);
  $id = intval($body['id'] ?? 0);
  $action = $body['action'] ?? '';
  if ($id <= 0 || !in_array($action, ['approve','reject'])) {
    http_response_code(400); echo json_encode(['ok'=>false, 'error'=>'invalid_input']); exit;
  }

  // Resolve business and reviewer for this review
  $stmt = $pdo->prepare("SELECT business_id, username, rating, text FROM bws2_reviews WHERE id=?");
  $stmt->execute([$id]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$row) { http_response_code(404); echo json_encode(['ok'=>false, 'error'=>'not_found']); exit; }
  $businessId = intval($row['business_id']);
  $reviewer = $row['username'];
  $revRating = intval($row['rating']);
  $revText = $row['text'];

  // Authorization: Initiator/Admin can act on any; Business owners can act on their own businesses
  if (!in_array($role, ['Initiator','Admin'])) {
    $own = $pdo->prepare("SELECT 1 FROM bws2_business_owners WHERE business_id=? AND username=? LIMIT 1");
    $own->execute([$businessId, $username]);
    if (!$own->fetchColumn()) { http_response_code(403); echo json_encode(['ok'=>false, 'error'=>'forbidden']); exit; }
  }

  $newStatus = $action === 'approve' ? 'approved' : 'rejected';
  $upd = $pdo->prepare("UPDATE bws2_reviews SET status=? WHERE id=?");
  $upd->execute([$newStatus, $id]);

  if ($newStatus === 'approved') {
    $agg = $pdo->prepare("UPDATE bws2_businesses b
      JOIN (SELECT business_id, AVG(rating) avg_rating, COUNT(*) cnt FROM bws2_reviews WHERE business_id=? AND status='approved') r
      ON b.id = r.business_id
      SET b.rating_avg = r.avg_rating, b.rating_count = r.cnt
      WHERE b.id = ?");
    $agg->execute([$businessId, $businessId]);
  }

  // Enqueue a reviewer follow-up notification
  try {
    $insN = $pdo->prepare("INSERT INTO bws2_notifications(type, recipient, payload) VALUES (?,?,?)");
    $payload = json_encode([
      'business_id' => $businessId,
      'decision'    => $newStatus,
      'rating'      => $revRating,
      'text'        => $revText
    ], JSON_UNESCAPED_UNICODE);
    $insN->execute(['review_status', $reviewer, $payload]);
  } catch (Throwable $e) { /* ignore */ }

  echo json_encode(['ok'=>true, 'status'=>$newStatus]);
  exit;
}

http_response_code(405); echo 'method_not_allowed';
