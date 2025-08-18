const view = document.getElementById('vc-view');
document.addEventListener('click', (e) => {
  const btn = e.target.closest('.vc-menu button');
  if (!btn) return;
  const dept = btn.dataset.dept;
  loadDept(dept);
});

async function loadDept(dept) {
  const res = await fetch(`depts/${dept}.html`);
  if (!res.ok) { view.innerHTML = '<p>Department not found.</p>'; return; }
  view.innerHTML = await res.text();
}