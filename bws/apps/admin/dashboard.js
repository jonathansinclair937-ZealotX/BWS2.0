import Chart from 'https://cdn.jsdelivr.net/npm/chart.js';

// Token chart demo
const ctx = document.getElementById('tokens-chart');
if (ctx) {
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: ['Jan','Feb','Mar','Apr','May','Jun'],
      datasets: [{ label:'Onyx', data:[10,20,15,25,30,40] }]
    }
  });
}

// Mock ad stats
document.getElementById('ad-views').textContent = 1200;
document.getElementById('ad-clicks').textContent = 300;

// Weather placeholder
document.getElementById('weather-status').textContent = 'Clear skies — system stable';
