<?php
// Stripe webhook handler (prototype)
require_once __DIR__ . '/config.php';

// In production, verify signature header: HTTP_STRIPE_SIGNATURE
$payload = @file_get_contents('php://input');
$event = json_decode($payload, true);

if (!$event || !isset($event['type'])) {
  http_response_code(400); echo 'invalid payload'; exit;
}

$type = $event['type'];
// TODO: persist event and update entitlements
// e.g., customer.subscription.created, .updated, .deleted
http_response_code(200);
echo 'ok';
?>