// PSI Login Component
document.getElementById('login-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const username = document.getElementById('username').value;
  const password = document.getElementById('password').value;

  const res = await fetch('../../packages/auth/token-service.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ username, password })
  });

  const statusDiv = document.getElementById('login-status');
  if (res.ok) {
    const data = await res.json();
    localStorage.setItem('bws2_token', data.access_token);
    statusDiv.textContent = 'Login successful!';
    // TODO: Redirect to directory or admin
  } else {
    statusDiv.textContent = 'Login failed';
  }
});