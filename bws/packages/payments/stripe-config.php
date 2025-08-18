<?php
// Stripe Config (Test Mode)
require 'vendor/autoload.php'; // stripe-php SDK required

\Stripe\Stripe::setApiKey(getenv('STRIPE_KEY') ?: 'sk_test_xxx');

function create_checkout_session($priceId, $successUrl, $cancelUrl) {
    return \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price' => $priceId,
            'quantity' => 1,
        ]],
        'mode' => 'subscription',
        'success_url' => $successUrl,
        'cancel_url' => $cancelUrl,
    ]);
}
?>