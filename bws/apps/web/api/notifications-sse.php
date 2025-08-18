<?php
// notifications-sse.php — real-time in-app bell via Server-Sent Events (SSE)
require_once __DIR__ . '/../../../packages/auth/session-middleware.php';
require_once __DIR__ . '/../../../packages/shared/db.php';

// Authenticate from JWT cookie or Authorization header
$user = auth_require();
$username = $user['sub'];

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

$pdo = pdo_mysql();

// Client may pass last known notification id
$last_id = isset($_GET['last_id']) ? intval($_GET['last_id']) : 0;

function sse_send($event, $data, $id = null) {
  if ($id !== null) echo "id: {$id}
";
  echo "event: {$event}
";
  echo "data: " . json_encode($data) . "

";
  @ob_flush(); @flush();
}

$start = time();
$interval = 2;   // seconds between checks
$timeout  = 60;  // seconds to keep connection open (client should reconnect)

// Initial count
$stmt = $pdo->prepare("SELECT COUNT(1) FROM bws2_notifications WHERE recipient=? AND status='queued' AND read_at IS NULL");
$stmt->execute([$username]);
$count = intval($stmt->fetchColumn());
sse_send('count', ['count'=>$count], $last_id);

// Main loop
while (time() - $start < $timeout) {
  // latest notification id for this user (queued or sent)
  $stmt = $pdo->prepare("SELECT MAX(id) FROM bws2_notifications WHERE recipient=?");
  $stmt->execute([$username]);
  $maxId = intval($stmt->fetchColumn());

  if ($maxId > $last_id) {
    // There are new notifications; send count + a compact list of new items
    $stmt = $pdo->prepare("SELECT id, type, payload, created_at FROM bws2_notifications WHERE recipient=? AND id>? ORDER BY id ASC LIMIT 50");
    $stmt->execute([$username, $last_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($items as &$it) {
      $it['payload'] = json_decode($it['payload'], true);
    }

    // Update count
    $stmt = $pdo->prepare("SELECT COUNT(1) FROM bws2_notifications WHERE recipient=? AND status='queued' AND read_at IS NULL");
    $stmt->execute([$username]);
    $count = intval($stmt->fetchColumn());

    sse_send('count', ['count'=>$count], $maxId);
    sse_send('new', ['items'=>$items], $maxId);
    $last_id = $maxId;
  }

  sleep($interval);
}
