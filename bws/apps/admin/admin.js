// Simple router + views
const view = document.getElementById('view');
const pageTitle = document.getElementById('page-title');
const userInfo = document.getElementById('user-info');

function setUserFromToken() {
  const token = localStorage.getItem('bws2_token');
  if (!token) { userInfo.textContent = 'Not signed in'; return; }
  try {
    const data = JSON.parse(atob(token));
    userInfo.textContent = `${data.sub} · ${data.role}`;
  } catch (e) {
    userInfo.textContent = 'Session error';
  }
}
setUserFromToken();

async function load(route) {
  document.querySelectorAll('.menu button').forEach(b=>b.classList.toggle('active', b.dataset.route===route));
  pageTitle.textContent = route.charAt(0).toUpperCase()+route.slice(1);
  const res = await fetch(`views/${route}.html`);
  if (!res.ok) { view.innerHTML = '<div class="card"><h3>Not found</h3></div>'; return; }
  view.innerHTML = await res.text();
  if (route === 'dashboard') initDashboard();
  if (route === 'clients') initClients();
  if (route === 'businesses') initBusinesses();
}

document.addEventListener('click', (e) => {
  const btn = e.target.closest('.menu button');
  if (!btn) return;
  load(btn.dataset.route);
});

// -------- Views init ----------
function initDashboard() {
  // KPIs
  const ctx = document.getElementById('tokenChart');
  if (window.Chart && ctx) {
    new Chart(ctx, {
      type: 'line',
      data: { labels: ['Jan','Feb','Mar','Apr','May','Jun'],
              datasets: [{ label: 'Onyx token (mock)', data: [10,14,9,16,18,21] }]},
      options: { responsive:true, maintainAspectRatio:false }
    });
  }
}

function initClients() {
  // Placeholder: later fetch via PHP/AJAX with role-based access
  console.log('Clients view loaded');
}

function initBusinesses() {
  console.log('Businesses view loaded');
}

// Default route
load('dashboard');