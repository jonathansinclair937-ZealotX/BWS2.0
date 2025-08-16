// PSI Profile Picker Logic
document.addEventListener('DOMContentLoaded', () => {
  const tiles = document.querySelectorAll('.profile-tile');
  tiles.forEach(tile => {
    tile.addEventListener('click', () => {
      const userId = tile.dataset.userId;
      console.log('Profile selected:', userId);
      // TODO: Hook into BWS SSO session + redirect to directory
    });
  });
});