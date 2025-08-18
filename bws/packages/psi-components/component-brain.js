// Component Brain (Extended with Auth Awareness)
export const PSI = {
  state: {
    size: 'lg',
    status: 'active',
    parent: null,
    user: null,
    token: null,
  },
  setState(updates) {
    this.state = { ...this.state, ...updates };
    console.log('PSI state updated', this.state);
  },
  login(user, token) {
    this.setState({ user, token });
    document.dispatchEvent(new CustomEvent('psi:userLogin', { detail: { user, token } }));
  },
  logout() {
    this.setState({ user: null, token: null });
    document.dispatchEvent(new CustomEvent('psi:userLogout'));
  }
};

// Example: listen for login form success
document.addEventListener('psi:userLogin', (e) => {
  console.log('User logged in:', e.detail.user);
});
