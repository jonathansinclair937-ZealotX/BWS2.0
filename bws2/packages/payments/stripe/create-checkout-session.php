<?php
// Creates a Stripe Checkout Session via REST (cURL).
// In production, prefer stripe-php SDK.

require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$priceId = $input['price_id'] ?? STRIPE_PRICE_BASIC;

$success_url = APP_BASE_URL . SUCCESS_PATH . '?session_id={CHECKOUT_SESSION_ID}';
$cancel_url  = APP_BASE_URL . CANCEL_PATH;

$data = http_build_query([
  'mode' => 'subscription',
  'success_url' => $success_url,
  'cancel_url'  => $cancel_url,
  'line_items[0][price]' => $priceId,
  'line_items[0][quantity]' => 1
]);

$ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  'Authorization: Bearer ' . STRIPE_SECRET_KEY,
  'Content-Type: application/x-www-form-urlencoded'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if ($response === false || $httpcode >= 400) {
  http_response_code(500);
  echo json_encode(['error' => 'stripe_error', 'detail' => curl_error($ch), 'status' => $httpcode]);
  curl_close($ch);
  exit;
}
curl_close($ch);
echo $response;
?>