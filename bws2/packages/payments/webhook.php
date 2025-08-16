<?php
// Stripe Webhook Handler (prototype)
require 'vendor/autoload.php';
\Stripe\Stripe::setApiKey(getenv('STRIPE_KEY') ?: 'sk_test_xxx');

$endpoint_secret = getenv('STRIPE_WEBHOOK_SECRET') ?: 'whsec_xxx';
$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
$event = null;

try {
    $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
} catch(Exception $e) {
    http_response_code(400); exit();
}

switch ($event->type) {
    case 'checkout.session.completed':
        $session = $event->data->object;
        // TODO: Mark user subscription active in DB
        error_log("Subscription success for session " . $session->id);
        break;
    case 'invoice.payment_failed':
        $invoice = $event->data->object;
        // TODO: Handle failed payment
        error_log("Payment failed for invoice " . $invoice->id);
        break;
    default:
        error_log("Unhandled event type " . $event->type);
}
http_response_code(200);
?>