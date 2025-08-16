import { PSI } from '../../packages/psi-components/component-brain.js';

async function createSession(priceKey) {
  const priceMap = {
    basic: '../packages/payments/stripe/create-checkout-session.php',
    pro:   '../packages/payments/stripe/create-checkout-session.php'
  };
  const priceIdEnv = {
    basic: 'STRIPE_PRICE_BASIC',
    pro: 'STRIPE_PRICE_PRO'
  };
  // This demo posts only the price id string; backend reads defaults from env
  const res = await fetch('../packages/payments/stripe/create-checkout-session.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ price_id: priceKey === 'pro' ? 'price_pro_xxx' : 'price_basic_xxx' })
  });
  if (!res.ok) throw new Error('Checkout session creation failed');
  return await res.json();
}

document.addEventListener('click', async (e) => {
  const btn = e.target.closest('.checkout-btn');
  if (!btn) return;
  const price = btn.dataset.price;
  const status = document.getElementById('sub-status');
  status.textContent = 'Creating checkout session...';
  try {
    const session = await createSession(price);
    const stripe = Stripe('pk_test_xxx'); // Replace with env publishable key
    const { error } = await stripe.redirectToCheckout({ sessionId: session.id });
    if (error) status.textContent = error.message;
  } catch (err) {
    status.textContent = err.message;
  }
});