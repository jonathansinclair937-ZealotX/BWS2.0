// PSI BaseCard Web Component (swap-in for Listingo card)
class PSIBaseCard extends HTMLElement {
  connectedCallback() {
    const data = this.dataset; // expects data-name, data-logo, data-desc, data-links (JSON)
    this.attachShadow({ mode: 'open' });
    const wrapper = document.createElement('div');
    wrapper.className = 'psi-card';
    wrapper.innerHTML = `
      <div class="img-wrap"><img alt="${data.name||''} logo"></div>
      <div class="meta">
        <div class="title">${data.name||''}</div>
        <div class="desc">${data.desc||''}</div>
      </div>`;
    const img = wrapper.querySelector('img');
    img.src = data.logo || '';
    const style = document.createElement('style');
    style.textContent = `
      :host { display:block; width:180px; }
      .psi-card { background:#1a1a1a; border-radius:12px; overflow:hidden; border:1px solid #2a2a2a; cursor:pointer; }
      .img-wrap { height:120px; background:#111; display:grid; place-items:center; }
      img { max-width:100%; max-height:100%; object-fit:contain; }
      .meta { padding:.5rem .6rem; }
      .title { font-weight:700; font-size:.95rem; }
      .desc { color:#9aa3ad; font-size:.8rem; line-height:1.2; height:2.2em; overflow:hidden; }
    `;
    this.shadowRoot.append(style, wrapper);

    // click behavior: first select, second open
    let selected = false;
    this.addEventListener('click', () => {
      const links = (() => { try { return JSON.parse(this.dataset.links||'[]'); } catch(e) { return []; } })();
      if (!selected) {
        selected = true;
        this.style.outline = '2px solid #6cf';
        const detail = {
          id: this.dataset.id, name: this.dataset.name,
          header: this.dataset.header, description: this.dataset.desc
        };
        document.dispatchEvent(new CustomEvent('psi:tileSelected', { detail }));
      } else if (links.length) {
        window.open(links[0], '_blank');
      }
    });
  }
}
customElements.define('psi-card', PSIBaseCard);
