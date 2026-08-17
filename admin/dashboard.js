const gold = '#9a7340';
let charts = {};

function fmtDuration(sec) {
  sec = Math.round(Number(sec) || 0);
  const m = Math.floor(sec / 60);
  const s = sec % 60;
  return `${m}m ${String(s).padStart(2, '0')}s`;
}

function listHtml(items, left, right) {
  if (!items.length) return '<p class="note">Sem dados no período.</p>';
  return `<ul class="list">${items.map(i => `<li><b>${left(i)}</b><span>${right(i)}</span></li>`).join('')}</ul>`;
}

function metricCard(title, value, sub) {
  return `<article class="metric"><span>${title}</span><strong>${value}</strong><small>${sub}</small></article>`;
}

function upsertChart(id, type, labels, data) {
  if (charts[id]) charts[id].destroy();
  const ctx = document.getElementById(id);
  charts[id] = new Chart(ctx, {
    type,
    data: {
      labels,
      datasets: [{
        data,
        backgroundColor: type === 'line' ? 'rgba(154,115,64,.18)' : gold,
        borderColor: gold,
        fill: type === 'line',
        tension: .35,
        borderRadius: 6,
      }],
    },
    options: {
      plugins: { legend: { display: false } },
      scales: { x: { grid: { display: false } }, y: { beginAtZero: true } },
      maintainAspectRatio: true,
    },
  });
}

async function load(period) {
  const res = await fetch('stats.php?period=' + period);
  const json = await res.json();
  const d = json.data || {};
  const banner = document.getElementById('banner');
  if (!json.ok) {
    banner.hidden = false;
    banner.textContent = json.error === 'credentials'
      ? 'Para ver dados reais, envie o arquivo credentials.json da conta de serviço do Google para a pasta admin.'
      : 'Não foi possível conectar à API do GA4. Os cartões abaixo ficam zerados até a autenticação.';
  } else {
    banner.hidden = true;
  }

  document.getElementById('metrics').innerHTML = [
    metricCard('Visitantes Únicos', d.users, 'Total de visitas: ' + d.sessions),
    metricCard('Visualizações', d.views, 'Média por visita: ' + d.pagesPerVisit),
    metricCard('Cliques WhatsApp', d.whatsapp, 'Eventos whatsapp_click'),
    metricCard('Tempo Médio de Sessão', fmtDuration(d.avgSession), 'Duração média por sessão'),
    metricCard('Taxa de Rejeição', Number(d.bounce).toFixed(1) + '%', 'Sessões com pouco engajamento'),
    metricCard('Taxa de Conversão', Number(d.conversion).toFixed(1) + '%', 'Cliques WhatsApp / sessões'),
    metricCard('Páginas por Sessão', d.pagesPerSession, 'Média de páginas visitadas'),
    metricCard('Eventos Totais', d.events, 'Interações registradas no GA4'),
  ].join('');

  upsertChart('chartHours', 'bar', [...Array(24).keys()].map(h => String(h).padStart(2,'0') + 'h'), d.hours || []);
  upsertChart('chartWeek', 'bar', d.weekLabels || [], d.week || []);
  upsertChart('chartTime', 'line', (d.timeline || []).map(t => t.date), (d.timeline || []).map(t => t.users));

  document.getElementById('pagesCard').innerHTML = '<h2>Páginas Mais Visitadas</h2>' + listHtml(d.pages || [], i => i.path, i => `${fmtDuration(i.avg)} médio · ${i.views} visualizações`);
  document.getElementById('devicesCard').innerHTML = '<h2>Dispositivos</h2>' + listHtml(d.devices || [], i => i.name, i => `${i.value} · ${i.pct}%`);
  document.getElementById('browsersCard').innerHTML = '<h2>Navegadores</h2>' + listHtml(d.browsers || [], i => i.name, i => i.value);
  document.getElementById('osCard').innerHTML = '<h2>Sistemas Operacionais</h2>' + listHtml(d.os || [], i => i.name, i => i.value);
  document.getElementById('sourceCard').innerHTML = '<h2>Origem do Tráfego</h2>' + listHtml(d.source || [], i => i.name, i => i.value);
  document.getElementById('landingCard').innerHTML = '<h2>Páginas de Entrada</h2>' + listHtml(d.landing || [], i => i.path, i => i.value + ' entradas');
  document.getElementById('exitsCard').innerHTML = '<h2>Páginas Mais Acessadas (saída / rejeição)</h2>' + listHtml(d.exits || [], i => i.path, i => `${i.value} · rejeição ${Number(i.bounce).toFixed(1)}%`);
  document.getElementById('countriesCard').innerHTML = '<h2>Acessos por País</h2>' + listHtml(d.countries || [], i => i.name, i => `${i.sessions} sessões · ${i.views} views`);
  document.getElementById('citiesCard').innerHTML = '<h2>Acessos por Cidade</h2>' + listHtml(d.cities || [], i => `${i.name}<br><small>${i.country}</small>`, i => `${i.sessions} sessões · ${i.views} views`);
}

document.getElementById('period').addEventListener('click', (e) => {
  const btn = e.target.closest('button');
  if (!btn) return;
  document.querySelectorAll('#period button').forEach(b => b.classList.remove('on'));
  btn.classList.add('on');
  load(btn.dataset.p);
});

load('30');
