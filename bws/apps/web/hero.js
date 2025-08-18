document.addEventListener('psi:tileSelected', (e) => {
  const { name, header, description } = e.detail;
  const heroImg = document.getElementById('hero-image');
  const heroTitle = document.getElementById('hero-title');
  const heroDesc = document.getElementById('hero-desc');
  if (heroImg) heroImg.style.backgroundImage = `url('${header || ''}')`;
  if (heroTitle) heroTitle.textContent = name || 'Selected';
  if (heroDesc) heroDesc.textContent = description || '';
});