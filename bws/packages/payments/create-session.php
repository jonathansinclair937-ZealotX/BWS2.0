<?php
require 'stripe-config.php';

$body = json_decode(file_get_contents('php://input'), true);
$priceId = $body['priceId'] ?? null;

if (!$priceId) {
    http_response_code(400); echo json_encode(['error'=>'Missing priceId']); exit;
}

try {
    $session = create_checkout_session($priceId, 'https://example.com/success', 'https://example.com/cancel');
    header('Content-Type: application/json');
    echo json_encode([ 'id' => $session->id ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>