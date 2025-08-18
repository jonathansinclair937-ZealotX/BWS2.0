// Weighted search ranking
export function weightedRank(businesses) {
  // Score based on: ownership %, boosted listing, rating, distance, freshness
  return businesses.map(b => {
    let score = 0;
    score += (b.black_ownership_pct || 0) * 2; // heavy weight
    score += (b.boosted ? 50 : 0);
    score += (b.rating || 0) * 10;
    score -= (b.distance || 0) * 2;
    score += (b.freshness_days ? Math.max(0, 30 - b.freshness_days) : 0);
    return { ...b, score };
  }).sort((a,b)=> b.score - a.score);
}