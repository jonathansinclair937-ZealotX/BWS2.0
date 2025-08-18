// notifications-bell.js
let notifOpen = false;
let notifCache = [];

function el(html){ const t=document.createElement('template'); t.innerHTML=html.trim(); return t.content.firstChild; }

export async function refreshBellCount() {
  const token = localStorage.getItem('bws2_token');
  if (!token) return;
  try {
    const res = await fetch('./api/notifications-count.php', { headers: { 'Authorization': 'Bearer '+token }});
    if (!res.ok) return;
    const { count } = await res.json();
    const badge = document.querySelector('.notif-bell .badge');
    if (badge) { badge.textContent = String(count); badge.style.display = count>0 ? 'block':'none'; }
  } catch {}
}

async function fetchList() {
  const token = localStorage.getItem('bws2_token');
  if (!token) return [];
  const res = await fetch('./api/notifications-list.php?limit=50', { headers: { 'Authorization': 'Bearer '+token }});
  if (!res.ok) return [];
  const data = await res.json();
  return data.items || [];
}

function renderPanel(items) {
  const panel = document.querySelector('.notif-panel'); if (!panel) return;
  if (!items.length) {
    panel.innerHTML = '<div class="notif-item">No notifications.</div>';
    return;
  }
  panel.innerHTML = items.map(it => {
    if (it.type === 'review_pending') {
      const p = it.payload || {};
      return `<div class="notif-item" data-id="${it.id}">
        <div><strong>Review pending</strong> — Biz #${p.business_id}, ⭐ ${p.rating} by ${p.review_username}</div>
        <div class="muted">${(p.text||'').slice(0,120)}${(p.text||'').length>120?'…':''}</div>
      </div>`;
    }
    if (it.type === 'review_status') {
      const p = it.payload || {};
      return `<div class="notif-item" data-id="${it.id}">
        <div><strong>Review status</strong> — ${p.decision||'updated'} (Biz #${p.business_id})</div>
        <div class="muted">⭐ ${p.rating||''} ${(p.text||'').slice(0,100)}${(p.text||'').length>100?'…':''}</div>
      </div>`;
    }
    return `<div class="notif-item" data-id="${it.id}"><div>${it.type}</div></div>`;
  }).join('') + `<div class="notif-actions">
    <a class="btn" href="../admin/index.html#moderation" target="_blank">Open Moderation</a>
    <button class="btn" id="mark-read">Mark all read</button>
  </div>`;

  document.getElementById('mark-read')?.addEventListener('click', async () => {
    const ids = items.filter(x => !x.read_at).map(x => x.id);
    if (!ids.length) return;
    const token = localStorage.getItem('bws2_token');
    await fetch('./api/notifications-read.php', {
      method:'POST',
      headers:{ 'Content-Type':'application/json', 'Authorization': 'Bearer '+token },
      body: JSON.stringify({ ids })
    });
    await refreshBellCount();
    notifCache = await fetchList();
    renderPanel(notifCache);
  });
}

function ensureBellUI() {
  const topbar = document.querySelector('.topbar');
  if (!topbar || topbar.querySelector('.notif-bell')) return;

  const bell = el(`<div class="notif-bell" aria-label="Notifications" title="Notifications">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" style="opacity:.85"><path d="M12 24a2.5 2.5 0 0 0 2.45-2h-4.9A2.5 2.5 0 0 0 12 24Zm7-6V11a7 7 0 0 0-5-6.71V3a2 2 0 1 0-4 0v1.29A7 7 0 0 0 5 11v7l-2 2v1h18v-1l-2-2Z"/></svg>
    <span class="badge" style="display:none">0</span>
    <div class="notif-panel"></div>
  </div>`);
  topbar.appendChild(bell);

  bell.addEventListener('click', async (e) => {
    const panel = bell.querySelector('.notif-panel');
    notifOpen = !notifOpen;
    panel.classList.toggle('open', notifOpen);
    if (notifOpen) {
      notifCache = await fetchList();
      renderPanel(notifCache);
    }
  });
}

export function initNotificationsBell() {
  ensureBellUI();
  refreshBellCount();
  setInterval(refreshBellCount, 30000);
}

// Close panel on outside click
document.addEventListener('click', (e) => {
  const bell = document.querySelector('.notif-bell');
  if (!bell) return;
  if (!bell.contains(e.target)) {
    bell.querySelector('.notif-panel')?.classList.remove('open');
    notifOpen = false;
  }
});


let es = null;
let lastEventId = 0;

function startSSE() {
  try {
    es = new EventSource(`./api/notifications-sse.php${lastEventId ? ('?last_id='+lastEventId) : ''}`, { withCredentials: true });
    es.addEventListener('count', (e) => {
      try {
        const data = JSON.parse(e.data);
        const badge = document.querySelector('.notif-bell .badge');
        if (badge) { const c = Number(data.count||0); badge.textContent = String(c); badge.style.display = c>0 ? 'block':'none'; }
        if (e.lastEventId) lastEventId = parseInt(e.lastEventId, 10) || lastEventId;
      } catch {}
    });
    es.addEventListener('new', async (e) => {
      try {
        const data = JSON.parse(e.data);
        if (e.lastEventId) lastEventId = parseInt(e.lastEventId, 10) || lastEventId;
        // If panel is open, refresh it to show the latest
        const panel = document.querySelector('.notif-panel');
        if (panel && panel.classList.contains('open')) {
          const items = await fetchList();
          renderPanel(items);
        }
      } catch {}
    });
    es.onerror = () => {
      // Fallback to polling if SSE errors persist
      if (es) { es.close(); es = null; }
      setTimeout(refreshBellCount, 3000);
    };
  } catch {
    // Browser unsupported → polling
    refreshBellCount();
  }
}

// Start SSE after UI exists
export function initNotificationsBell() {
  ensureBellUI();
  // Try SSE, fallback to initial polling count as backup
  startSSE();
  // Keep a backup poll in case SSE is blocked by proxies
  setInterval(() => { if (!es) refreshBellCount(); }, 30000);
}
