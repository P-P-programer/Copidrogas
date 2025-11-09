import { Chart, BarController, BarElement, CategoryScale, LinearScale, Tooltip, Legend } from 'chart.js';

Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip, Legend);

async function fetchJSON(url) {
  const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
  if (!res.ok) throw new Error(`HTTP ${res.status}`);
  return await res.json();
}

function makeBarChart(ctx, labels, values, color) {
  const isMobile = window.innerWidth < 768;
  
  return new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [{ 
        label: 'Cantidad', 
        data: values, 
        backgroundColor: color,
        borderRadius: isMobile ? 4 : 6, // menos redondeado en móvil
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      animation: {
        duration: isMobile ? 400 : 750, // animación más rápida en móvil
        easing: 'easeOutQuad'
      },
      plugins: { 
        legend: { display: false },
        tooltip: {
          enabled: true,
          mode: 'index',
          intersect: false,
          animation: { duration: 0 } // sin animación en tooltips
        }
      },
      scales: { 
        y: { 
          beginAtZero: true, 
          ticks: { 
            precision: 0,
            maxTicksLimit: isMobile ? 5 : 8, // menos ticks en móvil
            font: { size: isMobile ? 10 : 12 }
          },
          grid: {
            display: !isMobile // sin grid en móvil para aligerar
          }
        },
        x: {
          ticks: {
            font: { size: isMobile ? 10 : 12 },
            maxRotation: isMobile ? 45 : 0,
            minRotation: isMobile ? 45 : 0
          },
          grid: { display: false }
        }
      },
      interaction: {
        mode: 'nearest',
        axis: 'x',
        intersect: false
      }
    }
  });
}

let charts = { top: null, low: null };

async function loadCharts() {
  const loadingMsg = document.createElement('div');
  loadingMsg.textContent = 'Cargando gráficas...';
  loadingMsg.style.cssText = 'text-align:center; padding:1rem; color:#6b7280;';
  document.querySelector('.grid')?.prepend(loadingMsg);

  try {
    const [top, low] = await Promise.all([
      fetchJSON('/analytics/top-products'),
      fetchJSON('/analytics/low-stock'),
    ]);

    loadingMsg.remove();

    const topCtx = document.getElementById('chartTopProducts');
    const lowCtx = document.getElementById('chartLowStock');
    
    if (topCtx) {
      charts.top = makeBarChart(topCtx, top.map(i => i.label), top.map(i => i.value), '#1a4175');
    }
    if (lowCtx) {
      charts.low = makeBarChart(lowCtx, low.map(i => i.label), low.map(i => i.value), '#ef4444');
    }
  } catch (e) {
    console.error('Error al cargar analítica:', e);
    loadingMsg.textContent = 'Error al cargar datos.';
    loadingMsg.style.color = '#dc2626';
  }
}

// Destruir y recrear gráficas al cambiar orientación/tamaño
let resizeTimer;
window.addEventListener('resize', () => {
  clearTimeout(resizeTimer);
  resizeTimer = setTimeout(() => {
    Object.values(charts).forEach(chart => chart?.destroy());
    charts = { top: null, low: null };
    loadCharts();
  }, 500);
});

// Lazy load: solo carga cuando el contenedor es visible
function initLazyCharts() {
  const section = document.querySelector('.analytics-section');
  if (!section) return;

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        loadCharts();
        observer.disconnect();
      }
    });
  }, { rootMargin: '50px' });

  observer.observe(section);
}

document.addEventListener('DOMContentLoaded', () => {
  if ('IntersectionObserver' in window) {
    initLazyCharts();
  } else {
    loadCharts(); // fallback navegadores antiguos
  }
});