<?php
require_once __DIR__ . '/../packages/shared/db.php';
require_once __DIR__ . '/../packages/shared/mailer.php';

$pdo = pdo_mysql();

function email_for_username(PDO $pdo, $username) {
  if (strpos($username, '@') !== false) return $username;
  $stmt = $pdo->prepare("SELECT email FROM bws2_users WHERE username=? LIMIT 1");
  $stmt->execute([$username]);
  $email = $stmt->fetchColumn();
  return $email ?: ($username . '@example.com');
}

$stmt = $pdo->prepare("SELECT id, type, recipient, payload FROM bws2_notifications WHERE status='queued' ORDER BY id ASC LIMIT 100");
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($items as $n) {
  $id = $n['id'];
  $type = $n['type'];
  $recipient = $n['recipient'];
  $payload = json_decode($n['payload'], true);

  try {
    if ($type === 'review_pending') {
      $to = email_for_username($pdo, $recipient);
      $subject = "New review pending for your business (BWS)";
      $body = "A new review was submitted.\n"
            . "Business ID: {$payload['business_id']}\n"
            . "Reviewer: {$payload['review_username']}\n"
            . "Rating: {$payload['rating']}\n"
            . "Text: {$payload['text']}\n\n"
            . "Visit the Moderation panel to approve or reject.";
      bws_send_mail($to, $subject, $body);
    }
    } elseif ($type === 'review_status') {
      $to = email_for_username($pdo, $recipient);
      $decision = $payload['decision'] ?? 'updated';
      $subject = "Your review was $decision (BWS)";
      $body = "Your review status changed.\n"
            . "Business ID: {$payload['business_id']}\n"
            . "Decision: {$decision}\n"
            . "Rating: ".($payload['rating'] ?? '')."\n"
            . "Text: ".($payload['text'] ?? '')."\n\n"
            . "Thanks for contributing to the community.";
      bws_send_mail($to, $subject, $body);
    }
    $upd = $pdo->prepare("UPDATE bws2_notifications SET status='sent', sent_at=NOW() WHERE id=?");
    $upd->execute([$id]);
    echo "Sent notification #$id to {$recipient}\n";
  } catch (Throwable $e) {
    $upd = $pdo->prepare("UPDATE bws2_notifications SET status='failed' WHERE id=?");
    $upd->execute([$id]);
    echo "Failed notification #$id: " . $e->getMessage() . "\n";
  }
}



// Handle review_status notifications
// (keeps existing logic intact)
