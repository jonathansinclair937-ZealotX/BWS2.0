<?php
require_once __DIR__ . '/../../../packages/auth/session-middleware.php';
require_once __DIR__ . '/../../../packages/shared/db.php';

header('Content-Type: application/json');

$user = auth_require(); 
$username = $user['sub'];
$role = $user['role'] ?? 'Client';

$body = json_decode(file_get_contents('php://input'), true);
$businessId = isset($body['business_id']) ? intval($body['business_id']) : 0;
$rating = isset($body['rating']) ? intval($body['rating']) : 0;
$text = trim($body['text'] ?? '');

if ($businessId <= 0 || $rating < 1 || $rating > 5 || strlen($text) < 5) {
  http_response_code(400);
  echo json_encode(['ok'=>false, 'error'=>'invalid_input']);
  exit;
}

$pdo = pdo_mysql();
// Simple rate limit
$rateStmt = $pdo->prepare("SELECT COUNT(1) FROM bws2_reviews WHERE business_id=? AND username=? AND created_at >= (NOW() - INTERVAL 12 HOUR)");
$rateStmt->execute([$businessId, $username]);
if ($rateStmt->fetchColumn() > 0) {
  http_response_code(429);
  echo json_encode(['ok'=>false, 'error'=>'rate_limited']);
  exit;
}

$status = in_array($role, ['Admin','Initiator']) ? 'approved' : 'pending';
$stmt = $pdo->prepare("INSERT INTO bws2_reviews (business_id, username, rating, text, status) VALUES (?,?,?,?,?)");
$stmt->execute([$businessId, $username, $rating, $text, $status]);

if ($status === 'approved') {
  $upd = $pdo->prepare("UPDATE bws2_businesses b
    JOIN (SELECT business_id, AVG(rating) avg_rating, COUNT(*) cnt FROM bws2_reviews WHERE business_id=? AND status='approved') r
    ON b.id = r.business_id
    SET b.rating_avg = r.avg_rating, b.rating_count = r.cnt
    WHERE b.id = ?");
  $upd->execute([$businessId, $businessId]);
  // Reviewer follow-up (auto-approved)
  try {
    $insN = $pdo->prepare("INSERT INTO bws2_notifications(type, recipient, payload) VALUES (?,?,?)");
    $payload = json_encode(['business_id'=>$businessId,'decision'=>'approved','rating'=>$rating,'text'=>$text], JSON_UNESCAPED_UNICODE);
    $insN->execute(['review_status', $username, $payload]);
  } catch (Throwable $e) {}
}

/** Notify business owners about pending review */
try {
  $own = $pdo->prepare("SELECT username FROM bws2_business_owners WHERE business_id=?");
  $own->execute([$businessId]);
  $owners = $own->fetchAll(PDO::FETCH_COLUMN) ?: [];
  if (!empty($owners) && $status === 'pending') {
    $insN = $pdo->prepare("INSERT INTO bws2_notifications(type, recipient, payload) VALUES (?,?,?)");
    foreach ($owners as $ownerUser) {
      $payload = json_encode([
        'business_id'=>$businessId,
        'review_username'=>$username,
        'rating'=>$rating,
        'text'=>$text
      ], JSON_UNESCAPED_UNICODE);
      $insN->execute(['review_pending', $ownerUser, $payload]);
    }
  }
} catch (Throwable $e) { /* ignore */ }

echo json_encode(['ok'=>true, 'status'=>$status]);
