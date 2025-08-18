export function renderReviews(reviews) {
  const list = document.getElementById('reviews-list');
  if (!list) return;
  list.innerHTML = reviews.map(r => `
    <div class="review">
      <img src="${r.logo}" class="review-logo" alt="${r.business} logo">
      <div class="bubble"><strong>${r.business}</strong>: ${r.text.slice(0,100)}...</div>
      <div class="rating">⭐ ${r.rating}</div>
    </div>`).join('');
}

export async function fetchAndRenderReviews(businessId=null) {
  const qs = new URLSearchParams();
  if (businessId) qs.set('business_id', String(businessId));
  const res = await fetch(`./api/reviews.php?${qs.toString()}`);
  if (!res.ok) { console.error('Failed to load reviews'); return; }
  const data = await res.json();
  renderReviews(data.items || []);
}