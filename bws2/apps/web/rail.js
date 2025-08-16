// PSI Rail Logic (enhanced)
let selectedTileId = null;

function emitTileSelected(detail) {
  const evt = new CustomEvent('psi:tileSelected', { detail });
  document.dispatchEvent(evt);
}

function renderTile(tileData) {
  return `<div class="psi-tile" data-business-id="${tileData.id}"
              data-links='${JSON.stringify(tileData.links||[])}'
              data-header="${tileData.header||''}"
              data-name="${tileData.name||''}"
              data-description="${tileData.description||''}">
    <img src="${tileData.logo}" class="tile-logo" alt="${tileData.name} logo">
    <div class="tile-overlay">
      <span class="tile-name">${tileData.name}</span>
      <span class="tile-meta">⭐ ${tileData.rating_avg || 0} · ${tileData.rating_count || 0}</span>
    </div>
  </div>`;
}

export function renderRail(category, tiles) {
  const container = document.querySelector(`[data-category="${category}"] .rail-tiles`);
  if (!container) return;
  container.innerHTML = tiles.map(renderTile).join('');

  container.querySelectorAll('.psi-tile').forEach(tile => {
    tile.addEventListener('click', () => {
      const id = tile.dataset.businessId;
      const links = JSON.parse(tile.dataset.links || "[]");
      const name = tile.dataset.name;
      const header = tile.dataset.header;
      const description = tile.dataset.description;
      if (selectedTileId !== id) {
        selectedTileId = id;
        container.querySelectorAll('.psi-tile.selected').forEach(t=>t.classList.remove('selected'));
        tile.classList.add('selected');
        emitTileSelected({ id, name, header, description });
      } else {
        if (links.length) window.open(links[0], '_blank');
      }
    });
  });
}

export async function loadAndRenderRail(category, { sort='weighted', lat=null, lon=null, limit=50 } = {}) {
  const qs = new URLSearchParams();
  if (sort) qs.set('sort', sort);
  if (lat != null && lon != null) { qs.set('lat', lat); qs.set('lon', lon); }
  if (limit) qs.set('limit', String(limit));
  const res = await fetch(`./api/businesses.php?${qs.toString()}`);
  if (!res.ok) { console.error('Failed to load businesses'); return; }
  const data = await res.json();
  renderRail(category, data.items || []);
}