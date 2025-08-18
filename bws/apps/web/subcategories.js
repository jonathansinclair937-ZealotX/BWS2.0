// Dynamic subcategories and sort logic
export function sortBusinesses(businesses, mode='distance') {
  const copy = [...businesses];
  if (mode === 'distance') copy.sort((a,b)=> (a.distance||Infinity) - (b.distance||Infinity));
  else if (mode === 'reviews') copy.sort((a,b)=> (b.rating||0) - (a.rating||0));
  else if (mode === 'ownership') copy.sort((a,b)=> (b.black_ownership_pct||0) - (a.black_ownership_pct||0));
  return copy;
}

export function buildSubcategoryFilters(modes=['distance','reviews','ownership']) {
  const container = document.querySelector('[data-psi="subcategory-filters"]');
  if (!container) return;
  container.innerHTML = modes.map(m => `<button class="filter-btn" data-mode="${m}">${m}</button>`).join('');
}